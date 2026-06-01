@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-100 tracking-tight">Recruitment Pipeline</h1>
            <p class="text-xs text-slate-400 mt-1">Publish job requisitions, evaluate candidate applications, and schedule panel interviews.</p>
        </div>
        
        <div class="flex gap-2">
            <a href="{{ route('jobs.ai.dashboard') }}" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-xs font-semibold px-4 py-2 rounded-xl transition duration-200 flex items-center gap-2 shadow shadow-indigo-600/35">
                <i class="fa-solid fa-robot"></i> AI Recruitment Hub
            </a>
            <button onclick="document.getElementById('job_modal').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold px-4 py-2 rounded-xl transition duration-200 flex items-center gap-2 shadow shadow-indigo-600/35">
                <i class="fa-solid fa-plus"></i> Add Job Post
            </button>
        </div>
    </div>

    <!-- Active Job Postings Carousel/Roster -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @foreach($jobs as $job)
        <div class="glass-card p-4.5 rounded-2xl flex flex-col justify-between hover:border-slate-700/80 transition duration-300 gap-3.5 relative overflow-hidden">
            <div class="absolute -top-10 -left-10 w-20 h-20 bg-indigo-500/5 rounded-full blur-xl pointer-events-none"></div>

            <div class="space-y-2">
                <div class="flex items-start justify-between">
                    <span class="text-xs font-bold text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded font-mono uppercase tracking-wider">{{ $job->department->name }}</span>
                    <span class="text-[9px] font-mono text-slate-500 uppercase tracking-widest">{{ $job->status }}</span>
                </div>
                <h3 class="text-sm font-bold text-slate-200 leading-tight">{{ $job->title }}</h3>
                <p class="text-[10px] text-slate-500 font-mono leading-none">Exp: {{ $job->experience_required }} • Salary: {{ $job->salary_range }}</p>
            </div>

            <div class="flex items-center justify-between border-t border-slate-800/60 pt-3 text-xs mt-1.5">
                <span class="text-slate-400 font-medium">Candidates: <span class="font-bold text-slate-200">{{ $job->applications_count }}</span></span>
                <a href="{{ route('jobs.index', ['job_post_id' => $job->id]) }}" class="text-[10px] text-indigo-400 hover:text-indigo-300 font-bold flex items-center gap-1">
                    Manage Roster <i class="fa-solid fa-angle-right"></i>
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Job filter reset -->
    @if($selectedJobId)
    <div class="glass-card p-3 rounded-xl flex items-center justify-between bg-slate-900/30 border border-indigo-500/15">
        <span class="text-xs text-indigo-400 font-semibold">Filtering candidates for: <span class="font-bold text-slate-200">{{ \App\Models\JobPost::find($selectedJobId)->title }}</span></span>
        <a href="{{ route('jobs.index') }}" class="text-[10px] text-slate-400 hover:text-slate-200 font-bold"><i class="fa-solid fa-xmark mr-1"></i> Clear Filter</a>
    </div>
    @endif

    <!-- Candidate applications funnel grid list -->
    <div class="glass-card rounded-2xl p-5">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-800/60">
            <h2 class="text-sm font-bold text-slate-200 tracking-wide uppercase">Candidate Pipeline funnel</h2>
            <div class="flex gap-1.5">
                @foreach(['Applied', 'Shortlisted', 'Interview Scheduled', 'Hired'] as $stage)
                <a href="{{ route('jobs.index', ['status' => $stage, 'job_post_id' => $selectedJobId]) }}" class="px-2.5 py-1 rounded bg-slate-900 hover:bg-slate-800 text-[10px] font-bold text-slate-400 hover:text-slate-200 border border-slate-800">
                    {{ $stage }}
                </a>
                @endforeach
            </div>
        </div>

        <div class="overflow-x-auto">
            @if($applications->isEmpty())
            <div class="h-48 flex flex-col items-center justify-center text-slate-500 text-xs gap-2">
                <i class="fa-solid fa-users-rectangle text-2xl text-slate-600"></i>
                <span>No applications found at this stage. Publish jobs to get profiles.</span>
            </div>
            @else
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800/60 text-[9px] text-slate-500 uppercase tracking-wider font-bold bg-slate-900/10">
                        <th class="p-3.5">Applicant Details</th>
                        <th class="p-3.5">Applied Position</th>
                        <th class="p-3.5">Stage Status</th>
                        <th class="p-3.5">AI Match Score</th>
                        <th class="p-3.5">Candidate Resume</th>
                        <th class="p-3.5 text-right">Process Applicant</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40 text-xs text-slate-300">
                    @foreach($applications as $app)
                    <tr class="hover:bg-slate-900/10">
                        <td class="p-3.5">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-200">{{ $app->full_name }}</span>
                                <span class="text-[9px] text-slate-500 font-mono">{{ $app->email }} • {{ $app->contact_number }}</span>
                            </div>
                        </td>
                        <td class="p-3.5 font-semibold text-slate-400">
                            {{ $app->jobPost->title }}
                            <span class="text-[9px] font-mono text-indigo-400 uppercase block mt-0.5">{{ $app->jobPost->department->name }}</span>
                        </td>
                        <td class="p-3.5">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider
                                {{ $app->status === 'Hired' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($app->status === 'Rejected' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20') }}">
                                {{ $app->status }}
                            </span>
                        </td>
                        <td class="p-3.5">
                            @if($app->matchScore)
                                <a href="{{ route('jobs.candidate.ai', $app->id) }}" class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-[10px] font-bold font-mono bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 hover:bg-indigo-500/20 transition">
                                    <i class="fa-solid fa-robot"></i> {{ $app->matchScore->match_score }}%
                                </a>
                            @else
                                <span class="px-2 py-1 rounded-lg text-[9px] font-bold font-mono bg-slate-800/40 text-slate-500 border border-slate-800">
                                    No Score
                                </span>
                            @endif
                        </td>
                        <td class="p-3.5">
                            <a href="{{ asset('storage/' . $app->resume_path) }}" target="_blank" class="text-[10px] text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1 font-mono">
                                <i class="fa-solid fa-file-pdf"></i> VIEW_RESUME.PDF
                            </a>
                        </td>
                        <td class="p-3.5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <!-- Status selector -->
                                <form action="{{ route('applications.status', $app) }}" method="POST" class="flex items-center gap-1.5 bg-slate-900/60 border border-slate-800 rounded-lg p-1">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()" class="bg-transparent border-none text-[10px] text-slate-300 focus:outline-none cursor-pointer">
                                        <option value="">Status...</option>
                                        <option value="Shortlisted">Shortlist</option>
                                        <option value="Interview Scheduled">Interview</option>
                                        <option value="Selected">Select</option>
                                        <option value="Rejected">Reject</option>
                                        <option value="Hired">Hire</option>
                                    </select>
                                </form>

                                <!-- Schedule Interview Quick Button -->
                                @if($app->status !== 'Hired' && $app->status !== 'Rejected')
                                <button onclick="openInterviewModal({{ $app->id }}, '{{ $app->full_name }}')" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-1 px-2.5 rounded-lg text-[10px] shadow transition" title="Schedule Interview">
                                    Schedule
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    <!-- Job Creation Modal Overlay -->
    <div id="job_modal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-6">
        <div class="glass-card rounded-2xl p-6 w-full max-w-lg relative">
            <h2 class="text-sm font-bold text-slate-200 uppercase tracking-wide mb-4">Create Job Requisition</h2>
            <form action="{{ route('jobs.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wide mb-1.5">Job Title</label>
                    <input type="text" name="title" required placeholder="e.g. Senior PHP Architect" class="block w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-200">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wide mb-1.5">Department</label>
                        <select name="department_id" required class="block w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-300">
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wide mb-1.5">Experience</label>
                        <input type="text" name="experience_required" required placeholder="e.g. 3-5 Years" class="block w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-200">
                    </div>
                </div>

                <div>
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wide mb-1.5">Salary Range</label>
                    <input type="text" name="salary_range" required placeholder="e.g. ₹8,00,000 - ₹12,00,000 per annum" class="block w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-200">
                </div>

                <div>
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wide mb-1.5">Requisition Details</label>
                    <textarea name="description" required rows="4" placeholder="Job details, skillsets, duties..." class="block w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-200"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('job_modal').classList.add('hidden')" class="px-4 py-2 bg-slate-900 border border-slate-800 hover:bg-slate-800 text-slate-400 rounded-lg text-xs font-semibold">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-bold shadow">
                        Publish Requisition
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Interview Scheduler Modal Overlay -->
    <div id="interview_modal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-6">
        <div class="glass-card rounded-2xl p-6 w-full max-w-md relative">
            <h2 class="text-sm font-bold text-slate-200 uppercase tracking-wide mb-4">Schedule Interview</h2>
            <p class="text-[11px] text-slate-400 mb-4">Applicant: <span id="candidate_name" class="font-bold text-indigo-400">Candidate</span></p>
            <form id="interview_form" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wide mb-1.5">Interview Date & Time</label>
                        <input type="datetime-local" name="interview_date" required class="block w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-300 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wide mb-1.5">Interview Panel (Designated Members)</label>
                    <input type="text" name="interview_panel" required placeholder="e.g. Saurav (HR), Rohit (Tech Lead)" class="block w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-200">
                </div>

                <div>
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wide mb-1.5">Interview Notes / Instructions</label>
                    <textarea name="notes" rows="3" placeholder="Additional brief information..." class="block w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-200"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('interview_modal').classList.add('hidden')" class="px-4 py-2 bg-slate-900 border border-slate-800 hover:bg-slate-800 text-slate-400 rounded-lg text-xs font-semibold">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-bold shadow">
                        Schedule Interview
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openInterviewModal(id, name) {
            document.getElementById('candidate_name').textContent = name;
            document.getElementById('interview_form').action = `/applications/${id}/interview`;
            document.getElementById('interview_modal').classList.remove('hidden');
        }
    </script>

</div>
@endsection
