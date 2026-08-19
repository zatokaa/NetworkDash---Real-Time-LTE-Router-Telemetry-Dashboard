<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/85 backdrop-blur-md flex items-center justify-center p-4">
            <div class="relative w-full max-w-2xl rounded-3xl bg-[#111418] border border-[#232931] p-6 sm:p-8 shadow-2xl shadow-black/80 space-y-6 max-h-[90vh] overflow-y-auto">
                <!-- Ambient Glow -->
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-[#D4A017]/10 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-[#171B20] pb-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#F2C94C] flex items-center justify-center text-[#0B0D0F] shadow-lg shadow-[#D4A017]/20">
                            <i data-lucide="radio-tower" class="w-6 h-6 stroke-[2.5]"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-white tracking-tight">Update Signal Telemetry</h3>
                            <p class="text-xs text-[#9CA3AF]">Record LTE radio parameters from router diagnostic page</p>
                        </div>
                    </div>
                    <button wire:click="close" class="p-2 rounded-xl bg-[#171B20] hover:bg-[#232931] text-[#9CA3AF] hover:text-white transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Quick Presets -->
                <div class="space-y-2">
                    <span class="text-[11px] font-mono uppercase tracking-wider text-[#9CA3AF] flex items-center gap-1.5">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#F2C94C]"></i>
                        Quick Presets / Simulation:
                    </span>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <button type="button" wire:click="applyPreset('optimal')" class="p-2 rounded-xl bg-[#171B20] hover:bg-emerald-500/10 border border-[#232931] hover:border-emerald-500/30 text-xs font-mono text-emerald-400 transition-all text-left">
                            <span class="block font-bold">● Optimal</span>
                            <span class="text-[10px] text-[#6B7280]">B40 • RSRP -78</span>
                        </button>
                        <button type="button" wire:click="applyPreset('good')" class="p-2 rounded-xl bg-[#171B20] hover:bg-[#D4A017]/10 border border-[#232931] hover:border-[#D4A017]/30 text-xs font-mono text-[#F2C94C] transition-all text-left">
                            <span class="block font-bold">● Good</span>
                            <span class="text-[10px] text-[#6B7280]">B40 • RSRP -88</span>
                        </button>
                        <button type="button" wire:click="applyPreset('interference')" class="p-2 rounded-xl bg-[#171B20] hover:bg-amber-500/10 border border-[#232931] hover:border-amber-500/30 text-xs font-mono text-amber-400 transition-all text-left">
                            <span class="block font-bold">▲ Noise / Interf.</span>
                            <span class="text-[10px] text-[#6B7280]">B3 • SINR 2 dB</span>
                        </button>
                        <button type="button" wire:click="applyPreset('weak')" class="p-2 rounded-xl bg-[#171B20] hover:bg-rose-500/10 border border-[#232931] hover:border-rose-500/30 text-xs font-mono text-rose-400 transition-all text-left">
                            <span class="block font-bold">■ Weak Signal</span>
                            <span class="text-[10px] text-[#6B7280]">B20 • RSRP -114</span>
                        </button>
                    </div>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="save" class="space-y-5">
                    <!-- Router Selector -->
                    <div>
                        <label class="block text-xs font-mono text-[#9CA3AF] uppercase mb-1.5">Target Router</label>
                        <select wire:model="router_id" class="w-full px-3.5 py-2.5 rounded-xl bg-[#0B0D0F] border border-[#232931] text-xs font-mono text-white focus:border-[#D4A017] focus:outline-none" required>
                            @foreach($routers as $r)
                                <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->model }}) @if($r->is_active) - Active @endif</option>
                            @endforeach
                        </select>
                        @error('router_id') <span class="text-xs text-rose-400 font-mono">{{ $message }}</span> @enderror
                    </div>

                    <!-- 1. Primary Signal Decibels -->
                    <div class="p-4 rounded-2xl bg-[#0B0D0F] border border-[#232931] space-y-3">
                        <span class="text-xs font-bold font-mono text-white uppercase flex items-center gap-2">
                            <i data-lucide="activity" class="w-4 h-4 text-[#F2C94C]"></i>
                            1. Primary Signal Strength & Quality
                        </span>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 font-mono">
                            <div>
                                <label class="block text-[10px] text-[#9CA3AF] uppercase mb-1">RSRP (dBm)</label>
                                <input type="number" step="0.1" wire:model="rsrp" class="w-full px-3 py-2 rounded-xl bg-[#171B20] border border-[#232931] text-xs text-white focus:border-[#D4A017] focus:outline-none" placeholder="-88.0" required>
                                @error('rsrp') <span class="text-[10px] text-rose-400">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] text-[#9CA3AF] uppercase mb-1">RSSI (dBm)</label>
                                <input type="number" step="0.1" wire:model="rssi" class="w-full px-3 py-2 rounded-xl bg-[#171B20] border border-[#232931] text-xs text-white focus:border-[#D4A017] focus:outline-none" placeholder="-62.0" required>
                                @error('rssi') <span class="text-[10px] text-rose-400">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] text-[#9CA3AF] uppercase mb-1">RSRQ (dB)</label>
                                <input type="number" step="0.1" wire:model="rsrq" class="w-full px-3 py-2 rounded-xl bg-[#171B20] border border-[#232931] text-xs text-white focus:border-[#D4A017] focus:outline-none" placeholder="-12.0" required>
                                @error('rsrq') <span class="text-[10px] text-rose-400">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] text-[#9CA3AF] uppercase mb-1">SINR (dB)</label>
                                <input type="number" step="0.1" wire:model="sinr" class="w-full px-3 py-2 rounded-xl bg-[#171B20] border border-[#232931] text-xs text-white focus:border-[#D4A017] focus:outline-none" placeholder="14.0" required>
                                @error('sinr') <span class="text-[10px] text-rose-400">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 2. LTE Carrier & Modulation -->
                    <div class="p-4 rounded-2xl bg-[#0B0D0F] border border-[#232931] space-y-3">
                        <span class="text-xs font-bold font-mono text-white uppercase flex items-center gap-2">
                            <i data-lucide="radio" class="w-4 h-4 text-[#D4A017]"></i>
                            2. Carrier & Radio Modulation
                        </span>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 font-mono text-xs">
                            <div>
                                <label class="block text-[10px] text-[#9CA3AF] uppercase mb-1">Band</label>
                                <input type="text" wire:model="band" class="w-full px-3 py-2 rounded-xl bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" placeholder="B40" required>
                            </div>
                            <div>
                                <label class="block text-[10px] text-[#9CA3AF] uppercase mb-1">Bandwidth</label>
                                <input type="text" wire:model="bandwidth" class="w-full px-3 py-2 rounded-xl bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" placeholder="20 MHz" required>
                            </div>
                            <div>
                                <label class="block text-[10px] text-[#9CA3AF] uppercase mb-1">EARFCN</label>
                                <input type="number" wire:model="earfcn" class="w-full px-3 py-2 rounded-xl bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" placeholder="39146" required>
                            </div>
                            <div>
                                <label class="block text-[10px] text-[#9CA3AF] uppercase mb-1">TX Power (dBm)</label>
                                <input type="number" step="0.1" wire:model="tx_power" class="w-full px-3 py-2 rounded-xl bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" placeholder="23.0" required>
                            </div>
                            <div>
                                <label class="block text-[10px] text-[#9CA3AF] uppercase mb-1">Trans. Mode</label>
                                <input type="text" wire:model="transmission_mode" class="w-full px-3 py-2 rounded-xl bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" placeholder="TM8" required>
                            </div>
                            <div>
                                <label class="block text-[10px] text-[#9CA3AF] uppercase mb-1">RRC State</label>
                                <select wire:model="rrc_state" class="w-full px-3 py-2 rounded-xl bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none">
                                    <option value="Connected">Connected</option>
                                    <option value="Idle">Idle</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] text-[#9CA3AF] uppercase mb-1">MCS (0-31)</label>
                                <input type="number" wire:model="mcs" class="w-full px-3 py-2 rounded-xl bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" min="0" max="31" required>
                            </div>
                            <div>
                                <label class="block text-[10px] text-[#9CA3AF] uppercase mb-1">CQI (1-15)</label>
                                <input type="number" wire:model="cqi" class="w-full px-3 py-2 rounded-xl bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" min="1" max="15" required>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Base Station & Cell ID -->
                    <div class="p-4 rounded-2xl bg-[#0B0D0F] border border-[#232931] space-y-3">
                        <span class="text-xs font-bold font-mono text-white uppercase flex items-center gap-2">
                            <i data-lucide="tower-control" class="w-4 h-4 text-[#10B981]"></i>
                            3. Cell Tower & Sector Identifiers
                        </span>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 font-mono text-xs">
                            <div>
                                <label class="block text-[10px] text-[#9CA3AF] uppercase mb-1">eNodeB ID</label>
                                <input type="text" wire:model="enodeb" class="w-full px-3 py-2 rounded-xl bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" placeholder="2994" required>
                            </div>
                            <div>
                                <label class="block text-[10px] text-[#9CA3AF] uppercase mb-1">Cell ID</label>
                                <input type="text" wire:model="cell_id" class="w-full px-3 py-2 rounded-xl bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" placeholder="2" required>
                            </div>
                            <div>
                                <label class="block text-[10px] text-[#9CA3AF] uppercase mb-1">Global Cell ID</label>
                                <input type="text" wire:model="global_cell_id" class="w-full px-3 py-2 rounded-xl bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" placeholder="BB202" required>
                            </div>
                            <div>
                                <label class="block text-[10px] text-[#9CA3AF] uppercase mb-1">Physical Cell ID (PCI)</label>
                                <input type="text" wire:model="physical_cell_id" class="w-full px-3 py-2 rounded-xl bg-[#171B20] border border-[#232931] text-white focus:border-[#D4A017] focus:outline-none" placeholder="11" required>
                            </div>
                        </div>
                    </div>

                    <!-- Submit & Cancel Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#171B20]">
                        <button type="button" wire:click="close" class="px-4 py-2.5 rounded-xl bg-[#171B20] hover:bg-[#232931] text-xs font-semibold text-[#9CA3AF] hover:text-white transition-colors">
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#D4A017] to-[#F2C94C] text-[#0B0D0F] font-bold text-xs shadow-lg shadow-[#D4A017]/20 hover:brightness-110 active:scale-95 transition-all disabled:opacity-50 flex items-center gap-2"
                        >
                            <span wire:loading.remove class="flex items-center gap-1.5">
                                <i data-lucide="check-circle" class="w-4 h-4 stroke-[2.5]"></i>
                                Save Signal Reading
                            </span>
                            <span wire:loading class="flex items-center gap-1.5">
                                <svg class="animate-spin h-4 w-4 text-[#0B0D0F]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Recording...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
