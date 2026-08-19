<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (! $setting) {
            return $default;
        }

        $val = $setting->value;
        $decoded = json_decode($val, true);
        return (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : $val;
    }

    public static function set(string $key, mixed $value): self
    {
        $val = is_array($value) ? json_encode($value) : (string) $value;
        return static::updateOrCreate(['key' => $key], ['value' => $val]);
    }

    public static function has(string $key): bool
    {
        return static::where('key', $key)->exists();
    }
}
