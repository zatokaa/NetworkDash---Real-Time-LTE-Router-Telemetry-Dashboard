<?php

namespace App\Services;

use App\Models\Router;
use App\Services\Contracts\RouterDataProviderInterface;

class ManualRouterDataProvider implements RouterDataProviderInterface
{
    protected array $inputData;

    public function __construct(array $inputData = [])
    {
        $this->inputData = $inputData;
    }

    public function setInputData(array $inputData): self
    {
        $this->inputData = $inputData;
        return $this;
    }

    public function fetchReading(Router $router): array
    {
        return array_merge([
            'router_id' => $router->id,
            'recorded_at' => now(),
            'rsrp' => -88.0,
            'rssi' => -62.0,
            'rsrq' => -12.0,
            'sinr' => 14.0,
            'band' => 'B40',
            'bandwidth' => '20 MHz',
            'earfcn' => 39146,
            'transmission_mode' => 'TM8',
            'tx_power' => 23.0,
            'rrc_state' => 'Connected',
            'mcs' => 24,
            'cqi' => 10,
            'enodeb' => '2994',
            'cell_id' => '2',
            'global_cell_id' => 'BB202',
            'physical_cell_id' => '11',
        ], $this->inputData);
    }
}
