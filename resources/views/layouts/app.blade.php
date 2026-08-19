<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} - {{ config('app.name', 'NetworkDash') }}</title>

    <!-- Favicon & Icons -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#0B0D0F] text-[#F5F5F5] min-h-screen font-sans selection:bg-[#D4A017] selection:text-[#0B0D0F] antialiased flex flex-col justify-between"
      x-data="{
          mobileMenuOpen: false,
          notifications: [],
          notify(message, type = 'success') {
              const id = Date.now();
              this.notifications.push({ id, message, type });
              setTimeout(() => {
                  this.notifications = this.notifications.filter(n => n.id !== id);
              }, 4000);
          }
      }"
      @notify.window="notify($event.detail.message, $event.detail.type)"
>
    <!-- Toast Notifications Container -->
    <div class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none">
        <template x-for="item in notifications" :key="item.id">
            <div class="pointer-events-auto p-4 rounded-2xl bg-[#171B20] border border-[#232931] shadow-2xl flex items-center gap-3 transform transition-all duration-300">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-sm font-bold"
                     :class="item.type === 'success' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/10 text-rose-400 border border-rose-500/30'">
                    <template x-if="item.type === 'success'">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                    </template>
                    <template x-if="item.type !== 'success'">
                        <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                    </template>
                </div>
                <div class="flex-1">
                    <p class="text-xs font-semibold text-white" x-text="item.message"></p>
                </div>
            </div>
        </template>
    </div>

    <div>
        <!-- Top Navigation / Brand Bar -->
        <header class="border-b border-[#171B20] bg-[#111418]/90 backdrop-blur-md sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
                <!-- Brand & Logo -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                        <img src="{{ asset('images/logo-horizontal.png') }}" alt="NetworkDash" class="h-8 sm:h-9 w-auto object-contain group-hover:brightness-110 transition-all">
                        <span class="text-[10px] font-mono font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-[#171B20] text-[#D4A017] border border-[#D4A017]/30 hidden lg:inline-block">4G LTE</span>
                    </a>
                </div>

                <!-- Right Side Actions & User Status -->
                <div class="flex items-center gap-2 sm:gap-3">
                    @auth
                        <!-- History Link -->
                        <a href="{{ route('history.index') }}" class="px-3 py-1.5 rounded-xl bg-[#171B20] hover:bg-[#232931] border border-[#232931] text-xs font-mono text-[#F5F5F5] flex items-center gap-1.5 transition-colors @if(request()->routeIs('history.*')) border-[#D4A017]/50 text-[#F2C94C] @endif">
                            <i data-lucide="history" class="w-3.5 h-3.5 text-[#F2C94C]"></i>
                            <span class="hidden sm:inline">History</span>
                        </a>

                        <!-- Routers Link -->
                        <a href="{{ route('routers.index') }}" class="px-3 py-1.5 rounded-xl bg-[#171B20] hover:bg-[#232931] border border-[#232931] text-xs font-mono text-[#F5F5F5] flex items-center gap-1.5 transition-colors @if(request()->routeIs('routers.*')) border-[#D4A017]/50 text-[#F2C94C] @endif">
                            <i data-lucide="router" class="w-3.5 h-3.5 text-[#F2C94C]"></i>
                            <span class="hidden sm:inline">Routers</span>
                        </a>

                        <!-- Settings Link -->
                        <a href="{{ route('settings.index') }}" class="px-3 py-1.5 rounded-xl bg-[#171B20] hover:bg-[#232931] border border-[#232931] text-xs font-mono text-[#F5F5F5] flex items-center gap-1.5 transition-colors @if(request()->routeIs('settings.*')) border-[#D4A017]/50 text-[#F2C94C] @endif" title="Thresholds & Settings">
                            <i data-lucide="settings" class="w-3.5 h-3.5 text-[#F2C94C]"></i>
                            <span class="hidden sm:inline">Settings</span>
                        </a>

                        <!-- Connection Status Badge -->
                        <div class="hidden lg:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[#0B0D0F] border border-[#232931] text-xs font-mono">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-emerald-400 font-bold">CONNECTED</span>
                        </div>

                        <!-- User Profile & Sign Out -->
                        <div class="flex items-center gap-2 pl-2 border-l border-[#232931]">
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#171B20] to-[#232931] border border-[#232931] flex items-center justify-center text-xs font-bold font-mono text-[#F2C94C] shadow-inner">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <span class="text-xs font-medium text-white hidden md:block">{{ auth()->user()->name }}</span>
                            
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button 
                                    type="submit" 
                                    title="Sign Out"
                                    class="p-2 rounded-xl bg-[#171B20] hover:bg-rose-500/10 border border-[#232931] hover:border-rose-500/30 text-[#9CA3AF] hover:text-rose-400 transition-colors"
                                >
                                    <i data-lucide="log-out" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="flex items-center gap-2">
                            <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-xl bg-[#171B20] hover:bg-[#232931] border border-[#232931] text-xs font-semibold text-white transition-colors">
                                Sign In
                            </a>
                            <a href="{{ route('register') }}" class="px-3.5 py-1.5 rounded-xl bg-[#D4A017] hover:bg-[#F2C94C] text-xs font-bold text-[#0B0D0F] shadow-md transition-colors">
                                Register
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Main Content Area with Mobile Safe Bottom Padding -->
        <main class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 pb-28 md:pb-8">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

    @auth
        <!-- Mobile Bottom Navigation Bar (Screens < 768px) -->
        <nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-[#111418]/95 backdrop-blur-xl border-t border-[#232931] shadow-2xl px-2 py-2">
            <div class="grid grid-cols-4 items-center text-center font-mono text-[10px]">
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 py-1 px-2 rounded-xl transition-colors @if(request()->routeIs('dashboard')) text-[#F2C94C] font-bold @else text-[#9CA3AF] hover:text-white @endif">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span>Dashboard</span>
                </a>

                <!-- History -->
                <a href="{{ route('history.index') }}" class="flex flex-col items-center gap-1 py-1 px-2 rounded-xl transition-colors @if(request()->routeIs('history.*')) text-[#F2C94C] font-bold @else text-[#9CA3AF] hover:text-white @endif">
                    <i data-lucide="history" class="w-5 h-5"></i>
                    <span>History</span>
                </a>

                <!-- Routers -->
                <a href="{{ route('routers.index') }}" class="flex flex-col items-center gap-1 py-1 px-2 rounded-xl transition-colors @if(request()->routeIs('routers.*')) text-[#F2C94C] font-bold @else text-[#9CA3AF] hover:text-white @endif">
                    <i data-lucide="router" class="w-5 h-5"></i>
                    <span>Routers</span>
                </a>

                <!-- Settings -->
                <a href="{{ route('settings.index') }}" class="flex flex-col items-center gap-1 py-1 px-2 rounded-xl transition-colors @if(request()->routeIs('settings.*')) text-[#F2C94C] font-bold @else text-[#9CA3AF] hover:text-white @endif">
                    <i data-lucide="settings" class="w-5 h-5"></i>
                    <span>Settings</span>
                </a>
            </div>
        </nav>
    @endauth

    <!-- Minimal Modern Footer -->
    <footer class="border-t border-[#171B20] py-5 bg-[#0B0D0F] text-center text-xs text-[#9CA3AF] mb-16 md:mb-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs font-mono">
                <img src="{{ asset('images/logo-icon.png') }}" alt="NetworkDash" class="w-4 h-4 object-contain">
                <span class="text-white font-bold">NetworkDash</span>
                <span class="text-[#6B7280]">v1.0 • 4G LTE Telemetry</span>
            </div>
            <div class="flex items-center gap-3 text-[11px] font-mono text-[#6B7280]">
                <span>Laravel {{ app()->version() }}</span>
                <span>•</span>
                <span>Livewire 3</span>
                <span>•</span>
                <span>Tailwind CSS</span>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
