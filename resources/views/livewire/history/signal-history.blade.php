<div class="space-y-6">
    <!-- Top Action Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 p-5 sm:p-6 rounded-2xl sm:rounded-3xl bg-[#111418] border border-[#232931] shadow-xl">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#F2C94C] flex items-center justify-center text-[#0B0D0F] shadow-lg shadow-[#D4A017]/20 flex-shrink-0">
                <i data-lucide="history" class="w-6 h-6 stroke-[2.5]"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white">Historical Telemetry Log</h1>
                <p class="text-xs text-[#9CA3AF]">Complete time-series record of LTE signal strength, quality, and radio modulation</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <a href="{{ route('dashboard') }}" class="px-3.5 py-2 rounded-xl bg-[#171B20] hover:bg-[#232931] border border-[#232931] text-xs font-semibold text-[#F5F5F5] flex items-center gap-1.5 transition-colors">
                <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-[#D4A017]"></i>
                Dashboard
            </a>

            <button 
                wire:click="exportCsv" 
                type="button" 
                class="px-3.5 py-2 rounded-xl bg-[#171B20] hover:bg-emerald-500/10 border border-[#232931] hover:border-emerald-500/30 text-xs font-mono text-emerald-400 flex items-center gap-1.5 transition-colors"
            >
                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                <span>Export CSV</span>
            </button>

            @if($readings->total() > 0)
                <button 
                    wire:click="clearHistory" 
                    wire:confirm="Are you sure you want to clear all telemetry records for this router? This action cannot be undone."
                    type="button" 
                    class="px-3.5 py-2 rounded-xl bg-[#171B20] hover:bg-rose-500/10 border border-[#232931] hover:border-rose-500/30 text-xs font-mono text-rose-400 flex items-center gap-1.5 transition-colors"
                >
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    <span>Clear Log</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Filter & Search Bento Toolbar -->
    <div class="p-4 sm:p-5 rounded-2xl bg-[#111418] border border-[#232931] shadow-lg flex flex-col md:flex-row gap-4 justify-between items-stretch md:items-center">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Router Filter -->
            @if($routers->count() > 1)
                <div>
                    <select wire:model.live="selectedRouterId" class="px-3 py-2 rounded-xl bg-[#0B0D0F] border border-[#232931] text-xs font-mono text-white focus:border-[#D4A017] focus:outline-none">
                        <option value="">All Routers</option>
                        @foreach($routers as $r)
                            <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->model }})</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Timeframe Pills -->
            <div class="flex items-center gap-1 bg-[#0B0D0F] p-1 rounded-xl border border-[#232931] overflow-x-auto text-xs font-mono">
                @foreach(['all' => 'ALL', '15m' => '15M', '30m' => '30M', '1h' => '1H', '6h' => '6H', '24h' => '24H', '7d' => '7D'] as $tfKey => $tfText)
                    <button 
                        wire:click="$set('timeframe', '{{ $tfKey }}')" 
                        type="button" 
                        class="px-2.5 py-1 rounded-lg transition-colors @if($timeframe === $tfKey) bg-[#D4A017] text-[#0B0D0F] font-bold @else text-[#9CA3AF] hover:text-white @endif"
                    >
                        {{ $tfText }}
                    </button>
                @endforeach
            </div>

            <!-- Quality Rating Filter -->
            <div>
                <select wire:model.live="qualityFilter" class="px-3 py-2 rounded-xl bg-[#0B0D0F] border border-[#232931] text-xs font-mono text-white focus:border-[#D4A017] focus:outline-none">
                    <option value="all">All Ratings</option>
                    <option value="EXCELLENT">Excellent</option>
                    <option value="VERY GOOD">Very Good</option>
                    <option value="GOOD">Good</option>
                    <option value="FAIR">Fair</option>
                    <option value="POOR">Poor</option>
                </select>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="relative min-w-[240px]">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#9CA3AF]">
                <i data-lucide="search" class="w-3.5 h-3.5"></i>
            </div>
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Search Cell ID, eNodeB, Band, PCI..." 
                class="w-full pl-9 pr-4 py-2 rounded-xl bg-[#0B0D0F] border border-[#232931] text-xs font-mono text-white placeholder-gray-600 focus:border-[#D4A017] focus:outline-none"
            >
        </div>
    </div>

    <!-- MOBILE VIEW (Single-Column Cards on Screens < 768px) -->
    <div class="block md:hidden space-y-4">
        @forelse($readings as $r)
            <div class="p-4 rounded-2xl bg-[#111418] border border-[#232931] shadow-lg space-y-3">
                <div class="flex items-center justify-between border-b border-[#171B20] pb-2.5">
                    <div class="flex items-center gap-2">
                        <x-status-badge :status="$r->overall_quality" size="sm" />
                        <span class="text-xs font-mono font-bold text-white">{{ $r->recorded_at->format('M d, H:i:s') }}</span>
                    </div>
                    <button 
                        wire:click="deleteReading({{ $r->id }})" 
                        wire:confirm="Delete this telemetry entry?"
                        type="button" 
                        class="p-1.5 rounded-lg bg-[#0B0D0F] text-[#6B7280] hover:text-rose-400"
                    >
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    </button>
                </div>

                <!-- Primary Metrics 2x2 Grid -->
                <div class="grid grid-cols-2 gap-2 text-xs font-mono">
                    <div class="p-2 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                        <span class="text-[10px] text-[#6B7280] uppercase block">RSRP (Power)</span>
                        <span class="text-sm font-bold @if($r->rsrp >= -90) text-emerald-400 @elseif($r->rsrp >= -105) text-[#F2C94C] @else text-rose-400 @endif">
                            {{ $r->rsrp }} dBm
                        </span>
                    </div>
                    <div class="p-2 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                        <span class="text-[10px] text-[#6B7280] uppercase block">SINR (SNR)</span>
                        <span class="text-sm font-bold @if($r->sinr >= 13) text-emerald-400 @elseif($r->sinr >= 0) text-[#F2C94C] @else text-rose-400 @endif">
                            {{ $r->sinr }} dB
                        </span>
                    </div>
                    <div class="p-2 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                        <span class="text-[10px] text-[#6B7280] uppercase block">RSRQ (Quality)</span>
                        <span class="text-sm font-bold text-white">{{ $r->rsrq }} dB</span>
                    </div>
                    <div class="p-2 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                        <span class="text-[10px] text-[#6B7280] uppercase block">RSSI (Total)</span>
                        <span class="text-sm font-bold text-[#9CA3AF]">{{ $r->rssi }} dBm</span>
                    </div>
                </div>

                <!-- Carrier & Cell Details -->
                <div class="flex items-center justify-between text-[11px] font-mono text-[#9CA3AF] pt-1">
                    <span>{{ $r->band }} ({{ $r->bandwidth }})</span>
                    <span>Cell: {{ $r->cell_id }} • eNB: {{ $r->enodeb }}</span>
                </div>
            </div>
        @empty
            <div class="p-8 text-center rounded-2xl bg-[#111418] border border-dashed border-[#232931] text-[#9CA3AF]">
                <p class="text-xs font-semibold text-white">No telemetry records found</p>
            </div>
        @endforelse

        @if($readings->hasPages())
            <div class="p-4 rounded-2xl bg-[#111418] border border-[#232931]">
                {{ $readings->links() }}
            </div>
        @endif
    </div>

    <!-- DESKTOP & TABLET VIEW (Wide Table on Screens >= 768px) -->
    <div class="hidden md:block rounded-2xl sm:rounded-3xl bg-[#111418] border border-[#232931] shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse font-mono text-xs">
                <thead>
                    <tr class="border-b border-[#232931] bg-[#0B0D0F] text-[#9CA3AF] uppercase text-[10px] tracking-wider select-none">
                        <th class="py-3.5 px-4 cursor-pointer hover:text-white" wire:click="sortByField('recorded_at')">
                            Timestamp @if($sortBy === 'recorded_at') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                        </th>
                        <th class="py-3.5 px-3 cursor-pointer hover:text-white" wire:click="sortByField('rsrp')">
                            RSRP (dBm) @if($sortBy === 'rsrp') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                        </th>
                        <th class="py-3.5 px-3 cursor-pointer hover:text-white" wire:click="sortByField('rssi')">
                            RSSI (dBm) @if($sortBy === 'rssi') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                        </th>
                        <th class="py-3.5 px-3 cursor-pointer hover:text-white" wire:click="sortByField('rsrq')">
                            RSRQ (dB) @if($sortBy === 'rsrq') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                        </th>
                        <th class="py-3.5 px-3 cursor-pointer hover:text-white" wire:click="sortByField('sinr')">
                            SINR (dB) @if($sortBy === 'sinr') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                        </th>
                        <th class="py-3.5 px-3">Quality</th>
                        <th class="py-3.5 px-3">Band / BW</th>
                        <th class="py-3.5 px-3">Cell / eNB</th>
                        <th class="py-3.5 px-3">PCI / EARFCN</th>
                        <th class="py-3.5 px-3">MCS / CQI</th>
                        <th class="py-3.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#171B20]">
                    @forelse($readings as $r)
                        <tr class="hover:bg-[#171B20]/60 transition-colors">
                            <!-- Timestamp -->
                            <td class="py-3 px-4 text-white font-medium whitespace-nowrap">
                                <div>{{ $r->recorded_at->format('M d, H:i:s') }}</div>
                                <div class="text-[10px] text-[#6B7280]">{{ $r->recorded_at->diffForHumans() }}</div>
                            </td>

                            <!-- RSRP -->
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="font-bold @if($r->rsrp >= -90) text-emerald-400 @elseif($r->rsrp >= -105) text-[#F2C94C] @else text-rose-400 @endif">
                                    {{ $r->rsrp }}
                                </span>
                            </td>

                            <!-- RSSI -->
                            <td class="py-3 px-3 text-[#9CA3AF] whitespace-nowrap">
                                {{ $r->rssi }}
                            </td>

                            <!-- RSRQ -->
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="@if($r->rsrq >= -12) text-white @else text-amber-400 @endif">
                                    {{ $r->rsrq }}
                                </span>
                            </td>

                            <!-- SINR -->
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="font-bold @if($r->sinr >= 13) text-emerald-400 @elseif($r->sinr >= 0) text-[#F2C94C] @else text-rose-400 @endif">
                                    {{ $r->sinr }}
                                </span>
                            </td>

                            <!-- Quality Badge -->
                            <td class="py-3 px-3 whitespace-nowrap">
                                <x-status-badge :status="$r->overall_quality" size="sm" />
                            </td>

                            <!-- Band / Bandwidth -->
                            <td class="py-3 px-3 text-white whitespace-nowrap">
                                <span class="text-[#F2C94C] font-semibold">{{ $r->band }}</span>
                                <span class="text-[10px] text-[#9CA3AF]">({{ $r->bandwidth }})</span>
                            </td>

                            <!-- Cell ID / eNodeB -->
                            <td class="py-3 px-3 text-[#9CA3AF] whitespace-nowrap">
                                <span class="text-white">{{ $r->cell_id }}</span> / <span class="text-[11px] text-[#6B7280]">{{ $r->enodeb }}</span>
                            </td>

                            <!-- PCI / EARFCN -->
                            <td class="py-3 px-3 text-[#9CA3AF] whitespace-nowrap">
                                PCI: <span class="text-white">{{ $r->physical_cell_id }}</span>
                                <span class="text-[10px] text-[#6B7280] block">E: {{ $r->earfcn }}</span>
                            </td>

                            <!-- MCS / CQI -->
                            <td class="py-3 px-3 text-[#9CA3AF] whitespace-nowrap">
                                {{ $r->mcs }} / {{ $r->cqi }}
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <button 
                                    wire:click="deleteReading({{ $r->id }})" 
                                    wire:confirm="Delete this telemetry entry?"
                                    type="button" 
                                    title="Delete Record"
                                    class="p-1.5 rounded-lg bg-[#0B0D0F] hover:bg-rose-500/10 text-[#6B7280] hover:text-rose-400 transition-colors"
                                >
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="py-12 text-center text-[#9CA3AF]">
                                <div class="w-10 h-10 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-center mx-auto mb-2 text-[#6B7280]">
                                    <i data-lucide="inbox" class="w-5 h-5"></i>
                                </div>
                                <p class="text-xs font-semibold text-white">No telemetry records match your filter criteria</p>
                                <p class="text-[11px] text-[#6B7280] mt-0.5">Try resetting the timeframe or search parameters</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        @if($readings->hasPages())
            <div class="p-4 border-t border-[#171B20] bg-[#0B0D0F]">
                {{ $readings->links() }}
            </div>
        @endif
    </div>
</div>
