<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 sm:p-6 rounded-2xl sm:rounded-3xl bg-[#111418] border border-[#232931] shadow-xl">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#F2C94C] flex items-center justify-center text-[#0B0D0F] shadow-lg shadow-[#D4A017]/20 flex-shrink-0">
                <i data-lucide="sliders" class="w-6 h-6 stroke-[2.5]"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white">Thresholds & System Settings</h1>
                <p class="text-xs text-[#9CA3AF]">Customize LTE signal evaluation cutoffs, algorithm weights, and security parameters</p>
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-xl bg-[#171B20] hover:bg-[#232931] border border-[#232931] text-xs font-semibold text-[#F5F5F5] flex items-center gap-1.5 transition-colors">
                <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-[#D4A017]"></i>
                Dashboard
            </a>
            <button 
                wire:click="resetToDefaults"
                wire:confirm="Reset all thresholds and weights back to standard LTE factory defaults?"
                type="button" 
                class="px-4 py-2 rounded-xl bg-[#171B20] hover:bg-rose-500/10 border border-[#232931] hover:border-rose-500/30 text-xs font-mono text-rose-400 flex items-center gap-1.5 transition-colors"
            >
                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                <span>Reset Defaults</span>
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 bg-[#111418] p-1.5 rounded-2xl border border-[#232931] text-xs font-mono">
        <button 
            wire:click="$set('activeTab', 'thresholds')" 
            type="button" 
            class="flex-1 py-2.5 rounded-xl font-bold transition-all flex items-center justify-center gap-2 @if($activeTab === 'thresholds') bg-[#D4A017] text-[#0B0D0F] shadow-md @else text-[#9CA3AF] hover:text-white @endif"
        >
            <i data-lucide="gauge" class="w-4 h-4"></i>
            <span>Signal Thresholds</span>
        </button>

        <button 
            wire:click="$set('activeTab', 'weights')" 
            type="button" 
            class="flex-1 py-2.5 rounded-xl font-bold transition-all flex items-center justify-center gap-2 @if($activeTab === 'weights') bg-[#D4A017] text-[#0B0D0F] shadow-md @else text-[#9CA3AF] hover:text-white @endif"
        >
            <i data-lucide="percent" class="w-4 h-4"></i>
            <span>Scoring & Weights</span>
        </button>

        <button 
            wire:click="$set('activeTab', 'security')" 
            type="button" 
            class="flex-1 py-2.5 rounded-xl font-bold transition-all flex items-center justify-center gap-2 @if($activeTab === 'security') bg-[#D4A017] text-[#0B0D0F] shadow-md @else text-[#9CA3AF] hover:text-white @endif"
        >
            <i data-lucide="shield-check" class="w-4 h-4"></i>
            <span>Security & Privacy</span>
        </button>
    </div>

    <!-- TAB 1: SIGNAL THRESHOLDS -->
    @if($activeTab === 'thresholds')
        <form wire:submit.prevent="saveThresholds" class="space-y-5">
            <!-- RSRP Thresholds Bento Card -->
            <div class="p-5 sm:p-6 rounded-2xl sm:rounded-3xl bg-[#111418] border border-[#232931] shadow-xl space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-[#171B20] border border-[#232931] flex items-center justify-center text-[#F2C94C]">
                            <i data-lucide="activity" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white font-mono uppercase">RSRP Boundaries (dBm)</h3>
                            <p class="text-[11px] text-[#6B7280]">Signal Power classification cutoffs</p>
                        </div>
                    </div>
                    <span class="text-xs font-mono text-[#D4A017]">dBm scale</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 font-mono text-xs">
                    <div class="p-3 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                        <label class="block text-[10px] text-emerald-400 uppercase font-bold mb-1">● Excellent (&ge;)</label>
                        <input type="number" step="1" wire:model="rsrp_excellent" class="w-full px-3 py-1.5 rounded-lg bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" required>
                    </div>
                    <div class="p-3 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                        <label class="block text-[10px] text-teal-300 uppercase font-bold mb-1">● Very Good (&ge;)</label>
                        <input type="number" step="1" wire:model="rsrp_very_good" class="w-full px-3 py-1.5 rounded-lg bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" required>
                    </div>
                    <div class="p-3 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                        <label class="block text-[10px] text-[#F2C94C] uppercase font-bold mb-1">● Good (&ge;)</label>
                        <input type="number" step="1" wire:model="rsrp_good" class="w-full px-3 py-1.5 rounded-lg bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" required>
                    </div>
                    <div class="p-3 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                        <label class="block text-[10px] text-amber-400 uppercase font-bold mb-1">● Fair (&ge;)</label>
                        <input type="number" step="1" wire:model="rsrp_fair" class="w-full px-3 py-1.5 rounded-lg bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" required>
                    </div>
                </div>
            </div>

            <!-- SINR Thresholds Bento Card -->
            <div class="p-5 sm:p-6 rounded-2xl sm:rounded-3xl bg-[#111418] border border-[#232931] shadow-xl space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-[#171B20] border border-[#232931] flex items-center justify-center text-[#10B981]">
                            <i data-lucide="radio" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white font-mono uppercase">SINR Boundaries (dB)</h3>
                            <p class="text-[11px] text-[#6B7280]">Signal to Interference plus Noise Ratio cutoffs</p>
                        </div>
                    </div>
                    <span class="text-xs font-mono text-emerald-400">dB scale</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 font-mono text-xs">
                    <div class="p-3 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                        <label class="block text-[10px] text-emerald-400 uppercase font-bold mb-1">● Excellent (&ge;)</label>
                        <input type="number" step="1" wire:model="sinr_excellent" class="w-full px-3 py-1.5 rounded-lg bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" required>
                    </div>
                    <div class="p-3 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                        <label class="block text-[10px] text-[#F2C94C] uppercase font-bold mb-1">● Good (&ge;)</label>
                        <input type="number" step="1" wire:model="sinr_good" class="w-full px-3 py-1.5 rounded-lg bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" required>
                    </div>
                    <div class="p-3 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                        <label class="block text-[10px] text-amber-400 uppercase font-bold mb-1">● Fair (&ge;)</label>
                        <input type="number" step="1" wire:model="sinr_fair" class="w-full px-3 py-1.5 rounded-lg bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" required>
                    </div>
                </div>
            </div>

            <!-- RSRQ Thresholds Bento Card -->
            <div class="p-5 sm:p-6 rounded-2xl sm:rounded-3xl bg-[#111418] border border-[#232931] shadow-xl space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-[#171B20] border border-[#232931] flex items-center justify-center text-[#06B6D4]">
                            <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white font-mono uppercase">RSRQ Boundaries (dB)</h3>
                            <p class="text-[11px] text-[#6B7280]">Signal Quality received power ratio cutoffs</p>
                        </div>
                    </div>
                    <span class="text-xs font-mono text-cyan-400">dB scale</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 font-mono text-xs">
                    <div class="p-3 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                        <label class="block text-[10px] text-emerald-400 uppercase font-bold mb-1">● Excellent (&ge;)</label>
                        <input type="number" step="1" wire:model="rsrq_excellent" class="w-full px-3 py-1.5 rounded-lg bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" required>
                    </div>
                    <div class="p-3 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                        <label class="block text-[10px] text-[#F2C94C] uppercase font-bold mb-1">● Good (&ge;)</label>
                        <input type="number" step="1" wire:model="rsrq_good" class="w-full px-3 py-1.5 rounded-lg bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" required>
                    </div>
                    <div class="p-3 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                        <label class="block text-[10px] text-amber-400 uppercase font-bold mb-1">● Fair (&ge;)</label>
                        <input type="number" step="1" wire:model="rsrq_fair" class="w-full px-3 py-1.5 rounded-lg bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" required>
                    </div>
                </div>
            </div>

            <!-- RSSI Thresholds Bento Card -->
            <div class="p-5 sm:p-6 rounded-2xl sm:rounded-3xl bg-[#111418] border border-[#232931] shadow-xl space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-[#171B20] border border-[#232931] flex items-center justify-center text-[#A78BFA]">
                            <i data-lucide="signal" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white font-mono uppercase">RSSI Boundaries (dBm)</h3>
                            <p class="text-[11px] text-[#6B7280]">Total Received Signal Strength classification cutoffs</p>
                        </div>
                    </div>
                    <span class="text-xs font-mono text-purple-400">dBm scale</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 font-mono text-xs">
                    <div class="p-3 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                        <label class="block text-[10px] text-emerald-400 uppercase font-bold mb-1">● Excellent (&ge;)</label>
                        <input type="number" step="1" wire:model="rssi_excellent" class="w-full px-3 py-1.5 rounded-lg bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" required>
                    </div>
                    <div class="p-3 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                        <label class="block text-[10px] text-[#F2C94C] uppercase font-bold mb-1">● Good (&ge;)</label>
                        <input type="number" step="1" wire:model="rssi_good" class="w-full px-3 py-1.5 rounded-lg bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" required>
                    </div>
                    <div class="p-3 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                        <label class="block text-[10px] text-amber-400 uppercase font-bold mb-1">● Fair (&ge;)</label>
                        <input type="number" step="1" wire:model="rssi_fair" class="w-full px-3 py-1.5 rounded-lg bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" required>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-2">
                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[#D4A017] to-[#F2C94C] text-[#0B0D0F] font-bold text-xs shadow-lg shadow-[#D4A017]/20 hover:brightness-110 active:scale-95 transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4 stroke-[2.5]"></i>
                    Save Threshold Boundaries
                </button>
            </div>
        </form>
    @endif

    <!-- TAB 2: SCORE WEIGHTS -->
    @if($activeTab === 'weights')
        <form wire:submit.prevent="saveWeights" class="space-y-5">
            <div class="p-5 sm:p-6 rounded-2xl sm:rounded-3xl bg-[#111418] border border-[#232931] shadow-xl space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-white font-mono uppercase">Health Score Weights</h3>
                        <p class="text-xs text-[#9CA3AF]">Define how each LTE parameter contributes to the 0-100 overall score (Total must equal 100%)</p>
                    </div>
                    @php $sum = $weight_rsrp + $weight_sinr + $weight_rsrq; @endphp
                    <div class="px-3 py-1.5 rounded-xl border text-xs font-mono font-bold @if($sum === 100) bg-emerald-500/10 text-emerald-400 border-emerald-500/30 @else bg-rose-500/10 text-rose-400 border-rose-500/30 @endif">
                        Total: {{ $sum }}% / 100%
                    </div>
                </div>

                <div class="space-y-4 font-mono text-xs">
                    <!-- RSRP Weight -->
                    <div class="p-4 rounded-2xl bg-[#0B0D0F] border border-[#232931] space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-white uppercase">RSRP Weight (Signal Strength)</span>
                            <span class="text-[#F2C94C] font-bold">{{ $weight_rsrp }}%</span>
                        </div>
                        <input type="range" min="0" max="100" wire:model.live="weight_rsrp" class="w-full accent-[#D4A017] cursor-pointer">
                    </div>

                    <!-- SINR Weight -->
                    <div class="p-4 rounded-2xl bg-[#0B0D0F] border border-[#232931] space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-white uppercase">SINR Weight (Noise & Interference)</span>
                            <span class="text-emerald-400 font-bold">{{ $weight_sinr }}%</span>
                        </div>
                        <input type="range" min="0" max="100" wire:model.live="weight_sinr" class="w-full accent-emerald-400 cursor-pointer">
                    </div>

                    <!-- RSRQ Weight -->
                    <div class="p-4 rounded-2xl bg-[#0B0D0F] border border-[#232931] space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-white uppercase">RSRQ Weight (Carrier Quality)</span>
                            <span class="text-cyan-400 font-bold">{{ $weight_rsrq }}%</span>
                        </div>
                        <input type="range" min="0" max="100" wire:model.live="weight_rsrq" class="w-full accent-cyan-400 cursor-pointer">
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[#D4A017] to-[#F2C94C] text-[#0B0D0F] font-bold text-xs shadow-lg shadow-[#D4A017]/20 hover:brightness-110 active:scale-95 transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4 stroke-[2.5]"></i>
                    Update Score Weights
                </button>
            </div>
        </form>
    @endif

    <!-- TAB 3: SECURITY & PRIVACY -->
    @if($activeTab === 'security')
        <form wire:submit.prevent="saveSecurity" class="space-y-5">
            <div class="p-5 sm:p-6 rounded-2xl sm:rounded-3xl bg-[#111418] border border-[#232931] shadow-xl space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-white font-mono uppercase">Sensitive Identifier Masking</h3>
                    <p class="text-xs text-[#9CA3AF]">Control visibility of hardware identifiers (IMEI, IMSI, ICCID, MAC address)</p>
                </div>

                <div class="p-4 rounded-2xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between gap-4">
                    <div class="space-y-1">
                        <span class="text-xs font-bold text-white font-mono block">Reveal Sensitive Hardware Identifiers</span>
                        <p class="text-[11px] text-[#6B7280]">When disabled (recommended), full IMEI, IMSI, ICCID, and MAC addresses are masked with asterisks to protect your cellular modem identity.</p>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                        <input type="checkbox" wire:model="reveal_sensitive_ids" class="sr-only peer">
                        <div class="w-11 h-6 bg-[#171B20] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#D4A017]"></div>
                    </label>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[#D4A017] to-[#F2C94C] text-[#0B0D0F] font-bold text-xs shadow-lg shadow-[#D4A017]/20 hover:brightness-110 active:scale-95 transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4 stroke-[2.5]"></i>
                    Save Privacy Preference
                </button>
            </div>
        </form>
    @endif
</div>
