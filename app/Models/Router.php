<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Router extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'model',
        'driver',
        'ip_address',
        'username',
        'password',
        'firmware_version',
        'hardware_version',
        'modem_version',
        'config_version',
        'build_date',
        'system_uptime',
        'load_average',
        'connection_time',
        'network_mode',
        'mode_status',
        'cs_status',
        'ps_status',
        'eps_status',
        'plmn',
        'wan_ip',
        'wan_gateway',
        'wan_dns',
        'imei',
        'imsi',
        'iccid',
        'mac_address',
        'description',
        'status',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'password' => 'encrypted',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function signalReadings(): HasMany
    {
        return $this->hasMany(SignalReading::class);
    }

    public function latestReading(): HasOne
    {
        return $this->hasOne(SignalReading::class)->latestOfMany();
    }

    public function connectionEvents(): HasMany
    {
        return $this->hasMany(ConnectionEvent::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first() ?? static::first();
    }

    public function setActive(): void
    {
        static::where('id', '!=', $this->id)->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }

    /**
     * Return safely masked IMEI (e.g. 8645************) unless explicitly unmasked in settings
     */
    public function getMaskedImeiAttribute(): string
    {
        if (empty($this->imei)) {
            return '8645************';
        }

        if (\App\Models\Setting::get('reveal_sensitive_ids', false)) {
            return $this->imei;
        }

        return substr($this->imei, 0, 4) . str_repeat('*', max(0, strlen($this->imei) - 8)) . substr($this->imei, -4);
    }

    /**
     * Return safely masked MAC address (e.g. 3C:7A:**:**:**:9E) unless unmasked in settings
     */
    public function getMaskedMacAttribute(): string
    {
        if (empty($this->mac_address)) {
            return '3C:7A:**:**:**:9E';
        }

        if (\App\Models\Setting::get('reveal_sensitive_ids', false)) {
            return $this->mac_address;
        }

        $parts = explode(':', $this->mac_address);
        if (count($parts) === 6) {
            return "{$parts[0]}:{$parts[1]}:**:**:**:{$parts[5]}";
        }
        return '3C:7A:**:**:**:9E';
    }

    public function getMaskedImsiAttribute(): string
    {
        if (empty($this->imsi)) {
            return '4130************';
        }

        if (\App\Models\Setting::get('reveal_sensitive_ids', false)) {
            return $this->imsi;
        }

        return substr($this->imsi, 0, 4) . '************';
    }

    public function getMaskedIccidAttribute(): string
    {
        if (empty($this->iccid)) {
            return '8994************';
        }

        if (\App\Models\Setting::get('reveal_sensitive_ids', false)) {
            return $this->iccid;
        }

        return substr($this->iccid, 0, 4) . '************';
    }
}
