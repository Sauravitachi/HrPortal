@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header backlink & Title -->
    <div class="flex items-center justify-between">
        <a href="{{ route('jobs.ai.dashboard') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold flex items-center gap-1.5 transition">
            <i class="fa-solid fa-arrow-left"></i> Back to AI Dashboard
        </a>
        <span class="text-[10px] text-slate-400 font-mono uppercase tracking-wider">AI Powered Evaluation</span>
    </div>

    <!-- Main Container Card -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-8 shadow-sm relative overflow-hidden">
        <div class="absolute -top-32 -right-32 w-64 h-64 bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -left-32 w-64 h-64 bg-purple-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative space-y-6">
            <!-- Header Text -->
            <div class="border-b border-slate-100 pb-5">
                <h1 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2.5">
                    <div class="w-10 h-10 bg-gradient-to-tr from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white text-lg shadow-lg shadow-indigo-500/25">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </div>
                    <span>AI ATS Resume Checker</span>
                </h1>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    Upload a candidate's resume (PDF or Word DOCX) and immediately compare it against any active job post. Our AI models will parse the profile, evaluate skills, compute an ATS Match Score, and generate customized interview guides.
                </p>
            </div>

            <!-- Main Form -->
            <form id="ats-check-form" action="{{ route('jobs.ats.check.process') }}" method="POST" enctype="multipart/form-data" class="space-y-6" onsubmit="showAtsLoader()">
                @csrf

                <!-- Grid layout for form parts -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Left Side: Job & File Upload -->
                    <div class="space-y-5">
                        <!-- Job Requisition -->
                        <div class="space-y-2">
                            <label for="job_post_id" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-briefcase text-indigo-500"></i> Target Job Requisition <span class="text-red-500">*</span>
                            </label>
                            <select id="job_post_id" name="job_post_id" required class="block w-full bg-slate-50 border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-3.5 py-3 text-xs text-slate-700 transition">
                                <option value="">-- Select Active Job Requisition --</option>
                                @foreach($jobs as $job)
                                    <option value="{{ $job->id }}">{{ $job->title }} ({{ $job->department->name }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Resume Upload Box -->
                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-file-arrow-up text-indigo-500"></i> Upload Candidate Resume <span class="text-red-500">*</span>
                            </label>
                            
                            <div class="relative group border-2 border-dashed border-slate-200 hover:border-indigo-500/80 rounded-2xl p-6 bg-slate-50/50 hover:bg-slate-50 transition duration-300 flex flex-col items-center justify-center text-center cursor-pointer min-h-[180px]">
                                <input type="file" id="resume_file" name="resume_file" required accept=".pdf,.docx" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="updateFileName(this)">
                                
                                <div id="upload-prompt" class="space-y-3">
                                    <div class="w-12 h-12 bg-white border border-slate-100 rounded-xl flex items-center justify-center text-slate-400 group-hover:text-indigo-500 group-hover:scale-110 transition duration-300 mx-auto shadow-sm">
                                        <i class="fa-solid fa-cloud-arrow-up text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-700">Drag and drop resume here, or <span class="text-indigo-600 group-hover:underline">browse</span></p>
                                        <p class="text-[10px] text-slate-400 mt-1">Supports PDF & DOCX formats (Max 5MB)</p>
                                    </div>
                                </div>

                                <div id="file-details" class="hidden space-y-3">
                                    <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 mx-auto border border-indigo-100 shadow-sm animate-pulse">
                                        <i class="fa-solid fa-file-pdf text-xl" id="file-icon"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-indigo-600 truncate max-w-[280px]" id="selected-file-name">filename.pdf</p>
                                        <p class="text-[9px] text-slate-400 mt-1" id="selected-file-size">0.0 KB</p>
                                    </div>
                                    <button type="button" onclick="clearFileSelection(event)" class="text-[10px] font-bold text-slate-400 hover:text-red-500 transition-colors uppercase tracking-wider flex items-center gap-1 mx-auto">
                                        <i class="fa-solid fa-trash-can"></i> Remove File
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Optional Applicant Tracking Info -->
                    <div class="space-y-5 bg-slate-50/50 border border-slate-100/80 rounded-2xl p-5 relative">
                        <div class="absolute top-4 right-4 text-[9px] font-bold uppercase tracking-wider bg-slate-200/50 text-slate-500 px-2 py-0.5 rounded font-mono">Optional</div>
                        
                        <div class="border-b border-slate-100 pb-3">
                            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Candidate Registry Details</h3>
                            <p class="text-[10px] text-slate-400 mt-0.5">Provide applicant details to register them in the ATS candidates log, or let the AI automatically extract them from the resume.</p>
                        </div>

                        <div class="space-y-4">
                            <div class="space-y-1.5">
                                <label for="full_name" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Candidate Full Name</label>
                                <input type="text" id="full_name" name="full_name" placeholder="e.g. John Doe (Auto-extracted if blank)" class="block w-full bg-white border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-3 py-2 text-xs text-slate-700 transition">
                            </div>

                            <div class="space-y-1.5">
                                <label for="email" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Email Address</label>
                                <input type="email" id="email" name="email" placeholder="e.g. john.doe@example.com (Auto-extracted if blank)" class="block w-full bg-white border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-3 py-2 text-xs text-slate-700 transition">
                            </div>

                            <div class="space-y-1.5">
                                <label for="contact_number" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Contact Number</label>
                                <input type="text" id="contact_number" name="contact_number" placeholder="e.g. +91 9876543210 (Auto-extracted if blank)" class="block w-full bg-white border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-3 py-2 text-xs text-slate-700 transition">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end pt-4 border-t border-slate-100">
                    <button type="submit" class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-xs font-bold rounded-xl transition duration-200 shadow-lg shadow-indigo-600/20 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-wand-magic-sparkles text-sm"></i> Run AI ATS Check
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- AI Loader overlay that screens while processing -->
<div id="ats-loader" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-md z-50 flex items-center justify-center p-6 transition duration-300">
    <div class="glass-card rounded-3xl p-8 max-w-sm w-full text-center space-y-6 relative overflow-hidden border border-slate-800">
        <div class="absolute -top-16 -right-16 w-32 h-32 bg-indigo-500/10 rounded-full blur-xl"></div>
        
        <div class="space-y-4">
            <!-- Pulsing Loading Indicator -->
            <div class="relative w-20 h-20 mx-auto flex items-center justify-center">
                <div class="absolute inset-0 rounded-full border-4 border-slate-800/40"></div>
                <div class="absolute inset-0 rounded-full border-4 border-t-indigo-500 border-r-indigo-500 border-b-transparent border-l-transparent animate-spin"></div>
                <i class="fa-solid fa-brain text-indigo-400 text-3xl animate-pulse"></i>
            </div>
            
            <div class="space-y-2">
                <h3 class="text-sm font-extrabold text-slate-200 tracking-tight">AI Screening in Progress</h3>
                <p class="text-[10px] text-slate-400 leading-normal px-2">
                    Hang tight! Our neural parsers are extracting profile summaries, scoring matching heuristics, and generating custom grading rubrics...
                </p>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-800/40 text-[9px] font-mono text-indigo-400 uppercase tracking-widest animate-pulse flex items-center justify-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Analysing Profile Streams
        </div>
    </div>
</div>

<script>
    function updateFileName(input) {
        const file = input.files[0];
        if (file) {
            const name = file.name;
            const size = (file.size / 1024).toFixed(1) + ' KB';
            const extension = name.split('.').pop().toLowerCase();
            
            const fileIcon = document.getElementById('file-icon');
            if (extension === 'pdf') {
                fileIcon.className = 'fa-solid fa-file-pdf text-red-500 text-xl';
            } else {
                fileIcon.className = 'fa-solid fa-file-word text-blue-500 text-xl';
            }

            document.getElementById('selected-file-name').textContent = name;
            document.getElementById('selected-file-size').textContent = size;
            
            document.getElementById('upload-prompt').classList.add('hidden');
            document.getElementById('file-details').classList.remove('hidden');
        }
    }

    function clearFileSelection(event) {
        event.stopPropagation();
        event.preventDefault();
        
        const input = document.getElementById('resume_file');
        input.value = '';
        
        document.getElementById('upload-prompt').classList.remove('hidden');
        document.getElementById('file-details').classList.add('hidden');
    }

    function showAtsLoader() {
        const form = document.getElementById('ats-check-form');
        if (form.checkValidity()) {
            document.getElementById('ats-loader').classList.remove('hidden');
        }
    }
</script>
@endsection
