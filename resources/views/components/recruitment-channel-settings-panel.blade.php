@props(['platformKey', 'platformName', 'integration', 'assets'])

@php
    $isActive = $integration && $integration->is_active;
@endphp

<!-- Platform Settings Panel -->
<div id="panel_{{ $platformKey }}" class="platform-settings-panel fade-panel space-y-6 hidden">
    
    <!-- Dynamic Brand Header Banner -->
    <div class="p-6 text-slate-800 border-b border-slate-100 relative" style="background: {{ $assets['gradient'] }}">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl shadow-sm" style="background-color: #ffffff; border: 1px solid {{ $assets['borderLight'] }}; color: {{ $assets['color'] }}">
                    <i class="{{ $assets['icon'] }}"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">{{ $platformName }} Integrator</h3>
                    <p class="text-[10.5px] text-slate-500 leading-normal mt-0.5">{{ $assets['category'] }} • Syndicate openings securely</p>
                </div>
            </div>
            
            <!-- Toggle switch displayed prominently in header -->
            <div class="flex items-center gap-2 bg-white/80 border border-slate-200 px-3 py-1.5 rounded-xl shadow-2xs">
                <span class="text-[9px] font-bold uppercase tracking-wider font-mono {{ $isActive ? 'text-emerald-500' : 'text-slate-400' }}">
                    {{ $isActive ? 'Sync Enabled' : 'Sync Disabled' }}
                </span>
            </div>
        </div>

        <!-- Brief Description -->
        <p class="text-xs text-slate-600 mt-4 leading-relaxed max-w-2xl">{{ $assets['desc'] }}</p>
    </div>

    <!-- Main Form -->
    <form action="{{ route('jobs.integrations.save') }}" method="POST" class="p-6 space-y-6">
        @csrf
        <input type="hidden" name="platform" value="{{ $platformKey }}">
        
        <!-- Core Integration Capabilities Checklist -->
        <div class="space-y-2">
            <h4 class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Supported Syndication Capabilities</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @foreach($assets['features'] as $feat)
                <div class="bg-slate-50/80 border border-slate-150 rounded-xl p-3 flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-check text-[10px]" style="color: {{ $assets['color'] }}"></i>
                    <span class="text-[10.5px] text-slate-600 font-medium">{{ $feat }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Credentials block -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- API Key Input -->
            <div class="space-y-1.5">
                <label class="block text-[9.5px] font-bold text-slate-500 uppercase tracking-wider">API Client Key / ID</label>
                <div class="relative">
                    <input type="text" name="api_key" value="{{ $integration->api_key ?? '' }}" placeholder="{{ $assets['placeholderKey'] }}" class="pl-9.5 pr-9.5 block w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition duration-150">
                </div>
            </div>

            <!-- API Secret Input -->
            <div class="space-y-1.5">
                <label class="block text-[9.5px] font-bold text-slate-500 uppercase tracking-wider">API Client Secret</label>
                <div class="relative">
                    <input type="password" id="secret_{{ $platformKey }}" name="api_secret" value="{{ $integration->api_secret ?? '' }}" placeholder="{{ $assets['placeholderSecret'] }}" class="pl-9.5 block w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition duration-150">
                    <button type="button" onclick="togglePasswordVisibility('secret_{{ $platformKey }}')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                        
                    </button>
                </div>
            </div>
        </div>

        <!-- JSON Meta Properties editor (Console design) -->
        <div class="space-y-1.5">
            <div class="flex items-center justify-between">
                <label class="block text-[9.5px] font-bold text-slate-500 uppercase tracking-wider">JSON Meta Properties</label>
                <span id="json_status_{{ $platformKey }}" class="text-[9px] font-bold uppercase tracking-wider flex items-center gap-1 text-slate-400 font-mono">
                    <i class="fa-solid fa-code"></i> JSON READY
                </span>
            </div>
            <div class="relative rounded-2xl overflow-hidden border border-slate-200 shadow-inner">
                <div class="flex items-center justify-between px-4 py-1.5 bg-[#1e293b] border-b border-slate-800 text-[9px] text-slate-400 font-mono">
                    <span>INTEGRATION TERMINAL PROPERTIES</span>
                    <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span> AES-256 ENCRYPTED</span>
                </div>
                <textarea id="json_{{ $platformKey }}" name="settings_json" rows="5" oninput="validateLiveJson('{{ $platformKey }}')" placeholder='e.g. {&#10;  "access_token": "YOUR_OAUTH_TOKEN",&#10;  "organization_id": "YOUR_ORGANIZATION_ID"&#10;}' class="code-editor-panel block w-full p-4 text-xs font-mono focus:outline-none leading-relaxed">{{ $integration && $integration->settings ? json_encode($integration->settings, JSON_PRETTY_PRINT) : '' }}</textarea>
            </div>
        </div>

        <!-- Bottom Controls -->
        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
            <!-- Toggle switch -->
            <label class="flex items-center gap-3 cursor-pointer select-none">
                <input type="checkbox" id="chk_{{ $platformKey }}" name="is_active" value="1" {{ $isActive ? 'checked' : '' }} class="switch-checkbox">
                <label for="chk_{{ $platformKey }}" class="switch-label"></label>
                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">Syndicate To {{ $platformName }}</span>
            </label>

            <!-- Save Button with brand color hover states -->
            <button
                type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl shadow-sm transition duration-200 cursor-pointer"
            >
                <i class="fa-solid fa-floppy-disk text-white"></i>
                <span class="text-white">Save Credentials</span>
            </button>
        </div>
    </form>
</div>
