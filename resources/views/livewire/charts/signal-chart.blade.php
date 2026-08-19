<div class="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-[#111418] border border-[#232931] p-5 sm:p-6 shadow-xl"
     x-data="{
         chartId: 'signalChartCanvas',
         chartData: @js($chartData),
         initChart() {
             this.$nextTick(() => {
                 if (window.renderSignalChart && this.chartData.labels && this.chartData.labels.length > 0) {
                     window.renderSignalChart(this.chartId, this.chartData.labels, this.chartData, this.chartData.metric);
                 }
             });
         }
     }"
     x-init="initChart()"
     x-effect="
         chartData = @js($chartData);
         initChart();
     "
>
    <!-- Chart Header & Controls -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5 relative z-10">
        <!-- Title & Metric Badge -->
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-[#171B20] border border-[#232931] flex items-center justify-center text-[#F2C94C]">
                <i data-lucide="line-chart" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-white font-mono flex items-center gap-2">
                    Signal Telemetry History
                    <span class="text-[10px] uppercase font-bold px-2.5 py-0.5 rounded-full @if($activeMetric === 'all') bg-gradient-to-r from-[#D4A017]/20 to-emerald-500/20 text-[#F2C94C] border border-[#D4A017]/40 @elseif($activeMetric === 'rsrp') bg-[#D4A017]/10 text-[#F2C94C] border border-[#D4A017]/30 @elseif($activeMetric === 'sinr') bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 @elseif($activeMetric === 'rsrq') bg-purple-500/10 text-purple-400 border border-purple-500/30 @else bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 @endif font-mono">
                        {{ $activeMetric === 'all' ? '★ ALL 4 METRICS' : strtoupper($activeMetric) }}
                    </span>
                </h3>
                <p class="text-[11px] text-[#6B7280]">Real-time trend analysis • {{ $chartData['count'] ?? 0 }} data points</p>
            </div>
        </div>

        <!-- Metric Switchers & Timeframe Filters -->
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <!-- Metric Toggle Buttons (Including All in One) -->
            <div class="flex items-center gap-1 bg-[#0B0D0F] p-1 rounded-xl border border-[#232931] text-xs font-mono">
                <button 
                    wire:click="setMetric('all')" 
                    type="button" 
                    class="px-2.5 py-1 rounded-lg transition-all @if($activeMetric === 'all') bg-gradient-to-r from-[#D4A017] to-[#F2C94C] text-[#0B0D0F] font-bold shadow-sm @else text-[#9CA3AF] hover:text-white @endif"
                >
                    ★ All in One
                </button>
                <button 
                    wire:click="setMetric('rsrp')" 
                    type="button" 
                    class="px-2.5 py-1 rounded-lg transition-all @if($activeMetric === 'rsrp') bg-[#D4A017] text-[#0B0D0F] font-bold shadow-sm @else text-[#9CA3AF] hover:text-white @endif"
                >
                    RSRP
                </button>
                <button 
                    wire:click="setMetric('rssi')" 
                    type="button" 
                    class="px-2.5 py-1 rounded-lg transition-all @if($activeMetric === 'rssi') bg-sky-400 text-[#0B0D0F] font-bold shadow-sm @else text-[#9CA3AF] hover:text-white @endif"
                >
                    RSSI
                </button>
                <button 
                    wire:click="setMetric('rsrq')" 
                    type="button" 
                    class="px-2.5 py-1 rounded-lg transition-all @if($activeMetric === 'rsrq') bg-purple-400 text-[#0B0D0F] font-bold shadow-sm @else text-[#9CA3AF] hover:text-white @endif"
                >
                    RSRQ
                </button>
                <button 
                    wire:click="setMetric('sinr')" 
                    type="button" 
                    class="px-2.5 py-1 rounded-lg transition-all @if($activeMetric === 'sinr') bg-emerald-400 text-[#0B0D0F] font-bold shadow-sm @else text-[#9CA3AF] hover:text-white @endif"
                >
                    SINR
                </button>
            </div>

            <!-- Timeframe Selector -->
            <div class="flex items-center gap-1 bg-[#0B0D0F] p-1 rounded-xl border border-[#232931] text-xs font-mono">
                @foreach(['15m' => '15M', '30m' => '30M', '1h' => '1H', '6h' => '6H', '24h' => '24H', '7d' => '7D'] as $tfKey => $tfLabel)
                    <button 
                        wire:click="setTimeframe('{{ $tfKey }}')" 
                        type="button" 
                        class="px-2 py-1 rounded-lg transition-colors @if($timeframe === $tfKey) bg-[#171B20] text-[#F2C94C] font-bold border border-[#232931] @else text-[#6B7280] hover:text-white @endif text-[11px]"
                    >
                        {{ $tfLabel }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Canvas Container -->
    <div class="relative w-full h-64 sm:h-72">
        <canvas id="signalChartCanvas" wire:ignore></canvas>

        @if(($chartData['count'] ?? 0) === 0)
            <div class="absolute inset-0 flex flex-col items-center justify-center bg-[#111418]/90 backdrop-blur-sm rounded-2xl">
                <div class="w-10 h-10 rounded-xl bg-[#171B20] text-[#6B7280] flex items-center justify-center mb-2">
                    <i data-lucide="activity" class="w-5 h-5"></i>
                </div>
                <p class="text-xs font-bold text-white">No historical readings in this timeframe</p>
                <p class="text-[11px] text-[#6B7280] mt-0.5">Record telemetry data to view trend history</p>
            </div>
        @endif
    </div>
</div>
