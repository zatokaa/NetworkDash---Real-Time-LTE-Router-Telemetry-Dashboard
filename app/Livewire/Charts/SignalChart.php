<?php

namespace App\Livewire\Charts;

use App\Models\Router;
use App\Models\SignalReading;
use Livewire\Component;

class SignalChart extends Component
{
    public ?int $routerId = null;
    public string $activeMetric = 'all'; // 'all', 'rsrp', 'rssi', 'rsrq', 'sinr'
    public string $timeframe = '30m'; // '15m', '30m', '1h', '6h', '24h', '7d'

    protected $listeners = [
        'signal-reading-recorded' => '$refresh',
        'router-switched' => 'switchRouter',
    ];

    public function mount(?int $routerId = null)
    {
        if ($routerId) {
            $this->routerId = $routerId;
        } else {
            $active = Router::getActive();
            $this->routerId = $active ? $active->id : null;
        }
    }

    public function switchRouter(int $routerId)
    {
        $this->routerId = $routerId;
    }

    public function setMetric(string $metric)
    {
        $this->activeMetric = $metric;
    }

    public function setTimeframe(string $tf)
    {
        $this->timeframe = $tf;
    }

    public function getChartDataProperty(): array
    {
        if (! $this->routerId) {
            return [
                'labels' => [],
                'values' => [],
                'series' => [
                    'rsrp' => [],
                    'rssi' => [],
                    'rsrq' => [],
                    'sinr' => [],
                ],
                'metric' => $this->activeMetric,
                'count'  => 0,
            ];
        }

        $query = SignalReading::where('router_id', $this->routerId);

        if ($this->timeframe !== 'all') {
            $query->inTimeframe($this->timeframe);
        }

        $readings = $query->orderBy('recorded_at', 'asc')->get();

        if ($readings->isEmpty()) {
            $readings = SignalReading::where('router_id', $this->routerId)->latest('recorded_at')->take(12)->get()->reverse();
        }

        $labels = [];
        $rsrp = [];
        $rssi = [];
        $rsrq = [];
        $sinr = [];
        $tz = config('app.timezone', 'Asia/Colombo');

        foreach ($readings as $r) {
            $localTime = $r->recorded_at->timezone($tz);

            if ($this->timeframe === '7d') {
                $labels[] = $localTime->format('M d H:i');
            } elseif ($this->timeframe === '24h' || $this->timeframe === '6h') {
                $labels[] = $localTime->format('H:i');
            } else {
                $labels[] = $localTime->format('H:i:s');
            }

            $rsrp[] = (float) $r->rsrp;
            $rssi[] = (float) $r->rssi;
            $rsrq[] = (float) $r->rsrq;
            $sinr[] = (float) $r->sinr;
        }

        $values = match($this->activeMetric) {
            'sinr' => $sinr,
            'rsrq' => $rsrq,
            'rssi' => $rssi,
            'rsrp' => $rsrp,
            default => $rsrp,
        };

        return [
            'labels' => $labels,
            'values' => $values,
            'series' => [
                'rsrp' => $rsrp,
                'rssi' => $rssi,
                'rsrq' => $rsrq,
                'sinr' => $sinr,
            ],
            'metric' => $this->activeMetric,
            'count'  => count($labels),
        ];
    }

    public function render()
    {
        return view('livewire.charts.signal-chart', [
            'chartData' => $this->chartData,
        ]);
    }
}
