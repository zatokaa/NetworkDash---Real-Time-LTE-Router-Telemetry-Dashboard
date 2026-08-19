<div class="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-[#111418] border border-[#232931] p-5 sm:p-6 shadow-xl">
    <!-- Header & Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5 border-b border-[#171B20] pb-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-[#171B20] border border-[#232931] flex items-center justify-center text-[#F2C94C]">
                <i data-lucide="bell" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-white font-mono flex items-center gap-2">
                    Connection & Network Events
                </h3>
                <p class="text-[11px] text-[#6B7280]">Last 5 carrier transitions, cell handovers, and signal alerts</p>
            </div>
        </div>

        <!-- Filter Buttons -->
        <div class="flex flex-wrap items-center gap-1.5 bg-[#0B0D0F] p-1 rounded-xl border border-[#232931] text-xs font-mono">
            <button 
                wire:click="setFilter('all')" 
                type="button" 
                class="px-2.5 py-1 rounded-lg transition-colors @if($filter === 'all') bg-[#D4A017] text-[#0B0D0F] font-bold @else text-[#9CA3AF] hover:text-white @endif"
            >
                All (5)
            </button>
            <button 
                wire:click="setFilter('cell_changed')" 
                type="button" 
                class="px-2.5 py-1 rounded-lg transition-colors @if($filter === 'cell_changed') bg-[#D4A017] text-[#0B0D0F] font-bold @else text-[#9CA3AF] hover:text-white @endif"
            >
                Handovers
            </button>
            <button 
                wire:click="setFilter('band_changed')" 
                type="button" 
                class="px-2.5 py-1 rounded-lg transition-colors @if($filter === 'band_changed') bg-[#D4A017] text-[#0B0D0F] font-bold @else text-[#9CA3AF] hover:text-white @endif"
            >
                Bands
            </button>
            <button 
                wire:click="setFilter('alerts')" 
                type="button" 
                class="px-2.5 py-1 rounded-lg transition-colors @if($filter === 'alerts') bg-[#D4A017] text-[#0B0D0F] font-bold @else text-[#9CA3AF] hover:text-white @endif"
            >
                Alerts
            </button>
        </div>
    </div>

    <!-- Timeline List -->
    <div class="space-y-4 relative before:absolute before:inset-0 before:left-4 before:w-0.5 before:bg-[#232931] before:pointer-events-none">
        @forelse($events as $event)
            @php $sev = $event->severity_color; @endphp
            <div class="relative flex items-start gap-3.5 pl-1 group">
                <!-- Event Icon Pill -->
                <div class="w-8 h-8 rounded-xl {{ $sev['bg'] }} border {{ $sev['border'] }} flex items-center justify-center {{ $sev['icon'] }} flex-shrink-0 z-10 shadow-md">
                    <i data-lucide="{{ $event->icon }}" class="w-4 h-4"></i>
                </div>

                <!-- Event Content Card -->
                <div class="flex-1 p-3.5 rounded-2xl bg-[#0B0D0F] border border-[#232931] group-hover:border-[#D4A017]/40 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1">
                        <span class="text-xs font-bold text-white tracking-tight font-mono">{{ $event->title }}</span>
                        <span class="text-[10px] font-mono text-[#6B7280] whitespace-nowrap">
                            {{ $event->occurred_at->timezone(config('app.timezone', 'Asia/Colombo'))->format('H:i:s') }} ({{ $event->occurred_at->diffForHumans() }})
                        </span>
                    </div>

                    <p class="text-xs text-[#9CA3AF] leading-relaxed mb-2 font-sans">{{ $event->description }}</p>

                    @if($event->previous_value && $event->new_value)
                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-[#171B20] border border-[#232931] text-[11px] font-mono">
                            <span class="text-[#6B7280]">{{ $event->previous_value }}</span>
                            <i data-lucide="arrow-right" class="w-3 h-3 text-[#F2C94C]"></i>
                            <span class="text-[#F2C94C] font-semibold">{{ $event->new_value }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="py-8 text-center text-[#9CA3AF] pl-8">
                <div class="w-10 h-10 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-center mx-auto mb-2 text-[#6B7280]">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-400"></i>
                </div>
                <p class="text-xs font-bold text-white">Network Link Stable</p>
                <p class="text-[11px] text-[#6B7280] mt-0.5">No carrier handovers or degradation events recorded</p>
            </div>
        @endforelse
    </div>
</div>
