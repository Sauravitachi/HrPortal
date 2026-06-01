@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                <span>AI Recruitment Suite</span>
                <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-full uppercase tracking-wider">Active</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Harness advanced machine learning models to parser resumes, score candidates, generate interview kits, and syndicate listings.</p>
        </div>
        
        <div class="flex gap-2">
            <a href="{{ route('jobs.index') }}" class="px-4 py-2 border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition duration-200 shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-briefcase"></i> Job Pipeline
            </a>
            <a href="{{ route('jobs.integrations') }}" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-xs font-semibold rounded-xl transition duration-200 shadow-md flex items-center gap-1.5">
                <i class="fa-solid fa-circle-nodes"></i> Board Integrations
            </a>
        </div>
    </div>

    <!-- Analytics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <x-recruitment-stats-card title="Excellent Matches" :count="$excellentCount" subtitle="<i class='fa-solid fa-arrow-trend-up'></i> Score &ge; 90%" color="emerald" icon="fa-circle-check" />
        <x-recruitment-stats-card title="Strong Matches" :count="$strongCount" subtitle="<i class='fa-solid fa-arrow-trend-up'></i> Score 80% - 89%" color="indigo" icon="fa-chart-line" />
        <x-recruitment-stats-card title="Moderate Matches" :count="$moderateCount" subtitle="<i class='fa-solid fa-scale-balanced'></i> Score 60% - 79%" color="amber" icon="fa-scale-balanced" />
        <x-recruitment-stats-card title="Weak Matches" :count="$weakCount" subtitle="<i class='fa-solid fa-triangle-exclamation'></i> Score &lt; 50%" color="red" icon="fa-triangle-exclamation" />
    </div>

    <!-- Active Feed Links Information -->
    <x-recruitment-feed-links />

    <!-- Main Grid: Top Ranked Candidates & Integrations Status -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Top Candidates -->
        <div class="lg:col-span-2">
            <x-recruitment-top-candidates :topCandidates="$topCandidates" />
        </div>

        <!-- Right Side: Publishing Channels & Log History -->
        <div class="space-y-6">
            <!-- Active Channels list -->
            <x-recruitment-channels-list :platforms="$platforms" :integrations="$integrations" />

            <!-- Manual Publishing Panel -->
            <x-recruitment-quick-publish :jobs="$jobs" :platforms="$platforms" />
        </div>
    </div>
</div>
@endsection
