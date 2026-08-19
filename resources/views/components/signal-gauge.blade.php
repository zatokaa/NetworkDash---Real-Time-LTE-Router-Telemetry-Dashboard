@props([
    'label' => 'RSRP',
    'value' => '--',
    'unit' => 'dBm',
    'percentage' => 0, // 0 - 100
    'status' => 'DISCONNECTED',
    'min' => '-120',
    'max' => '-70',
])

@php
    $isDisconnected = in_array(strtoupper($status ?? ''), ['DISCONNECTED', 'OFFLINE', 'NO SIGNAL']) || $value === '--' || $value === null;
    $percentageClamped = $isDisconnected ? 0 : max(5, min(100, (int)$percentage));
    $barColor = match(strtoupper($status ?? '')) {
        'EXCELLENT' => 'bg-gradient-to-r from-emerald-500 to-teal-400',
        'VERY GOOD' => 'bg-gradient-to-r from-teal-500 to-emerald-400',
        'GOOD' => 'bg-gradient-to-r from-[#D4A017] to-[#F2C94C]',
        'FAIR' => 'bg-gradient-to-r from-amber-500 to-yellow-400',
        'POOR', 'WEAK' => 'bg-gradient-to-r from-rose-600 to-rose-400',
        'DISCONNECTED', 'OFFLINE', 'NO SIGNAL' => 'bg-zinc-800',
        default => $isDisconnected ? 'bg-zinc-800' : 'bg-gradient-to-r from-[#D4A017] to-[#F2C94C]',
    };
@endphp

<div class="space-y-2">
    <div class="flex items-center justify-between text-xs font-mono">
        <div class="flex items-center gap-2">
            <span class="font-bold text-white uppercase">{{ $label }}</span>
            <span class="text-[#9CA3AF]">{{ $value ?? '--' }} {{ $unit }}</span>
        </div>
        <x-status-badge :status="$status ?? 'DISCONNECTED'" size="sm" />
    </div>

    <!-- Gauge Track -->
    <div class="relative h-2.5 w-full rounded-full bg-[#0B0D0F] border border-[#232931] overflow-hidden p-[1px]">
        <div 
            class="h-full rounded-full transition-all duration-700 ease-out {{ $barColor }}"
            style="width: {{ $percentageClamped }}%;"
        ></div>
    </div>

    <!-- Min/Max Scale -->
    <div class="flex justify-between text-[10px] font-mono text-[#6B7280]">
        <span>Min: {{ $min }} {{ $unit }}</span>
        <span>Max: {{ $max }} {{ $unit }}</span>
    </div>
</div>
