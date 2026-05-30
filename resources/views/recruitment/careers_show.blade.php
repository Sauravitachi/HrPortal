@extends('layouts.careers')

@section('content')
<div class="space-y-6">

    <!-- Back Navigation -->
    <a href="{{ route('careers.index') }}" class="text-xs font-bold text-slate-500 hover:text-blue-600 inline-flex items-center gap-1 transition">
        <i class="fa-solid fa-angle-left"></i> Back to Open Positions
    </a>

    <!-- Job Header Block -->
    <div class="glass-card p-6 md:p-8 rounded-3xl bg-white space-y-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[10px] font-bold text-blue-600 bg-blue-500/10 px-2.5 py-0.5 rounded-full font-mono uppercase tracking-wider">
                {{ $job->department->name }}
            </span>
            @if($job->jobCategory)
            <span class="text-[10px] font-bold text-indigo-600 bg-indigo-500/10 px-2.5 py-0.5 rounded-full font-mono uppercase tracking-wider">
                {{ $job->jobCategory->name }}
            </span>
            @endif
        </div>
        <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-800">{{ $job->title }}</h1>
        <div class="flex items-center gap-5 text-xs text-slate-500 font-mono">
            <span><i class="fa-solid fa-briefcase mr-1.5 text-slate-400"></i> Experience: {{ $job->experience_required }}</span>
            <span><i class="fa-solid fa-wallet mr-1.5 text-slate-400"></i> Salary: {{ $job->salary_range }}</span>
            <span><i class="fa-solid fa-clock mr-1.5 text-slate-400"></i> Posted: {{ $job->created_at->diffForHumans() }}</span>
        </div>
    </div>

    <!-- Description & Apply Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Job Details -->
        <div class="glass-card p-6 md:p-8 rounded-3xl bg-white lg:col-span-2 space-y-6">
            <div>
                <h2 class="text-sm font-bold text-slate-700 uppercase tracking-widest border-b border-slate-200 pb-2.5 mb-4">Job Description</h2>
                <div class="text-xs text-slate-600 leading-relaxed space-y-3 whitespace-pre-line">
                    {{ $job->description }}
                </div>
            </div>
        </div>

        <!-- Application Form -->
        <div class="glass-card p-6 md:p-8 rounded-3xl bg-white space-y-6">
            <div>
                <h2 class="text-sm font-bold text-slate-700 uppercase tracking-widest pb-1 border-b border-slate-200 mb-4">Apply Online</h2>
                <form action="{{ route('careers.apply', $job) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Full Name</label>
                        <input type="text" name="full_name" required placeholder="e.g. Karan Dev" class="block w-full bg-slate-50 border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl px-3.5 py-2 text-xs text-slate-800 transition">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Email Address</label>
                        <input type="email" name="email" required placeholder="e.g. karan@example.com" class="block w-full bg-slate-50 border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl px-3.5 py-2 text-xs text-slate-800 transition">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Contact Number</label>
                        <input type="text" name="contact_number" required placeholder="e.g. +91 9999999999" class="block w-full bg-slate-50 border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl px-3.5 py-2 text-xs text-slate-800 transition">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Resume Upload (PDF/DOCX)</label>
                        <input type="file" name="resume_file" required accept=".pdf,.docx" class="block w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[10px] file:font-semibold file:bg-blue-500/10 file:text-blue-600 hover:file:bg-blue-500/20 cursor-pointer">
                        <span class="text-[9px] text-slate-400 block mt-1">Maximum file size: 5MB</span>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2.5 px-4 rounded-xl text-xs shadow shadow-indigo-600/35 transition duration-200 mt-2 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i> Submit Application
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection
