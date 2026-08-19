<?php

namespace App\Services;

class SignalQualityEvaluator
{
    protected array $config;

    public function __construct(?array $config = null)
    {
        $baseConfig = config('signal', []);
        
        // Merge database-stored settings if available
        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $dbThresholds = \App\Models\Setting::get('signal_thresholds');
            if (is_array($dbThresholds)) {
                $baseConfig['thresholds'] = array_replace_recursive($baseConfig['thresholds'] ?? [], $dbThresholds);
            }

            $dbWeights = \App\Models\Setting::get('signal_weights');
            if (is_array($dbWeights)) {
                $baseConfig['weights'] = array_replace($baseConfig['weights'] ?? [], $dbWeights);
            }
        }

        $this->config = $config ?? $baseConfig;
    }

    /**
     * Evaluate RSRP (Reference Signal Received Power)
     */
    public function evaluateRsrp(float $rsrp): string
    {
        $t = $this->config['thresholds']['rsrp'] ?? [
            'excellent' => -80.0,
            'very_good' => -90.0,
            'good'      => -100.0,
            'fair'      => -110.0,
        ];

        if ($rsrp >= $t['excellent']) return 'EXCELLENT';
        if ($rsrp >= $t['very_good']) return 'VERY GOOD';
        if ($rsrp >= $t['good'])      return 'GOOD';
        if ($rsrp >= $t['fair'])      return 'FAIR';
        return 'POOR';
    }

    /**
     * Evaluate RSRQ (Reference Signal Received Quality)
     */
    public function evaluateRsrq(float $rsrq): string
    {
        $t = $this->config['thresholds']['rsrq'] ?? [
            'excellent' => -8.0,
            'good'      => -12.0,
            'fair'      => -16.0,
        ];

        if ($rsrq >= $t['excellent']) return 'EXCELLENT';
        if ($rsrq >= $t['good'])      return 'GOOD';
        if ($rsrq >= $t['fair'])      return 'FAIR';
        return 'POOR';
    }

    /**
     * Evaluate SINR (Signal to Interference plus Noise Ratio)
     */
    public function evaluateSinr(float $sinr): string
    {
        $t = $this->config['thresholds']['sinr'] ?? [
            'excellent' => 20.0,
            'good'      => 13.0,
            'fair'      => 0.0,
        ];

        if ($sinr >= $t['excellent']) return 'EXCELLENT';
        if ($sinr >= $t['good'])      return 'GOOD';
        if ($sinr >= $t['fair'])      return 'FAIR';
        return 'POOR';
    }

    /**
     * Evaluate RSSI (Received Signal Strength Indicator)
     */
    public function evaluateRssi(float $rssi): string
    {
        $t = $this->config['thresholds']['rssi'] ?? [
            'excellent' => -65.0,
            'good'      => -75.0,
            'fair'      => -85.0,
        ];

        if ($rssi >= $t['excellent']) return 'EXCELLENT';
        if ($rssi >= $t['good'])      return 'GOOD';
        if ($rssi >= $t['fair'])      return 'FAIR';
        return 'POOR';
    }

    /**
     * Calculate comprehensive weighted score and overall status
     */
    public function calculateOverallScore(float $rsrp, float $rsrq, float $sinr, float $rssi = 0): array
    {
        $weights = $this->config['weights'] ?? [
            'rsrp' => 0.40,
            'sinr' => 0.35,
            'rsrq' => 0.25,
        ];

        // 1. RSRP Normalization (-120 dBm = 0%, -75 dBm = 100%)
        $rsrpScore = max(0, min(100, (($rsrp - (-120.0)) / ((-75.0) - (-120.0))) * 100));

        // 2. SINR Normalization (-5 dB = 0%, 25 dB = 100%)
        $sinrScore = max(0, min(100, (($sinr - (-5.0)) / (25.0 - (-5.0))) * 100));

        // 3. RSRQ Normalization (-20 dB = 0%, -5 dB = 100%)
        $rsrqScore = max(0, min(100, (($rsrq - (-20.0)) / ((-5.0) - (-20.0))) * 100));

        $totalScore = (int) round(
            ($rsrpScore * ($weights['rsrp'] ?? 0.40)) +
            ($sinrScore * ($weights['sinr'] ?? 0.35)) +
            ($rsrqScore * ($weights['rsrq'] ?? 0.25))
        );

        $ratings = $this->config['score_ratings'] ?? [
            'excellent' => 85,
            'very_good' => 70,
            'good'      => 55,
            'fair'      => 40,
        ];

        $rating = match (true) {
            $totalScore >= ($ratings['excellent'] ?? 85) => 'EXCELLENT',
            $totalScore >= ($ratings['very_good'] ?? 70) => 'VERY GOOD',
            $totalScore >= ($ratings['good'] ?? 55)      => 'GOOD',
            $totalScore >= ($ratings['fair'] ?? 40)      => 'FAIR',
            default                                       => 'POOR',
        };

        $interference = $this->detectInterference($rsrp, $sinr);

        return [
            'score'                => $totalScore,
            'rating'               => $rating,
            'is_healthy'           => in_array($rating, ['EXCELLENT', 'VERY GOOD', 'GOOD']),
            'has_interference'     => $interference['has_interference'],
            'interference_message' => $interference['message'],
            'rsrp_rating'          => $this->evaluateRsrp($rsrp),
            'rsrq_rating'          => $this->evaluateRsrq($rsrq),
            'sinr_rating'          => $this->evaluateSinr($sinr),
            'rssi_rating'          => $this->evaluateRssi($rssi),
        ];
    }

    /**
     * Check if low SINR is present despite strong RSRP (RF Noise / Interference condition)
     */
    public function detectInterference(float $rsrp, float $sinr): array
    {
        $minRsrp = $this->config['interference']['min_rsrp_for_strong'] ?? -95.0;
        $maxSinr = $this->config['interference']['max_sinr_for_noise'] ?? 5.0;

        $hasInterference = ($rsrp >= $minRsrp && $sinr <= $maxSinr);

        if ($hasInterference) {
            $message = "Signal strength is good ({$rsrp} dBm), but interference/noise (SINR {$sinr} dB) may be affecting performance.";
        } else {
            $message = "Normal radio propagation with minimal RF noise.";
        }

        return [
            'has_interference' => $hasInterference,
            'message'          => $message,
        ];
    }

    /**
     * Calculate normalized gauge percentage (0 - 100)
     */
    public function calculateGaugePercentage(string $metric, float $value): int
    {
        $t = $this->config['thresholds'][$metric] ?? null;
        if (! $t) return 50;

        $min = (float) ($t['gauge_min'] ?? -120.0);
        $max = (float) ($t['gauge_max'] ?? -70.0);

        if ($max <= $min) return 50;

        $pct = (($value - $min) / ($max - $min)) * 100;
        return max(5, min(100, (int) round($pct)));
    }
}
