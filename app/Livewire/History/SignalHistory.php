<?php

namespace App\Livewire\History;

use App\Models\Router;
use App\Models\SignalReading;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SignalHistory extends Component
{
    use WithPagination;

    public ?int $selectedRouterId = null;
    public string $timeframe = 'all'; // '15m', '30m', '1h', '6h', '24h', '7d', 'all'
    public string $qualityFilter = 'all'; // 'all', 'EXCELLENT', 'VERY GOOD', 'GOOD', 'FAIR', 'POOR'
    public string $search = '';
    public string $sortBy = 'recorded_at';
    public string $sortDirection = 'desc';
    public int $perPage = 15;

    protected $queryString = [
        'selectedRouterId' => ['except' => null],
        'timeframe' => ['except' => 'all'],
        'qualityFilter' => ['except' => 'all'],
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        if (! $this->selectedRouterId) {
            $active = Router::getActive();
            $this->selectedRouterId = $active ? $active->id : null;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTimeframe()
    {
        $this->resetPage();
    }

    public function updatingQualityFilter()
    {
        $this->resetPage();
    }

    public function updatingSelectedRouterId()
    {
        $this->resetPage();
    }

    public function sortByField(string $field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function deleteReading(int $id)
    {
        $reading = SignalReading::findOrFail($id);
        $reading->delete();
        $this->dispatch('notify', message: 'Reading record deleted.', type: 'info');
    }

    public function clearHistory()
    {
        if ($this->selectedRouterId) {
            SignalReading::where('router_id', $this->selectedRouterId)->delete();
            $this->dispatch('notify', message: 'All telemetry history cleared for selected router.', type: 'warning');
        }
    }

    public function exportCsv(): StreamedResponse
    {
        $router = Router::find($this->selectedRouterId);
        $routerName = $router ? str_replace(' ', '_', strtolower($router->name)) : 'all_routers';
        $filename = "telemetry_{$routerName}_" . now()->format('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');

            // CSV Header
            fputcsv($handle, [
                'Timestamp',
                'Router',
                'RSRP (dBm)',
                'RSSI (dBm)',
                'RSRQ (dB)',
                'SINR (dB)',
                'Overall Quality',
                'Signal Score',
                'Band',
                'Bandwidth',
                'EARFCN',
                'eNodeB',
                'Cell ID',
                'Global Cell ID',
                'Physical Cell ID',
                'TX Power (dBm)',
                'RRC State',
                'MCS',
                'CQI',
            ]);

            $query = SignalReading::with('router');
            if ($this->selectedRouterId) {
                $query->where('router_id', $this->selectedRouterId);
            }

            $query->orderBy('recorded_at', 'desc')->chunk(200, function ($readings) use ($handle) {
                foreach ($readings as $r) {
                    fputcsv($handle, [
                        $r->recorded_at->format('Y-m-d H:i:s'),
                        $r->router->name ?? 'Unknown',
                        $r->rsrp,
                        $r->rssi,
                        $r->rsrq,
                        $r->sinr,
                        $r->overall_quality,
                        $r->signal_score,
                        $r->band,
                        $r->bandwidth,
                        $r->earfcn,
                        $r->enodeb,
                        $r->cell_id,
                        $r->global_cell_id,
                        $r->physical_cell_id,
                        $r->tx_power,
                        $r->rrc_state,
                        $r->mcs,
                        $r->cqi,
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        $query = SignalReading::with('router');

        if ($this->selectedRouterId) {
            $query->where('router_id', $this->selectedRouterId);
        }

        if ($this->timeframe !== 'all') {
            $query->inTimeframe($this->timeframe);
        }

        if ($this->qualityFilter !== 'all') {
            $query->where('overall_quality', $this->qualityFilter);
        }

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('cell_id', 'like', "%{$this->search}%")
                  ->orWhere('enodeb', 'like', "%{$this->search}%")
                  ->orWhere('global_cell_id', 'like', "%{$this->search}%")
                  ->orWhere('physical_cell_id', 'like', "%{$this->search}%")
                  ->orWhere('band', 'like', "%{$this->search}%")
                  ->orWhere('earfcn', 'like', "%{$this->search}%");
            });
        }

        $readings = $query->orderBy($this->sortBy, $this->sortDirection)->paginate($this->perPage);

        return view('livewire.history.signal-history', [
            'readings' => $readings,
            'routers' => Router::all(),
            'totalCount' => SignalReading::when($this->selectedRouterId, fn($q) => $q->where('router_id', $this->selectedRouterId))->count(),
        ])->layout('layouts.app', ['title' => 'Historical Signal Telemetry']);
    }
}
