<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignalReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'router_id',
        'recorded_at',
        'rsrp',
        'rssi',
        'rsrq',
        'sinr',
        'band',
        'bandwidth',
        'earfcn',
        'transmission_mode',
        'tx_power',
        'rrc_state',
        'mcs',
        'cqi',
        'enodeb',
        'cell_id',
        'global_cell_id',
        'physical_cell_id',
        'overall_quality',
        'signal_score',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'rsrp' => 'float',
        'rssi' => 'float',
        'rsrq' => 'float',
        'sinr' => 'float',
        'earfcn' => 'integer',
        'tx_power' => 'float',
        'mcs' => 'integer',
        'cqi' => 'integer',
        'signal_score' => 'integer',
    ];

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function scopeForRouter($query, int $routerId)
    {
        return $query->where('router_id', $routerId);
    }

    public function scopeInTimeframe($query, string $range = '30m')
    {
        $since = match($range) {
            '15m' => now()->subMinutes(15),
            '30m' => now()->subMinutes(30),
            '1h'  => now()->subHour(),
            '6h'  => now()->subHours(6),
            '24h' => now()->subHours(24),
            '7d'  => now()->subDays(7),
            default => now()->subMinutes(30),
        };

        return $query->where('recorded_at', '>=', $since);
    }

    /**
     * Rating for RSRP based on configurable thresholds
     */
    public function getRsrpQualityAttribute(): string
    {
        return app(\App\Services\SignalQualityEvaluator::class)->evaluateRsrp((float) $this->rsrp);
    }

    /**
     * Rating for RSRQ based on configurable thresholds
     */
    public function getRsrqQualityAttribute(): string
    {
        return app(\App\Services\SignalQualityEvaluator::class)->evaluateRsrq((float) $this->rsrq);
    }

    /**
     * Rating for SINR based on configurable thresholds
     */
    public function getSinrQualityAttribute(): string
    {
        return app(\App\Services\SignalQualityEvaluator::class)->evaluateSinr((float) $this->sinr);
    }

    /**
     * Rating for RSSI based on configurable thresholds
     */
    public function getRssiQualityAttribute(): string
    {
        return app(\App\Services\SignalQualityEvaluator::class)->evaluateRssi((float) $this->rssi);
    }

    /**
     * Calculate Gauge Percentage for RSRP
     */
    public function getRsrpPercentageAttribute(): int
    {
        return app(\App\Services\SignalQualityEvaluator::class)->calculateGaugePercentage('rsrp', (float) $this->rsrp);
    }

    /**
     * Calculate Gauge Percentage for RSRQ
     */
    public function getRsrqPercentageAttribute(): int
    {
        return app(\App\Services\SignalQualityEvaluator::class)->calculateGaugePercentage('rsrq', (float) $this->rsrq);
    }

    /**
     * Calculate Gauge Percentage for SINR
     */
    public function getSinrPercentageAttribute(): int
    {
        return app(\App\Services\SignalQualityEvaluator::class)->calculateGaugePercentage('sinr', (float) $this->sinr);
    }
}
