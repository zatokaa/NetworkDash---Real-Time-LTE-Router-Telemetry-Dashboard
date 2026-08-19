<?php

namespace App\Livewire\Dashboard;

use App\Models\Router;
use App\Models\SignalReading;
use App\Services\DataProviders\ZltRouterDataProvider;
use App\Services\SignalDataService;
use Livewire\Component;

class MainDashboard extends Component
{
    public ?int $selectedRouterId = null;
    public string $autoRefreshInterval = 'off'; // 'off', '10s', '30s', '1m', '5m'
    public string $timeframe = '30m'; // '15m', '30m', '1h', '6h', '24h', '7d'
    public bool $isCustomizing = false;

    public array $defaultOrder = [
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
    ];

    public array $bentoOrder = [];

    protected $listeners = [
        'signal-reading-recorded' => '$refresh',
        'router-switched' => 'selectRouter',
    ];

    public function mount()
    {
        $active = Router::getActive();
        $this->selectedRouterId = $active ? $active->id : null;
        $this->autoRefreshInterval = session('auto_refresh_interval', 'off');

        $savedOrder = session('bento_dashboard_order');
        if (is_array($savedOrder) && !empty($savedOrder) && str_starts_with($savedOrder[0] ?? '', 'card_')) {
            // Ensure any newly added cards are included
            $missing = array_diff($this->defaultOrder, $savedOrder);
            $this->bentoOrder = array_merge($savedOrder, array_values($missing));
        } else {
            $this->bentoOrder = $this->defaultOrder;
        }
    }

    public function selectRouter(int $routerId)
    {
        $this->selectedRouterId = $routerId;
        $router = Router::find($routerId);
        if ($router) {
            $router->setActive();
            $this->dispatch('notify', message: "Switched active view to {$router->name}", type: 'success');
        }
    }

    public function toggleMobileNetwork(ZltRouterDataProvider $zltProvider, SignalDataService $signalService)
    {
        $active = Router::find($this->selectedRouterId) ?? Router::getActive();
        if (! $active) {
            $this->dispatch('notify', message: 'No router configured.', type: 'error');
            return;
        }

        $isCurrentlyConnected = ($active->status === 'connected' && ($active->mode_status ?? 'Connected') === 'Connected');
        $targetAction = ! $isCurrentlyConnected; // true = connect, false = disconnect

        if ($active->driver === 'zlt') {
            $success = $zltProvider->toggleMobileNetwork($active, $targetAction);
            if ($success) {
                $statusText = $targetAction ? 'Connected' : 'Disconnected';
                $this->dispatch('notify', message: "Mobile network WAN {$statusText} successfully!", type: $targetAction ? 'success' : 'warning');
                if ($targetAction) {
                    $signalService->fetchAndRecordTelemetry($active);
                }
                $this->dispatch('signal-reading-recorded');
            } else {
                $this->dispatch('notify', message: 'Failed to toggle mobile network. Check router connection.', type: 'error');
            }
        } else {
            $active->update([
                'status' => $targetAction ? 'connected' : 'disconnected',
                'mode_status' => $targetAction ? 'Connected' : 'Disconnect'
            ]);
            $this->dispatch('notify', message: "Status switched to " . ($targetAction ? 'Connected' : 'Disconnected'), type: 'info');
        }
    }

    public function toggleCustomize()
    {
        $this->isCustomizing = ! $this->isCustomizing;
    }

    public function updateBentoOrder(array $newOrder)
    {
        $this->bentoOrder = $newOrder;
        session(['bento_dashboard_order' => $newOrder]);
        $this->dispatch('notify', message: 'Dashboard card layout customized and saved!', type: 'success');
    }

    public function resetBentoOrder()
    {
        $this->bentoOrder = $this->defaultOrder;
        session(['bento_dashboard_order' => $this->defaultOrder]);
        $this->dispatch('notify', message: 'Dashboard layout reset to default order!', type: 'info');
    }

    public function setAutoRefresh(string $interval)
    {
        $this->autoRefreshInterval = $interval;
        session(['auto_refresh_interval' => $interval]);
        $label = $interval === 'off' ? 'Disabled' : $interval;
        $this->dispatch('notify', message: "Auto-refresh set to: {$label}", type: 'info');
    }

    public function autoPoll(SignalDataService $signalService)
    {
        if ($this->autoRefreshInterval === 'off') {
            return;
        }

        $active = Router::find($this->selectedRouterId) ?? Router::getActive();
        if ($active && $active->driver !== 'manual') {
            try {
                $signalService->fetchAndRecordTelemetry($active);
                $this->dispatch('signal-reading-recorded');
            } catch (\Exception $e) {
                // If router unreachable, mark as disconnected
                if ($active->status !== 'disconnected') {
                    $active->update(['status' => 'disconnected']);
                }
            }
        }
    }

    public function refreshData(SignalDataService $signalService)
    {
        $active = Router::find($this->selectedRouterId) ?? Router::getActive();
        if ($active && $active->driver !== 'manual') {
            try {
                $reading = $signalService->fetchAndRecordTelemetry($active);
                $this->dispatch('notify', message: "Fetched live telemetry from {$active->name} ({$active->ip_address})", type: 'success');
                $this->dispatch('signal-reading-recorded');
                return;
            } catch (\Exception $e) {
                if ($active->status !== 'disconnected') {
                    $active->update(['status' => 'disconnected']);
                }
                $this->dispatch('notify', message: "Live query notice: " . $e->getMessage(), type: 'warning');
                return;
            }
        }

        $this->dispatch('notify', message: 'Telemetry updated with latest readings', type: 'info');
    }

    public function openSignalEntryModal()
    {
        $this->dispatch('open-signal-entry-modal', routerId: $this->selectedRouterId);
    }

    /**
     * Generate plain-English signal interpretation
     */
    public function getSignalInterpretation(?SignalReading $reading, ?Router $router = null): array
    {
        if (! $reading || ! $router || $router->status === 'disconnected') {
            return [
                'headline' => 'No Router Connected / Offline',
                'explanation' => 'No active router is connected or reachable. Check router power, ethernet/Wi-Fi connection, or gateway IP address.',
                'interference_status' => 'No Signal',
                'overall_health' => 'Disconnected',
            ];
        }

        $rsrp = $reading->rsrp;
        $sinr = $reading->sinr;
        $rsrq = $reading->rsrq;

        $hasInterference = ($sinr < 5.0 && $rsrp >= -95.0);
        $isWeak = ($rsrp < -105.0);
        $isClean = ($sinr >= 13.0);

        if ($hasInterference) {
            $headline = 'High RF Noise / Interference Detected';
            $explanation = 'Signal strength is strong enough, but high radio interference/noise is impacting data throughput. Try adjusting the router orientation or switching to a clearer band.';
            $interf = 'High Noise Present';
            $health = 'Degraded by RF interference.';
        } elseif ($isWeak) {
            $headline = 'Weak LTE Signal Coverage';
            $explanation = 'Router is located far from the serving cell tower. Moving the router near a window or using an external LTE antenna is recommended.';
            $interf = 'Low Signal Level';
            $health = 'Weak carrier link.';
        } elseif ($isClean) {
            $headline = 'Excellent & Clean LTE Link';
            $explanation = 'Signal strength is solid with minimal radio noise. Radio conditions are optimal for low latency gaming and max downlink throughput.';
            $interf = 'Minimal / Clean';
            $health = 'Optimal LTE connection.';
        } else {
            $headline = 'Stable LTE Connection';
            $explanation = 'LTE radio metrics are in a normal operational range. General browsing and HD streaming will perform reliably.';
            $interf = 'Moderate / Acceptable';
            $health = 'Healthy connection.';
        }

        return [
            'headline' => $headline,
            'explanation' => $explanation,
            'interference_status' => $interf,
            'overall_health' => $health,
        ];
    }

    /**
     * Compute delta vs previous reading
     */
    public function getDelta(?SignalReading $current, ?SignalReading $previous, string $metric): ?array
    {
        if (! $current || ! $previous) {
            return null;
        }

        $currVal = (float) $current->$metric;
        $prevVal = (float) $previous->$metric;
        $diff = round($currVal - $prevVal, 1);

        if ($diff > 0) {
            return ['text' => "+{$diff}", 'direction' => 'up'];
        } elseif ($diff < 0) {
            return ['text' => "{$diff}", 'direction' => 'down'];
        } else {
            return ['text' => '0', 'direction' => 'neutral'];
        }
    }

    public function render(SignalDataService $signalService)
    {
        $routers = Router::all();
        $activeRouter = Router::find($this->selectedRouterId) ?? Router::getActive();

        $latestReading = null;
        $previousReading = null;
        $statistics = null;
        $recentReadings = collect();

        $isConnected = ($activeRouter && $activeRouter->status !== 'disconnected');

        if ($isConnected) {
            $readings = $activeRouter->signalReadings()->latest('recorded_at')->take(2)->get();
            $latestReading = $readings->first();
            $previousReading = $readings->count() > 1 ? $readings->get(1) : null;
            if ($latestReading) {
                $statistics = $signalService->getStatistics($activeRouter, $this->timeframe);
                $recentReadings = $activeRouter->signalReadings()->latest('recorded_at')->take(6)->get();
            }
        }

        $interpretation = $this->getSignalInterpretation($latestReading, $activeRouter);

        return view('livewire.dashboard.main-dashboard', [
            'routers' => $routers,
            'activeRouter' => $activeRouter,
            'isConnected' => $isConnected && $latestReading !== null,
            'latest' => $latestReading,
            'previous' => $previousReading,
            'statistics' => $statistics,
            'interpretation' => $interpretation,
            'recentReadings' => $recentReadings,
        ])->layout('layouts.app', ['title' => 'Signal Dashboard']);
    }
}
