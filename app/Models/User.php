<?php

namespace App\Models;

use App\Services\JsonStorageService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class User implements Authenticatable
{
    public int $id = 1;
    public string $name = 'Administrator';
    public string $email = 'admin@example.com';
    public string $password = '';
    public ?string $remember_token = null;
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
        $items = static::storage()->read('users');
        return collect($items)->map(fn ($data) => new static($data));
    }

    public static function find(int|string|null $id): ?static
    {
        if (! $id) return null;
        $items = static::storage()->read('users');
        foreach ($items as $data) {
            if ((string) $data['id'] === (string) $id) {
                return new static($data);
            }
        }
        return null;
    }

    public static function findByEmail(string $email): ?static
    {
        $items = static::storage()->read('users');
        foreach ($items as $data) {
            if (strtolower($data['email']) === strtolower($email)) {
                return new static($data);
            }
        }
        return null;
    }

    public static function first(): ?static
    {
        return static::all()->first();
    }

    public static function create(array $attributes): static
    {
        $storage = static::storage();
        $items = $storage->read('users');
        $id = $storage->nextId('users');

        $attributes['id'] = $id;
        if (isset($attributes['password']) && ! str_starts_with($attributes['password'], '$2y$')) {
            $attributes['password'] = Hash::make($attributes['password']);
        }
        $attributes['created_at'] = now()->toIso8601String();
        $attributes['updated_at'] = now()->toIso8601String();

        $items[] = $attributes;
        $storage->write('users', $items);

        return new static($attributes);
    }

    public function update(array $attributes): bool
    {
        $storage = static::storage();
        $items = $storage->read('users');

        foreach ($items as $index => $item) {
            if ((string) $item['id'] === (string) $this->id) {
                if (isset($attributes['password']) && ! str_starts_with($attributes['password'], '$2y$')) {
                    $attributes['password'] = Hash::make($attributes['password']);
                }
                $attributes['updated_at'] = now()->toIso8601String();
                $merged = array_merge($item, $attributes);
                $items[$index] = $merged;

                foreach ($merged as $k => $v) {
                    $this->{$k} = $v;
                }

                return $storage->write('users', $items);
            }
        }

        return false;
    }

    // Authenticatable Interface Implementation
    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->id;
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getAuthPassword(): string
    {
        return $this->password;
    }

    public function getRememberToken(): ?string
    {
        return $this->remember_token;
    }

    public function setRememberToken($value): void
    {
        $this->remember_token = $value;
        $this->update(['remember_token' => $value]);
    }

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }
}
