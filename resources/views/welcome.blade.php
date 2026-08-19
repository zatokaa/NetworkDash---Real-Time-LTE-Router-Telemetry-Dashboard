@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Top Action & Router Header Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-5 rounded-2xl bg-[#111418] border border-[#232931]">
        <!-- Router Info & Active Status -->
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-[#171B20] border border-[#232931] flex items-center justify-center text-[#F2C94C] shadow-inner">
                <i data-lucide="router" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-bold text-white">Home LTE Router</h2>
                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                        ● CONNECTED
                    </span>
                </div>
                <p class="text-xs font-mono text-[#9CA3AF]">ZLT P11X • 192.168.8.1 • B40 (20 MHz)</p>
            </div>
        </div>

        <!-- Real-time Controls -->
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <div class="px-3 py-1.5 rounded-xl bg-[#0B0D0F] border border-[#232931] text-xs font-mono text-[#9CA3AF] flex items-center gap-2">
                <i data-lucide="clock" class="w-3.5 h-3.5 text-[#F2C94C]"></i>
                <span>Updated: <strong class="text-white">12s ago</strong></span>
            </div>

            <button type="button" class="px-3.5 py-1.5 rounded-xl bg-[#171B20] hover:bg-[#232931] border border-[#232931] text-xs font-mono text-[#F5F5F5] flex items-center gap-1.5 transition-colors">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-[#F2C94C]"></i>
                <span>Refresh</span>
            </button>

            <div class="px-3 py-1.5 rounded-xl bg-[#0B0D0F] border border-[#232931] text-xs font-mono text-[#9CA3AF] flex items-center gap-2">
                <span class="text-[10px] uppercase text-[#6B7280]">Auto:</span>
                <span class="text-[#F2C94C] font-semibold">10s</span>
            </div>
        </div>
    </div>

    <!-- PRIMARY SIGNAL CARDS (4-Column Bento Grid) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <x-metric-card 
            label="RSRP" 
            value="-88" 
            unit="dBm" 
            status="GOOD" 
            description="Signal Strength (Reference Signal Received Power)" 
            trend="+2 dBm" 
            trendDirection="up"
        />

        <x-metric-card 
            label="RSSI" 
            value="-62" 
            unit="dBm" 
            status="EXCELLENT" 
            description="Total Received Power (Includes signal + noise)" 
            trend="+1 dBm" 
            trendDirection="up"
        />

        <x-metric-card 
            label="RSRQ" 
            value="-12" 
            unit="dB" 
            status="GOOD" 
            description="Signal Quality (Reference Signal Received Quality)" 
            trend="-1 dB" 
            trendDirection="down"
        />

        <x-metric-card 
            label="SINR" 
            value="14" 
            unit="dB" 
            status="GOOD" 
            description="Signal to Noise Ratio (Cleanliness of signal)" 
            trend="+3 dB" 
            trendDirection="up"
        />
    </div>

    <!-- SECONDARY BENTO GRID (Quality, Gauges, Connection, Cell Info) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Signal Quality & Interpretation Bento Card (Span 1) -->
        <x-bento-card title="Overall Signal Quality" subtitle="Calculated connection health" icon="shield-check" :glow="true">
            <div class="space-y-5">
                <div class="p-4 rounded-2xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-mono text-[#9CA3AF] block mb-1">Status Rating</span>
                        <x-status-badge status="GOOD" size="lg" />
                    </div>
                    <div class="text-right font-mono">
                        <span class="text-2xl font-black text-[#F2C94C]">82<span class="text-xs text-[#9CA3AF]">/100</span></span>
                        <span class="text-[10px] text-[#9CA3AF] block">Signal Score</span>
                    </div>
                </div>

                <!-- Interpretation explanation -->
                <div class="p-3.5 rounded-xl bg-[#171B20]/60 border border-[#232931] space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#F2C94C] font-mono flex items-center gap-1.5">
                        <i data-lucide="info" class="w-3.5 h-3.5"></i>
                        What Does This Mean?
                    </span>
                    <p class="text-xs text-[#9CA3AF] leading-relaxed">
                        Your LTE signal strength (<strong class="text-white">RSRP -88 dBm</strong>) and SNR (<strong class="text-white">14 dB</strong>) are healthy with minimal interference. Connection is optimal for high-throughput streaming and gaming.
                    </p>
                </div>
            </div>
        </x-bento-card>

        <!-- Signal Gauges (Span 1) -->
        <x-bento-card title="Signal Gauges" subtitle="Visual telemetry meters" icon="gauge">
            <div class="space-y-4">
                <x-signal-gauge label="RSRP" value="-88" unit="dBm" percentage="72" status="GOOD" min="-120" max="-70" />
                <x-signal-gauge label="RSRQ" value="-12" unit="dB" percentage="65" status="GOOD" min="-20" max="-3" />
                <x-signal-gauge label="SINR" value="14" unit="dB" percentage="70" status="GOOD" min="-5" max="30" />
            </div>
        </x-bento-card>

        <!-- LTE Carrier & Radio Link (Span 1) -->
        <x-bento-card title="LTE Connection" subtitle="Radio link parameters" icon="radio">
            <div class="grid grid-cols-2 gap-2.5 text-xs font-mono">
                <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                    <span class="text-[10px] text-[#9CA3AF] uppercase block">Band</span>
                    <span class="text-sm font-bold text-[#F2C94C]">B40 (2300 MHz)</span>
                </div>
                <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                    <span class="text-[10px] text-[#9CA3AF] uppercase block">Bandwidth</span>
                    <span class="text-sm font-bold text-white">20 MHz</span>
                </div>
                <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                    <span class="text-[10px] text-[#9CA3AF] uppercase block">EARFCN</span>
                    <span class="text-sm font-bold text-white">39146</span>
                </div>
                <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                    <span class="text-[10px] text-[#9CA3AF] uppercase block">RRC State</span>
                    <span class="text-sm font-bold text-emerald-400">Connected</span>
                </div>
                <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                    <span class="text-[10px] text-[#9CA3AF] uppercase block">TX Power</span>
                    <span class="text-sm font-bold text-white">23 dBm</span>
                </div>
                <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                    <span class="text-[10px] text-[#9CA3AF] uppercase block">MCS / CQI</span>
                    <span class="text-sm font-bold text-white">24 / 10</span>
                </div>
            </div>
        </x-bento-card>
    </div>

    <!-- TERTIARY BENTO GRID (Cell Info & Router Diagnostic Details) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Cell Tower Identification (Copy-friendly) -->
        <x-bento-card title="Cell Tower Information" subtitle="Click to copy diagnostic IDs" icon="tower-control">
            <x-cell-info-card 
                enodeb="2994" 
                cell="2" 
                globalCellId="BB202" 
                pci="11" 
                earfcn="39146" 
                band="B40" 
            />
        </x-bento-card>

        <!-- Router Hardware Information (Masked/Safe) -->
        <x-bento-card title="Router Hardware" subtitle="Hardware & firmware specifications" icon="cpu">
            <div class="grid grid-cols-2 gap-2.5 text-xs font-mono">
                <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                    <span class="text-[10px] text-[#9CA3AF] uppercase block">Router Model</span>
                    <span class="text-sm font-bold text-white">ZLT P11X</span>
                </div>
                <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                    <span class="text-[10px] text-[#9CA3AF] uppercase block">Firmware</span>
                    <span class="text-sm font-bold text-white">6.4.2.25</span>
                </div>
                <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                    <span class="text-[10px] text-[#9CA3AF] uppercase block">Hardware</span>
                    <span class="text-sm font-bold text-white">TZ7.821.172</span>
                </div>
                <div class="p-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                    <span class="text-[10px] text-[#9CA3AF] uppercase block">LTE Modem</span>
                    <span class="text-sm font-bold text-white">P705A_1.0.9</span>
                </div>
            </div>
            <div class="mt-3 text-[11px] font-mono text-[#6B7280] flex items-center justify-between">
                <span>Build Date: 2022-11-08</span>
                <span class="text-[#D4A017] flex items-center gap-1">
                    <i data-lucide="shield" class="w-3 h-3"></i>
                    IMEI & MAC Masked
                </span>
            </div>
        </x-bento-card>
    </div>
</div>
@endsection
