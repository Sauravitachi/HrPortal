@extends('layouts.app')

@section('content')
@php
    $activeCount = $integrations->filter(fn($int) => $int->is_active)->count();
    
    // Custom Brand Assets to override standard default looks
    $brandAssets = [
        'linkedin' => [
            'color' => '#0077b5',
            'bgLight' => 'rgba(0, 119, 181, 0.08)',
            'borderLight' => 'rgba(0, 119, 181, 0.15)',
            'icon' => 'fa-brands fa-linkedin-in',
            'desc' => 'Share professional requisitions and engage with the world\'s largest professional network.',
            'features' => ['1-Click Apply Support', 'Applicant Syncing', 'Company Page Posting'],
            'placeholderKey' => 'e.g. ln_api_key_849a...',
            'placeholderSecret' => 'e.g. ln_secret_99f3...'
        ],
        'indeed' => [
            'color' => '#2164f3',
            'bgLight' => 'rgba(33, 100, 243, 0.08)',
            'borderLight' => 'rgba(33, 100, 243, 0.15)',
            'icon' => 'fa-solid fa-briefcase',
            'desc' => 'Distribute listings directly to the world\'s #1 job search site and capture premium intent.',
            'features' => ['Direct XML Feeds', 'Sponsored Posts', 'Indeed Apply Schema'],
            'placeholderKey' => 'e.g. ind_api_key_a837...',
            'placeholderSecret' => 'e.g. ind_secret_bc29...'
        ],
        'glassdoor' => [
            'color' => '#0cad41',
            'bgLight' => 'rgba(12, 173, 65, 0.08)',
            'borderLight' => 'rgba(12, 173, 65, 0.15)',
            'icon' => 'fa-solid fa-door-open',
            'desc' => 'Promote openings alongside company reviews to cultivate trust and attract elite candidates.',
            'features' => ['Employer Branding Insights', 'Review Analytics Sync', 'Global Job Feeds'],
            'placeholderKey' => 'e.g. gd_key_55d1...',
            'placeholderSecret' => 'e.g. gd_secret_2a4e...'
        ],
        'foundit' => [
            'color' => '#733f94',
            'bgLight' => 'rgba(115, 63, 148, 0.08)',
            'borderLight' => 'rgba(115, 63, 148, 0.15)',
            'icon' => 'fa-solid fa-cube',
            'desc' => 'Broadcast to one of the largest active applicant portals in Asia Pacific & Gulf regions.',
            'features' => ['Resume Matching Engine', 'Automated Search Syndication', 'SMS Candidate Alerts'],
            'placeholderKey' => 'e.g. fi_api_key_d928...',
            'placeholderSecret' => 'e.g. fi_secret_e301...'
        ],
        'naukri' => [
            'color' => '#092348',
            'bgLight' => 'rgba(9, 35, 72, 0.08)',
            'borderLight' => 'rgba(9, 35, 72, 0.15)',
            'icon' => 'fa-solid fa-user-graduate',
            'desc' => 'Syndicate active job postings directly to India\'s largest job finder ecosystem.',
            'features' => ['Resume database access', 'Enterprise Search Credits', 'Premium Branding Templates'],
            'placeholderKey' => 'e.g. nk_client_id_44b7...',
            'placeholderSecret' => 'e.g. nk_secret_772c...'
        ]
    ];
@endphp

<style>
    /* Custom CSS Overrides and Enhancements */
    .brand-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .brand-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -10px rgba(32, 107, 196, 0.12), 0 4px 12px -5px rgba(0, 0, 0, 0.04) !important;
    }
    
    /* Premium iOS style checkbox toggles */
    .switch-checkbox {
        display: none;
    }
    .switch-label {
        position: relative;
        display: inline-block;
        width: 42px;
        height: 22px;
        background-color: #cbd5e1;
        border-radius: 22px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .switch-label:after {
        content: '';
        position: absolute;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background-color: #ffffff;
        top: 2px;
        left: 2px;
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        box-shadow: 0 1px 4px rgba(0,0,0,0.15);
    }
    .switch-checkbox:checked + .switch-label {
        background-color: #206bc4 !important;
    }
    .switch-checkbox:checked + .switch-label:after {
        left: 22px;
    }
    
    /* Code editor style textarea overriding core template background constraints */
    .glass-card textarea.code-textarea {
        background-color: #0f172a !important;
        color: #10b981 !important;
        font-family: 'Fira Code', 'Courier New', Courier, monospace !important;
        font-size: 11px !important;
        line-height: 1.6 !important;
        border: 1px solid #1e293b !important;
        border-radius: 12px !important;
        padding: 12px !important;
    }
    .glass-card textarea.code-textarea:focus {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.15) !important;
        outline: none !important;
    }
    .glass-card textarea.code-textarea::placeholder {
        color: #475569 !important;
    }

    /* Live status pulsing dot */
    @keyframes pulse-green {
        0% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
        }
        70% {
            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }
    .pulse-dot-green {
        animation: pulse-green 2.5s infinite;
    }
</style>

<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-circle-nodes text-indigo-600"></i>
                <span>Job Board Syndication</span>
                <span class="text-[9px] font-mono font-bold text-indigo-600 bg-indigo-500/10 border border-indigo-500/20 px-2 py-0.5 rounded-full uppercase tracking-wider">Channel Hub</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Syndicate job postings automatically to regional and international channels using native REST integration adapters.</p>
        </div>
        
        <div class="flex items-center gap-2">
            <a href="{{ route('jobs.ai.dashboard') }}" class="px-4 py-2 bg-slate-900 border border-slate-200 hover:bg-slate-800 text-slate-700 text-xs font-semibold rounded-xl transition duration-200 shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-chart-line text-indigo-500"></i> AI Screening Report
            </a>
            <a href="{{ route('jobs.index') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition duration-200 shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-briefcase"></i> Job Pipeline
            </a>
        </div>
    </div>

    <!-- Active Connections Metric Dashboard -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <div class="glass-card p-4.5 rounded-2xl flex items-center justify-between border-l-4 border-l-indigo-500">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Channels Configured</span>
                <span class="text-lg font-bold text-slate-800 block">{{ $activeCount }} / 5 Platforms</span>
                <div class="w-24 h-1.5 bg-slate-100 rounded-full mt-1.5 overflow-hidden">
                    <div class="h-full bg-indigo-500 rounded-full" style="width: {{ ($activeCount / 5) * 100 }}%"></div>
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-600">
                <i class="fa-solid fa-network-wired text-sm"></i>
            </div>
        </div>

        <div class="glass-card p-4.5 rounded-2xl flex items-center justify-between border-l-4 border-l-emerald-500">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Syndication Status</span>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 pulse-dot-green inline-block"></span>
                    <span class="text-sm font-semibold text-slate-800">All Adapters Nominal</span>
                </div>
                <span class="text-[9px] font-mono text-emerald-600 block mt-0.5">API HEALTH STATUS: 100%</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-600">
                <i class="fa-solid fa-circle-nodes text-sm"></i>
            </div>
        </div>

        <div class="glass-card p-4.5 rounded-2xl flex items-center justify-between border-l-4 border-l-purple-500">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Background Sync Queue</span>
                <span class="text-sm font-semibold text-slate-800 block mt-0.5">Active & Listening</span>
                <span class="text-[9px] font-mono text-purple-600 block mt-0.5">QUEUE WORKER: REDIS/DATABASE</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-600">
                <i class="fa-solid fa-server text-sm"></i>
            </div>
        </div>

        <div class="glass-card p-4.5 rounded-2xl flex items-center justify-between border-l-4 border-l-amber-500">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Instant Publishing</span>
                <span class="text-sm font-semibold text-slate-800 block mt-0.5">Dual-Stream Syndication</span>
                <span class="text-[9px] font-mono text-amber-600 block mt-0.5">FORMAT: JSON SYNC PAYLOAD</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-600">
                <i class="fa-solid fa-bolt text-sm"></i>
            </div>
        </div>

    </div>

    <!-- Info Notice Card -->
    <div class="glass-card p-5 rounded-2xl flex flex-col md:flex-row md:items-center justify-between bg-indigo-500/5 border border-indigo-500/15 gap-4 relative overflow-hidden">
        <div class="absolute -right-12 -top-12 w-28 h-28 bg-indigo-500/5 rounded-full blur-xl pointer-events-none"></div>
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-600 shrink-0 mt-0.5">
                <i class="fa-solid fa-circle-info text-base"></i>
            </div>
            <div class="space-y-1 max-w-2xl">
                <h4 class="text-xs font-bold text-slate-800">API Credentials and Syndication Rules</h4>
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    By saving valid credentials, active requisitions will sync in real time when publishing from your <strong>Job Pipeline Roster</strong>. Fill out the specific integration fields and customize platform-specific properties inside the JSON settings terminal payload block.
                </p>
            </div>
        </div>
    </div>

    <!-- Platform Adapters Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($platforms as $pKey => $pName)
        @php 
            $int = $integrations->get($pKey);
            $isActive = $int && $int->is_active;
            $assets = $brandAssets[$pKey] ?? [
                'color' => '#64748b',
                'bgLight' => 'rgba(100, 116, 139, 0.08)',
                'borderLight' => 'rgba(100, 116, 139, 0.15)',
                'icon' => 'fa-solid fa-circle-nodes',
                'desc' => 'Automated syndication configuration options for the specified platform adapter.',
                'features' => ['Active XML Syndication', 'Standard Application Schema'],
                'placeholderKey' => 'Key...',
                'placeholderSecret' => 'Secret...'
            ];
        @endphp
        
        <div class="glass-card rounded-3xl p-5.5 space-y-4 brand-card flex flex-col justify-between relative overflow-hidden">
            <!-- Accent Top Stripe representing brand color -->
            <div class="absolute top-0 left-0 right-0 h-1" style="background-color: {{ $assets['color'] }}"></div>
            
            <div class="space-y-4">
                <!-- Card Header -->
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-base" style="background-color: {{ $assets['bgLight'] }}; border: 1px solid {{ $assets['borderLight'] }}; color: {{ $assets['color'] }}">
                            <i class="{{ $assets['icon'] }}"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-slate-800 tracking-wide uppercase">{{ $pName }}</h3>
                            <span class="text-[9px] text-slate-400 block mt-0.5">Syndication Adapter</span>
                        </div>
                    </div>

                    <!-- Modern Switch Toggle -->
                    <div class="flex items-center gap-2.5">
                        <span class="text-[9px] font-bold uppercase tracking-wider font-mono {{ $isActive ? 'text-emerald-500' : 'text-slate-400' }}">
                            {{ $isActive ? 'Active' : 'Disabled' }}
                        </span>
                    </div>
                </div>

                <!-- Short Platform Description -->
                <p class="text-[10.5px] text-slate-500 leading-relaxed">{{ $assets['desc'] }}</p>

                <!-- Core Integration Capabilities -->
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 space-y-1.5">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Adapter Sync Support</span>
                    @foreach($assets['features'] as $feat)
                    <div class="flex items-center gap-2 text-[10px] text-slate-600">
                        <i class="fa-solid fa-circle-check text-[9px]" style="color: {{ $assets['color'] }}"></i>
                        <span>{{ $feat }}</span>
                    </div>
                    @endforeach
                </div>

                <!-- Configuration Form -->
                <form action="{{ route('jobs.integrations.save') }}" method="POST" class="space-y-4 pt-1">
                    @csrf
                    <input type="hidden" name="platform" value="{{ $pKey }}">
                    
                    <!-- Client Credentials -->
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">API Client Key / ID</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-[10px]">
                                    <i class="fa-solid fa-key"></i>
                                </span>
                                <input type="text" name="api_key" value="{{ $int->api_key ?? '' }}" placeholder="{{ $assets['placeholderKey'] }}" class="pl-9 block w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition duration-150">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">API Client Secret</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-[10px]">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                <input type="password" id="secret_{{ $pKey }}" name="api_secret" value="{{ $int->api_secret ?? '' }}" placeholder="{{ $assets['placeholderSecret'] }}" class="pl-9 pr-9 block w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition duration-150">
                                <button type="button" onclick="togglePasswordVisibility('secret_{{ $pKey }}')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                                    <i class="fa-regular fa-eye text-xs" id="eye_secret_{{ $pKey }}"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- JSON Options Terminal Block -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">JSON Meta Properties</label>
                            <span id="json_status_{{ $pKey }}" class="text-[9px] font-bold uppercase tracking-wider flex items-center gap-1 text-slate-400 font-mono">
                                <i class="fa-solid fa-code"></i> JSON OK
                            </span>
                        </div>
                        <div class="relative rounded-xl overflow-hidden border border-slate-200">
                            <textarea id="json_{{ $pKey }}" name="settings_json" rows="4" oninput="validateLiveJson('{{ $pKey }}')" placeholder='e.g. {&#10;  "access_token": "YOUR_TOKEN",&#10;  "organization_id": "YOUR_ORG_ID"&#10;}' class="code-textarea block w-full">{{ $int && $int->settings ? json_encode($int->settings, JSON_PRETTY_PRINT) : '' }}</textarea>
                        </div>
                    </div>

                    <!-- Bottom Controls -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 mt-2">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" id="chk_{{ $pKey }}" name="is_active" value="1" {{ $isActive ? 'checked' : '' }} class="switch-checkbox">
                            <label for="chk_{{ $pKey }}" class="switch-label"></label>
                            <span class="text-[9.5px] font-bold text-slate-500 uppercase tracking-wider">Syndication</span>
                        </label>

                        <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-[10px] font-bold transition duration-200 shadow-sm flex items-center gap-1.5 hover:shadow-md active:translate-y-px">
                            <i class="fa-solid fa-floppy-disk text-[9px]"></i> Save Keys
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
    /**
     * Show/Hide Password Toggle helper
     */
    function togglePasswordVisibility(id) {
        const input = document.getElementById(id);
        const eye = document.getElementById('eye_' + id);
        
        if (input.type === 'password') {
            input.type = 'text';
            eye.classList.remove('fa-eye');
            eye.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            eye.classList.remove('fa-eye-slash');
            eye.classList.add('fa-eye');
        }
    }

    /**
     * Real-time JSON validation feedback
     */
    function validateLiveJson(pKey) {
        const textarea = document.getElementById('json_' + pKey);
        const statusSpan = document.getElementById('json_status_' + pKey);
        const val = textarea.value.trim();
        
        if (val === '') {
            statusSpan.innerHTML = '<i class="fa-solid fa-code"></i> JSON EMPTY';
            statusSpan.className = "text-[9px] font-bold uppercase tracking-wider flex items-center gap-1 text-slate-400 font-mono";
            return;
        }

        try {
            JSON.parse(val);
            statusSpan.innerHTML = '<i class="fa-solid fa-circle-check"></i> JSON VALID';
            statusSpan.className = "text-[9px] font-bold uppercase tracking-wider flex items-center gap-1 text-emerald-500 font-mono";
        } catch (e) {
            statusSpan.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> INVALID JSON';
            statusSpan.className = "text-[9px] font-bold uppercase tracking-wider flex items-center gap-1 text-red-500 font-mono";
        }
    }

    // Run initial parsing checks for existing fields on load
    document.addEventListener("DOMContentLoaded", function() {
        @foreach($platforms as $pKey => $pName)
            validateLiveJson('{{ $pKey }}');
        @endforeach
    });
</script>
@endsection
