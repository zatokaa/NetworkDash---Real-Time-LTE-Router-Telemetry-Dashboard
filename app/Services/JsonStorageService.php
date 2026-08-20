<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class JsonStorageService
{
    protected string $storagePath;

    public function __construct()
    {
        $this->storagePath = storage_path('app/data');
        $this->ensureStorageDirectoryExists();
        $this->seedDefaultsIfMissing();
    }

    protected function ensureStorageDirectoryExists(): void
    {
        if (! is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0775, true);
        }
    }

    protected function getFilePath(string $collection): string
    {
        return $this->storagePath . '/' . $collection . '.json';
    }

    /**
     * Read collection from JSON file
     */
    public function read(string $collection): array
    {
        $path = $this->getFilePath($collection);
        if (! file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);
        if (empty($content)) {
            return [];
        }

        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Write collection to JSON file with exclusive lock
     */
    public function write(string $collection, array $data): bool
    {
        $this->ensureStorageDirectoryExists();
        $path = $this->getFilePath($collection);
        $payload = array_is_list($data) ? array_values($data) : $data;
        $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return (bool) file_put_contents($path, $encoded, LOCK_EX);
    }

    /**
     * Get next auto-incrementing ID for a collection
     */
    public function nextId(string $collection): int
    {
        $items = $this->read($collection);
        if (empty($items)) {
            return 1;
        }

        $max = 0;
        foreach ($items as $item) {
            if (isset($item['id']) && $item['id'] > $max) {
                $max = (int) $item['id'];
            }
        }

        return $max + 1;
    }

    /**
     * Seed initial default records if files don't exist
     */
    public function seedDefaultsIfMissing(): void
    {
        // 1. Users
        if (! file_exists($this->getFilePath('users')) || empty($this->read('users'))) {
            $this->write('users', [
                [
                    'id' => 1,
                    'name' => 'Administrator',
                    'email' => 'admin@example.com',
                    'password' => Hash::make('admin1234'),
                    'remember_token' => null,
                    'created_at' => now()->toIso8601String(),
                    'updated_at' => now()->toIso8601String(),
                ]
            ]);
        }

        // 2. Routers
        if (! file_exists($this->getFilePath('routers')) || empty($this->read('routers'))) {
            $this->write('routers', [
                [
                    'id' => 1,
                    'user_id' => 1,
                    'name' => 'LTE Gateway Router',
                    'driver' => 'zlt',
                    'model' => 'ZLT P11X',
                    'ip_address' => '192.168.0.1',
                    'username' => 'admin',
                    'password' => '1234',
                    'firmware_version' => '6.4.2.25',
                    'hardware_version' => 'TZ7.821.172',
                    'config_version' => 'V1.0.0',
                    'modem_version' => 'P705A_1.0.0',
                    'build_date' => '2022-11-08',
                    'imei' => '860000000000000',
                    'imsi' => '413000000000000',
                    'iccid' => '89900000000000000000',
                    'mac' => '00:11:22:33:44:55',
                    'wan_ip' => '10.0.0.100',
                    'wan_gateway' => '10.0.0.1',
                    'wan_dns' => '1.1.1.1, 8.8.8.8',
                    'system_uptime' => '2hour(s)15min(s)',
                    'connection_time' => '2 hour(s) 10 min(s)',
                    'load_average' => '0.15, 0.20, 0.18',
                    'mode_status' => 'Connected',
                    'network_mode' => '4G',
                    'cs_status' => 'No Service',
                    'ps_status' => 'Registered, the local network',
                    'eps_status' => 'Registered, the local network',
                    'plmn' => 'Sample LTE Operator',
                    'description' => 'Primary 4G LTE Gateway Router',
                    'status' => 'connected',
                    'is_active' => true,
                    'created_at' => now()->toIso8601String(),
                    'updated_at' => now()->toIso8601String(),
                ]
            ]);
        }

        // 3. Settings
        if (! file_exists($this->getFilePath('settings')) || empty($this->read('settings'))) {
            $this->write('settings', [
                'auto_refresh_interval' => 'off',
                'timezone' => 'Asia/Colombo',
                'bento_order' => [
                    'card_rsrp',
                    'card_rssi',
                    'card_rsrq',
                    'card_sinr',
                    'card_chart',
                    'card_mobile_network',
                    'card_running_status',
                    'card_signal_quality',
                    'card_signal_gauges',
                    'card_lte_connection',
                    'card_cell_tower',
                    'card_device_info',
                    'card_modem_info',
                    'card_interpretation',
                    'card_event_timeline',
                    'card_statistical_aggregates',
                ],
                'thresholds' => [
                    'rsrp' => ['excellent' => -80, 'good' => -90, 'fair' => -100, 'poor' => -110],
                    'rssi' => ['excellent' => -65, 'good' => -75, 'fair' => -85, 'poor' => -95],
                    'rsrq' => ['excellent' => -10, 'good' => -15, 'fair' => -20, 'poor' => -25],
                    'sinr' => ['excellent' => 20, 'good' => 13, 'fair' => 0, 'poor' => -5],
                ]
            ]);
        }

        // 4. Initial Seed Readings
        if (! file_exists($this->getFilePath('readings')) || empty($this->read('readings'))) {
            $samples = [
                ['rsrp' => -92.0, 'rssi' => -66.0, 'rsrq' => -12.0, 'sinr' => 12.0, 'm' => 25],
                ['rsrp' => -90.0, 'rssi' => -64.0, 'rsrq' => -11.0, 'sinr' => 14.0, 'm' => 20],
                ['rsrp' => -88.0, 'rssi' => -62.0, 'rsrq' => -10.0, 'sinr' => 15.0, 'm' => 15],
                ['rsrp' => -87.0, 'rssi' => -60.0, 'rsrq' => -9.0, 'sinr' => 16.0, 'm' => 10],
                ['rsrp' => -86.0, 'rssi' => -59.0, 'rsrq' => -8.0, 'sinr' => 18.0, 'm' => 5],
                ['rsrp' => -85.0, 'rssi' => -58.0, 'rsrq' => -7.0, 'sinr' => 20.0, 'm' => 0],
            ];

            $readings = [];
            $id = 1;
            foreach ($samples as $s) {
                $readings[] = [
                    'id' => $id++,
                    'router_id' => 1,
                    'recorded_at' => now()->subMinutes($s['m'])->toIso8601String(),
                    'rsrp' => (float) $s['rsrp'],
                    'rssi' => (float) $s['rssi'],
                    'rsrq' => (float) $s['rsrq'],
                    'sinr' => (float) $s['sinr'],
                    'band' => 'B40',
                    'bandwidth' => '20 MHz',
                    'earfcn' => 39146,
                    'transmission_mode' => 'TM8',
                    'tx_power' => 23.0,
                    'rrc_state' => 'Connected',
                    'mcs' => 24,
                    'cqi' => 10,
                    'enodeb' => '1001',
                    'cell_id' => '1',
                    'global_cell_id' => 'AA101',
                    'physical_cell_id' => '10',
                    'created_at' => now()->toIso8601String(),
                    'updated_at' => now()->toIso8601String(),
                ];
            }
            $this->write('readings', $readings);
        }

        // 5. Initial Seed Events
        if (! file_exists($this->getFilePath('events')) || empty($this->read('events'))) {
            $this->write('events', [
                [
                    'id' => 1,
                    'router_id' => 1,
                    'event_type' => 'connected',
                    'title' => 'LTE Radio Link Established',
                    'description' => 'Attached to LTE Band 40 carrier network with 20 MHz bandwidth.',
                    'previous_value' => 'Disconnected',
                    'new_value' => 'Connected (B40)',
                    'severity' => 'success',
                    'occurred_at' => now()->subMinutes(30)->toIso8601String(),
                    'created_at' => now()->toIso8601String(),
                    'updated_at' => now()->toIso8601String(),
                ],
                [
                    'id' => 2,
                    'router_id' => 1,
                    'event_type' => 'cell_changed',
                    'title' => 'Cell Sector Locked',
                    'description' => 'Locked to serving cell sector 1 (PCI 10, eNodeB 1001).',
                    'previous_value' => 'Scanning',
                    'new_value' => 'Cell 1 (eNB 1001)',
                    'severity' => 'info',
                    'occurred_at' => now()->subMinutes(25)->toIso8601String(),
                    'created_at' => now()->toIso8601String(),
                    'updated_at' => now()->toIso8601String(),
                ]
            ]);
        }
    }
}
