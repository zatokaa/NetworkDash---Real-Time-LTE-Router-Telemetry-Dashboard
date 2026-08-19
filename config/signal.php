<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LTE Signal Quality Thresholds
    |--------------------------------------------------------------------------
    | These thresholds define the classification boundaries for LTE radio
    | metrics. They can be customized or overridden via environment / settings.
    */
    'thresholds' => [
        'rsrp' => [
            'excellent' => -80.0,
            'very_good' => -90.0,
            'good'      => -100.0,
            'fair'      => -110.0,
            // below -110 is poor
            'gauge_min' => -125.0,
            'gauge_max' => -70.0,
        ],
        'rsrq' => [
            'excellent' => -8.0,
            'good'      => -12.0,
            'fair'      => -16.0,
            // below -16 is poor
            'gauge_min' => -20.0,
            'gauge_max' => -3.0,
        ],
        'sinr' => [
            'excellent' => 20.0,
            'good'      => 13.0,
            'fair'      => 0.0,
            // below 0 is poor
            'gauge_min' => -5.0,
            'gauge_max' => 30.0,
        ],
        'rssi' => [
            'excellent' => -65.0,
            'good'      => -75.0,
            'fair'      => -85.0,
            // below -85 is poor
            'gauge_min' => -105.0,
            'gauge_max' => -50.0,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Signal Health Score Weights (Total 100%)
    |--------------------------------------------------------------------------
    */
    'weights' => [
        'rsrp' => 0.40, // Signal Power (40%)
        'sinr' => 0.35, // Signal Noise Ratio (35%)
        'rsrq' => 0.25, // Signal Quality (25%)
    ],

    /*
    |--------------------------------------------------------------------------
    | Overall Score Rating Boundaries (0 - 100)
    |--------------------------------------------------------------------------
    */
    'score_ratings' => [
        'excellent' => 85,
        'very_good' => 70,
        'good'      => 55,
        'fair'      => 40,
    ],

    /*
    |--------------------------------------------------------------------------
    | RF Noise & Interference Condition Rule
    |--------------------------------------------------------------------------
    */
    'interference' => [
        'min_rsrp_for_strong' => -95.0, // Strong signal baseline
        'max_sinr_for_noise'  => 5.0,   // Low SINR indicating interference
    ],
];
