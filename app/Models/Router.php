<?php

namespace App\Models;

use App\Services\JsonStorageService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class Router
{
    public int $id = 1;
    public ?int $user_id = 1;
    public string $name = 'LTE Gateway Router';
    public string $driver = 'zlt';
    public ?string $model = 'ZLT P11X';
    public ?string $ip_address = '192.168.0.1';
    public ?string $username = 'admin';
    public ?string $password = '1234';
    public ?string $api_key = null;
    public ?string $firmware_version = '6.4.2.25';
    public ?string $hardware_version = 'TZ7.821.172';
    public ?string $config_version = 'V1.0.0';
    public ?string $modem_version = 'P705A_1.0.0';
    public ?string $build_date = '2022-11-08';
    public ?string $imei = '860000000000000';
    public ?string $imsi = '413000000000000';
    public ?string $iccid = '89900000000000000000';
    public ?string $mac = '00:11:22:33:44:55';
    public ?string $wan_ip = '10.0.0.100';
    public ?string $wan_gateway = '10.0.0.1';
    public ?string $wan_dns = '1.1.1.1, 8.8.8.8';
    public ?string $system_uptime = '2hour(s)15min(s)';
    public ?string $connection_time = '2 hour(s) 10 min(s)';
    public ?string $load_average = '0.15, 0.20, 0.18';
    public ?string $mode_status = 'Connected';
    public ?string $network_mode = '4G';
    public ?string $cs_status = 'No Service';
    public ?string $ps_status = 'Registered, the local network';
    public ?string $eps_status = 'Registered, the local network';
    public ?string $plmn = 'Sample LTE Operator';
    public ?string $description = null;
    public string $status = 'connected';
    public bool $is_active = true;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }
    }

    protected static function storage(): JsonStorageService
    {
        return app(JsonStorageService::class);
    }

    public static function all(): Collection
    {
        $items = static::storage()->read('routers');
        return collect($items)->map(fn ($data) => new static($data));
    }

    public static function count(): int
    {
        return static::all()->count();
    }

    public static function find(int|string|null $id): ?static
    {
        if (! $id) return null;
        $items = static::storage()->read('routers');
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
            throw new \Exception("Router with ID {$id} not found.");
        }
        return $found;
    }

    public static function first(): ?static
    {
        return static::all()->first();
    }

    public static function query(): RouterQuery
    {
        return new RouterQuery();
    }

    public static function latest(string $column = 'created_at'): RouterQuery
    {
        return (new RouterQuery())->latest($column);
    }

    public static function where(string $key, mixed $value): Collection
    {
        return static::all()->filter(function ($item) use ($key, $value) {
            return (string) ($item->{$key} ?? '') === (string) $value;
        })->values();
    }

    public static function getActive(): ?static
    {
        $routers = static::all();
        $active = $routers->firstWhere('is_active', true);
        return $active ?? $routers->first();
    }

    public static function create(array $attributes): static
    {
        $storage = static::storage();
        $items = $storage->read('routers');
        $id = $storage->nextId('routers');

        $attributes['id'] = $id;
        $attributes['created_at'] = now()->toIso8601String();
        $attributes['updated_at'] = now()->toIso8601String();

        if (! empty($attributes['is_active'])) {
            foreach ($items as &$it) {
                $it['is_active'] = false;
            }
        }

        $items[] = $attributes;
        $storage->write('routers', $items);

        return new static($attributes);
    }

    public function update(array $attributes): bool
    {
        $storage = static::storage();
        $items = $storage->read('routers');

        foreach ($items as $index => $item) {
            if ((string) $item['id'] === (string) $this->id) {
                $attributes['updated_at'] = now()->toIso8601String();
                $merged = array_merge($item, $attributes);
                $items[$index] = $merged;

                foreach ($merged as $k => $v) {
                    $this->{$k} = $v;
                }

                return $storage->write('routers', $items);
            }
        }

        return false;
    }

    public function delete(): bool
    {
        $storage = static::storage();
        $items = $storage->read('routers');

        $filtered = array_filter($items, fn ($it) => (string) $it['id'] !== (string) $this->id);
        
        // Also delete associated readings & events
        $readings = $storage->read('readings');
        $filteredReadings = array_filter($readings, fn ($r) => (string) ($r['router_id'] ?? '') !== (string) $this->id);
        $storage->write('readings', array_values($filteredReadings));

        $events = $storage->read('events');
        $filteredEvents = array_filter($events, fn ($e) => (string) ($e['router_id'] ?? '') !== (string) $this->id);
        $storage->write('events', array_values($filteredEvents));

        return $storage->write('routers', array_values($filtered));
    }

    public function setActive(): void
    {
        $storage = static::storage();
        $items = $storage->read('routers');

        foreach ($items as &$it) {
            $it['is_active'] = ((string) $it['id'] === (string) $this->id);
        }

        $this->is_active = true;
        $storage->write('routers', $items);
    }

    public function signalReadings(): SignalReadingQuery
    {
        return new SignalReadingQuery($this->id);
    }

    public function connectionEvents(): ConnectionEventQuery
    {
        return new ConnectionEventQuery($this->id);
    }

    // Accessors
    public function getMaskedImeiAttribute(): string
    {
        $imei = $this->imei ?? '';
        if (strlen($imei) >= 8) {
            return substr($imei, 0, 4) . ' ' . str_repeat('•', strlen($imei) - 8) . ' ' . substr($imei, -4);
        }
        return $imei ?: '•••• •••• ••••';
    }

    public function getMaskedImsiAttribute(): string
    {
        $imsi = $this->imsi ?? '';
        if (strlen($imsi) >= 8) {
            return substr($imsi, 0, 4) . ' ' . str_repeat('•', strlen($imsi) - 8) . ' ' . substr($imsi, -4);
        }
        return $imsi ?: '•••• •••• ••••';
    }

    public function getMaskedIccidAttribute(): string
    {
        $iccid = $this->iccid ?? '';
        if (strlen($iccid) >= 8) {
            return substr($iccid, 0, 4) . ' ' . str_repeat('•', strlen($iccid) - 8) . ' ' . substr($iccid, -4);
        }
        return $iccid ?: '•••• •••• ••••';
    }

    public function getMaskedMacAttribute(): string
    {
        $mac = $this->mac ?? '';
        $parts = explode(':', $mac);
        if (count($parts) === 6) {
            return $parts[0] . ':' . $parts[1] . ':••:••:' . $parts[4] . ':' . $parts[5];
        }
        return $mac ?: '••:••:••:••:••:••';
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

/**
 * Fluent Query Adapter for Routers
 */
class RouterQuery
{
    protected ?string $orderByField = 'created_at';
    protected string $orderDirection = 'desc';

    public function withCount(mixed $relations): static
    {
        return $this;
    }

    public function latest(string $column = 'created_at'): static
    {
        $this->orderByField = $column;
        $this->orderDirection = 'desc';
        return $this;
    }

    public function get(): Collection
    {
        $items = Router::all();
        if ($this->orderByField) {
            $f = $this->orderByField;
            $items = $items->sortBy(fn ($r) => $r->{$f} ?? null, SORT_REGULAR, $this->orderDirection === 'desc');
        }
        return $items->values();
    }
}

/**
 * Fluent Query Adapter for Signal Readings
 */
class SignalReadingQuery
{
    protected ?int $routerId = null;
    protected ?string $qualityFilter = null;
    protected ?string $searchTerm = null;
    protected ?string $orderByField = null;
    protected string $orderDirection = 'desc';
    protected ?int $limit = null;
    protected ?Carbon $timeCutoff = null;

    public function __construct(?int $routerId = null)
    {
        $this->routerId = $routerId;
    }

    public function where(mixed $column, mixed $operator = null, mixed $value = null): static
    {
        if ($column instanceof \Closure) {
            // Handled in get() or search
            return $this;
        }

        if ($operator !== null && $value === null) {
            $value = $operator;
            $operator = '=';
        }

        if ($column === 'router_id') {
            $this->routerId = (int) $value;
        } elseif ($column === 'overall_quality') {
            $this->qualityFilter = (string) $value;
        }

        return $this;
    }

    public function when(mixed $condition, callable $callback): static
    {
        if ($condition) {
            $callback($this, $condition);
        }
        return $this;
    }

    public function with(mixed $relations): static
    {
        return $this;
    }

    public function inTimeframe(string $timeframe): static
    {
        $minutes = match($timeframe) {
            '15m' => 15,
            '30m' => 30,
            '1h'  => 60,
            '6h'  => 360,
            '24h' => 1440,
            '7d'  => 10080,
            default => 30,
        };
        $this->timeCutoff = Carbon::now()->subMinutes($minutes);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): static
    {
        $this->orderByField = $column;
        $this->orderDirection = $direction;
        return $this;
    }

    public function latest(string $column = 'recorded_at'): static
    {
        $this->orderByField = $column;
        $this->orderDirection = 'desc';
        return $this;
    }

    public function oldest(string $column = 'recorded_at'): static
    {
        $this->orderByField = $column;
        $this->orderDirection = 'asc';
        return $this;
    }

    public function take(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function count(): int
    {
        return $this->get()->count();
    }

    public function max(string $column): ?float
    {
        $values = $this->get()->pluck($column)->filter(fn ($v) => $v !== null && $v !== '');
        return $values->isEmpty() ? null : (float) $values->max();
    }

    public function min(string $column): ?float
    {
        $values = $this->get()->pluck($column)->filter(fn ($v) => $v !== null && $v !== '');
        return $values->isEmpty() ? null : (float) $values->min();
    }

    public function avg(string $column): ?float
    {
        $values = $this->get()->pluck($column)->filter(fn ($v) => $v !== null && $v !== '');
        return $values->isEmpty() ? null : (float) $values->avg();
    }

    public function first(): ?SignalReading
    {
        return $this->get()->first();
    }

    public function chunk(int $count, callable $callback): bool
    {
        $all = $this->get();
        foreach ($all->chunk($count) as $chunk) {
            if ($callback($chunk) === false) {
                return false;
            }
        }
        return true;
    }

    public function delete(): bool
    {
        $storage = app(JsonStorageService::class);
        $readings = $storage->read('readings');

        if ($this->routerId !== null) {
            $filtered = array_filter($readings, fn ($r) => (string) ($r['router_id'] ?? '') !== (string) $this->routerId);
            return $storage->write('readings', array_values($filtered));
        }

        return true;
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], string $pageName = 'page', ?int $page = null): \Illuminate\Pagination\LengthAwarePaginator
    {
        $page = $page ?: \Illuminate\Pagination\Paginator::resolveCurrentPage($pageName);
        $items = $this->get();
        $total = $items->count();

        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]
        );
    }

    public function get(): Collection
    {
        $storage = app(JsonStorageService::class);
        $readings = $storage->read('readings');

        $collection = collect($readings);

        if ($this->routerId !== null) {
            $collection = $collection->filter(fn ($r) => (string) ($r['router_id'] ?? '') === (string) $this->routerId);
        }

        if ($this->qualityFilter !== null && $this->qualityFilter !== 'all') {
            $qf = $this->qualityFilter;
            $collection = $collection->filter(fn ($r) => ($r['overall_quality'] ?? '') === $qf);
        }

        if ($this->timeCutoff !== null) {
            $cutoff = $this->timeCutoff;
            $collection = $collection->filter(function ($r) use ($cutoff) {
                $rec = isset($r['recorded_at']) ? Carbon::parse($r['recorded_at']) : null;
                return $rec && $rec->greaterThanOrEqualTo($cutoff);
            });
        }

        if ($this->orderByField) {
            $field = $this->orderByField;
            $collection = $collection->sortBy(function ($item) use ($field) {
                return $item[$field] ?? null;
            }, SORT_REGULAR, $this->orderDirection === 'desc');
        }

        if ($this->limit !== null) {
            $collection = $collection->take($this->limit);
        }

        return $collection->values()->map(fn ($data) => new SignalReading($data));
    }
}

/**
 * Fluent Query Adapter for Connection Events
 */
class ConnectionEventQuery
{
    protected ?int $routerId = null;
    protected ?string $eventType = null;
    protected ?array $eventTypes = null;
    protected ?string $orderByField = 'occurred_at';
    protected string $orderDirection = 'desc';
    protected ?int $limit = null;

    public function __construct(?int $routerId = null)
    {
        $this->routerId = $routerId;
    }

    public function where(string $column, mixed $value): static
    {
        if ($column === 'router_id') {
            $this->routerId = (int) $value;
        } elseif ($column === 'event_type') {
            $this->eventType = (string) $value;
        }
        return $this;
    }

    public function whereIn(string $column, array $values): static
    {
        if ($column === 'event_type') {
            $this->eventTypes = $values;
        }
        return $this;
    }

    public function orderBy(string $column, string $direction = 'desc'): static
    {
        $this->orderByField = $column;
        $this->orderDirection = $direction;
        return $this;
    }

    public function take(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function count(): int
    {
        return $this->get()->count();
    }

    public function delete(): bool
    {
        $storage = app(JsonStorageService::class);
        $events = $storage->read('events');

        if ($this->routerId !== null) {
            $filtered = array_filter($events, fn ($e) => (string) ($e['router_id'] ?? '') !== (string) $this->routerId);
            return $storage->write('events', array_values($filtered));
        }

        return true;
    }

    public function get(): Collection
    {
        $storage = app(JsonStorageService::class);
        $events = $storage->read('events');

        $collection = collect($events);

        if ($this->routerId !== null) {
            $collection = $collection->filter(fn ($e) => (string) ($e['router_id'] ?? '') === (string) $this->routerId);
        }

        if ($this->eventType !== null) {
            $et = $this->eventType;
            $collection = $collection->filter(fn ($e) => ($e['event_type'] ?? '') === $et);
        }

        if ($this->eventTypes !== null) {
            $ets = $this->eventTypes;
            $collection = $collection->filter(fn ($e) => in_array($e['event_type'] ?? '', $ets));
        }

        if ($this->orderByField) {
            $field = $this->orderByField;
            $collection = $collection->sortBy(function ($item) use ($field) {
                return $item[$field] ?? null;
            }, SORT_REGULAR, $this->orderDirection === 'desc');
        }

        if ($this->limit !== null) {
            $collection = $collection->take($this->limit);
        }

        return $collection->values()->map(fn ($data) => new ConnectionEvent($data));
    }
}
