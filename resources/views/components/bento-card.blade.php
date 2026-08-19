@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'action' => null,
    'class' => '',
    'glow' => false,
    'badge' => null,
])

<div {{ $attributes->merge(['class' => 'group relative overflow-hidden rounded-2xl sm:rounded-3xl bg-[#111418] border border-[#232931] p-5 sm:p-6 shadow-xl transition-all duration-300 hover:border-[#D4A017]/40 hover:shadow-2xl hover:shadow-[#D4A017]/5 ' . $class]) }}>
    @if($glow)
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-[#D4A017]/10 rounded-full blur-3xl pointer-events-none group-hover:bg-[#D4A017]/20 transition-all"></div>
    @endif

    @if($title || $icon || $action || $badge)
        <div class="flex items-center justify-between gap-3 mb-4 relative z-10">
            <div class="flex items-center gap-2.5">
                @if($icon)
                    <div class="w-8 h-8 rounded-xl bg-[#171B20] border border-[#232931] flex items-center justify-center text-[#F2C94C] shadow-sm">
                        <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
                    </div>
                @endif
                <div>
                    @if($title)
                        <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#9CA3AF] font-mono flex items-center gap-2">
                            {{ $title }}
                            @if($badge)
                                <span class="text-[10px] lowercase font-normal px-2 py-0.5 rounded-full bg-[#171B20] text-[#D4A017] border border-[#D4A017]/30">{{ $badge }}</span>
                            @endif
                        </h3>
                    @endif
                    @if($subtitle)
                        <p class="text-[11px] text-[#6B7280]">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>

            @if($action)
                <div class="flex items-center gap-2">
                    {{ $action }}
                </div>
            @endif
        </div>
    @endif

    <div class="relative z-10">
        {{ $slot }}
    </div>
</div>
