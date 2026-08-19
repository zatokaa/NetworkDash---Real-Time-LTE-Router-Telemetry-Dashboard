<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Livewire\Component;

class SettingsManager extends Component
{
    public string $activeTab = 'thresholds'; // 'thresholds', 'weights', 'security'

    // RSRP Thresholds
    public float $rsrp_excellent = -80.0;
    public float $rsrp_very_good = -90.0;
    public float $rsrp_good = -100.0;
    public float $rsrp_fair = -110.0;

    // SINR Thresholds
    public float $sinr_excellent = 20.0;
    public float $sinr_good = 13.0;
    public float $sinr_fair = 0.0;

    // RSRQ Thresholds
    public float $rsrq_excellent = -8.0;
    public float $rsrq_good = -12.0;
    public float $rsrq_fair = -16.0;

    // RSSI Thresholds
    public float $rssi_excellent = -65.0;
    public float $rssi_good = -75.0;
    public float $rssi_fair = -85.0;

    // Health Score Weights (%)
    public int $weight_rsrp = 40;
    public int $weight_sinr = 35;
    public int $weight_rsrq = 25;

    // Security & Privacy Settings
    public bool $reveal_sensitive_ids = false;

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        $configThresholds = config('signal.thresholds', []);
        $savedThresholds = Setting::get('signal_thresholds', $configThresholds);

        $this->rsrp_excellent = (float) ($savedThresholds['rsrp']['excellent'] ?? -80.0);
        $this->rsrp_very_good = (float) ($savedThresholds['rsrp']['very_good'] ?? -90.0);
        $this->rsrp_good      = (float) ($savedThresholds['rsrp']['good'] ?? -100.0);
        $this->rsrp_fair      = (float) ($savedThresholds['rsrp']['fair'] ?? -110.0);

        $this->sinr_excellent = (float) ($savedThresholds['sinr']['excellent'] ?? 20.0);
        $this->sinr_good      = (float) ($savedThresholds['sinr']['good'] ?? 13.0);
        $this->sinr_fair      = (float) ($savedThresholds['sinr']['fair'] ?? 0.0);

        $this->rsrq_excellent = (float) ($savedThresholds['rsrq']['excellent'] ?? -8.0);
        $this->rsrq_good      = (float) ($savedThresholds['rsrq']['good'] ?? -12.0);
        $this->rsrq_fair      = (float) ($savedThresholds['rsrq']['fair'] ?? -16.0);

        $this->rssi_excellent = (float) ($savedThresholds['rssi']['excellent'] ?? -65.0);
        $this->rssi_good      = (float) ($savedThresholds['rssi']['good'] ?? -75.0);
        $this->rssi_fair      = (float) ($savedThresholds['rssi']['fair'] ?? -85.0);

        $savedWeights = Setting::get('signal_weights', config('signal.weights', []));
        $this->weight_rsrp = (int) round(($savedWeights['rsrp'] ?? 0.40) * 100);
        $this->weight_sinr = (int) round(($savedWeights['sinr'] ?? 0.35) * 100);
        $this->weight_rsrq = (int) round(($savedWeights['rsrq'] ?? 0.25) * 100);

        $this->reveal_sensitive_ids = (bool) Setting::get('reveal_sensitive_ids', false);
    }

    public function saveThresholds()
    {
        $this->validate([
            'rsrp_excellent' => 'required|numeric',
            'rsrp_very_good' => 'required|numeric|lt:rsrp_excellent',
            'rsrp_good'      => 'required|numeric|lt:rsrp_very_good',
            'rsrp_fair'      => 'required|numeric|lt:rsrp_good',
            'sinr_excellent' => 'required|numeric',
            'sinr_good'      => 'required|numeric|lt:sinr_excellent',
            'sinr_fair'      => 'required|numeric|lt:sinr_good',
            'rsrq_excellent' => 'required|numeric',
            'rsrq_good'      => 'required|numeric|lt:rsrq_excellent',
            'rsrq_fair'      => 'required|numeric|lt:rsrq_good',
            'rssi_excellent' => 'required|numeric',
            'rssi_good'      => 'required|numeric|lt:rssi_excellent',
            'rssi_fair'      => 'required|numeric|lt:rssi_good',
        ]);

        $thresholds = [
            'rsrp' => [
                'excellent' => $this->rsrp_excellent,
                'very_good' => $this->rsrp_very_good,
                'good'      => $this->rsrp_good,
                'fair'      => $this->rsrp_fair,
                'gauge_min' => -125.0,
                'gauge_max' => -60.0,
            ],
            'sinr' => [
                'excellent' => $this->sinr_excellent,
                'good'      => $this->sinr_good,
                'fair'      => $this->sinr_fair,
                'gauge_min' => -5.0,
                'gauge_max' => 30.0,
            ],
            'rsrq' => [
                'excellent' => $this->rsrq_excellent,
                'good'      => $this->rsrq_good,
                'fair'      => $this->rsrq_fair,
                'gauge_min' => -20.0,
                'gauge_max' => -3.0,
            ],
            'rssi' => [
                'excellent' => $this->rssi_excellent,
                'good'      => $this->rssi_good,
                'fair'      => $this->rssi_fair,
                'gauge_min' => -105.0,
                'gauge_max' => -50.0,
            ],
        ];

        Setting::set('signal_thresholds', $thresholds);
        $this->dispatch('notify', message: 'Signal quality thresholds saved successfully!', type: 'success');
    }

    public function saveWeights()
    {
        $sum = $this->weight_rsrp + $this->weight_sinr + $this->weight_rsrq;
        if ($sum !== 100) {
            $this->dispatch('notify', message: "Total weights must equal 100%. Current sum: {$sum}%", type: 'error');
            return;
        }

        $weights = [
            'rsrp' => $this->weight_rsrp / 100,
            'sinr' => $this->weight_sinr / 100,
            'rsrq' => $this->weight_rsrq / 100,
        ];

        Setting::set('signal_weights', $weights);
        $this->dispatch('notify', message: 'Scoring weights updated successfully!', type: 'success');
    }

    public function saveSecurity()
    {
        Setting::set('reveal_sensitive_ids', $this->reveal_sensitive_ids);
        $msg = $this->reveal_sensitive_ids ? 'Sensitive identifiers unmasked.' : 'Sensitive identifiers securely masked.';
        $this->dispatch('notify', message: $msg, type: 'info');
    }

    public function resetToDefaults()
    {
        Setting::whereIn('key', ['signal_thresholds', 'signal_weights', 'reveal_sensitive_ids'])->delete();
        $this->loadSettings();
        $this->dispatch('notify', message: 'All threshold and scoring settings reset to factory defaults.', type: 'info');
    }

    public function render()
    {
        return view('livewire.settings.settings-manager')
            ->layout('layouts.app', ['title' => 'Signal & Threshold Settings']);
    }
}
