@extends('layouts.app')

@section('content')
<!-- Scoped Styles for Premium AI Candidate Report -->
<style>
    /* Sleek Transitions and Interactive Elevates */
    .report-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(226, 232, 240, 0.8) !important;
    }
    .report-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 20px -8px rgba(32, 107, 196, 0.08) !important;
        border-color: rgba(32, 107, 196, 0.2) !important;
    }
    
    /* Elegant Horizontal Progress Indicators */
    .metric-bar-track {
        background-color: #f1f5f9;
        border-radius: 9999px;
        height: 6px;
        overflow: hidden;
        position: relative;
    }
    .metric-bar-fill {
        height: 100%;
        border-radius: 9999px;
        background: linear-gradient(90deg, #206bc4 0%, #60a5fa 100%);
        transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Interactive Skills Badges */
    .skill-badge {
        transition: all 0.2s ease;
        border: 1px solid rgba(226, 232, 240, 0.9) !important;
        background-color: #ffffff !important;
    }
    .skill-badge:hover {
        border-color: #206bc4 !important;
        background-color: rgba(32, 107, 196, 0.04) !important;
        color: #206bc4 !important;
        transform: translateY(-1px);
    }
    
    /* Quote highlights with border left */
    .ai-quote-card {
        position: relative;
        border-left: 4px solid #206bc4 !important;
        background: linear-gradient(90deg, rgba(32, 107, 196, 0.02) 0%, rgba(255, 255, 255, 0) 100%);
    }

    /* Timelines */
    .timeline-container {
        position: relative;
        padding-left: 1.5rem;
    }
    .timeline-container::before {
        content: '';
        position: absolute;
        left: 4px;
        top: 6px;
        bottom: -16px;
        width: 2px;
        background-color: #f1f5f9;
    }
    .timeline-container:last-child::before {
        display: none;
    }
    .timeline-circle {
        position: absolute;
        left: 0;
        top: 6px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #206bc4;
        border: 2px solid #ffffff;
        box-shadow: 0 0 0 3px rgba(32, 107, 196, 0.15);
    }

    /* Match Recommendations Tags */
    .hiring-tag {
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .hiring-tag-excellent {
        background-color: rgba(16, 185, 129, 0.08) !important;
        color: #10b981 !important;
        border: 1px solid rgba(16, 185, 129, 0.2) !important;
    }
    .hiring-tag-strong {
        background-color: rgba(32, 107, 196, 0.08) !important;
        color: #206bc4 !important;
        border: 1px solid rgba(32, 107, 196, 0.2) !important;
    }
    .hiring-tag-moderate {
        background-color: rgba(245, 158, 11, 0.08) !important;
        color: #d97706 !important;
        border: 1px solid rgba(245, 158, 11, 0.2) !important;
    }
    .hiring-tag-weak {
        background-color: rgba(239, 68, 68, 0.08) !important;
        color: #ef4444 !important;
        border: 1px solid rgba(239, 68, 68, 0.2) !important;
    }

    /* Accordions / Questions */
    .question-row {
        transition: all 0.2s ease;
    }
    .question-row:hover {
        background-color: rgba(246, 248, 251, 0.5);
    }
</style>

<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Header Backlink & Audit Log -->
    <div class="flex items-center justify-between">
        <a href="{{ route('jobs.ai.dashboard') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold flex items-center gap-1.5 transition group">
            <i class="fa-solid fa-arrow-left transition-transform group-hover:-translate-x-1"></i> Back to AI Dashboard
        </a>
        <div class="flex items-center gap-2">
            <span class="text-[10px] text-slate-400 font-mono uppercase tracking-wider bg-white px-3 py-1 rounded-lg border border-slate-200/60 shadow-sm">
                Screened on: {{ $application->matchScore->created_at->format('M d, Y H:i') }}
            </span>
        </div>
    </div>

    <!-- Main Assessor Summary Card -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm relative overflow-hidden report-card">
        <!-- Glow Blob in Background -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10 space-y-6">
            <!-- Top Row: Profile Info & Match Score -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-slate-100">
                <!-- Left: Avatar, Name & Applied Job -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4.5">
                    <div class="w-16 h-16 bg-gradient-to-tr from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white text-2xl font-black shadow-lg shadow-indigo-500/20 shrink-0">
                        {{ strtoupper(substr($application->full_name, 0, 2)) }}
                    </div>
                    <div class="space-y-1">
                        <h1 class="text-2xl font-black text-slate-800 tracking-tight leading-tight">{{ $application->full_name }}</h1>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2.5 py-0.5 bg-indigo-50 text-indigo-600 text-[10px] font-bold uppercase tracking-wider rounded border border-indigo-100/80">
                                {{ $application->jobPost->title }}
                            </span>
                            <span class="text-xs text-slate-400">• Applied Position</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Match Score Dial & Recommendation Pill -->
                <div class="flex items-center gap-5 shrink-0 bg-slate-50/50 border border-slate-100 p-3.5 rounded-2xl shadow-inner">
                    <div class="relative w-24 h-24 flex items-center justify-center">
                        @php
                            $percentage = $application->matchScore->match_score ?? 0;
                            $strokeDash = 2 * pi() * 34; // circum = 2 * pi * r
                            $dashOffset = $strokeDash - ($strokeDash * $percentage / 100);
                        @endphp
                        <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 96 96">
                            <circle cx="48" cy="48" r="34" stroke="#f1f5f9" stroke-width="7" fill="transparent" />
                            <circle cx="48" cy="48" r="34" 
                                    stroke="{{ $percentage >= 90 ? '#10b981' : ($percentage >= 75 ? '#206bc4' : ($percentage >= 60 ? '#f59e0b' : '#ef4444')) }}" 
                                    stroke-width="7" 
                                    fill="transparent" 
                                    stroke-dasharray="{{ $strokeDash }}" 
                                    stroke-dashoffset="{{ $dashOffset }}" 
                                    stroke-linecap="round"
                                    class="transition duration-1000 ease-out" />
                        </svg>
                        <div class="absolute flex flex-col items-center">
                            <span class="text-xl font-black text-slate-800 font-mono tracking-tight">{{ $percentage }}%</span>
                            <span class="text-[7px] font-bold text-slate-400 uppercase tracking-widest block">Match</span>
                        </div>
                    </div>
                    
                    <div class="space-y-1">
                        <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest block">AI RECOMMENDATION</span>
                        @php
                            $tagClass = 'hiring-tag-strong';
                            if ($percentage >= 90) { $tagClass = 'hiring-tag-excellent'; }
                            elseif ($percentage >= 75) { $tagClass = 'hiring-tag-strong'; }
                            elseif ($percentage >= 60) { $tagClass = 'hiring-tag-moderate'; }
                            else { $tagClass = 'hiring-tag-weak'; }
                        @endphp
                        <span class="px-3.5 py-1.5 rounded-xl text-[9px] font-bold uppercase tracking-wider text-center block shadow-sm hiring-tag {{ $tagClass }}">
                            {{ $application->matchScore->hiring_recommendation }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Contact Details & Action Buttons -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-2">
                <!-- Contact Info Details (No truncating cards, spacious horizontal display) -->
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2.5 text-xs text-slate-600">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-envelope text-slate-400 text-sm"></i>
                        <span class="font-medium">{{ $application->email }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-phone text-slate-400 text-sm"></i>
                        <span class="font-medium">{{ $application->contact_number }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-location-dot text-slate-400 text-sm"></i>
                        <span class="font-medium">{{ $application->resumeData->location ?? 'Not Specified' }}</span>
                    </div>
                </div>

                <!-- Action Links -->
                <div class="flex flex-wrap gap-2 shrink-0">
                    @if($application->resumeData->linkedin_url)
                    <a href="{{ $application->resumeData->linkedin_url }}" target="_blank" class="px-3 py-1.5 border border-slate-200 bg-white rounded-xl hover:bg-slate-50 hover:border-slate-300 text-slate-600 font-bold transition flex items-center gap-1.5 text-xs shadow-sm">
                        <i class="fa-brands fa-linkedin text-blue-500"></i> LinkedIn
                    </a>
                    @endif
                    
                    @if($application->resumeData->portfolio_url)
                    <a href="{{ $application->resumeData->portfolio_url }}" target="_blank" class="px-3 py-1.5 border border-slate-200 bg-white rounded-xl hover:bg-slate-50 hover:border-slate-300 text-slate-600 font-bold transition flex items-center gap-1.5 text-xs shadow-sm">
                        <i class="fa-solid fa-globe text-emerald-500"></i> Portfolio
                    </a>
                    @endif
                    
                    <a href="{{ asset('storage/' . $application->resume_path) }}" target="_blank" class="px-3 py-1.5 border border-slate-200 bg-white rounded-xl hover:bg-slate-50 hover:border-slate-300 text-slate-600 font-bold transition flex items-center gap-1.5 text-xs font-mono shadow-sm">
                        <i class="fa-solid fa-file-pdf text-red-500"></i> RAW_RESUME.PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Evaluation Assessment & Strengths/Gaps -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Key Evaluation Points (2 cols) -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm lg:col-span-2 space-y-6 report-card">
            <h2 class="text-xs font-bold text-slate-800 tracking-wider uppercase pb-3 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-indigo-500"></i> AI Scoring Heuristics Rubric
            </h2>
            
            <div class="ai-quote-card p-4 rounded-xl border border-slate-100 text-xs text-slate-700 leading-relaxed font-medium italic">
                <i class="fa-solid fa-quote-left text-slate-300 mr-1.5 text-sm"></i>
                {{ $application->matchScore->analysis_summary }}
            </div>

            <!-- Scorecard Metrics breakdown with progress bars -->
            <div class="space-y-4 pt-2">
                <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Evaluation Metrics Breakdown</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @php 
                        $scorecard = $application->matchScore->evaluation_scorecard;
                    @endphp
                    @if($scorecard)
                        @foreach($scorecard as $key => $eval)
                        <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-700 capitalize flex items-center gap-1.5">
                                    <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                                    {{ str_replace('_', ' ', $key) }}
                                </span>
                                <span class="font-extrabold font-mono text-indigo-600">{{ $eval['score'] ?? 80 }}%</span>
                            </div>
                            
                            <!-- Custom HTML progress bar -->
                            <div class="metric-bar-track">
                                <div class="metric-bar-fill" style="width: {{ $eval['score'] ?? 80 }}%;"></div>
                            </div>
                            
                            <p class="text-[10px] text-slate-500 leading-normal pt-1">{{ $eval['feedback'] ?? '' }}</p>
                        </div>
                        @endforeach
                    @else
                        <p class="text-xs text-slate-400 italic">No breakdown scorecard data found.</p>
                    @endif
                </div>
            </div>

            <!-- Feedback Summary Form -->
            @php 
                $feedback = $application->matchScore->feedback_form;
            @endphp
            @if($feedback)
            <div class="pt-4 border-t border-slate-100 space-y-4">
                <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hiring Feedback Form Summaries</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-4 rounded-xl border border-slate-100 bg-white shadow-sm flex flex-col justify-between">
                        <div>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-1">
                                <i class="fa-solid fa-circle-check text-emerald-500 mr-1 text-[8px]"></i> Strengths
                            </span>
                            <p class="text-[10px] text-slate-600 leading-normal font-medium">{{ $feedback['strengths_summary'] ?? 'Good technical skills.' }}</p>
                        </div>
                    </div>
                    
                    <div class="p-4 rounded-xl border border-slate-100 bg-white shadow-sm flex flex-col justify-between">
                        <div>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-1">
                                <i class="fa-solid fa-circle-xmark text-amber-500 mr-1 text-[8px]"></i> Gaps / Limits
                            </span>
                            <p class="text-[10px] text-slate-600 leading-normal font-medium">{{ $feedback['weaknesses_summary'] ?? 'Minor cloud exposure gaps.' }}</p>
                        </div>
                    </div>
                    
                    <div class="p-4 rounded-xl border border-slate-100 bg-white shadow-sm flex flex-col justify-between">
                        <div>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-1">
                                <i class="fa-solid fa-circle-info text-indigo-500 mr-1 text-[8px]"></i> Overall Fit
                            </span>
                            <p class="text-[10px] text-slate-600 leading-normal font-medium">{{ $feedback['overall_fit'] ?? 'Solid fit for core coding duties.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right: Strengths & Missing Skills Gaps (1 col) -->
        <div class="space-y-6">
            <!-- Strengths card -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm report-card space-y-4">
                <h2 class="text-xs font-bold text-emerald-600 tracking-wider uppercase pb-3 border-b border-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i> Matched Strengths
                </h2>
                
                <div class="flex flex-wrap gap-1.5">
                    @php 
                        $strengths = $application->matchScore->strengths ?? [];
                    @endphp
                    @forelse($strengths as $str)
                    <span class="px-2.5 py-1.5 bg-emerald-50 border border-emerald-100 rounded-xl text-[10px] font-bold text-emerald-600 flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-check text-[8px] bg-emerald-500 text-white rounded-full p-0.5"></i> {{ $str }}
                    </span>
                    @empty
                    <span class="text-xs text-slate-400 italic">No explicit strengths listed.</span>
                    @endforelse
                </div>
            </div>

            <!-- Gaps card -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm report-card space-y-4">
                <h2 class="text-xs font-bold text-amber-600 tracking-wider uppercase pb-3 border-b border-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i> Missing Gaps
                </h2>
                
                <div class="flex flex-wrap gap-1.5">
                    @php 
                        $missing = $application->matchScore->missing_skills ?? [];
                    @endphp
                    @forelse($missing as $mis)
                    <span class="px-2.5 py-1.5 bg-amber-50 border border-amber-100 rounded-xl text-[10px] font-bold text-amber-700 flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-xmark text-[8px] bg-amber-500 text-white rounded-full p-0.5"></i> {{ $mis }}
                    </span>
                    @empty
                    <span class="text-xs text-slate-400 italic">Fully matched all job requirements!</span>
                    @endforelse
                </div>
                
                <div class="mt-2 pt-4 border-t border-slate-100">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5">Experience Assessment</span>
                    <p class="text-[10px] text-slate-600 leading-relaxed font-medium bg-slate-50 p-3 rounded-lg border border-slate-100">{{ $application->matchScore->experience_gap ?? 'No experience gaps identified.' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Candidate Parsed Profile Sections -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm space-y-6 report-card">
        <h2 class="text-xs font-bold text-slate-800 tracking-wider uppercase pb-3 border-b border-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-id-card text-indigo-500"></i> Parsed Profile Summary
        </h2>

        <!-- Top Part: Balanced 3-Column Profile Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Left: Current Experience -->
            <div class="space-y-4">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Current Professional Summary</span>
                
                <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 space-y-3 shadow-inner">
                    <div class="flex items-center justify-between text-xs text-slate-600 border-b border-slate-200/40 pb-2">
                        <span class="font-medium">Total Experience</span>
                        <span class="font-black font-mono text-slate-800 bg-white px-2 py-0.5 rounded border border-slate-200 shadow-sm">{{ $application->resumeData->total_experience_years ?? 0.0 }} Years</span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-600 border-b border-slate-200/40 pb-2">
                        <span class="font-medium text-left">Current Designation</span>
                        <span class="font-bold text-right text-slate-800">{{ $application->resumeData->current_designation ?? 'Not Listed' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-600">
                        <span class="font-medium text-left">Current Employer</span>
                        <span class="font-bold text-right text-slate-800">{{ $application->resumeData->current_company ?? 'Not Listed' }}</span>
                    </div>
                </div>
            </div>

            <!-- Middle: Timeline structured Education -->
            <div class="space-y-4">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Academic Credentials</span>
                
                <div class="space-y-5">
                    @forelse($application->education as $edu)
                    <div class="timeline-container">
                        <div class="timeline-circle"></div>
                        <div class="space-y-1">
                            <span class="text-xs font-bold text-slate-800 block leading-tight">{{ $edu->degree }}</span>
                            <span class="text-[10px] text-slate-500 block">{{ $edu->college }}</span>
                            @if($edu->passing_year)
                            <span class="inline-block px-2 py-0.5 bg-slate-100 border border-slate-200 rounded text-[9px] font-mono font-bold text-slate-600">Class of {{ $edu->passing_year }}</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 italic">No structured education data extracted.</p>
                    @endforelse
                </div>
            </div>

            <!-- Right: Parsed Projects -->
            <div class="space-y-4">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Relevance Projects Portfolio</span>
                
                <div class="space-y-3.5">
                    @forelse($application->projects as $proj)
                    <div class="p-4 rounded-xl border border-slate-100 hover:border-slate-200 transition bg-slate-50/20 flex items-start gap-3 shadow-sm">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-500 border border-emerald-100 flex items-center justify-center text-xs shrink-0 mt-0.5">
                            <i class="fa-solid fa-folder-open"></i>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs font-bold text-slate-800 block leading-tight">{{ $proj->project_name }}</span>
                            @if($proj->technologies_used)
                            <span class="text-[9px] text-slate-500 font-mono block bg-white p-2 rounded-lg border border-slate-200/60 leading-normal">
                                <span class="font-bold text-slate-400">Tech:</span> {{ $proj->technologies_used }}
                            </span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 italic">No structured projects data extracted.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Full-Width Bottom Row: Extracted Skill Badges (Resolves horizontal overflow scrollbar, wraps beautifully) -->
        <div class="pt-6 border-t border-slate-100 space-y-3">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block flex items-center gap-1">
                <i class="fa-solid fa-tags text-indigo-500"></i> Extracted Skill Badges
            </span>
            <div class="flex flex-wrap gap-2.5 w-full" style="flex-wrap: wrap !important; display: flex !important; white-space: normal !important;">
                @foreach($application->skills as $sk)
                <span class="px-3 py-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 rounded-xl text-[10px] font-semibold transition shadow-sm skill-badge inline-block" style="white-space: nowrap !important; flex-shrink: 0 !important;">
                    <span class="font-bold text-slate-800">{{ $sk->skill_name }}</span>
                    <span class="text-[8px] font-mono text-slate-400 font-bold uppercase tracking-wider block mt-0.5">{{ $sk->skill_type }}</span>
                </span>
                @endforeach
            </div>
        </div>
    </div>

    <!-- AI Generated Interview Guide Kit -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm space-y-6 report-card">
        <div>
            <h2 class="text-xs font-bold text-slate-800 tracking-wider uppercase flex items-center gap-2 pb-3 border-b border-slate-100">
                <i class="fa-solid fa-clipboard-question text-indigo-500"></i> AI-Generated Interview Guide Kit
            </h2>
            <p class="text-[10px] text-slate-400 mt-1.5 leading-relaxed">
                Custom structured guidelines and screening assessment questions formulated specifically for this candidate based on tech stack relevance gaps.
            </p>
        </div>

        <div class="space-y-4 pt-2">
            @forelse($application->generatedQuestions as $index => $q)
            <div class="p-5 rounded-xl border border-slate-100 bg-slate-50/30 hover:border-slate-200 transition duration-200 space-y-3 relative question-row">
                <div class="flex items-center justify-between flex-wrap gap-2 pb-2 border-b border-slate-100">
                    <span class="text-[10px] font-black text-indigo-500 uppercase tracking-widest font-mono flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded bg-indigo-500"></span> Question #0{{ $index + 1 }}
                    </span>
                    <div class="flex gap-1.5">
                        <span class="px-2 py-0.5 bg-slate-200/60 border border-slate-300/40 rounded text-[9px] font-bold text-slate-600 capitalize tracking-wide">{{ $q->category }}</span>
                        
                        @php
                            $diff = strtolower($q->difficulty ?? '');
                            $diffClass = 'bg-emerald-50 text-emerald-600 border border-emerald-100';
                            if ($diff === 'hard') { $diffClass = 'bg-red-50 text-red-500 border border-red-100'; }
                            elseif ($diff === 'medium') { $diffClass = 'bg-amber-50 text-amber-500 border border-amber-100'; }
                        @endphp
                        
                        <span class="px-2.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider {{ $diffClass }}">
                            {{ $q->difficulty }}
                        </span>
                    </div>
                </div>
                
                <h4 class="text-xs font-bold text-slate-800 leading-relaxed pt-1 flex items-start gap-2">
                    <i class="fa-solid fa-circle-question text-slate-400 mt-0.5"></i>
                    <span>{{ $q->question }}</span>
                </h4>
                
                @if($q->suggested_answer)
                <div class="mt-3 bg-white p-4 rounded-xl border border-slate-100 space-y-1.5 shadow-sm">
                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest block flex items-center gap-1">
                        <i class="fa-solid fa-lightbulb text-amber-400 text-xs"></i> AI Suggested Grading Rubric / Ideal Answer
                    </span>
                    <p class="text-[10px] text-slate-600 leading-relaxed font-medium">{{ $q->suggested_answer }}</p>
                </div>
                @endif
            </div>
            @empty
            <p class="text-xs text-slate-400 italic">No interview guide questions pre-generated.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
