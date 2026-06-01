@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <span>Job Board Integrations</span>
                <span class="text-[9px] font-mono text-slate-400 bg-slate-100 border px-2 py-0.5 rounded-full uppercase tracking-wider">Settings</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Configure and manage API syndication credentials to automatically publish active job postings to external job boards.</p>
        </div>
        
        <a href="{{ route('jobs.ai.dashboard') }}" class="px-4 py-2 border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition duration-200 shadow-sm flex items-center gap-1.5">
            <i class="fa-solid fa-chart-line"></i> AI Dashboard
        </a>
    </div>

    <!-- Main Settings Panel -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($platforms as $pKey => $pName)
        @php 
            $int = $integrations->get($pKey);
            $isActive = $int && $int->is_active;
        @endphp
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5.5 shadow-sm space-y-4 hover:border-slate-300/80 transition duration-350 relative overflow-hidden group">
            <div class="absolute -top-10 -left-10 w-20 h-20 bg-indigo-500/5 rounded-full blur-xl pointer-events-none"></div>

            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold
                        {{ $pKey === 'linkedin' ? 'bg-blue-50 text-blue-600 border border-blue-100' : ($pKey === 'indeed' ? 'bg-indigo-50 text-indigo-600 border border-indigo-100' : ($pKey === 'glassdoor' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200')) }}">
                        <i class="fa-brands fa-{{ $pKey === 'linkedin' ? 'linkedin-in' : ($pKey === 'indeed' ? 'square-instagram' : 'stripe-s') }}"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-800 tracking-wide uppercase">{{ $pName }} Integration</h3>
                        <span class="text-[9px] text-slate-400 block mt-0.5">Automated publishing adapter</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-[9px] font-bold uppercase tracking-wider
                        {{ $isActive ? 'text-emerald-500' : 'text-slate-400' }}">
                        {{ $isActive ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>
            </div>

            <!-- Form -->
            <form action="{{ route('jobs.integrations.save') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="platform" value="{{ $pKey }}">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Client Key / API Key</label>
                        <input type="text" name="api_key" value="{{ $int->api_key ?? '' }}" placeholder="Key..." class="block w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-600 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Client Secret / Secret Key</label>
                        <input type="password" name="api_secret" value="{{ $int->api_secret ?? '' }}" placeholder="Secret..." class="block w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-600 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Custom Settings (JSON Format)</label>
                    <textarea name="settings_json" rows="3" placeholder='e.g. {"access_token": "YOUR_TOKEN", "organization_id": "YOUR_ORG_ID"}' class="block w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-600 focus:outline-none focus:ring-1 focus:ring-indigo-500 font-mono">{{ $int && $int->settings ? json_encode($int->settings, JSON_PRETTY_PRINT) : '' }}</textarea>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ $isActive ? 'checked' : '' }} class="rounded text-indigo-600 focus:ring-indigo-500">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Enable Automated Publishing</span>
                    </label>

                    <button type="submit" class="px-4.5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-[10px] font-bold transition duration-200 shadow-sm flex items-center gap-1">
                        <i class="fa-solid fa-floppy-disk"></i> Save Key
                    </button>
                </div>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endsection
