<?php

namespace App\Livewire\Signal;

use App\Models\Router;
use App\Services\SignalDataService;
use Livewire\Component;

class SignalEntryModal extends Component
{
    public bool $isOpen = false;
    public ?int $router_id = null;

    // Signal Metrics
    public float $rsrp = -88.0;
    public float $rssi = -62.0;
    public float $rsrq = -12.0;
    public float $sinr = 14.0;

    // Carrier Parameters
    public string $band = 'B40';
    public string $bandwidth = '20 MHz';
    public int $earfcn = 39146;
    public string $transmission_mode = 'TM8';
    public float $tx_power = 23.0;
    public string $rrc_state = 'Connected';
    public int $mcs = 24;
    public int $cqi = 10;

    // Cell Identifiers
    public string $enodeb = '2994';
    public string $cell_id = '2';
    public string $global_cell_id = 'BB202';
    public string $physical_cell_id = '11';

    protected $listeners = [
        'open-signal-entry-modal' => 'open',
    ];

    protected function rules(): array
    {
        return [
            'router_id' => 'required|exists:routers,id',
            'rsrp' => 'required|numeric|between:-140,-40',
            'rssi' => 'required|numeric|between:-120,-30',
            'rsrq' => 'required|numeric|between:-30,0',
            'sinr' => 'required|numeric|between:-20,40',
            'band' => 'required|string|max:20',
            'bandwidth' => 'required|string|max:20',
            'earfcn' => 'required|integer|min:0',
            'transmission_mode' => 'required|string|max:20',
            'tx_power' => 'required|numeric|between:-10,40',
            'rrc_state' => 'required|string|max:30',
            'mcs' => 'required|integer|between:0,31',
            'cqi' => 'required|integer|between:1,15',
            'enodeb' => 'required|string|max:50',
            'cell_id' => 'required|string|max:50',
            'global_cell_id' => 'required|string|max:50',
            'physical_cell_id' => 'required|string|max:50',
        ];
    }

    public function mount(?int $routerId = null)
    {
        if ($routerId) {
            $this->router_id = $routerId;
        } else {
            $active = Router::getActive();
            $this->router_id = $active ? $active->id : null;
        }
    }

    public function open(?int $routerId = null)
    {
        if ($routerId) {
            $this->router_id = $routerId;
        } elseif (! $this->router_id) {
            $active = Router::getActive();
            $this->router_id = $active ? $active->id : null;
        }

        $this->isOpen = true;
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function applyPreset(string $preset)
    {
        match ($preset) {
            'optimal' => [
                $this->rsrp = -78.0,
                $this->rssi = -55.0,
                $this->rsrq = -7.0,
                $this->sinr = 24.0,
                $this->band = 'B40',
                $this->bandwidth = '20 MHz',
                $this->earfcn = 39146,
                $this->mcs = 28,
                $this->cqi = 14,
            ],
            'good' => [
                $this->rsrp = -88.0,
                $this->rssi = -62.0,
                $this->rsrq = -12.0,
                $this->sinr = 14.0,
                $this->band = 'B40',
                $this->bandwidth = '20 MHz',
                $this->earfcn = 39146,
                $this->mcs = 24,
                $this->cqi = 10,
            ],
            'interference' => [
                $this->rsrp = -82.0, // Strong signal
                $this->rssi = -58.0,
                $this->rsrq = -17.0, // Poor quality
                $this->sinr = 2.0,   // Severe noise / interference
                $this->band = 'B3',
                $this->bandwidth = '15 MHz',
                $this->earfcn = 1650,
                $this->mcs = 12,
                $this->cqi = 5,
            ],
            'weak' => [
                $this->rsrp = -114.0,
                $this->rssi = -88.0,
                $this->rsrq = -18.0,
                $this->sinr = -4.0,
                $this->band = 'B20',
                $this->bandwidth = '10 MHz',
                $this->earfcn = 6300,
                $this->mcs = 6,
                $this->cqi = 3,
            ],
            default => null,
        };
    }

    public function save(SignalDataService $service)
    {
        $validated = $this->validate();

        $router = Router::findOrFail($validated['router_id']);
        $service->recordReading($router, $validated);

        $this->dispatch('signal-reading-recorded', routerId: $router->id);
        $this->dispatch('notify', message: 'Signal telemetry recorded successfully!', type: 'success');

        $this->isOpen = false;
    }

    public function render()
    {
        return view('livewire.signal.signal-entry-modal', [
            'routers' => Router::all(),
        ]);
    }
}
