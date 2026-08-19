<div class="space-y-5" 
     @if($autoRefreshInterval === '10s') wire:poll.10s="autoPoll" 
     @elseif($autoRefreshInterval === '30s') wire:poll.30s="autoPoll" 
     @elseif($autoRefreshInterval === '1m') wire:poll.60s="autoPoll" 
     @elseif($autoRefreshInterval === '5m') wire:poll.300s="autoPoll" 
     @endif
>
    <!-- TOP STATUS & CONTROL HEADER -->
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 p-5 sm:p-6 rounded-2xl sm:rounded-3xl bg-[#111418] border border-[#232931] shadow-xl">
        <!-- Router Summary & Quick Switcher -->
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-[#171B20] border border-[#232931] flex items-center justify-center text-[#F2C94C] shadow-inner relative flex-shrink-0">
                <i data-lucide="radio" class="w-6 h-6"></i>
                @if($latest && $isConnected)
                    <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-emerald-500 border-2 border-[#111418] rounded-full animate-pulse"></span>
                @else
                    <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-rose-500 border-2 border-[#111418] rounded-full"></span>
                @endif
            </div>

            <div class="space-y-0.5">
                <div class="flex flex-wrap items-center gap-2">
                    @if($routers->count() > 1)
                        <div class="relative inline-block">
                            <select 
                                wire:model.live="selectedRouterId" 
                                class="bg-[#0B0D0F] border border-[#232931] hover:border-[#D4A017]/50 rounded-xl px-3 py-1 text-sm font-bold text-white focus:outline-none focus:border-[#D4A017] font-sans pr-8 cursor-pointer"
                            >
                                @foreach($routers as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->model }})</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <h1 class="text-base sm:text-lg font-black tracking-tight text-white">{{ $activeRouter->name ?? 'No Router Configured' }}</h1>
                    @endif

                    <!-- Connection Status Badge -->
                    <x-status-badge :status="strtoupper($activeRouter->status ?? 'DISCONNECTED')" size="sm" />

                    <!-- Mobile Network Quick Status Pill -->
                    <div class="px-2.5 py-0.5 rounded-full bg-[#0B0D0F] border border-[#232931] text-xs font-mono flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full {{ ($latest && $isConnected) ? 'bg-emerald-400 animate-pulse' : 'bg-rose-500' }}"></span>
                        <span class="text-[11px] {{ ($latest && $isConnected) ? 'text-emerald-400 font-bold' : 'text-rose-400' }}">
                            WAN: {{ ($latest && $isConnected) ? ($activeRouter->mode_status ?? 'Connected') : 'Disconnected' }}
                        </span>
                    </div>
                </div>

                <p class="text-xs font-mono text-[#9CA3AF] mt-0.5">
                    {{ $activeRouter->model ?? '--' }} • {{ $activeRouter->ip_address ?? '--' }} • 
                    <span class="text-[#F2C94C] font-semibold">
                        @if($latest && $isConnected)
                            {{ $latest->band }} ({{ $latest->bandwidth }}) • {{ $activeRouter->plmn ?? '41311 / Dialog' }}
                        @else
                            No Carrier Link
                        @endif
                    </span>
                </p>
            </div>
        </div>

        <!-- Controls: Connect/Disconnect, Refresh, Auto-refresh, Customize, Settings -->
        <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
            <!-- Mobile Network Connect / Disconnect Quick Button -->
            @if($activeRouter)
                @if($latest && $isConnected)
                    <button 
                        wire:click="toggleMobileNetwork"
                        wire:loading.attr="disabled"
                        type="button"
                        title="Disconnect Cellular Mobile Network"
                        class="px-3 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 text-xs font-mono text-rose-400 flex items-center gap-1.5 transition-all cursor-pointer disabled:opacity-50"
                    >
                        <i data-lucide="power" class="w-3.5 h-3.5" wire:loading.class="animate-spin" wire:target="toggleMobileNetwork"></i>
                        <span>Disconnect WAN</span>
                    </button>
                @else
                    <button 
                        wire:click="toggleMobileNetwork"
                        wire:loading.attr="disabled"
                        type="button"
                        title="Connect Cellular Mobile Network"
                        class="px-3 py-1.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-xs font-mono text-emerald-400 flex items-center gap-1.5 transition-all cursor-pointer disabled:opacity-50"
                    >
                        <i data-lucide="wifi" class="w-3.5 h-3.5" wire:loading.class="animate-spin" wire:target="toggleMobileNetwork"></i>
                        <span>Connect WAN</span>
                    </button>
                @endif
            @endif

            <!-- Last Updated Relative Timer -->
            <div 
                x-data="{
                    secondsAgo: 0,
                    interval: null,
                    init() {
                        this.interval = setInterval(() => { this.secondsAgo++ }, 1000);
                    }
                }"
                class="px-3 py-1.5 rounded-xl bg-[#0B0D0F] border border-[#232931] text-xs font-mono text-[#9CA3AF] hidden lg:flex items-center gap-2"
            >
                <i data-lucide="clock" class="w-3.5 h-3.5 text-[#F2C94C]"></i>
                <span>Updated: <strong class="text-white">{{ ($latest && $isConnected) ? $latest->recorded_at->diffForHumans() : 'Never' }}</strong></span>
            </div>

            <!-- Auto-Refresh Toggle Dropdown with Live Pulse -->
            <div class="relative" x-data="{ open: false }">
                <button 
                    @click="open = !open" 
                    type="button" 
                    class="px-3 py-1.5 rounded-xl bg-[#0B0D0F] hover:bg-[#171B20] border @if($autoRefreshInterval !== 'off') border-[#D4A017]/40 shadow-sm shadow-[#D4A017]/10 @else border-[#232931] @endif text-xs font-mono text-[#9CA3AF] flex items-center gap-1.5 transition-colors cursor-pointer"
                >
                    @if($autoRefreshInterval !== 'off')
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    @endif
                    <span class="text-[10px] text-[#6B7280] uppercase">Auto:</span>
                    <strong class="@if($autoRefreshInterval !== 'off') text-[#F2C94C] @else text-gray-400 @endif">{{ strtoupper($autoRefreshInterval) }}</strong>
                    <i data-lucide="chevron-down" class="w-3 h-3 text-[#9CA3AF]"></i>
                </button>

                <div 
                    x-show="open" 
                    @click.away="open = false" 
                    x-transition 
                    class="absolute right-0 mt-2 w-36 rounded-2xl bg-[#171B20] border border-[#232931] shadow-2xl py-1.5 z-50 text-xs font-mono"
                    style="display: none;"
                >
                    @foreach(['off' => 'OFF (Manual)', '10s' => '10 Seconds', '30s' => '30 Seconds', '1m' => '1 Minute', '5m' => '5 Minutes'] as $val => $txt)
                        <button 
                            type="button" 
                            wire:click="setAutoRefresh('{{ $val }}')" 
                            @click="open = false"
                            class="w-full text-left px-3 py-1.5 hover:bg-[#232931] transition-colors flex items-center justify-between cursor-pointer @if($autoRefreshInterval === $val) text-[#F2C94C] font-bold @else text-[#9CA3AF] @endif"
                        >
                            <span>{{ $txt }}</span>
                            @if($autoRefreshInterval === $val)
                                <i data-lucide="check" class="w-3 h-3 text-[#F2C94C]"></i>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Manual Refresh Button -->
            <button 
                wire:click="refreshData" 
                wire:loading.attr="disabled"
                type="button" 
                title="Refresh Signal Telemetry"
                class="p-2 sm:px-3 sm:py-1.5 rounded-xl bg-[#171B20] hover:bg-[#232931] border border-[#232931] text-xs font-mono text-[#F5F5F5] flex items-center gap-1.5 transition-colors disabled:opacity-50 cursor-pointer"
            >
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-[#F2C94C]" wire:loading.class="animate-spin" wire:target="refreshData"></i>
                <span class="hidden sm:inline">Refresh</span>
            </button>

            <!-- Customize Layout Drag & Drop Toggle -->
            <button 
                wire:click="toggleCustomize" 
                type="button" 
                title="Rearrange Bento Boxes"
                class="p-2 sm:px-3 sm:py-1.5 rounded-xl @if($isCustomizing) bg-[#D4A017] text-[#0B0D0F] font-bold @else bg-[#171B20] hover:bg-[#232931] border border-[#232931] text-[#F5F5F5] @endif text-xs font-mono flex items-center gap-1.5 transition-colors cursor-pointer"
            >
                <i data-lucide="layout-grid" class="w-3.5 h-3.5 @if(!$isCustomizing) text-[#F2C94C] @endif"></i>
                <span class="hidden sm:inline">{{ $isCustomizing ? 'Done Customizing' : 'Drag & Drop Boxes' }}</span>
            </button>

            <!-- Settings Direct Link Button -->
            <a 
                href="{{ route('settings.index') }}" 
                class="p-2 sm:px-3 sm:py-1.5 rounded-xl bg-[#171B20] hover:bg-[#232931] border border-[#232931] text-xs font-mono text-[#F5F5F5] flex items-center gap-1.5 transition-colors"
                title="Thresholds & Settings"
            >
                <i data-lucide="settings" class="w-3.5 h-3.5 text-[#F2C94C]"></i>
                <span class="hidden sm:inline">Settings</span>
            </a>
        </div>
    </div>

    <!-- CUSTOMIZE LAYOUT BANNER -->
    @if($isCustomizing)
        <div class="p-4 rounded-2xl bg-[#D4A017]/10 border border-[#D4A017]/30 flex flex-wrap items-center justify-between gap-3 text-xs font-mono text-[#F2C94C]">
            <div class="flex items-center gap-2.5">
                <i data-lucide="move" class="w-4 h-4 text-[#F2C94C] animate-bounce"></i>
                <span><strong>Drag & Drop Boxes Active:</strong> You can drag <strong>EACH INDIVIDUAL BOX</strong> to any position. Layout is auto-saved.</span>
            </div>
            <div class="flex items-center gap-2">
                <button 
                    wire:click="resetBentoOrder" 
                    type="button" 
                    class="px-3 py-1.5 rounded-xl bg-[#171B20] border border-[#232931] text-[#9CA3AF] hover:text-white transition-colors cursor-pointer"
                >
                    Reset Default Order
                </button>
                <button 
                    wire:click="toggleCustomize" 
                    type="button" 
                    class="px-3.5 py-1.5 rounded-xl bg-[#D4A017] text-[#0B0D0F] font-bold shadow-md cursor-pointer"
                >
                    Save & Finish
                </button>
            </div>
        </div>
    @endif

    <!-- DYNAMIC BENTO GRID CONTAINER WITH SORTABLE DRAG & DROP FOR EACH BOX -->
    <div 
        id="dashboardBentoGrid" 
        class="grid grid-cols-1 md:grid-cols-6 lg:grid-cols-12 gap-5"
        x-data="{
            sortableInstance: null,
            init() {
                this.$nextTick(() => {
                    if (window.initBentoSortable) {
                        this.sortableInstance = window.initBentoSortable('dashboardBentoGrid', (order) => {
                            $wire.updateBentoOrder(order);
                        });
                    }
                });
            }
        }"
    >
        @php
            $rsrpDelta = $isConnected ? $this->getDelta($latest, $previous, 'rsrp') : null;
            $rssiDelta = $isConnected ? $this->getDelta($latest, $previous, 'rssi') : null;
            $rsrqDelta = $isConnected ? $this->getDelta($latest, $previous, 'rsrq') : null;
            $sinrDelta = $isConnected ? $this->getDelta($latest, $previous, 'sinr') : null;
        @endphp

        @foreach($bentoOrder as $cardId)
            @if($cardId === 'card_rsrp')
                <!-- 1. RSRP BOX -->
                <div data-bento-id="card_rsrp" wire:key="box-card_rsrp" class="col-span-1 md:col-span-3 lg:col-span-3 relative group">
                    @if($isCustomizing)
                        <div class="bento-drag-handle absolute -top-3 left-4 z-30 px-2.5 py-0.5 rounded-lg bg-[#D4A017] text-[#0B0D0F] font-mono text-[10px] font-black uppercase flex items-center gap-1 cursor-grab active:cursor-grabbing shadow-lg">
                            <i data-lucide="grip-vertical" class="w-3 h-3"></i>
                            <span>⠿ Move RSRP</span>
                        </div>
                    @endif
                    <x-metric-card 
                        label="RSRP" 
                        value="{{ ($latest && $isConnected) ? $latest->rsrp : '--' }}" 
                        unit="dBm" 
                        status="{{ ($latest && $isConnected) ? $latest->rsrp_quality : 'DISCONNECTED' }}" 
                        description="Signal Strength (Reference Signal Received Power)" 
                        :trend="$rsrpDelta ? $rsrpDelta['text'] . ' dBm' : null" 
                        :trendDirection="$rsrpDelta ? $rsrpDelta['direction'] : 'neutral'"
                    />
                </div>

            @elseif($cardId === 'card_rssi')
                <!-- 2. RSSI BOX -->
                <div data-bento-id="card_rssi" wire:key="box-card_rssi" class="col-span-1 md:col-span-3 lg:col-span-3 relative group">
                    @if($isCustomizing)
                        <div class="bento-drag-handle absolute -top-3 left-4 z-30 px-2.5 py-0.5 rounded-lg bg-[#D4A017] text-[#0B0D0F] font-mono text-[10px] font-black uppercase flex items-center gap-1 cursor-grab active:cursor-grabbing shadow-lg">
                            <i data-lucide="grip-vertical" class="w-3 h-3"></i>
                            <span>⠿ Move RSSI</span>
                        </div>
                    @endif
                    <x-metric-card 
                        label="RSSI" 
                        value="{{ ($latest && $isConnected) ? $latest->rssi : '--' }}" 
                        unit="dBm" 
                        status="{{ ($latest && $isConnected) ? $latest->rssi_quality : 'DISCONNECTED' }}" 
                        description="Total Received Power (Includes signal + carrier noise)" 
                        :trend="$rssiDelta ? $rssiDelta['text'] . ' dBm' : null" 
                        :trendDirection="$rssiDelta ? $rssiDelta['direction'] : 'neutral'"
                    />
                </div>

            @elseif($cardId === 'card_rsrq')
                <!-- 3. RSRQ BOX -->
                <div data-bento-id="card_rsrq" wire:key="box-card_rsrq" class="col-span-1 md:col-span-3 lg:col-span-3 relative group">
                    @if($isCustomizing)
                        <div class="bento-drag-handle absolute -top-3 left-4 z-30 px-2.5 py-0.5 rounded-lg bg-[#D4A017] text-[#0B0D0F] font-mono text-[10px] font-black uppercase flex items-center gap-1 cursor-grab active:cursor-grabbing shadow-lg">
                            <i data-lucide="grip-vertical" class="w-3 h-3"></i>
                            <span>⠿ Move RSRQ</span>
                        </div>
                    @endif
                    <x-metric-card 
                        label="RSRQ" 
                        value="{{ ($latest && $isConnected) ? $latest->rsrq : '--' }}" 
                        unit="dB" 
                        status="{{ ($latest && $isConnected) ? $latest->rsrq_quality : 'DISCONNECTED' }}" 
                        description="Signal Quality (Reference Signal Received Quality)" 
                        :trend="$rsrqDelta ? $rsrqDelta['text'] . ' dB' : null" 
                        :trendDirection="$rsrqDelta ? $rsrqDelta['direction'] : 'neutral'"
                    />
                </div>

            @elseif($cardId === 'card_sinr')
                <!-- 4. SINR BOX -->
                <div data-bento-id="card_sinr" wire:key="box-card_sinr" class="col-span-1 md:col-span-3 lg:col-span-3 relative group">
                    @if($isCustomizing)
                        <div class="bento-drag-handle absolute -top-3 left-4 z-30 px-2.5 py-0.5 rounded-lg bg-[#D4A017] text-[#0B0D0F] font-mono text-[10px] font-black uppercase flex items-center gap-1 cursor-grab active:cursor-grabbing shadow-lg">
                            <i data-lucide="grip-vertical" class="w-3 h-3"></i>
                            <span>⠿ Move SINR</span>
                        </div>
                    @endif
                    <x-metric-card 
                        label="SINR" 
                        value="{{ ($latest && $isConnected) ? $latest->sinr : '--' }}" 
                        unit="dB" 
                        status="{{ ($latest && $isConnected) ? $latest->sinr_quality : 'DISCONNECTED' }}" 
                        description="Signal to Noise Ratio (Cleanliness of signal link)" 
                        :trend="$sinrDelta ? $sinrDelta['text'] . ' dB' : null" 
                        :trendDirection="$sinrDelta ? $sinrDelta['direction'] : 'neutral'"
                    />
                </div>

            @elseif($cardId === 'card_chart')
                <!-- 5. LIVE SIGNAL TELEMETRY CHART BOX -->
                <div data-bento-id="card_chart" wire:key="box-card_chart" class="col-span-1 md:col-span-6 lg:col-span-12 relative group">
                    @if($isCustomizing)
                        <div class="bento-drag-handle absolute -top-3 left-4 z-30 px-2.5 py-0.5 rounded-lg bg-[#D4A017] text-[#0B0D0F] font-mono text-[10px] font-black uppercase flex items-center gap-1 cursor-grab active:cursor-grabbing shadow-lg">
                            <i data-lucide="grip-vertical" class="w-3 h-3"></i>
                            <span>⠿ Move Telemetry Chart</span>
                        </div>
                    @endif
                    <livewire:charts.signal-chart :router-id="$selectedRouterId" wire:key="signal-chart-{{ $selectedRouterId }}" />
                </div>

            @elseif($cardId === 'card_mobile_network')
                <!-- 6. MOBILE NETWORK SERVICE STATE BOX -->
                <div data-bento-id="card_mobile_network" wire:key="box-card_mobile_network" class="col-span-1 md:col-span-3 lg:col-span-4 relative group">
                    @if($isCustomizing)
                        <div class="bento-drag-handle absolute -top-3 left-4 z-30 px-2.5 py-0.5 rounded-lg bg-[#D4A017] text-[#0B0D0F] font-mono text-[10px] font-black uppercase flex items-center gap-1 cursor-grab active:cursor-grabbing shadow-lg">
                            <i data-lucide="grip-vertical" class="w-3 h-3"></i>
                            <span>⠿ Move Mobile Network</span>
                        </div>
                    @endif
                    <x-bento-card title="Mobile Network" subtitle="Cellular service & registration state" icon="radio">
                        <div class="space-y-3 font-mono text-xs">
                            <div class="grid grid-cols-2 gap-2.5">
                                <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                                    <span class="text-[10px] text-[#9CA3AF] uppercase block">Mode Status</span>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="w-2 h-2 rounded-full {{ ($latest && $isConnected) ? 'bg-emerald-400 animate-pulse' : 'bg-rose-500' }}"></span>
                                        <span class="text-xs font-bold {{ ($latest && $isConnected) ? 'text-emerald-400' : 'text-rose-400' }}">
                                            {{ ($latest && $isConnected) ? ($activeRouter->mode_status ?? 'Connected') : 'Disconnected' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                                    <span class="text-[10px] text-[#9CA3AF] uppercase block">Network Mode</span>
                                    <span class="text-xs font-bold text-[#F2C94C]">{{ ($latest && $isConnected) ? ($activeRouter->network_mode ?? '4G') : '--' }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2.5">
                                <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                                    <span class="text-[10px] text-[#9CA3AF] uppercase block">Signal Strength</span>
                                    <span class="text-xs font-bold text-white">{{ ($latest && $isConnected) ? $latest->rsrp . ' dBm' : '--' }}</span>
                                </div>
                                <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                                    <span class="text-[10px] text-[#9CA3AF] uppercase block">Signal Quality</span>
                                    <span class="text-xs font-bold text-white">{{ ($latest && $isConnected) ? $latest->rsrq . ' dB' : '--' }}</span>
                                </div>
                            </div>

                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between">
                                <span class="text-[10px] text-[#9CA3AF] uppercase">CS Status</span>
                                <span class="text-xs font-bold text-white">{{ ($latest && $isConnected) ? ($activeRouter->cs_status ?? 'No Service') : '--' }}</span>
                            </div>

                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between">
                                <span class="text-[10px] text-[#9CA3AF] uppercase">PLMN Operator</span>
                                <span class="text-xs font-bold text-[#F2C94C]">{{ ($latest && $isConnected) ? ($activeRouter->plmn ?? '41311 / Dialog') : '--' }}</span>
                            </div>

                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931] space-y-1">
                                <div class="flex justify-between items-center text-[10px] text-[#9CA3AF] uppercase">
                                    <span>PS Status:</span>
                                    <span class="text-emerald-400 font-bold">{{ ($latest && $isConnected) ? ($activeRouter->ps_status ?? 'Registered, local network') : '--' }}</span>
                                </div>
                                <div class="flex justify-between items-center text-[10px] text-[#9CA3AF] uppercase pt-1 border-t border-[#171B20]">
                                    <span>EPS Status:</span>
                                    <span class="text-emerald-400 font-bold">{{ ($latest && $isConnected) ? ($activeRouter->eps_status ?? 'Registered, local network') : '--' }}</span>
                                </div>
                            </div>
                        </div>
                    </x-bento-card>
                </div>

            @elseif($cardId === 'card_running_status')
                <!-- 7. RUNNING STATUS & UPTIME BOX -->
                <div data-bento-id="card_running_status" wire:key="box-card_running_status" class="col-span-1 md:col-span-3 lg:col-span-4 relative group">
                    @if($isCustomizing)
                        <div class="bento-drag-handle absolute -top-3 left-4 z-30 px-2.5 py-0.5 rounded-lg bg-[#D4A017] text-[#0B0D0F] font-mono text-[10px] font-black uppercase flex items-center gap-1 cursor-grab active:cursor-grabbing shadow-lg">
                            <i data-lucide="grip-vertical" class="w-3 h-3"></i>
                            <span>⠿ Move Running Status</span>
                        </div>
                    @endif
                    <x-bento-card title="Running Status" subtitle="Router system uptime & operational load" icon="activity">
                        <div class="space-y-3 font-mono text-xs">
                            <div class="p-3 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] text-[#9CA3AF] uppercase block">Running Time (Uptime)</span>
                                    <span class="text-sm font-bold text-white">{{ ($latest && $isConnected) ? ($activeRouter->system_uptime ?? '--') : '--' }}</span>
                                </div>
                                <i data-lucide="clock" class="w-5 h-5 text-[#F2C94C]"></i>
                            </div>

                            <div class="p-3 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] text-[#9CA3AF] uppercase block">Connection Time (WAN Link)</span>
                                    <span class="text-sm font-bold text-emerald-400">{{ ($latest && $isConnected) ? ($activeRouter->connection_time ?? '--') : '--' }}</span>
                                </div>
                                <i data-lucide="wifi" class="w-5 h-5 text-emerald-400"></i>
                            </div>

                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                                <span class="text-[10px] text-[#9CA3AF] uppercase block mb-1">Average Load (1m, 5m, 15m)</span>
                                <span class="text-xs font-bold text-[#F2C94C]">{{ ($latest && $isConnected) ? ($activeRouter->load_average ?? '0.00, 0.00, 0.00') : '--' }}</span>
                            </div>

                            <div class="grid grid-cols-2 gap-2.5">
                                <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                                    <span class="text-[10px] text-[#9CA3AF] uppercase block">WAN IP</span>
                                    <span class="text-xs font-bold text-white truncate block">{{ ($latest && $isConnected) ? ($activeRouter->wan_ip ?? '--') : '--' }}</span>
                                </div>
                                <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                                    <span class="text-[10px] text-[#9CA3AF] uppercase block">Gateway</span>
                                    <span class="text-xs font-bold text-white truncate block">{{ ($latest && $isConnected) ? ($activeRouter->wan_gateway ?? '--') : '--' }}</span>
                                </div>
                            </div>

                            <div class="p-2 rounded-xl bg-[#171B20]/60 border border-[#232931] text-[11px] text-[#9CA3AF] truncate">
                                <span class="text-[#6B7280]">DNS: </span>{{ ($latest && $isConnected) ? ($activeRouter->wan_dns ?? '--') : '--' }}
                            </div>
                        </div>
                    </x-bento-card>
                </div>

            @elseif($cardId === 'card_signal_quality')
                <!-- 8. OVERALL SIGNAL QUALITY & SCORE BOX -->
                <div data-bento-id="card_signal_quality" wire:key="box-card_signal_quality" class="col-span-1 md:col-span-3 lg:col-span-4 relative group">
                    @if($isCustomizing)
                        <div class="bento-drag-handle absolute -top-3 left-4 z-30 px-2.5 py-0.5 rounded-lg bg-[#D4A017] text-[#0B0D0F] font-mono text-[10px] font-black uppercase flex items-center gap-1 cursor-grab active:cursor-grabbing shadow-lg">
                            <i data-lucide="grip-vertical" class="w-3 h-3"></i>
                            <span>⠿ Move Signal Score</span>
                        </div>
                    @endif
                    <x-bento-card title="Signal Quality & Health" subtitle="Calculated connection score" icon="shield-check" :glow="true">
                        <div class="space-y-4">
                            <div class="p-4 rounded-2xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] uppercase font-mono text-[#9CA3AF] block mb-1">Overall Rating</span>
                                    <x-status-badge :status="($latest && $isConnected) ? $latest->overall_quality : 'DISCONNECTED'" size="lg" />
                                </div>
                                <div class="text-right font-mono">
                                    <span class="text-3xl font-black text-[#F2C94C]">{{ ($latest && $isConnected) ? $latest->signal_score : 0 }}<span class="text-xs text-[#9CA3AF]">/100</span></span>
                                    <span class="text-[10px] text-[#9CA3AF] block">Connection Score</span>
                                </div>
                            </div>

                            <!-- Interpretation Summary -->
                            <div class="p-3.5 rounded-xl bg-[#171B20]/60 border border-[#232931] space-y-1.5">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-[#F2C94C] font-mono flex items-center gap-1.5">
                                    <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                                    {{ $interpretation['headline'] }}
                                </span>
                                <p class="text-xs text-[#9CA3AF] leading-relaxed">
                                    {{ $interpretation['explanation'] }}
                                </p>
                            </div>
                        </div>
                    </x-bento-card>
                </div>

            @elseif($cardId === 'card_signal_gauges')
                <!-- 9. SIGNAL GAUGES BOX -->
                <div data-bento-id="card_signal_gauges" wire:key="box-card_signal_gauges" class="col-span-1 md:col-span-3 lg:col-span-4 relative group">
                    @if($isCustomizing)
                        <div class="bento-drag-handle absolute -top-3 left-4 z-30 px-2.5 py-0.5 rounded-lg bg-[#D4A017] text-[#0B0D0F] font-mono text-[10px] font-black uppercase flex items-center gap-1 cursor-grab active:cursor-grabbing shadow-lg">
                            <i data-lucide="grip-vertical" class="w-3 h-3"></i>
                            <span>⠿ Move Signal Gauges</span>
                        </div>
                    @endif
                    <x-bento-card title="Signal Gauges" subtitle="Normalized radio telemetry meters" icon="gauge">
                        <div class="space-y-4">
                            <x-signal-gauge 
                                label="RSRP" 
                                value="{{ ($latest && $isConnected) ? $latest->rsrp : '--' }}" 
                                unit="dBm" 
                                percentage="{{ ($latest && $isConnected) ? $latest->rsrp_percentage : 0 }}" 
                                status="{{ ($latest && $isConnected) ? $latest->rsrp_quality : 'DISCONNECTED' }}" 
                                min="-120" 
                                max="-70" 
                            />

                            <x-signal-gauge 
                                label="RSRQ" 
                                value="{{ ($latest && $isConnected) ? $latest->rsrq : '--' }}" 
                                unit="dB" 
                                percentage="{{ ($latest && $isConnected) ? $latest->rsrq_percentage : 0 }}" 
                                status="{{ ($latest && $isConnected) ? $latest->rsrq_quality : 'DISCONNECTED' }}" 
                                min="-20" 
                                max="-3" 
                            />

                            <x-signal-gauge 
                                label="SINR" 
                                value="{{ ($latest && $isConnected) ? $latest->sinr : '--' }}" 
                                unit="dB" 
                                percentage="{{ ($latest && $isConnected) ? $latest->sinr_percentage : 0 }}" 
                                status="{{ ($latest && $isConnected) ? $latest->sinr_quality : 'DISCONNECTED' }}" 
                                min="-5" 
                                max="30" 
                            />
                        </div>
                    </x-bento-card>
                </div>

            @elseif($cardId === 'card_lte_connection')
                <!-- 10. LTE CARRIER CONNECTION BOX -->
                <div data-bento-id="card_lte_connection" wire:key="box-card_lte_connection" class="col-span-1 md:col-span-3 lg:col-span-4 relative group">
                    @if($isCustomizing)
                        <div class="bento-drag-handle absolute -top-3 left-4 z-30 px-2.5 py-0.5 rounded-lg bg-[#D4A017] text-[#0B0D0F] font-mono text-[10px] font-black uppercase flex items-center gap-1 cursor-grab active:cursor-grabbing shadow-lg">
                            <i data-lucide="grip-vertical" class="w-3 h-3"></i>
                            <span>⠿ Move LTE Connection</span>
                        </div>
                    @endif
                    <x-bento-card title="LTE Connection" subtitle="Active carrier radio link parameters" icon="radio">
                        <div class="grid grid-cols-2 gap-2.5 text-xs font-mono">
                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                                <span class="text-[10px] text-[#9CA3AF] uppercase block">Carrier Band</span>
                                <span class="text-sm font-bold text-[#F2C94C]">{{ ($latest && $isConnected) ? $latest->band : '--' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                                <span class="text-[10px] text-[#9CA3AF] uppercase block">Bandwidth</span>
                                <span class="text-sm font-bold text-white">{{ ($latest && $isConnected) ? $latest->bandwidth : '--' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                                <span class="text-[10px] text-[#9CA3AF] uppercase block">EARFCN</span>
                                <span class="text-sm font-bold text-white">{{ ($latest && $isConnected) ? $latest->earfcn : '--' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                                <span class="text-[10px] text-[#9CA3AF] uppercase block">RRC State</span>
                                <span class="text-sm font-bold {{ $isConnected ? 'text-emerald-400' : 'text-gray-500' }}">{{ ($latest && $isConnected) ? $latest->rrc_state : 'Disconnected' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                                <span class="text-[10px] text-[#9CA3AF] uppercase block">Trans. Mode</span>
                                <span class="text-sm font-bold text-white">{{ ($latest && $isConnected) ? $latest->transmission_mode : '--' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                                <span class="text-[10px] text-[#9CA3AF] uppercase block">TX Power</span>
                                <span class="text-sm font-bold text-white">{{ ($latest && $isConnected) ? $latest->tx_power . ' dBm' : '--' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                                <span class="text-[10px] text-[#9CA3AF] uppercase block">MCS Index</span>
                                <span class="text-sm font-bold text-white">{{ ($latest && $isConnected) ? $latest->mcs : '--' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                                <span class="text-[10px] text-[#9CA3AF] uppercase block">CQI Rating</span>
                                <span class="text-sm font-bold text-white">{{ ($latest && $isConnected) ? $latest->cqi . ' / 15' : '--' }}</span>
                            </div>
                        </div>
                    </x-bento-card>
                </div>

            @elseif($cardId === 'card_cell_tower')
                <!-- 11. CELL TOWER INFO BOX -->
                <div data-bento-id="card_cell_tower" wire:key="box-card_cell_tower" class="col-span-1 md:col-span-3 lg:col-span-4 relative group">
                    @if($isCustomizing)
                        <div class="bento-drag-handle absolute -top-3 left-4 z-30 px-2.5 py-0.5 rounded-lg bg-[#D4A017] text-[#0B0D0F] font-mono text-[10px] font-black uppercase flex items-center gap-1 cursor-grab active:cursor-grabbing shadow-lg">
                            <i data-lucide="grip-vertical" class="w-3 h-3"></i>
                            <span>⠿ Move Cell Tower</span>
                        </div>
                    @endif
                    <x-bento-card title="Cell Tower Information" subtitle="Click any diagnostic value to copy" icon="tower-control">
                        <x-cell-info-card 
                            enodeb="{{ ($latest && $isConnected) ? $latest->enodeb : '--' }}" 
                            cell="{{ ($latest && $isConnected) ? $latest->cell_id : '--' }}" 
                            globalCellId="{{ ($latest && $isConnected) ? $latest->global_cell_id : '--' }}" 
                            pci="{{ ($latest && $isConnected) ? $latest->physical_cell_id : '--' }}" 
                            earfcn="{{ ($latest && $isConnected) ? $latest->earfcn : '--' }}" 
                            band="{{ ($latest && $isConnected) ? $latest->band : '--' }}" 
                        />
                    </x-bento-card>
                </div>

            @elseif($cardId === 'card_device_info')
                <!-- 12. DEVICE & VERSION INFORMATION BOX -->
                <div data-bento-id="card_device_info" wire:key="box-card_device_info" class="col-span-1 md:col-span-3 lg:col-span-4 relative group">
                    @if($isCustomizing)
                        <div class="bento-drag-handle absolute -top-3 left-4 z-30 px-2.5 py-0.5 rounded-lg bg-[#D4A017] text-[#0B0D0F] font-mono text-[10px] font-black uppercase flex items-center gap-1 cursor-grab active:cursor-grabbing shadow-lg">
                            <i data-lucide="grip-vertical" class="w-3 h-3"></i>
                            <span>⠿ Move Device Specs</span>
                        </div>
                    @endif
                    <x-bento-card title="Device & Version Information" subtitle="Hardware model & firmware specifications" icon="cpu">
                        <div class="space-y-2.5 text-xs font-mono">
                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between">
                                <span class="text-[10px] text-[#9CA3AF] uppercase">Item / Model</span>
                                <span class="font-bold text-white">{{ $activeRouter->model ?? '--' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between">
                                <span class="text-[10px] text-[#9CA3AF] uppercase">Build Date / Time</span>
                                <span class="font-bold text-[#F2C94C]">{{ $activeRouter->build_date ?? '--' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between">
                                <span class="text-[10px] text-[#9CA3AF] uppercase">Firmware Version</span>
                                <span class="font-bold text-white">{{ $activeRouter->firmware_version ?? '--' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between">
                                <span class="text-[10px] text-[#9CA3AF] uppercase">Hardware Version</span>
                                <span class="font-bold text-white">{{ $activeRouter->hardware_version ?? '--' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between">
                                <span class="text-[10px] text-[#9CA3AF] uppercase">Configuration Ver.</span>
                                <span class="font-bold text-white">{{ $activeRouter->config_version ?? '--' }}</span>
                            </div>
                        </div>
                    </x-bento-card>
                </div>

            @elseif($cardId === 'card_modem_info')
                <!-- 13. MODEM INFORMATION BOX -->
                <div data-bento-id="card_modem_info" wire:key="box-card_modem_info" class="col-span-1 md:col-span-3 lg:col-span-4 relative group">
                    @if($isCustomizing)
                        <div class="bento-drag-handle absolute -top-3 left-4 z-30 px-2.5 py-0.5 rounded-lg bg-[#D4A017] text-[#0B0D0F] font-mono text-[10px] font-black uppercase flex items-center gap-1 cursor-grab active:cursor-grabbing shadow-lg">
                            <i data-lucide="grip-vertical" class="w-3 h-3"></i>
                            <span>⠿ Move Modem Info</span>
                        </div>
                    @endif
                    <x-bento-card title="Modem Information" subtitle="LTE module & carrier SIM identities" icon="shield">
                        <div class="space-y-2.5 text-xs font-mono">
                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between">
                                <span class="text-[10px] text-[#9CA3AF] uppercase">LTE Version</span>
                                <span class="font-bold text-[#F2C94C] truncate">{{ $activeRouter->modem_version ?? '--' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between">
                                <span class="text-[10px] text-[#9CA3AF] uppercase">IMEI</span>
                                <span class="font-bold text-white">{{ $activeRouter ? $activeRouter->masked_imei : '------------' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between">
                                <span class="text-[10px] text-[#9CA3AF] uppercase">IMSI</span>
                                <span class="font-bold text-white">{{ $activeRouter ? $activeRouter->masked_imsi : '------------' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between">
                                <span class="text-[10px] text-[#9CA3AF] uppercase">ICCID</span>
                                <span class="font-bold text-white">{{ $activeRouter ? $activeRouter->masked_iccid : '------------' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between">
                                <span class="text-[10px] text-[#9CA3AF] uppercase">LAN MAC</span>
                                <span class="font-bold text-white">{{ $activeRouter ? $activeRouter->masked_mac : '--:--:--:--:--:--' }}</span>
                            </div>
                        </div>
                    </x-bento-card>
                </div>

            @elseif($cardId === 'card_interpretation')
                <!-- 14. "WHAT DOES THIS MEAN?" BOX -->
                <div data-bento-id="card_interpretation" wire:key="box-card_interpretation" class="col-span-1 md:col-span-3 lg:col-span-4 relative group">
                    @if($isCustomizing)
                        <div class="bento-drag-handle absolute -top-3 left-4 z-30 px-2.5 py-0.5 rounded-lg bg-[#D4A017] text-[#0B0D0F] font-mono text-[10px] font-black uppercase flex items-center gap-1 cursor-grab active:cursor-grabbing shadow-lg">
                            <i data-lucide="grip-vertical" class="w-3 h-3"></i>
                            <span>⠿ Move Plain-English Guide</span>
                        </div>
                    @endif
                    <x-bento-card title="What Does This Mean?" subtitle="Plain-English connection diagnostics" icon="help-circle">
                        <div class="space-y-2.5 text-xs">
                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between">
                                <span class="text-[#9CA3AF] font-mono">Signal Strength (RSRP):</span>
                                <strong class="text-white font-mono">{{ ($latest && $isConnected) ? $latest->rsrp_quality : 'DISCONNECTED' }}</strong>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between">
                                <span class="text-[#9CA3AF] font-mono">Signal Quality (RSRQ):</span>
                                <strong class="text-white font-mono">{{ ($latest && $isConnected) ? $latest->rsrq_quality : 'DISCONNECTED' }}</strong>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between">
                                <span class="text-[#9CA3AF] font-mono">RF Noise / Interf. (SINR):</span>
                                <strong class="text-[#F2C94C] font-mono">{{ $interpretation['interference_status'] }}</strong>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#171B20] border border-[#232931] flex items-center justify-between">
                                <span class="text-[#9CA3AF] font-mono">Overall Link Health:</span>
                                <strong class="{{ $isConnected ? 'text-emerald-400' : 'text-gray-500' }} font-mono">{{ $interpretation['overall_health'] }}</strong>
                            </div>
                        </div>
                    </x-bento-card>
                </div>

            @elseif($cardId === 'card_event_timeline')
                <!-- 15. CONNECTION & NETWORK EVENT TIMELINE BOX -->
                <div data-bento-id="card_event_timeline" wire:key="box-card_event_timeline" class="col-span-1 md:col-span-6 lg:col-span-12 relative group">
                    @if($isCustomizing)
                        <div class="bento-drag-handle absolute -top-3 left-4 z-30 px-2.5 py-0.5 rounded-lg bg-[#D4A017] text-[#0B0D0F] font-mono text-[10px] font-black uppercase flex items-center gap-1 cursor-grab active:cursor-grabbing shadow-lg">
                            <i data-lucide="grip-vertical" class="w-3 h-3"></i>
                            <span>⠿ Move Event Timeline</span>
                        </div>
                    @endif
                    <livewire:events.event-timeline :router-id="$selectedRouterId" wire:key="event-timeline-{{ $selectedRouterId }}" />
                </div>

            @elseif($cardId === 'card_statistical_aggregates')
                <!-- 16. STATISTICAL AGGREGATES BOX -->
                @if($statistics && $isConnected)
                    <div data-bento-id="card_statistical_aggregates" wire:key="box-card_statistical_aggregates" class="col-span-1 md:col-span-6 lg:col-span-12 relative group">
                        @if($isCustomizing)
                            <div class="bento-drag-handle absolute -top-3 left-4 z-30 px-2.5 py-0.5 rounded-lg bg-[#D4A017] text-[#0B0D0F] font-mono text-[10px] font-black uppercase flex items-center gap-1 cursor-grab active:cursor-grabbing shadow-lg">
                                <i data-lucide="grip-vertical" class="w-3 h-3"></i>
                                <span>⠿ Move Statistics Box</span>
                            </div>
                        @endif

                        <div class="p-5 sm:p-6 rounded-2xl sm:rounded-3xl bg-[#111418] border border-[#232931] shadow-xl space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-[#171B20] border border-[#232931] flex items-center justify-center text-[#F2C94C]">
                                        <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#9CA3AF] font-mono">
                                            Historical Telemetry Aggregates ({{ strtoupper($timeframe) }})
                                        </h3>
                                        <p class="text-[11px] text-[#6B7280]">Calculated across {{ $statistics['count'] }} stored telemetry samples</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 text-xs font-mono">
                                    @foreach(['15m' => '15M', '30m' => '30M', '1h' => '1H', '24h' => '24H'] as $tfKey => $tfLabel)
                                        <button 
                                            wire:click="$set('timeframe', '{{ $tfKey }}')" 
                                            type="button" 
                                            class="px-2.5 py-1 rounded-lg text-xs font-mono transition-colors cursor-pointer @if($timeframe === $tfKey) bg-[#D4A017] text-[#0B0D0F] font-bold @else bg-[#0B0D0F] text-[#9CA3AF] hover:text-white border border-[#232931] @endif"
                                        >
                                            {{ $tfLabel }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Stats Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 font-mono text-xs">
                                <!-- RSRP Stats -->
                                <div class="p-4 rounded-2xl bg-[#0B0D0F] border border-[#232931] space-y-2">
                                    <div class="flex justify-between items-center text-[#9CA3AF] uppercase">
                                        <span class="font-bold text-white">RSRP (dBm)</span>
                                        <span class="text-[10px]">Signal Strength</span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 pt-1 border-t border-[#171B20]">
                                        <div>
                                            <span class="text-[10px] text-[#6B7280] block">Best</span>
                                            <span class="text-sm font-bold text-emerald-400">{{ $statistics['rsrp']['best'] }}</span>
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-[#6B7280] block">Worst</span>
                                            <span class="text-sm font-bold text-rose-400">{{ $statistics['rsrp']['worst'] }}</span>
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-[#6B7280] block">Average</span>
                                            <span class="text-sm font-bold text-white">{{ $statistics['rsrp']['avg'] }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- SINR Stats -->
                                <div class="p-4 rounded-2xl bg-[#0B0D0F] border border-[#232931] space-y-2">
                                    <div class="flex justify-between items-center text-[#9CA3AF] uppercase">
                                        <span class="font-bold text-white">SINR (dB)</span>
                                        <span class="text-[10px]">Signal Noise Ratio</span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 pt-1 border-t border-[#171B20]">
                                        <div>
                                            <span class="text-[10px] text-[#6B7280] block">Best</span>
                                            <span class="text-sm font-bold text-emerald-400">{{ $statistics['sinr']['best'] }}</span>
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-[#6B7280] block">Worst</span>
                                            <span class="text-sm font-bold text-rose-400">{{ $statistics['sinr']['worst'] }}</span>
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-[#6B7280] block">Average</span>
                                            <span class="text-sm font-bold text-white">{{ $statistics['sinr']['avg'] }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- RSRQ Stats -->
                                <div class="p-4 rounded-2xl bg-[#0B0D0F] border border-[#232931] space-y-2">
                                    <div class="flex justify-between items-center text-[#9CA3AF] uppercase">
                                        <span class="font-bold text-white">RSRQ (dB)</span>
                                        <span class="text-[10px]">Signal Quality</span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 pt-1 border-t border-[#171B20]">
                                        <div>
                                            <span class="text-[10px] text-[#6B7280] block">Best</span>
                                            <span class="text-sm font-bold text-emerald-400">{{ $statistics['rsrq']['best'] }}</span>
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-[#6B7280] block">Worst</span>
                                            <span class="text-sm font-bold text-rose-400">{{ $statistics['rsrq']['worst'] }}</span>
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-[#6B7280] block">Average</span>
                                            <span class="text-sm font-bold text-white">{{ $statistics['rsrq']['avg'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        @endforeach
    </div>
</div>
