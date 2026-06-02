@props(['platforms', 'integrations', 'brandAssets'])

<div class="glass-card rounded-2xl p-4.5 space-y-2">
    <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-2 mb-3">Syndication Channels</h4>
    
    @foreach($platforms as $pKey => $pName)
    @php
        $int = $integrations->get($pKey);
        $isActive = $int && $int->is_active;
        $assets = $brandAssets[$pKey];
    @endphp
    <button type="button" onclick="switchPlatform('{{ $pKey }}')" id="tab_btn_{{ $pKey }}" class="platform-tab-btn w-full text-left rounded-xl p-3 border border-transparent flex items-center justify-between hover:bg-slate-50 relative group">
        <!-- Brand Indicator Line -->
        <div class="brand-indicator absolute left-0 top-3 bottom-3 w-1 rounded-r-lg" style="background-color: {{ $assets['color'] }}"></div>
        
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center text-sm transition-all group-hover:scale-105" style="background-color: {{ $assets['bgLight'] }}; border: 1px solid {{ $assets['borderLight'] }}; color: {{ $assets['color'] }}">
                <i class="{{ $assets['icon'] }}"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-800 block leading-tight">{{ $pName }}</span>
                <span class="text-[9px] text-slate-400 block mt-0.5">{{ $assets['category'] }}</span>
            </div>
        </div>

        <!-- Connection Status Badge -->
        <div class="flex items-center gap-1.5">
            @if($isActive)
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 pulse-dot-green"></span>
                <span class="text-[9px] font-bold uppercase font-mono text-emerald-500">Connected</span>
            @else
                <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                <span class="text-[9px] font-bold uppercase font-mono text-slate-400">Inactive</span>
            @endif
        </div>
    </button>
    @endforeach
</div>
