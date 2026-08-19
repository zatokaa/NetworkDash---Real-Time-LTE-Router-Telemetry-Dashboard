@props([
    'enodeb' => '2994',
    'cell' => '2',
    'globalCellId' => 'BB202',
    'pci' => '11',
    'earfcn' => '39146',
    'band' => 'B40',
])

<div x-data="{
    copied: '',
    copy(text, key) {
        navigator.clipboard.writeText(text);
        this.copied = key;
        setTimeout(() => this.copied = '', 2000);
    }
}" class="space-y-3">
    <div class="grid grid-cols-2 gap-3">
        <!-- eNodeB -->
        <div class="p-3 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between group">
            <div>
                <span class="text-[10px] uppercase font-mono text-[#9CA3AF] block">eNodeB ID</span>
                <span class="text-sm font-bold font-mono text-white">{{ $enodeb ?? '--' }}</span>
            </div>
            <button 
                type="button" 
                @click="copy('{{ $enodeb }}', 'enodeb')" 
                title="Copy eNodeB"
                class="p-1.5 rounded-lg bg-[#171B20] text-[#9CA3AF] hover:text-[#F2C94C] transition-colors"
            >
                <span x-show="copied !== 'enodeb'"><i data-lucide="copy" class="w-3.5 h-3.5"></i></span>
                <span x-show="copied === 'enodeb'" style="display:none;" class="text-emerald-400"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>
            </button>
        </div>

        <!-- Cell ID -->
        <div class="p-3 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between group">
            <div>
                <span class="text-[10px] uppercase font-mono text-[#9CA3AF] block">Sector / Cell</span>
                <span class="text-sm font-bold font-mono text-white">{{ $cell ?? '--' }}</span>
            </div>
            <button 
                type="button" 
                @click="copy('{{ $cell }}', 'cell')" 
                title="Copy Cell ID"
                class="p-1.5 rounded-lg bg-[#171B20] text-[#9CA3AF] hover:text-[#F2C94C] transition-colors"
            >
                <span x-show="copied !== 'cell'"><i data-lucide="copy" class="w-3.5 h-3.5"></i></span>
                <span x-show="copied === 'cell'" style="display:none;" class="text-emerald-400"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>
            </button>
        </div>

        <!-- Global Cell ID (ECI) -->
        <div class="p-3 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between group">
            <div>
                <span class="text-[10px] uppercase font-mono text-[#9CA3AF] block">Global Cell ID (ECI)</span>
                <span class="text-sm font-bold font-mono text-white">{{ $globalCellId ?? '--' }}</span>
            </div>
            <button 
                type="button" 
                @click="copy('{{ $globalCellId }}', 'globalCellId')" 
                title="Copy Global Cell ID"
                class="p-1.5 rounded-lg bg-[#171B20] text-[#9CA3AF] hover:text-[#F2C94C] transition-colors"
            >
                <span x-show="copied !== 'globalCellId'"><i data-lucide="copy" class="w-3.5 h-3.5"></i></span>
                <span x-show="copied === 'globalCellId'" style="display:none;" class="text-emerald-400"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>
            </button>
        </div>

        <!-- Physical Cell ID (PCI) -->
        <div class="p-3 rounded-xl bg-[#0B0D0F] border border-[#232931] flex items-center justify-between group">
            <div>
                <span class="text-[10px] uppercase font-mono text-[#9CA3AF] block">Physical Cell ID (PCI)</span>
                <span class="text-sm font-bold font-mono text-white">{{ $pci ?? '--' }}</span>
            </div>
            <button 
                type="button" 
                @click="copy('{{ $pci }}', 'pci')" 
                title="Copy PCI"
                class="p-1.5 rounded-lg bg-[#171B20] text-[#9CA3AF] hover:text-[#F2C94C] transition-colors"
            >
                <span x-show="copied !== 'pci'"><i data-lucide="copy" class="w-3.5 h-3.5"></i></span>
                <span x-show="copied === 'pci'" style="display:none;" class="text-emerald-400"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>
            </button>
        </div>
    </div>
</div>
