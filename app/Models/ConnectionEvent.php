<?php

namespace App\Models;

use App\Services\JsonStorageService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ConnectionEvent
{
    public int $id = 1;
    public int $router_id = 1;
    public string $event_type = 'connected';
    public string $title = 'LTE Radio Link Established';
    public string $description = 'Attached to LTE Band 40 carrier network with 20 MHz bandwidth.';
    public ?string $previous_value = null;
    public ?string $new_value = null;
    public string $severity = 'info';
    public mixed $occurred_at;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }

        if (isset($this->occurred_at) && ! ($this->occurred_at instanceof Carbon)) {
            $this->occurred_at = Carbon::parse($this->occurred_at);
        } else if (! isset($this->occurred_at)) {
            $this->occurred_at = Carbon::now();
        }
    }

    protected static function storage(): JsonStorageService
    {
        return app(JsonStorageService::class);
    }

    public static function all(): Collection
    {
        $items = static::storage()->read('events');
        return collect($items)->map(fn ($data) => new static($data));
    }

    public static function count(): int
    {
        return static::all()->count();
    }

    public static function find(int|string|null $id): ?static
    {
        if (! $id) return null;
        $items = static::storage()->read('events');
        foreach ($items as $data) {
            if ((string) $data['id'] === (string) $id) {
                return new static($data);
            }
        }
        return null;
    }

    public static function create(array $attributes): static
    {
        $storage = static::storage();
        $items = $storage->read('events');
        $id = $storage->nextId('events');

        $attributes['id'] = $id;
        if (! isset($attributes['occurred_at'])) {
            $attributes['occurred_at'] = now()->toIso8601String();
        } elseif ($attributes['occurred_at'] instanceof Carbon) {
            $attributes['occurred_at'] = $attributes['occurred_at']->toIso8601String();
        }
        $attributes['created_at'] = now()->toIso8601String();
        $attributes['updated_at'] = now()->toIso8601String();

        // Keep last 1,000 events in ring buffer
        if (count($items) >= 1000) {
            array_shift($items);
        }

        $items[] = $attributes;
        $storage->write('events', $items);

        return new static($attributes);
    }

    public static function query(): ConnectionEventQuery
    {
        return new ConnectionEventQuery();
    }

    public static function where(string $key, mixed $value): ConnectionEventQuery
    {
        $q = new ConnectionEventQuery();
        return $q->where($key, $value);
    }

    public static function whereIn(string $key, array $values): ConnectionEventQuery
    {
        $q = new ConnectionEventQuery();
        return $q->whereIn($key, $values);
    }

    public static function deleteByRouterId(int $routerId): bool
    {
        $storage = static::storage();
        $items = $storage->read('events');

        $filtered = array_filter($items, fn ($it) => (string) ($it['router_id'] ?? '') !== (string) $routerId);
        return $storage->write('events', array_values($filtered));
    }

    public function delete(): bool
    {
        $storage = static::storage();
        $items = $storage->read('events');

        $filtered = array_filter($items, fn ($it) => (string) $it['id'] !== (string) $this->id);
        return $storage->write('events', array_values($filtered));
    }

    public function getSeverityColorAttribute(): array
    {
        return match ($this->severity) {
            'success' => [
                'bg' => 'bg-emerald-500/10',
                'border' => 'border-emerald-500/30',
                'text' => 'text-emerald-400',
                'icon' => 'text-emerald-400',
            ],
            'warning' => [
                'bg' => 'bg-amber-500/10',
                'border' => 'border-amber-500/30',
                'text' => 'text-amber-400',
                'icon' => 'text-amber-400',
            ],
            'danger' => [
                'bg' => 'bg-rose-500/10',
                'border' => 'border-rose-500/30',
                'text' => 'text-rose-400',
                'icon' => 'text-rose-400',
            ],
            default => [
                'bg' => 'bg-[#171B20]',
                'border' => 'border-[#232931]',
                'text' => 'text-[#F2C94C]',
                'icon' => 'text-[#F2C94C]',
            ],
        };
    }

    public function getIconAttribute(): string
    {
        return match ($this->event_type) {
            'cell_changed' => 'tower-control',
            'band_changed' => 'radio',
            'connected' => 'wifi',
            'disconnected' => 'wifi-off',
            'signal_weak' => 'alert-triangle',
            'signal_excellent' => 'zap',
            default => 'info',
        };
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
