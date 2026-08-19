@props([
    'label' => 'RSRP',
    'value' => '--',
    'unit' => 'dBm',
    'status' => 'GOOD',
    'description' => 'Signal Strength',
    'trend' => null,
    'trendDirection' => 'up', // 'up', 'down', 'neutral'
    'icon' => 'activity',
])

<div class="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-[#111418] border border-[#232931] p-5 sm:p-6 shadow-xl transition-all duration-300 hover:border-[#D4A017]/40 hover:shadow-2xl hover:shadow-[#D4A017]/5 flex flex-col justify-between">
    <div class="flex items-center justify-between gap-2 mb-3">
        <div class="flex items-center gap-2">
            <span class="text-xs font-mono font-bold tracking-wider text-[#9CA3AF] uppercase">{{ $label }}</span>
            <span class="text-[11px] text-[#6B7280]">({{ $unit }})</span>
        </div>
        <x-status-badge :status="$status" size="sm" />
    </div>

    <!-- Value & Unit Display -->
    <div class="my-2">
        <div class="flex items-baseline gap-1.5 font-mono">
            <span class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">{{ $value }}</span>
            <span class="text-xs font-semibold text-[#9CA3AF]">{{ $unit }}</span>
        </div>
        <p class="text-xs text-[#9CA3AF] mt-1 font-sans">{{ $description }}</p>
    </div>

    <!-- Trend / Delta info -->
    @if($trend !== null)
        <div class="mt-3 pt-3 border-t border-[#171B20] flex items-center justify-between text-[11px] font-mono text-[#9CA3AF]">
            <span>vs previous</span>
            <span class="flex items-center gap-1 @if($trendDirection === 'up') text-emerald-400 @elseif($trendDirection === 'down') text-rose-400 @else text-gray-400 @endif">
                @if($trendDirection === 'up')
                    <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                @elseif($trendDirection === 'down')
                    <i data-lucide="trending-down" class="w-3.5 h-3.5"></i>
                @else
                    <i data-lucide="minus" class="w-3.5 h-3.5"></i>
                @endif
                {{ $trend }}
            </span>
        </div>
    @endif
</div>
