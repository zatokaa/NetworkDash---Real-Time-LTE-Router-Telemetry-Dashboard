<?php

namespace App\Models;

use App\Services\JsonStorageService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SignalReading
{
    public int $id = 1;
    public int $router_id = 1;
    public mixed $recorded_at;
    public float $rsrp = -88.0;
    public float $rssi = -62.0;
    public float $rsrq = -10.0;
    public float $sinr = 15.0;
    public ?string $band = 'B40';
    public ?string $bandwidth = '20 MHz';
    public ?int $earfcn = 39146;
    public ?string $transmission_mode = 'TM8';
    public ?float $tx_power = 23.0;
    public ?string $rrc_state = 'Connected';
    public ?int $mcs = 24;
    public ?int $cqi = 10;
    public ?string $enodeb = '1001';
    public ?string $cell_id = '1';
    public ?string $global_cell_id = 'AA101';
    public ?string $physical_cell_id = '10';
    public ?string $created_at = null;
    public ?string $updated_at = null;

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }

        if (isset($this->recorded_at) && ! ($this->recorded_at instanceof Carbon)) {
            $this->recorded_at = Carbon::parse($this->recorded_at);
        } else if (! isset($this->recorded_at)) {
            $this->recorded_at = Carbon::now();
        }
    }

    protected static function storage(): JsonStorageService
    {
        return app(JsonStorageService::class);
    }

    public static function all(): Collection
    {
        $items = static::storage()->read('readings');
        return collect($items)->map(fn ($data) => new static($data));
    }

    public static function count(): int
    {
        return static::all()->count();
    }

    public static function find(int|string|null $id): ?static
    {
        if (! $id) return null;
        $items = static::storage()->read('readings');
        foreach ($items as $data) {
            if ((string) $data['id'] === (string) $id) {
                return new static($data);
            }
        }
        return null;
    }

    public static function findOrFail(int|string|null $id): static
    {
        $found = static::find($id);
        if (! $found) {
            throw new \Exception("Reading with ID {$id} not found.");
        }
        return $found;
    }

    public static function with(mixed $relations): SignalReadingQuery
    {
        return new SignalReadingQuery();
    }

    public static function when(mixed $condition, callable $callback): SignalReadingQuery
    {
        $query = new SignalReadingQuery();
        return $query->when($condition, $callback);
    }

    public static function query(): SignalReadingQuery
    {
        return new SignalReadingQuery();
    }

    public static function where(string $key, mixed $value): SignalReadingQuery
    {
        if ($key === 'router_id') {
            return new SignalReadingQuery((int) $value);
        }
        return new SignalReadingQuery();
    }

    public static function latest(string $column = 'recorded_at'): SignalReadingQuery
    {
        return (new SignalReadingQuery())->latest($column);
    }

    public static function create(array $attributes): static
    {
        $storage = static::storage();
        $items = $storage->read('readings');
        $id = $storage->nextId('readings');

        $attributes['id'] = $id;
        if (! isset($attributes['recorded_at'])) {
            $attributes['recorded_at'] = now()->toIso8601String();
        } elseif ($attributes['recorded_at'] instanceof Carbon) {
            $attributes['recorded_at'] = $attributes['recorded_at']->toIso8601String();
        }
        $attributes['created_at'] = now()->toIso8601String();
        $attributes['updated_at'] = now()->toIso8601String();

        // Keep last 2,500 readings in ring buffer for lightning speed
        if (count($items) >= 2500) {
            array_shift($items);
        }

        $items[] = $attributes;
        $storage->write('readings', $items);

        return new static($attributes);
    }

    public function delete(): bool
    {
        $storage = static::storage();
        $items = $storage->read('readings');

        $filtered = array_filter($items, fn ($it) => (string) $it['id'] !== (string) $this->id);
        return $storage->write('readings', array_values($filtered));
    }

    public function router(): ?Router
    {
        return Router::find($this->router_id);
    }

    // Dynamic Signal Quality Calculations
    public function getRsrpQualityAttribute(): string
    {
        $v = (float) $this->rsrp;
        if ($v >= -80.0) return 'EXCELLENT';
        if ($v >= -90.0) return 'GOOD';
        if ($v >= -100.0) return 'FAIR';
        return 'POOR';
    }

    public function getRssiQualityAttribute(): string
    {
        $v = (float) $this->rssi;
        if ($v >= -65.0) return 'EXCELLENT';
        if ($v >= -75.0) return 'GOOD';
        if ($v >= -85.0) return 'FAIR';
        return 'POOR';
    }

    public function getRsrqQualityAttribute(): string
    {
        $v = (float) $this->rsrq;
        if ($v >= -10.0) return 'EXCELLENT';
        if ($v >= -15.0) return 'GOOD';
        if ($v >= -20.0) return 'FAIR';
        return 'POOR';
    }

    public function getSinrQualityAttribute(): string
    {
        $v = (float) $this->sinr;
        if ($v >= 20.0) return 'EXCELLENT';
        if ($v >= 13.0) return 'GOOD';
        if ($v >= 0.0) return 'FAIR';
        return 'POOR';
    }

    public function getOverallQualityAttribute(): string
    {
        $score = $this->signal_score;
        if ($score >= 80) return 'EXCELLENT';
        if ($score >= 60) return 'GOOD';
        if ($score >= 40) return 'FAIR';
        return 'POOR';
    }

    public function getSignalScoreAttribute(): int
    {
        $rsrp = (float) $this->rsrp;
        $sinr = (float) $this->sinr;
        $rsrq = (float) $this->rsrq;

        $rsrpScore = max(0, min(100, (($rsrp + 120) / 50) * 100)) * 0.40;
        $sinrScore = max(0, min(100, (($sinr + 5) / 35) * 100)) * 0.40;
        $rsrqScore = max(0, min(100, (($rsrq + 20) / 17) * 100)) * 0.20;

        return (int) round($rsrpScore + $sinrScore + $rsrqScore);
    }

    public function getRsrpPercentageAttribute(): int
    {
        $min = -120;
        $max = -70;
        return (int) max(0, min(100, round((($this->rsrp - $min) / ($max - $min)) * 100)));
    }

    public function getRsrqPercentageAttribute(): int
    {
        $min = -20;
        $max = -3;
        return (int) max(0, min(100, round((($this->rsrq - $min) / ($max - $min)) * 100)));
    }

    public function getSinrPercentageAttribute(): int
    {
        $min = -5;
        $max = 30;
        return (int) max(0, min(100, round((($this->sinr - $min) / ($max - $min)) * 100)));
    }

    public function __get(string $name): mixed
    {
        $getter = 'get' . str_replace('_', '', ucwords($name, '_')) . 'Attribute';
        if (method_exists($this, $getter)) {
            return $this->{$getter}();
        }
        return $this->{$name} ?? null;
    }
}
