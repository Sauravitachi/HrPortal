@props(['platforms', 'integrations'])

<div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
    <h2 class="text-sm font-bold text-slate-800 tracking-wide uppercase pb-3 border-b border-slate-100">Syndication Channels</h2>
    
    <div class="mt-4 space-y-3.5">
        @foreach($platforms as $p)
        @php 
            $int = $integrations->get($p);
            $isActive = $int && $int->is_active;
        @endphp
        <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 hover:border-slate-200 transition duration-200 bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm
                    {{ $p === 'linkedin' ? 'bg-blue-50 text-blue-500' : ($p === 'indeed' ? 'bg-indigo-50 text-indigo-600' : ($p === 'glassdoor' ? 'bg-emerald-50 text-emerald-500' : 'bg-slate-100 text-slate-500')) }}">
                    <i class="fa-brands fa-{{ $p === 'linkedin' ? 'linkedin-in' : ($p === 'indeed' ? 'square-instagram' : 'stripe-s') }}"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-700 capitalize">{{ $p }}</span>
                    <span class="text-[9px] text-slate-400 block mt-0.5">Syndication Pipeline</span>
                </div>
            </div>
            <span class="px-2 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider
                {{ $isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500' }}">
                {{ $isActive ? 'Active' : 'Inactive' }}
            </span>
        </div>
        @endforeach
    </div>
</div>
