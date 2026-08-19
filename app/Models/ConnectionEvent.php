<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConnectionEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'router_id',
        'event_type',
        'title',
        'description',
        'previous_value',
        'new_value',
        'severity',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function scopeForRouter($query, int $routerId)
    {
        return $query->where('router_id', $routerId);
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('occurred_at', 'desc')->take($limit);
    }

    public function getIconAttribute(): string
    {
        return match ($this->event_type) {
            'band_changed' => 'radio',
            'cell_changed' => 'tower-control',
            'signal_weak' => 'alert-triangle',
            'signal_excellent' => 'sparkles',
            'connected' => 'wifi',
            'disconnected' => 'wifi-off',
            default => 'activity',
        };
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
                'text' => 'text-[#9CA3AF]',
                'icon' => 'text-[#F2C94C]',
            ],
        };
    }
}
