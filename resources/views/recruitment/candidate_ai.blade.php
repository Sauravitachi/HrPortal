@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header Backlink -->
    <div class="flex items-center justify-between">
        <a href="{{ route('jobs.ai.dashboard') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold flex items-center gap-1.5 transition">
            <i class="fa-solid fa-arrow-left"></i> Back to AI Dashboard
        </a>
        <span class="text-[10px] text-slate-400 font-mono uppercase tracking-wider">Screened on: {{ $application->matchScore->created_at->format('M d, Y H:i') }}</span>
    </div>

    <!-- Main Assessor Summary Card -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm relative overflow-hidden">
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-center">
            <!-- Left 3 cols: Info -->
            <div class="md:col-span-3 space-y-4">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 bg-gradient-to-tr from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white text-xl shadow-lg shadow-indigo-500/25">
                        {{ strtoupper(substr($application->full_name, 0, 2)) }}
                    </div>
                    <div>
                        <h1 class="text-xl font-black text-slate-800 tracking-tight">{{ $application->full_name }}</h1>
                        <p class="text-xs text-slate-400 mt-1 flex items-center gap-2">
                            <span class="font-bold text-indigo-600 uppercase tracking-wide bg-indigo-50 px-2 py-0.5 rounded">{{ $application->jobPost->title }}</span>
                            • Applied Position
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 text-xs text-slate-500">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-envelope text-slate-400 w-4 text-center"></i>
                        <span>{{ $application->email }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-phone text-slate-400 w-4 text-center"></i>
                        <span>{{ $application->contact_number }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-location-dot text-slate-400 w-4 text-center"></i>
                        <span>{{ $application->resumeData->location ?? 'Not Specified' }}</span>
                    </div>
                </div>

                <div class="flex gap-3 text-[10px] pt-1">
                    @if($application->resumeData->linkedin_url)
                    <a href="{{ $application->resumeData->linkedin_url }}" target="_blank" class="px-3 py-1.5 border border-slate-200 bg-slate-50 rounded-xl hover:bg-slate-100 text-slate-600 font-bold transition flex items-center gap-1">
                        <i class="fa-brands fa-linkedin text-blue-500"></i> LinkedIn Profile
                    </a>
                    @endif
                    @if($application->resumeData->portfolio_url)
                    <a href="{{ $application->resumeData->portfolio_url }}" target="_blank" class="px-3 py-1.5 border border-slate-200 bg-slate-50 rounded-xl hover:bg-slate-100 text-slate-600 font-bold transition flex items-center gap-1">
                        <i class="fa-solid fa-globe text-emerald-500"></i> Portfolio Web
                    </a>
                    @endif
                    <a href="{{ asset('storage/' . $application->resume_path) }}" target="_blank" class="px-3 py-1.5 border border-slate-200 bg-slate-50 rounded-xl hover:bg-slate-100 text-slate-600 font-bold transition flex items-center gap-1 font-mono">
                        <i class="fa-solid fa-file-pdf text-red-500"></i> RAW_RESUME.PDF
                    </a>
                </div>
            </div>

            <!-- Right 1 col: Circular Gauge Match Score -->
            <div class="flex flex-col items-center justify-center p-3 bg-slate-50 border border-slate-100 rounded-2xl relative">
                <div class="relative w-28 h-28 flex items-center justify-center">
                    <!-- SVG Circular Progress -->
                    @php
                        $percentage = $application->matchScore->match_score ?? 0;
                        $strokeDash = 2 * pi() * 40; // circum = 2 * pi * r
                        $dashOffset = $strokeDash - ($strokeDash * $percentage / 100);
                    @endphp
                    <svg class="w-full h-full transform -rotate-90">
                        <circle cx="56" cy="56" r="40" stroke="#e2e8f0" stroke-width="8" fill="transparent" />
                        <circle cx="56" cy="56" r="40" 
                                stroke="{{ $percentage >= 90 ? '#10b981' : ($percentage >= 75 ? '#6366f1' : '#f59e0b') }}" 
                                stroke-width="8" 
                                fill="transparent" 
                                stroke-dasharray="{{ $strokeDash }}" 
                                stroke-dashoffset="{{ $dashOffset }}" 
                                stroke-linecap="round"
                                class="transition duration-1000 ease-out" />
                    </svg>
                    <div class="absolute flex flex-col items-center">
                        <span class="text-2xl font-black text-slate-800 font-mono">{{ $percentage }}%</span>
                        <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest block mt-0.5">Match Score</span>
                    </div>
                </div>
                
                <span class="mt-3 px-3 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider text-center block
                    {{ $percentage >= 90 ? 'bg-emerald-100 text-emerald-700' : ($percentage >= 75 ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700') }}">
                    {{ $application->matchScore->hiring_recommendation }}
                </span>
            </div>
        </div>
    </div>

    <!-- AI Evaluation Assessment & Strengths/Gaps -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Left: Key Evaluation Points -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm md:col-span-2 space-y-4">
            <h2 class="text-sm font-bold text-slate-800 tracking-wide uppercase pb-3 border-b border-slate-100 flex items-center gap-1.5">
                <i class="fa-solid fa-list-check text-indigo-500"></i> Scoring Analysis Rubric
            </h2>
            
            <p class="text-xs text-slate-600 leading-relaxed font-medium bg-slate-50 p-3.5 rounded-xl border border-slate-100 italic">
                "{{ $application->matchScore->analysis_summary }}"
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4.5 pt-2">
                @php 
                    $scorecard = $application->matchScore->evaluation_scorecard;
                @endphp
                @if($scorecard)
                    @foreach($scorecard as $key => $eval)
                    <div class="p-3.5 rounded-xl border border-slate-100 hover:border-slate-200 transition bg-slate-50/50 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                            <span class="text-xs font-extrabold font-mono text-indigo-600">{{ $eval['score'] ?? 80 }}%</span>
                        </div>
                        <p class="text-[10px] text-slate-400 leading-normal mt-1">{{ $eval['feedback'] ?? '' }}</p>
                    </div>
                    @endforeach
                @else
                    <p class="text-xs text-slate-400 italic">No breakdown scorecard data found.</p>
                @endif
            </div>

            <!-- Feedback Summary Form -->
            @php 
                $feedback = $application->matchScore->feedback_form;
            @endphp
            @if($feedback)
            <div class="pt-4 border-t border-slate-100 space-y-3">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Hiring Feedback Form Summaries</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-3 rounded-xl border border-slate-100 bg-white">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Strengths</span>
                        <p class="text-[10px] text-slate-600 leading-normal font-medium">{{ $feedback['strengths_summary'] ?? 'Good technical skills.' }}</p>
                    </div>
                    <div class="p-3 rounded-xl border border-slate-100 bg-white">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Gaps / Limits</span>
                        <p class="text-[10px] text-slate-600 leading-normal font-medium">{{ $feedback['weaknesses_summary'] ?? 'Minor cloud exposure gaps.' }}</p>
                    </div>
                    <div class="p-3 rounded-xl border border-slate-100 bg-white">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Overall Fit</span>
                        <p class="text-[10px] text-slate-600 leading-normal font-medium">{{ $feedback['overall_fit'] ?? 'Solid fit for core coding duties.' }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right: Strengths & Missing Skills Gaps -->
        <div class="space-y-6">
            <!-- Strengths list -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
                <h2 class="text-sm font-bold text-emerald-600 tracking-wide uppercase pb-3 border-b border-slate-100 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check"></i> Matched Strengths
                </h2>
                <div class="flex flex-wrap gap-1.5 mt-4">
                    @php 
                        $strengths = $application->matchScore->strengths ?? [];
                    @endphp
                    @forelse($strengths as $str)
                    <span class="px-3 py-1.5 bg-emerald-50 border border-emerald-100 rounded-xl text-[10px] font-bold text-emerald-600 flex items-center gap-1">
                        <i class="fa-solid fa-check text-[8px]"></i> {{ $str }}
                    </span>
                    @empty
                    <span class="text-xs text-slate-400 italic">No explicit strengths listed.</span>
                    @endforelse
                </div>
            </div>

            <!-- Gaps list -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
                <h2 class="text-sm font-bold text-amber-600 tracking-wide uppercase pb-3 border-b border-slate-100 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-exclamation"></i> Missing Gaps
                </h2>
                <div class="flex flex-wrap gap-1.5 mt-4">
                    @php 
                        $missing = $application->matchScore->missing_skills ?? [];
                    @endphp
                    @forelse($missing as $mis)
                    <span class="px-3 py-1.5 bg-amber-50 border border-amber-100 rounded-xl text-[10px] font-bold text-amber-600 flex items-center gap-1">
                        <i class="fa-solid fa-xmark text-[8px]"></i> {{ $mis }}
                    </span>
                    @empty
                    <span class="text-xs text-slate-400 italic">Fully matched all job requirements!</span>
                    @endforelse
                </div>
                
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Experience Assessment</span>
                    <p class="text-[10px] text-slate-600 leading-normal">{{ $application->matchScore->experience_gap ?? 'No experience gaps identified.' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Candidate Parsed Profile Sections -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm space-y-5">
        <h2 class="text-sm font-bold text-slate-800 tracking-wide uppercase pb-3 border-b border-slate-100 flex items-center gap-1.5">
            <i class="fa-solid fa-id-card text-indigo-500"></i> Parsed Candidate Profile
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left: Current Info & Skills list -->
            <div class="space-y-4">
                <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/50 space-y-2">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Current Experience Summary</span>
                    <div class="flex items-center justify-between text-xs text-slate-700">
                        <span class="font-medium">Total Experience:</span>
                        <span class="font-bold font-mono">{{ $application->resumeData->total_experience_years ?? 0.0 }} Years</span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-700">
                        <span class="font-medium">Current Designation:</span>
                        <span class="font-bold text-right">{{ $application->resumeData->current_designation ?? 'Not Listed' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-700">
                        <span class="font-medium">Current Employer:</span>
                        <span class="font-bold text-right">{{ $application->resumeData->current_company ?? 'Not Listed' }}</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Detailed Extracted Skills</span>
                    <div class="flex flex-wrap gap-1">
                        @foreach($application->skills as $sk)
                        <span class="px-2.5 py-1 bg-slate-100 rounded-lg text-[10px] font-medium text-slate-600">
                            {{ $sk->skill_name }} 
                            <span class="text-[8px] font-mono text-slate-400 font-bold block">{{ $sk->skill_type }}</span>
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Middle: Structured Education -->
            <div class="space-y-3.5">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Academic Credentials</span>
                
                @forelse($application->education as $edu)
                <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/30 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-500 border border-indigo-100 flex items-center justify-center text-xs shrink-0 mt-0.5">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-700 block leading-tight">{{ $edu->degree }}</span>
                        <span class="text-[10px] text-slate-400 block mt-1">{{ $edu->college }}</span>
                        @if($edu->passing_year)
                        <span class="inline-block mt-1.5 px-2 py-0.5 bg-slate-100 rounded text-[9px] font-mono font-bold text-slate-500">Class of {{ $edu->passing_year }}</span>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 italic">No structured education data extracted.</p>
                @endforelse
            </div>

            <!-- Right: Parsed Projects -->
            <div class="space-y-3.5">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Relevance Projects Portfolio</span>
                
                @forelse($application->projects as $proj)
                <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/30 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-500 border border-emerald-100 flex items-center justify-center text-xs shrink-0 mt-0.5">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-700 block leading-tight">{{ $proj->project_name }}</span>
                        @if($proj->technologies_used)
                        <span class="text-[9px] text-slate-400 font-mono block mt-1.5 bg-slate-100 p-1.5 rounded border border-slate-200/50">Tech: {{ $proj->technologies_used }}</span>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 italic">No structured projects data extracted.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- AI Generated Interview Guide Kit -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm space-y-4">
        <div>
            <h2 class="text-sm font-bold text-slate-800 tracking-wide uppercase flex items-center gap-1.5">
                <i class="fa-solid fa-clipboard-question text-indigo-500"></i> AI-Generated Interview Guide Kit
            </h2>
            <p class="text-[10px] text-slate-400 mt-0.5">Tailored interview assessment guidelines generated specifically for this candidate based on resume tech gap analysis.</p>
        </div>

        <div class="space-y-4.5 pt-2">
            @forelse($application->generatedQuestions as $index => $q)
            <div class="p-4 rounded-xl border border-slate-100 hover:border-slate-200 transition duration-200 bg-slate-50/50 space-y-2">
                <div class="flex items-center justify-between flex-wrap gap-2 pb-2 border-b border-slate-100">
                    <span class="text-[10px] font-black text-indigo-500 uppercase tracking-widest font-mono">Question #0{{ $index + 1 }}</span>
                    <div class="flex gap-1.5">
                        <span class="px-2 py-0.5 bg-slate-200 rounded text-[9px] font-bold text-slate-500 capitalize">{{ $q->category }}</span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider
                            {{ $q->difficulty === 'hard' ? 'bg-red-50 text-red-500 border border-red-100' : ($q->difficulty === 'medium' ? 'bg-amber-50 text-amber-500 border border-amber-100' : 'bg-emerald-50 text-emerald-500 border border-emerald-100') }}">
                            {{ $q->difficulty }}
                        </span>
                    </div>
                </div>
                
                <h4 class="text-xs font-bold text-slate-800 leading-relaxed pt-1">{{ $q->question }}</h4>
                
                @if($q->suggested_answer)
                <div class="mt-2.5 bg-white p-3 rounded-xl border border-slate-100/80 space-y-1">
                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest block"><i class="fa-solid fa-lightbulb text-amber-400 mr-0.5"></i> AI Suggested Grading Rubric / Answer key</span>
                    <p class="text-[10px] text-slate-500 leading-normal">{{ $q->suggested_answer }}</p>
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
