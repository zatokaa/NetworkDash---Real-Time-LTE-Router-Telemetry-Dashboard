<?php

namespace App\Services\Contracts;

use App\Models\Router;

interface RouterDataProviderInterface
{
    /**
     * Fetch latest signal telemetry data from the provider.
     * Returns an associative array matching SignalReading attributes.
     */
    public function fetchReading(Router $router): array;
}
