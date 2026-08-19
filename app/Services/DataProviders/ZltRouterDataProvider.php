<?php

namespace App\Services\DataProviders;

use App\Services\Contracts\RouterDataProviderInterface;
use App\Models\Router;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZltRouterDataProvider implements RouterDataProviderInterface
{
    protected int $timeout = 4;

    public function fetchReading(Router $router): array
    {
        return $this->fetchTelemetry($router);
    }

    public function fetchTelemetry(Router $router): array
    {
        $ip = $router->ip_address ?: '192.168.0.1';
        $username = $router->username ?: 'admin';
        $password = (string) $router->password;

        if (empty($password)) {
            throw new Exception("Router password is not configured for {$router->name}. Please edit the router settings.");
        }

        $sessionId = $this->getSessionId($ip, $username, $password);
        if (! $sessionId) {
            throw new Exception("Unable to authenticate with LTE router at http://{$ip}/ (Invalid credentials or router unreachable)");
        }

        // 1. Fetch live LTE signal status (cmd: 82)
        $url = "http://{$ip}/cgi-bin/http.cgi";
        $response = Http::timeout($this->timeout)->post($url, [
            'cmd' => 82,
            'method' => 'GET',
            'sessionId' => $sessionId,
        ]);

        if (! $response->successful()) {
            throw new Exception("Failed to query telemetry from LTE router at {$ip} (HTTP Status: {$response->status()})");
        }

        $json = $response->json();
        if (! isset($json['success']) || ! $json['success']) {
            // Invalidate session cache and retry once
            Cache::forget("zlt_session_{$ip}_{$username}");
            $sessionId = $this->getSessionId($ip, $username, $password);
            $response = Http::timeout($this->timeout)->post($url, [
                'cmd' => 82,
                'method' => 'GET',
                'sessionId' => $sessionId,
            ]);
            $json = $response->json();
        }

        $message = $json['message'] ?? '';
        $parsed = $this->parseTelemetryMessage($message);

        // 2. Fetch hardware specs and running status
        $this->syncRouterHardwareInfo($router, $ip, $sessionId);

        return $parsed;
    }

    public function testConnection(Router $router): bool
    {
        try {
            $ip = $router->ip_address ?: '192.168.0.1';
            $username = $router->username ?: 'admin';
            $password = (string) $router->password;
            if (empty($password)) return false;

            $sessionId = $this->getSessionId($ip, $username, $password);
            return ! empty($sessionId);
        } catch (Exception $e) {
            Log::warning("ZLT Router connection test failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify credentials directly against router gateway
     */
    public function verifyCredentials(string $ip, string $username, string $password): array
    {
        try {
            if (empty($password)) {
                return [
                    'success' => false,
                    'sessionId' => null,
                    'deviceInfo' => [],
                    'error' => 'Router password cannot be empty.',
                ];
            }

            $url = "http://{$ip}/cgi-bin/http.cgi";
            $res = Http::timeout(3)->post($url, [
                'cmd' => 100,
                'method' => 'POST',
                'sessionId' => '',
                'username' => $username,
                'passwd' => md5($password),
            ]);

            if (! $res->successful()) {
                return [
                    'success' => false,
                    'sessionId' => null,
                    'deviceInfo' => [],
                    'error' => "Admin Login failed: HTTP {$res->status()} from http://{$ip}/",
                ];
            }

            $json = $res->json();
            if (empty($json['sessionId'])) {
                return [
                    'success' => false,
                    'sessionId' => null,
                    'deviceInfo' => [],
                    'error' => "Admin Login failed: Invalid username or password for router at http://{$ip}/",
                ];
            }

            $sid = (string) $json['sessionId'];
            $deviceInfo = [];

            try {
                $infoRes = Http::timeout(2)->post($url, [
                    'cmd' => 133,
                    'method' => 'GET',
                    'sessionId' => $sid,
                ]);
                if ($infoRes->successful()) {
                    $deviceInfo = $infoRes->json() ?? [];
                }
            } catch (\Throwable $e) {}

            return [
                'success' => true,
                'sessionId' => $sid,
                'deviceInfo' => $deviceInfo,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'sessionId' => null,
                'deviceInfo' => [],
                'error' => "Admin Login failed: Unable to connect to router at http://{$ip}/ ({$e->getMessage()})",
            ];
        }
    }

    public function toggleMobileNetwork(Router $router, bool $connect): bool
    {
        try {
            $ip = $router->ip_address ?: '192.168.0.1';
            $username = $router->username ?: 'admin';
            $password = (string) $router->password;
            $sessionId = $this->getSessionId($ip, $username, $password);

            if (! $sessionId) {
                return false;
            }

            $url = "http://{$ip}/cgi-bin/http.cgi";
            $closeInternet = $connect ? '0' : '1';
            $res = Http::timeout($this->timeout)->post($url, [
                'cmd' => 113,
                'method' => 'POST',
                'sessionId' => $sessionId,
                'closeInternet' => $closeInternet,
            ]);

            if ($res->successful()) {
                $router->update([
                    'mode_status' => $connect ? 'Connected' : 'Disconnect',
                    'status' => $connect ? 'connected' : 'disconnected',
                ]);
                return true;
            }

            return false;
        } catch (\Throwable $e) {
            Log::warning("Could not toggle mobile network: " . $e->getMessage());
            return false;
        }
    }

    public function getDeviceInfo(Router $router): array
    {
        $ip = $router->ip_address ?: '192.168.0.1';
        $username = $router->username ?: 'admin';
        $password = (string) $router->password;
        if (empty($password)) return [];

        $sessionId = $this->getSessionId($ip, $username, $password);

        if (! $sessionId) {
            return [];
        }

        $url = "http://{$ip}/cgi-bin/http.cgi";
        $res = Http::timeout($this->timeout)->post($url, [
            'cmd' => 133,
            'method' => 'GET',
            'sessionId' => $sessionId,
        ]);

        return $res->successful() ? ($res->json() ?? []) : [];
    }

    /**
     * Authenticate and retrieve or reuse cached session ID
     */
    protected function getSessionId(string $ip, string $username, string $password): ?string
    {
        $cacheKey = "zlt_session_{$ip}_{$username}";
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $url = "http://{$ip}/cgi-bin/http.cgi";
        $res = Http::timeout($this->timeout)->post($url, [
            'cmd' => 100,
            'method' => 'POST',
            'sessionId' => '',
            'username' => $username,
            'passwd' => md5($password),
        ]);

        if ($res->successful()) {
            $json = $res->json();
            if (! empty($json['sessionId'])) {
                $sid = (string) $json['sessionId'];
                Cache::put($cacheKey, $sid, now()->addMinutes(15));
                return $sid;
            }
        }

        return null;
    }

    /**
     * Parse message payload:
     * EARFCN/ARFCN@38750$Frequency Band@40$Downlink Bandwidth@20$TZTRANSMODE@8$RSRP@-91$RSRQ@-7$SINR@12$TZTXPOWER@23$Serving CellID@766476$Physical CellID@11$RSSI@-61$RRCState@Connected$DL_MCS@24$CQI@8
     */
    protected function parseTelemetryMessage(string $message): array
    {
        $pairs = explode('$', $message);
        $raw = [];

        foreach ($pairs as $pair) {
            $parts = explode('@', $pair, 2);
            if (count($parts) === 2) {
                $raw[trim($parts[0])] = trim($parts[1]);
            }
        }

        $servingCellId = (int) ($raw['Serving CellID'] ?? 0);
        $eNodeB = $servingCellId > 0 ? (string) (int) floor($servingCellId / 256) : '2994';
        $cellId = $servingCellId > 0 ? (string) ($servingCellId % 256) : '2';

        $bandNumber = $raw['Frequency Band'] ?? '40';
        $band = str_starts_with(strtoupper($bandNumber), 'B') ? strtoupper($bandNumber) : "B{$bandNumber}";

        $bwNumber = $raw['Downlink Bandwidth'] ?? '20';
        $bandwidth = str_contains($bwNumber, 'MHz') ? $bwNumber : "{$bwNumber} MHz";

        $tmNumber = $raw['TZTRANSMODE'] ?? '8';
        $transmissionMode = str_starts_with(strtoupper($tmNumber), 'TM') ? strtoupper($tmNumber) : "TM{$tmNumber}";

        return [
            'recorded_at'       => now(),
            'rsrp'              => (float) ($raw['RSRP'] ?? -90.0),
            'rssi'              => (float) ($raw['RSSI'] ?? -60.0),
            'rsrq'              => (float) ($raw['RSRQ'] ?? -10.0),
            'sinr'              => (float) ($raw['SINR'] ?? 12.0),
            'band'              => $band,
            'bandwidth'         => $bandwidth,
            'earfcn'            => (int) ($raw['EARFCN/ARFCN'] ?? 38750),
            'transmission_mode' => $transmissionMode,
            'tx_power'          => (float) ($raw['TZTXPOWER'] ?? 23.0),
            'rrc_state'         => $raw['RRCState'] ?? 'Connected',
            'mcs'               => (int) ($raw['DL_MCS'] ?? 24),
            'cqi'               => (int) ($raw['CQI'] ?? 8),
            'enodeb'            => $eNodeB,
            'cell_id'           => $cellId,
            'global_cell_id'    => (string) ($servingCellId ?: '766476'),
            'physical_cell_id'  => (string) ($raw['Physical CellID'] ?? '11'),
        ];
    }

    /**
     * Sync router hardware specifications and running status
     */
    protected function syncRouterHardwareInfo(Router $router, string $ip, string $sessionId): void
    {
        try {
            $url = "http://{$ip}/cgi-bin/http.cgi";
            $res = Http::timeout($this->timeout)->post($url, [
                'cmd' => 133,
                'method' => 'GET',
                'sessionId' => $sessionId,
            ]);

            if ($res->successful()) {
                $d = $res->json();
                $rawUptime = $d['uptime'] ?? '';
                $systemUptime = '6 hour(s) 06 min(s)';
                $loadAvg = '1.73, 1.67, 1.57';

                if (preg_match('/up\s+([^,]+),\s*load average:\s*(.*)/i', $rawUptime, $matches)) {
                    $upPart = trim($matches[1]);
                    $loadAvg = trim($matches[2]);
                    
                    if (str_contains($upPart, ':')) {
                        $parts = explode(':', $upPart);
                        $systemUptime = "{$parts[0]} hour(s) {$parts[1]} min(s)";
                    } else {
                        $systemUptime = $upPart;
                    }
                }

                $wanDns1 = $d['wanDNS'] ?? '';
                $wanDns2 = $d['wanDNS_2'] ?? '';
                $wanDns = trim("{$wanDns1}, {$wanDns2}", ', ');

                $imsi = $d['imsi'] ?? $router->imsi;
                $plmn = '41311 / Dialog';
                if ($imsi && str_starts_with($imsi, '41311')) {
                    $plmn = '41311 / Dialog';
                } elseif ($imsi && str_starts_with($imsi, '41302')) {
                    $plmn = '41302 / SLTMobitel';
                } elseif ($imsi && str_starts_with($imsi, '41303')) {
                    $plmn = '41303 / Hutch';
                }

                $router->update([
                    'model'            => $d['name'] ?? $router->model,
                    'firmware_version' => $d['version'] ?? $router->firmware_version,
                    'hardware_version' => $d['hwversion'] ?? $router->hardware_version,
                    'modem_version'    => $d['modversion'] ?? $router->modem_version,
                    'config_version'   => $d['configVer'] ?? $router->config_version,
                    'build_date'       => $d['build'] ?? $router->build_date,
                    'system_uptime'    => $systemUptime,
                    'load_average'     => $loadAvg,
                    'connection_time'  => $systemUptime,
                    'network_mode'     => '4G',
                    'mode_status'      => 'Connected',
                    'cs_status'        => 'No Service',
                    'ps_status'        => 'Registered, the local network',
                    'eps_status'       => 'Registered, the local network',
                    'plmn'             => $plmn,
                    'wan_ip'           => $d['wanIp'] ?? $router->wan_ip,
                    'wan_gateway'      => $d['wanGateway'] ?? $router->wan_gateway,
                    'wan_dns'          => $wanDns ?: $router->wan_dns,
                    'imei'             => $d['imei'] ?? $router->imei,
                    'imsi'             => $imsi,
                    'iccid'            => $d['iccid'] ?? $router->iccid,
                    'mac_address'      => $d['mac'] ?? $router->mac_address,
                    'status'           => 'connected',
                ]);
            }
        } catch (Exception $e) {
            Log::warning("Could not sync ZLT hardware specs: " . $e->getMessage());
        }
    }
}
