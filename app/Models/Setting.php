<?php

namespace App\Models;

use App\Services\JsonStorageService;

class Setting
{
    protected static function storage(): JsonStorageService
    {
        return app(JsonStorageService::class);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::storage()->read('settings');
        return $settings[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): bool
    {
        $storage = static::storage();
        $settings = $storage->read('settings');
        $settings[$key] = $value;
        return $storage->write('settings', $settings);
    }

    public static function whereIn(string $key, array $values): SettingQuery
    {
        return new SettingQuery($values);
    }
}

class SettingQuery
{
    protected array $keys;

    public function __construct(array $keys)
    {
        $this->keys = $keys;
    }

    public function delete(): bool
    {
        $storage = app(JsonStorageService::class);
        $settings = $storage->read('settings');

        foreach ($this->keys as $k) {
            unset($settings[$k]);
        }

        return $storage->write('settings', $settings);
    }
}
