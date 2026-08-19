<div class="max-w-md mx-auto my-6 sm:my-12">
    <!-- Bento Login Card -->
    <div class="relative overflow-hidden rounded-3xl bg-[#111418] border border-[#232931] p-6 sm:p-8 shadow-2xl shadow-black/60">
        <!-- Ambient Glow -->
        <div class="absolute -top-12 -right-12 w-40 h-40 bg-[#D4A017]/10 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Header -->
        <div class="text-center mb-8 relative z-10">
            <div class="flex items-center justify-center mb-4">
                <img src="{{ asset('images/logo-horizontal.png') }}" alt="NetworkDash" class="h-10 w-auto object-contain">
            </div>
            <h2 class="text-xl font-bold tracking-tight text-white">Sign In</h2>
            <p class="text-xs text-[#9CA3AF] mt-1">Access your 4G LTE Router Monitoring Dashboard</p>
        </div>

        <!-- Form -->
        <form wire:submit.prevent="login" class="space-y-5 relative z-10">
            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-[#9CA3AF] mb-1.5 font-mono">
                    Email Address
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9CA3AF]">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </div>
                    <input 
                        type="email" 
                        id="email" 
                        wire:model.live.debounce.300ms="email"
                        class="w-full pl-10 pr-4 py-2.5 bg-[#0B0D0F] border @error('email') border-red-500 @else border-[#232931] @enderror rounded-xl text-sm text-white placeholder-gray-600 focus:outline-none focus:border-[#D4A017] focus:ring-1 focus:ring-[#D4A017] transition-all font-mono"
                        placeholder="admin@networkdash.local"
                        autocomplete="email"
                        required
                    >
                </div>
                @error('email')
                    <p class="mt-1.5 text-xs text-red-400 font-mono flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5 inline"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Password -->
            <div x-data="{ show: false }">
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-[#9CA3AF] font-mono">
                        Password
                    </label>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9CA3AF]">
                        <i data-lucide="key-round" class="w-4 h-4"></i>
                    </div>
                    <input 
                        :type="show ? 'text' : 'password'" 
                        id="password" 
                        wire:model.live.debounce.300ms="password"
                        class="w-full pl-10 pr-10 py-2.5 bg-[#0B0D0F] border @error('password') border-red-500 @else border-[#232931] @enderror rounded-xl text-sm text-white placeholder-gray-600 focus:outline-none focus:border-[#D4A017] focus:ring-1 focus:ring-[#D4A017] transition-all font-mono"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                    >
                    <button 
                        type="button" 
                        @click="show = !show" 
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#9CA3AF] hover:text-white transition-colors"
                        tabindex="-1"
                    >
                        <span x-show="!show"><i data-lucide="eye" class="w-4 h-4"></i></span>
                        <span x-show="show" style="display:none;"><i data-lucide="eye-off" class="w-4 h-4"></i></span>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1.5 text-xs text-red-400 font-mono flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5 inline"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 cursor-pointer select-none text-[#9CA3AF] hover:text-white transition-colors">
                    <input 
                        type="checkbox" 
                        wire:model="remember"
                        class="w-4 h-4 rounded bg-[#0B0D0F] border-[#232931] text-[#D4A017] focus:ring-[#D4A017] focus:ring-offset-0 focus:ring-offset-transparent cursor-pointer"
                    >
                    <span>Remember me on this device</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                wire:loading.attr="disabled"
                class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-[#D4A017] to-[#F2C94C] text-[#0B0D0F] font-bold text-sm tracking-wide shadow-lg shadow-[#D4A017]/20 hover:brightness-110 active:scale-[0.99] transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
                <span wire:loading.remove class="flex items-center gap-2">
                    <i data-lucide="log-in" class="w-4 h-4 stroke-[2.5]"></i>
                    Sign In to Dashboard
                </span>
                <span wire:loading class="inline-flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-[#0B0D0F]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Authenticating...
                </span>
            </button>
        </form>

        <!-- Footer Notice / Quick Demo Info -->
        <div class="mt-6 pt-6 border-t border-[#171B20] text-center">
            <div class="p-3 rounded-xl bg-[#0B0D0F]/60 border border-[#232931] text-xs font-mono text-[#9CA3AF] text-left">
                <span class="text-[#F2C94C] font-semibold block mb-1">⚡ Default Admin Credentials:</span>
                <div>Email: <span class="text-white">admin@networkdash.local</span></div>
                <div>Pass: <span class="text-white">admin1234</span></div>
            </div>
            <div class="mt-4">
                <a href="{{ route('register') }}" class="text-xs text-[#9CA3AF] hover:text-[#F2C94C] transition-colors">
                    Don't have an account? <span class="text-[#D4A017] underline underline-offset-4">Create Account</span>
                </a>
            </div>
        </div>
    </div>
</div>
