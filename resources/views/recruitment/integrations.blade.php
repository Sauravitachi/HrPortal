@extends('layouts.app')

@section('content')
@php
    $activeCount = $integrations->filter(fn($int) => $int->is_active)->count();
    
    // Custom Premium Brand Assets and Configurations
    $brandAssets = [
        'linkedin' => [
            'name' => 'LinkedIn',
            'category' => 'Professional Networking',
            'color' => '#0077b5',
            'gradient' => 'linear-gradient(135deg, rgba(0, 119, 181, 0.1) 0%, rgba(32, 107, 196, 0.05) 100%)',
            'bgLight' => 'rgba(0, 119, 181, 0.08)',
            'borderLight' => 'rgba(0, 119, 181, 0.15)',
            'icon' => 'fa-brands fa-linkedin-in',
            'desc' => 'Syndicate listings to the world\'s largest professional network, reaching high-caliber candidates directly on their feeds.',
            'features' => ['Direct Feed Syndication', 'Applicant Profile Syncing', 'Enterprise Company Page Support'],
            'placeholderKey' => 'e.g. ln_api_key_849a...',
            'placeholderSecret' => 'e.g. ln_secret_99f3...'
        ],
        'indeed' => [
            'name' => 'Indeed',
            'category' => 'Global Job Search',
            'color' => '#2164f3',
            'gradient' => 'linear-gradient(135deg, rgba(33, 100, 243, 0.1) 0%, rgba(32, 107, 196, 0.05) 100%)',
            'bgLight' => 'rgba(33, 100, 243, 0.08)',
            'borderLight' => 'rgba(33, 100, 243, 0.15)',
            'icon' => 'fa-solid fa-briefcase',
            'desc' => 'Broadcast open roles directly onto the world\'s #1 job search engine, capturing active premium intent globally.',
            'features' => ['Automated XML Syndication', 'Sponsored Post Support', 'Indeed Apply Schema Parsing'],
            'placeholderKey' => 'e.g. ind_api_key_a837...',
            'placeholderSecret' => 'e.g. ind_secret_bc29...'
        ],
        'glassdoor' => [
            'name' => 'Glassdoor',
            'category' => 'Employer Branding',
            'color' => '#0cad41',
            'gradient' => 'linear-gradient(135deg, rgba(12, 173, 65, 0.1) 0%, rgba(0, 200, 83, 0.05) 100%)',
            'bgLight' => 'rgba(12, 173, 65, 0.08)',
            'borderLight' => 'rgba(12, 173, 65, 0.15)',
            'icon' => 'fa-solid fa-door-open',
            'desc' => 'Promote active openings alongside corporate reviews to build candidate trust and improve recruitment conversions.',
            'features' => ['Employer Brand Alignment', 'Interview Reviews Tracking', 'Global Feed Syndication'],
            'placeholderKey' => 'e.g. gd_key_55d1...',
            'placeholderSecret' => 'e.g. gd_secret_2a4e...'
        ],
        'foundit' => [
            'name' => 'Foundit (Monster)',
            'category' => 'APAC & Gulf Portal',
            'color' => '#733f94',
            'gradient' => 'linear-gradient(135deg, rgba(115, 63, 148, 0.1) 0%, rgba(138, 75, 175, 0.05) 100%)',
            'bgLight' => 'rgba(115, 63, 148, 0.08)',
            'borderLight' => 'rgba(115, 63, 148, 0.15)',
            'icon' => 'fa-solid fa-cube',
            'desc' => 'Access one of the largest active recruitment structures across India, Southeast Asia, and the Gulf regions.',
            'features' => ['AI Candidate Matching', 'Automated Search Syndication', 'Direct CV Database Access'],
            'placeholderKey' => 'e.g. fi_api_key_d928...',
            'placeholderSecret' => 'e.g. fi_secret_e301...'
        ],
        'naukri' => [
            'name' => 'Naukri',
            'category' => 'Regional Core Portal',
            'color' => '#092348',
            'gradient' => 'linear-gradient(135deg, rgba(9, 35, 72, 0.1) 0%, rgba(32, 107, 196, 0.05) 100%)',
            'bgLight' => 'rgba(9, 35, 72, 0.08)',
            'borderLight' => 'rgba(9, 35, 72, 0.15)',
            'icon' => 'fa-solid fa-user-graduate',
            'desc' => 'Integrate with India\'s absolute largest recruitment database and job syndication ecosystem natively.',
            'features' => ['Automated Roster Syncing', 'Premium Branding Ads', 'Direct Candidate Alerts'],
            'placeholderKey' => 'e.g. nk_client_id_44b7...',
            'placeholderSecret' => 'e.g. nk_secret_772c...'
        ]
    ];
@endphp

<style>
    /* Premium Modern Workspace Styles */
    .platform-tab-btn {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .platform-tab-btn.active {
        background-color: #ffffff !important;
        border-color: rgba(32, 107, 196, 0.15) !important;
        box-shadow: 0 10px 25px -5px rgba(32, 107, 196, 0.08), 0 4px 12px -5px rgba(0, 0, 0, 0.03) !important;
    }

    .platform-tab-btn.active .brand-indicator {
        opacity: 1 !important;
        transform: scaleY(1) !important;
    }

    /* Modern active indicator bar */
    .brand-indicator {
        transition: all 0.3s ease;
        transform: scaleY(0.3);
        opacity: 0;
    }

    /* Premium iOS style checkbox toggles */
    .switch-checkbox {
        display: none;
    }
    .switch-label {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
        background-color: #cbd5e1;
        border-radius: 24px;
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
        top: 3px;
        left: 3px;
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        box-shadow: 0 2px 5px rgba(0,0,0,0.18);
    }
    .switch-checkbox:checked + .switch-label {
        background-color: #206bc4 !important;
    }
    .switch-checkbox:checked + .switch-label:after {
        left: 23px;
    }
    
    /* Code editor style textarea styling */
    .code-editor-panel {
        font-family: 'Fira Code', 'Courier New', Courier, monospace !important;
        background-color: #0b0f19 !important;
        color: #10b981 !important;
        border: 1px solid #1e293b !important;
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

    /* Animation effects for changing tabs */
    .fade-panel {
        animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(6px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-circle-nodes text-indigo-600"></i>
                <span>Job Board Syndication</span>
                <span class="text-[9px] font-mono font-bold text-indigo-600 bg-indigo-500/10 border border-indigo-500/20 px-2.5 py-0.5 rounded-full uppercase tracking-wider">Workspace</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Configure secure API syndication channels per tenant to broadcast jobs automatically across premium job boards.</p>
        </div>
        
        <div class="flex items-center gap-2">
            <a href="{{ route('jobs.ai.dashboard') }}" class="px-4 py-2 bg-slate-900 border border-slate-200 hover:bg-slate-800 text-slate-700 text-xs font-semibold rounded-xl transition duration-200 shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-chart-line text-indigo-500"></i> AI Dashboard
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
                    <span class="text-sm font-semibold text-slate-800">Tenant Adapters Online</span>
                </div>
                <span class="text-[9px] font-mono text-emerald-600 block mt-0.5">SECURE SaaS ISOLATION ACTIVE</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-600">
                <i class="fa-solid fa-shield-halved text-sm"></i>
            </div>
        </div>

        <div class="glass-card p-4.5 rounded-2xl flex items-center justify-between border-l-4 border-l-purple-500">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Background Queue</span>
                <span class="text-sm font-semibold text-slate-800 block mt-0.5">Active & Listening</span>
                <span class="text-[9px] font-mono text-purple-600 block mt-0.5">ISOLATION: JOB-POST-BOUNDED</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-600">
                <i class="fa-solid fa-server text-sm"></i>
            </div>
        </div>

        <div class="glass-card p-4.5 rounded-2xl flex items-center justify-between border-l-4 border-l-amber-500">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Encryption Level</span>
                <span class="text-sm font-semibold text-slate-800 block mt-0.5">AES-256 GCM Native</span>
                <span class="text-[9px] font-mono text-amber-600 block mt-0.5">SECURE DATABASE STORAGE</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-600">
                <i class="fa-solid fa-key text-sm"></i>
            </div>
        </div>

    </div>

    <!-- Main Workspace Layout (2-Column Tabs Grid) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left Panel: Platform Tabs Selector -->
        <div class="lg:col-span-4 space-y-3">
            <x-recruitment-channels-selector 
                :platforms="$platforms" 
                :integrations="$integrations" 
                :brandAssets="$brandAssets" 
            />
            
            <!-- Security Isolation Notice -->
            <div class="bg-indigo-50/40 border border-indigo-100 rounded-2xl p-4 space-y-2 relative overflow-hidden">
                <div class="flex gap-3">
                    <i class="fa-solid fa-circle-info text-indigo-500 text-sm mt-0.5"></i>
                    <div>
                        <h5 class="text-[11px] font-bold text-slate-800">Strict Tenant Boundaries</h5>
                        <p class="text-[10px] text-slate-500 leading-relaxed mt-1">
                            Your API integration tokens are fully isolated to this tenant. Under AES-256 database-level encryption, even global platform administrators cannot read your plain-text secret keys.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Active Platform Configuration Editor -->
        <div class="lg:col-span-8">
            <div class="glass-card rounded-3xl overflow-hidden border border-slate-150 shadow-sm relative min-h-[500px]">
                @foreach($platforms as $pKey => $pName)
                    <x-recruitment-channel-settings-panel 
                        :platformKey="$pKey" 
                        :platformName="$pName" 
                        :integration="$integrations->get($pKey)" 
                        :assets="$brandAssets[$pKey]" 
                    />
                @endforeach
            </div>
        </div>

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

    /**
     * Tab Switcher Handler
     */
    function switchPlatform(platformKey) {
        // 1. Hide all panels
        document.querySelectorAll('.platform-settings-panel').forEach(panel => {
            panel.classList.add('hidden');
        });

        // 2. Deactivate all buttons
        document.querySelectorAll('.platform-tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        // 3. Show active panel and button states
        const activePanel = document.getElementById('panel_' + platformKey);
        const activeBtn = document.getElementById('tab_btn_' + platformKey);
        
        if (activePanel) {
            activePanel.classList.remove('hidden');
        }
        if (activeBtn) {
            activeBtn.classList.add('active');
        }

        // 4. Record state in localStorage for persistent tab index reloading
        localStorage.setItem('active_integration_platform', platformKey);
    }

    // Run initial parsing checks and tab initialization on load
    document.addEventListener("DOMContentLoaded", function() {
        @foreach($platforms as $pKey => $pName)
            validateLiveJson('{{ $pKey }}');
        @endforeach

        // Persistent Tab Selection resolution
        const urlParams = new URLSearchParams(window.location.search);
        let activeTab = urlParams.get('platform') || localStorage.getItem('active_integration_platform') || 'linkedin';
        
        // Safety check if tab key exists
        if (!document.getElementById('panel_' + activeTab)) {
            activeTab = 'linkedin';
        }

        switchPlatform(activeTab);
    });
</script>
@endsection
