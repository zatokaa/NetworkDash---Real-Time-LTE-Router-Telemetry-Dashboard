@props([
    'status' => 'GOOD',
    'size' => 'md',
])

@php
    $statusUpper = strtoupper($status ?? 'UNKNOWN');
    
    $config = match($statusUpper) {
        'EXCELLENT' => [
            'bg' => 'bg-emerald-500/10',
            'border' => 'border-emerald-500/30',
            'text' => 'text-emerald-400',
            'dot' => 'bg-emerald-400',
            'glow' => 'shadow-emerald-500/20',
        ],
        'VERY GOOD' => [
            'bg' => 'bg-teal-500/10',
            'border' => 'border-teal-500/30',
            'text' => 'text-teal-300',
            'dot' => 'bg-teal-400',
            'glow' => 'shadow-teal-500/20',
        ],
        'GOOD' => [
            'bg' => 'bg-[#D4A017]/10',
            'border' => 'border-[#D4A017]/30',
            'text' => 'text-[#F2C94C]',
            'dot' => 'bg-[#F2C94C]',
            'glow' => 'shadow-[#D4A017]/20',
        ],
        'FAIR' => [
            'bg' => 'bg-amber-500/10',
            'border' => 'border-amber-500/30',
            'text' => 'text-amber-400',
            'dot' => 'bg-amber-400',
            'glow' => 'shadow-amber-500/20',
        ],
        'POOR', 'WEAK' => [
            'bg' => 'bg-rose-500/10',
            'border' => 'border-rose-500/30',
            'text' => 'text-rose-400',
            'dot' => 'bg-rose-400',
            'glow' => 'shadow-rose-500/20',
        ],
        'CONNECTED' => [
            'bg' => 'bg-emerald-500/10',
            'border' => 'border-emerald-500/30',
            'text' => 'text-emerald-400',
            'dot' => 'bg-emerald-400 animate-pulse',
            'glow' => 'shadow-emerald-500/20',
        ],
        'DISCONNECTED' => [
            'bg' => 'bg-rose-500/10',
            'border' => 'border-rose-500/30',
            'text' => 'text-rose-400',
            'dot' => 'bg-rose-400',
            'glow' => 'shadow-rose-500/20',
        ],
        default => [
            'bg' => 'bg-[#171B20]',
            'border' => 'border-[#232931]',
            'text' => 'text-[#9CA3AF]',
            'dot' => 'bg-[#9CA3AF]',
            'glow' => '',
        ]
    };

    $sizeClasses = match($size) {
        'sm' => 'px-2 py-0.5 text-[10px]',
        'lg' => 'px-3.5 py-1.5 text-sm font-bold',
        default => 'px-2.5 py-1 text-xs font-semibold',
    };
@endphp

<span class="inline-flex items-center gap-1.5 rounded-full font-mono tracking-wider uppercase border {{ $config['bg'] }} {{ $config['border'] }} {{ $config['text'] }} {{ $sizeClasses }} shadow-sm {{ $config['glow'] }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $config['dot'] }}"></span>
    {{ $status }}
</span>
