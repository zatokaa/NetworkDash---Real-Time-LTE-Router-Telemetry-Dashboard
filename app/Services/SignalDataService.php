<?php

namespace App\Services;

use App\Models\ConnectionEvent;
use App\Models\Router;
use App\Models\SignalReading;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class SignalDataService
{
    protected SignalQualityEvaluator $evaluator;

    public function __construct(?SignalQualityEvaluator $evaluator = null)
    {
        $this->evaluator = $evaluator ?? app(SignalQualityEvaluator::class);
    }

    /**
     * Fetch live telemetry directly from router hardware and record it.
     */
    public function fetchAndRecordTelemetry(Router $router): SignalReading
    {
        $provider = match ($router->driver) {
            'manual' => app(\App\Services\DataProviders\ManualRouterDataProvider::class),
            default  => app(\App\Services\DataProviders\ZltRouterDataProvider::class),
        };

        $telemetry = $provider->fetchTelemetry($router);
        return $this->recordReading($router, $telemetry);
    }

    /**
     * Store and process a new signal reading for a router.
     */
    public function recordReading(Router $router, array $data): SignalReading
    {
        $previousReading = $router->signalReadings()->latest('recorded_at')->first();

        // Calculate Overall Quality & Score using configurable evaluator
        $qualityAnalysis = $this->evaluator->calculateOverallScore(
            (float) ($data['rsrp'] ?? -88),
            (float) ($data['rsrq'] ?? -12),
            (float) ($data['sinr'] ?? 14),
            (float) ($data['rssi'] ?? -62)
        );

        $data['router_id'] = $router->id;
        $data['recorded_at'] = isset($data['recorded_at']) ? Carbon::parse($data['recorded_at']) : now();
        $data['overall_quality'] = $qualityAnalysis['rating'];
        $data['signal_score'] = $qualityAnalysis['score'];

        $reading = SignalReading::create($data);

        // Update Router status
        $router->update([
            'status' => strtolower($qualityAnalysis['rating']) === 'poor' ? 'weak' : 'connected',
        ]);

        // Check and log connection events if changes occurred
        if ($previousReading && Schema::hasTable('connection_events')) {
            $this->detectAndLogEvents($router, $previousReading, $reading);
        }

        return $reading;
    }

    /**
     * Compute comprehensive signal quality and health score (0 - 100) via Evaluator
     */
    public function calculateQuality(float $rsrp, float $rsrq, float $sinr, float $rssi): array
    {
        return $this->evaluator->calculateOverallScore($rsrp, $rsrq, $sinr, $rssi);
    }

    /**
     * Detect significant state or radio channel events between readings
     */
    protected function detectAndLogEvents(Router $router, SignalReading $prev, SignalReading $curr): void
    {
        // Band change event
        if ($prev->band !== $curr->band) {
            ConnectionEvent::create([
                'router_id' => $router->id,
                'event_type' => 'band_changed',
                'title' => 'LTE Carrier Band Changed',
                'description' => "Carrier switched from {$prev->band} to {$curr->band}",
                'previous_value' => $prev->band,
                'new_value' => $curr->band,
                'severity' => 'info',
                'occurred_at' => $curr->recorded_at,
            ]);
        }

        // Cell Tower / Sector change
        if ($prev->cell_id !== $curr->cell_id || $prev->enodeb !== $curr->enodeb) {
            ConnectionEvent::create([
                'router_id' => $router->id,
                'event_type' => 'cell_changed',
                'title' => 'Cell Handover / Sector Changed',
                'description' => "Handover from Cell {$prev->cell_id} (eNB {$prev->enodeb}) to Cell {$curr->cell_id} (eNB {$curr->enodeb})",
                'previous_value' => "eNB: {$prev->enodeb}, Cell: {$prev->cell_id}",
                'new_value' => "eNB: {$curr->enodeb}, Cell: {$curr->cell_id}",
                'severity' => 'info',
                'occurred_at' => $curr->recorded_at,
            ]);
        }

        // Signal Quality Degradation
        if ($curr->overall_quality === 'POOR' && $prev->overall_quality !== 'POOR') {
            ConnectionEvent::create([
                'router_id' => $router->id,
                'event_type' => 'signal_weak',
                'title' => 'Signal Quality Degraded to Poor',
                'description' => "RSRP dropped to {$curr->rsrp} dBm and SINR to {$curr->sinr} dB",
                'previous_value' => $prev->overall_quality,
                'new_value' => $curr->overall_quality,
                'severity' => 'warning',
                'occurred_at' => $curr->recorded_at,
            ]);
        }

        // Signal Quality Excellence
        if ($curr->overall_quality === 'EXCELLENT' && $prev->overall_quality !== 'EXCELLENT') {
            ConnectionEvent::create([
                'router_id' => $router->id,
                'event_type' => 'signal_excellent',
                'title' => 'Signal Quality Reached Excellent',
                'description' => "Optimal signal with RSRP {$curr->rsrp} dBm and SINR {$curr->sinr} dB",
                'previous_value' => $prev->overall_quality,
                'new_value' => $curr->overall_quality,
                'severity' => 'success',
                'occurred_at' => $curr->recorded_at,
            ]);
        }
    }

    /**
     * Compute historical aggregate telemetry (Best, Worst, Average)
     */
    public function getStatistics(Router $router, string $timeframe = '30m'): array
    {
        $query = $router->signalReadings()->inTimeframe($timeframe);

        if ($query->count() === 0) {
            $query = $router->signalReadings(); // fallback to all readings if no readings in specific timeframe
        }

        return [
            'rsrp' => [
                'best' => $query->max('rsrp') ?? -88.0,
                'worst' => $query->min('rsrp') ?? -88.0,
                'avg' => round((float) ($query->avg('rsrp') ?? -88.0), 1),
            ],
            'sinr' => [
                'best' => $query->max('sinr') ?? 14.0,
                'worst' => $query->min('sinr') ?? 14.0,
                'avg' => round((float) ($query->avg('sinr') ?? 14.0), 1),
            ],
            'rsrq' => [
                'best' => $query->max('rsrq') ?? -12.0,
                'worst' => $query->min('rsrq') ?? -12.0,
                'avg' => round((float) ($query->avg('rsrq') ?? -12.0), 1),
            ],
            'rssi' => [
                'best' => $query->max('rssi') ?? -62.0,
                'worst' => $query->min('rssi') ?? -62.0,
                'avg' => round((float) ($query->avg('rssi') ?? -62.0), 1),
            ],
            'count' => $query->count(),
        ];
    }
}
