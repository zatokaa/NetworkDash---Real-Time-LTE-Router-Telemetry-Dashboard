<div class="space-y-6">
    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 sm:p-6 rounded-2xl sm:rounded-3xl bg-[#111418] border border-[#232931] shadow-xl">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#F2C94C] flex items-center justify-center text-[#0B0D0F] shadow-lg shadow-[#D4A017]/20">
                <i data-lucide="router" class="w-6 h-6 stroke-[2.5]"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white">Router Management</h1>
                <p class="text-xs text-[#9CA3AF]">Manage monitored 4G LTE modems, real hardware drivers, and credentials</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="px-4 py-2.5 rounded-xl bg-[#171B20] hover:bg-[#232931] border border-[#232931] text-xs font-semibold text-[#F5F5F5] flex items-center gap-2 transition-colors">
                <i data-lucide="layout-dashboard" class="w-4 h-4 text-[#D4A017]"></i>
                Dashboard
            </a>
            <button 
                wire:click="openCreateModal"
                type="button"
                class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-[#D4A017] to-[#F2C94C] text-[#0B0D0F] font-bold text-xs shadow-lg shadow-[#D4A017]/20 hover:brightness-110 active:scale-95 transition-all flex items-center gap-2 cursor-pointer"
            >
                <i data-lucide="plus-circle" class="w-4 h-4 stroke-[2.5]"></i>
                Add New Router
            </button>
        </div>
    </div>

    <!-- Router Bento Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($routers as $router)
            <div wire:key="router-card-{{ $router->id }}" class="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-[#111418] border @if($router->is_active) border-[#D4A017] shadow-xl shadow-[#D4A017]/10 @else border-[#232931] @endif p-5 sm:p-6 transition-all duration-300 hover:border-[#D4A017]/50 flex flex-col justify-between">
                @if($router->is_active)
                    <div class="absolute top-0 right-0">
                        <div class="bg-gradient-to-l from-[#D4A017] to-[#F2C94C] text-[#0B0D0F] text-[10px] font-mono font-extrabold uppercase px-3 py-1 rounded-bl-xl shadow-md">
                            ★ Active Router
                        </div>
                    </div>
                @endif

                <div>
                    <!-- Router Header -->
                    <div class="flex items-start gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-[#171B20] border border-[#232931] flex items-center justify-center text-[#F2C94C]">
                            <i data-lucide="radio" class="w-5 h-5"></i>
                        </div>
                        <div class="pr-12">
                            <h3 class="text-base font-bold text-white tracking-tight">{{ $router->name }}</h3>
                            <span class="text-xs font-mono text-[#D4A017]">{{ $router->model }} ({{ strtoupper($router->driver ?? 'zlt') }})</span>
                        </div>
                    </div>

                    <!-- Status & IP Pill -->
                    <div class="flex items-center gap-2 mb-4">
                        <x-status-badge :status="strtoupper($router->status)" size="sm" />
                        <span class="text-xs font-mono px-2.5 py-0.5 rounded-full bg-[#0B0D0F] text-[#9CA3AF] border border-[#232931]">
                            {{ $router->ip_address ?? '192.168.0.1' }}
                        </span>
                    </div>

                    <!-- Hardware Spec Grid -->
                    <div class="grid grid-cols-2 gap-2 text-xs font-mono mb-4">
                        <div class="p-2 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                            <span class="text-[10px] text-[#6B7280] uppercase block">Firmware</span>
                            <span class="text-white font-medium truncate block">{{ $router->firmware_version ?: 'N/A' }}</span>
                        </div>
                        <div class="p-2 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                            <span class="text-[10px] text-[#6B7280] uppercase block">Hardware</span>
                            <span class="text-white font-medium truncate block">{{ $router->hardware_version ?: 'N/A' }}</span>
                        </div>
                        <div class="p-2 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                            <span class="text-[10px] text-[#6B7280] uppercase block">Modem</span>
                            <span class="text-white font-medium truncate block">{{ $router->modem_version ?: 'N/A' }}</span>
                        </div>
                        <div class="p-2 rounded-xl bg-[#0B0D0F] border border-[#232931]">
                            <span class="text-[10px] text-[#6B7280] uppercase block">Readings</span>
                            <span class="text-[#F2C94C] font-bold block">{{ $router->signal_readings_count ?? 0 }} stored</span>
                        </div>
                    </div>

                    <!-- Security Notice -->
                    <div class="p-2.5 rounded-xl bg-[#171B20]/60 border border-[#232931] text-[11px] font-mono text-[#9CA3AF] flex items-center justify-between">
                        <span class="flex items-center gap-1.5 text-[#6B7280]">
                            <i data-lucide="shield" class="w-3.5 h-3.5 text-[#D4A017]"></i>
                            Masked ID:
                        </span>
                        <span class="text-white">{{ $router->masked_imei }}</span>
                    </div>
                </div>

                <!-- Actions Footer -->
                <div class="mt-5 pt-4 border-t border-[#171B20] flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        @if(!$router->is_active)
                            <button 
                                wire:click="makeActive({{ $router->id }})"
                                type="button" 
                                class="px-3 py-1.5 rounded-xl bg-[#171B20] hover:bg-[#D4A017]/20 border border-[#232931] hover:border-[#D4A017]/50 text-xs font-mono text-[#F2C94C] transition-colors cursor-pointer"
                            >
                                Set Active
                            </button>
                        @else
                            <span class="text-xs font-mono text-emerald-400 flex items-center gap-1">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                Active
                            </span>
                        @endif

                        <!-- Live Sync Button -->
                        <button 
                            wire:click="syncLiveTelemetry({{ $router->id }})"
                            type="button" 
                            title="Query Real-Time Router Telemetry"
                            class="px-2.5 py-1.5 rounded-xl bg-[#171B20] hover:bg-emerald-500/10 border border-[#232931] hover:border-emerald-500/30 text-xs font-mono text-emerald-400 flex items-center gap-1 transition-colors cursor-pointer"
                        >
                            <i data-lucide="refresh-cw" class="w-3 h-3" wire:loading.class="animate-spin" wire:target="syncLiveTelemetry({{ $router->id }})"></i>
                            <span>Sync</span>
                        </button>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <!-- Edit Button -->
                        <button 
                            wire:click="openEditModal({{ $router->id }})"
                            type="button" 
                            title="Edit Router Configuration"
                            class="px-3 py-1.5 rounded-xl bg-[#171B20] hover:bg-[#232931] border border-[#232931] text-xs font-mono text-[#9CA3AF] hover:text-white transition-colors flex items-center gap-1 cursor-pointer"
                        >
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                            <span>Edit</span>
                        </button>

                        <!-- Delete Button -->
                        <button 
                            wire:click="confirmDelete({{ $router->id }})"
                            type="button" 
                            title="Delete Router"
                            class="px-3 py-1.5 rounded-xl bg-[#171B20] hover:bg-rose-500/10 border border-[#232931] hover:border-rose-500/30 text-xs font-mono text-[#9CA3AF] hover:text-rose-400 transition-colors flex items-center gap-1 cursor-pointer"
                        >
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            <span>Delete</span>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full p-12 text-center rounded-3xl bg-[#111418] border border-dashed border-[#232931]">
                <div class="w-12 h-12 rounded-2xl bg-[#171B20] text-[#9CA3AF] mx-auto flex items-center justify-center mb-3">
                    <i data-lucide="router" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-bold text-white">No routers configured</h3>
                <p class="text-xs text-[#9CA3AF] mt-1 mb-4">Add your LTE router gateway to begin real-time monitoring.</p>
                <button wire:click="openCreateModal" class="px-4 py-2 rounded-xl bg-[#D4A017] text-[#0B0D0F] text-xs font-bold cursor-pointer">
                    Add Router
                </button>
            </div>
        @endforelse
    </div>

    <!-- Create / Edit Router Modal with Live Authentication Gate -->
    @if($showModal)
        <div wire:key="router-form-modal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="relative w-full max-w-xl rounded-3xl bg-[#111418] border border-[#232931] p-6 sm:p-8 shadow-2xl space-y-6">
                <!-- Modal Header -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#D4A017] to-[#F2C94C] flex items-center justify-center text-[#0B0D0F]">
                            <i data-lucide="{{ $isEditing ? 'edit-2' : 'plus' }}" class="w-5 h-5 stroke-[2.5]"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">{{ $isEditing ? 'Edit Router Details' : 'Add New LTE Router' }}</h3>
                            <p class="text-xs text-[#9CA3AF]">Verifies admin login with router before saving</p>
                        </div>
                    </div>
                    <button type="button" wire:click="$set('showModal', false)" class="p-2 rounded-xl bg-[#171B20] text-[#9CA3AF] hover:text-white cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Admin Login Error Banner -->
                @if($loginError)
                    <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-xs font-mono text-rose-400 flex items-start gap-3">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-rose-400 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <strong class="font-bold text-sm block text-rose-300">Admin Login Failed:</strong>
                            <p class="text-xs text-rose-300/90 mt-0.5">{{ $loginError }}</p>
                        </div>
                    </div>
                @endif

                <!-- Form -->
                <form wire:submit.prevent="save" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Router Name -->
                        <div>
                            <label class="block text-xs font-mono text-[#9CA3AF] uppercase mb-1">Router Name</label>
                            <input type="text" wire:model="name" class="w-full px-3.5 py-2 rounded-xl bg-[#0B0D0F] border border-[#232931] text-xs text-white focus:border-[#D4A017] focus:outline-none font-mono" placeholder="Home LTE Router" required>
                            @error('name') <span class="text-[10px] text-rose-400 font-mono">{{ $message }}</span> @enderror
                        </div>

                        <!-- Driver Type -->
                        <div>
                            <label class="block text-xs font-mono text-[#9CA3AF] uppercase mb-1">Driver Type</label>
                            <select wire:model="driver" class="w-full px-3.5 py-2 rounded-xl bg-[#0B0D0F] border border-[#232931] text-xs font-mono text-white focus:border-[#D4A017] focus:outline-none cursor-pointer" required>
                                <option value="zlt">ZLT / Tozed / S10 / P11X / P21</option>
                                <option value="manual">Manual Entry Only</option>
                            </select>
                            @error('driver') <span class="text-[10px] text-rose-400 font-mono">{{ $message }}</span> @enderror
                        </div>

                        <!-- Router Model -->
                        <div>
                            <label class="block text-xs font-mono text-[#9CA3AF] uppercase mb-1">Router Model</label>
                            <input type="text" wire:model="model" class="w-full px-3.5 py-2 rounded-xl bg-[#0B0D0F] border border-[#232931] text-xs font-mono text-white focus:border-[#D4A017] focus:outline-none" placeholder="ZLT P11X" required>
                            @error('model') <span class="text-[10px] text-rose-400 font-mono">{{ $message }}</span> @enderror
                        </div>

                        <!-- IP Address -->
                        <div>
                            <label class="block text-xs font-mono text-[#9CA3AF] uppercase mb-1">Gateway IP</label>
                            <input type="text" wire:model="ip_address" class="w-full px-3.5 py-2 rounded-xl bg-[#0B0D0F] border border-[#232931] text-xs font-mono text-white focus:border-[#D4A017] focus:outline-none" placeholder="192.168.0.1">
                            @error('ip_address') <span class="text-[10px] text-rose-400 font-mono">{{ $message }}</span> @enderror
                        </div>

                        <!-- Web Username -->
                        <div>
                            <label class="block text-xs font-mono text-[#9CA3AF] uppercase mb-1">Router Username</label>
                            <input type="text" wire:model="username" class="w-full px-3.5 py-2 rounded-xl bg-[#0B0D0F] border border-[#232931] text-xs font-mono text-white focus:border-[#D4A017] focus:outline-none" placeholder="admin">
                            @error('username') <span class="text-[10px] text-rose-400 font-mono">{{ $message }}</span> @enderror
                        </div>

                        <!-- Web Password -->
                        <div>
                            <label class="block text-xs font-mono text-[#9CA3AF] uppercase mb-1">Router Password</label>
                            <input type="password" wire:model="password" class="w-full px-3.5 py-2 rounded-xl bg-[#0B0D0F] border border-[#232931] text-xs font-mono text-white focus:border-[#D4A017] focus:outline-none" placeholder="1234">
                            @error('password') <span class="text-[10px] text-rose-400 font-mono">{{ $message }}</span> @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-xs font-mono text-[#9CA3AF] uppercase mb-1">Status</label>
                            <select wire:model="status" class="w-full px-3.5 py-2 rounded-xl bg-[#0B0D0F] border border-[#232931] text-xs font-mono text-white focus:border-[#D4A017] focus:outline-none cursor-pointer">
                                <option value="connected">Connected</option>
                                <option value="disconnected">Disconnected</option>
                                <option value="idle">Idle</option>
                                <option value="weak">Weak Signal</option>
                            </select>
                        </div>

                        <!-- Firmware -->
                        <div>
                            <label class="block text-xs font-mono text-[#9CA3AF] uppercase mb-1">Firmware Version</label>
                            <input type="text" wire:model="firmware_version" class="w-full px-3.5 py-2 rounded-xl bg-[#0B0D0F] border border-[#232931] text-xs font-mono text-white focus:border-[#D4A017] focus:outline-none" placeholder="V1.0.0">
                        </div>
                    </div>

                    <!-- Sensitive IDs (Optional / Masked by Default) -->
                    <div class="p-3.5 rounded-2xl bg-[#0B0D0F] border border-[#232931] space-y-3">
                        <span class="text-[11px] font-mono text-[#D4A017] flex items-center gap-1.5">
                            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                            Hardware Identifiers (Auto-discovered on login verification)
                        </span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-mono text-[#6B7280] uppercase">IMEI</label>
                                <input type="text" wire:model="imei" class="w-full px-3 py-1.5 rounded-lg bg-[#171B20] border border-[#232931] text-xs font-mono text-white" placeholder="864500000000000">
                            </div>
                            <div>
                                <label class="block text-[10px] font-mono text-[#6B7280] uppercase">MAC Address</label>
                                <input type="text" wire:model="mac_address" class="w-full px-3 py-1.5 rounded-lg bg-[#171B20] border border-[#232931] text-xs font-mono text-white" placeholder="3C:7A:8B:12:34:56">
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-3">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2.5 rounded-xl bg-[#171B20] text-xs font-semibold text-[#9CA3AF] hover:text-white cursor-pointer">
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#D4A017] to-[#F2C94C] text-[#0B0D0F] font-bold text-xs shadow-lg flex items-center gap-2 disabled:opacity-50 cursor-pointer"
                        >
                            <span wire:loading.remove>{{ $isEditing ? 'Save & Verify' : 'Verify & Add Router' }}</span>
                            <span wire:loading class="inline-flex items-center gap-1.5">
                                <i data-lucide="refresh-cw" class="w-3.5 h-3.5 animate-spin"></i>
                                Verifying Admin Login...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($confirmingDeleteId)
        <div wire:key="router-delete-modal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-3xl bg-[#111418] border border-rose-500/30 p-6 sm:p-7 shadow-2xl space-y-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-400 border border-rose-500/30 flex items-center justify-center">
                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Delete Router?</h3>
                        <p class="text-xs text-[#9CA3AF]">This will remove the router and its telemetry history.</p>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-[#0B0D0F] border border-[#232931] text-xs font-mono text-[#9CA3AF]">
                    Are you sure you want to delete this router? This action cannot be undone.
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button 
                        type="button" 
                        wire:click="cancelDelete" 
                        class="px-4 py-2.5 rounded-xl bg-[#171B20] text-xs font-semibold text-[#9CA3AF] hover:text-white cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button" 
                        wire:click="deleteConfirmed" 
                        class="px-4 py-2.5 rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-bold text-xs shadow-lg shadow-rose-500/20 transition-all flex items-center gap-1.5 cursor-pointer"
                    >
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
