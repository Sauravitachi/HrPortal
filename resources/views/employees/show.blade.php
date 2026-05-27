@extends('layouts.app')

@section('content')
<div class="space-y-6">
    
    <!-- Top Bar -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-100 tracking-tight">Employee Directory</h1>
            <p class="text-xs text-slate-400 mt-1">Detailed operations record & document repositories.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('employees.index') }}" class="bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-200">
                <i class="fa-solid fa-arrow-left mr-1"></i> Directory
            </a>
            <a href="{{ route('employees.edit', $employee) }}" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-200 flex items-center gap-2 shadow shadow-indigo-600/35">
                <i class="fa-solid fa-user-pen"></i> Edit Profile
            </a>
        </div>
    </div>

    <!-- Main Content Split Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Basic Profile details -->
        <div class="space-y-6 lg:col-span-1">
            <!-- Profile Card -->
            <div class="glass-card rounded-2xl p-6 text-center space-y-4 relative overflow-hidden">
                <div class="absolute -top-10 -left-10 w-20 h-20 bg-indigo-500/5 rounded-full blur-xl"></div>
                <div class="absolute -bottom-10 -right-10 w-20 h-20 bg-purple-500/5 rounded-full blur-xl"></div>

                @if($employee->profile_image)
                <img src="{{ asset('storage/' . $employee->profile_image) }}" class="w-24 h-24 rounded-2xl object-cover mx-auto border-2 border-slate-800 shadow-md shadow-indigo-500/10">
                @else
                <div class="w-24 h-24 rounded-2xl bg-indigo-500/10 border-2 border-indigo-500/20 flex items-center justify-center text-indigo-400 text-4xl font-extrabold mx-auto shadow-md">
                    {{ substr($employee->full_name, 0, 2) }}
                </div>
                @endif

                <div class="space-y-1">
                    <h2 class="text-lg font-bold text-slate-100 tracking-tight">{{ $employee->full_name }}</h2>
                    <span class="text-xs font-mono text-slate-400">{{ $employee->employee_id }}</span>
                    <span class="text-[10px] text-indigo-400 font-mono uppercase bg-indigo-500/10 border border-indigo-500/15 px-2 py-0.5 rounded block w-fit mx-auto mt-2">
                        {{ $employee->designation->name }}
                    </span>
                </div>

                <div class="pt-4 border-t border-slate-800/60 flex flex-col gap-2.5 text-left text-xs">
                    <div class="flex items-center justify-between text-slate-400">
                        <span>Work Email:</span>
                        <span class="font-semibold text-slate-200">{{ $employee->email }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span>Contact Number:</span>
                        <span class="font-semibold text-slate-200">{{ $employee->contact_number }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span>Joining Date:</span>
                        <span class="font-mono text-slate-300">{{ $employee->joining_date->format('M d, Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span>Department:</span>
                        <span class="font-semibold text-indigo-400 uppercase tracking-wide text-[10px]">{{ $employee->department->name }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span>Location & Type:</span>
                        <span class="font-semibold text-slate-300">{{ $employee->employee_type }} ({{ $employee->work_location }})</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span>Manager:</span>
                        <span class="font-semibold text-slate-300">{{ $employee->reportingManager ? $employee->reportingManager->full_name : 'Direct Report' }}</span>
                    </div>
                </div>
            </div>

            <!-- Profile Info card -->
            <div class="glass-card rounded-2xl p-5 space-y-3.5">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Personal Coordinates</h3>
                <div class="space-y-2.5 text-xs text-slate-300">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Gender:</span>
                        <span>{{ $employee->gender }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Date of Birth:</span>
                        <span>{{ $employee->date_of_birth->format('F d, Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Blood Group:</span>
                        <span class="text-red-400 font-bold font-mono">{{ $employee->blood_group }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Emergency Contact:</span>
                        <span>{{ $employee->emergency_contact }}</span>
                    </div>
                    <div class="flex flex-col gap-1 pt-1.5 border-t border-slate-800/40">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase">Registered Address</span>
                        <span class="text-slate-400 leading-relaxed text-[11px]">{{ $employee->address }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Core Files Repository & Compensation Info -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Salary / Compensation overview -->
            <div class="glass-card rounded-2xl p-5">
                <h3 class="text-sm font-bold text-slate-200 tracking-wide uppercase mb-3 border-b border-slate-800 pb-2">Compensation Overview</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-3 bg-slate-900/60 border border-slate-800/40 rounded-xl flex items-center justify-between">
                        <div class="space-y-0.5">
                            <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Monthly Basic Salary</span>
                            <span class="text-lg font-bold font-mono text-slate-200">₹{{ number_format($employee->basic_salary, 2) }}</span>
                        </div>
                        <i class="fa-solid fa-coins text-slate-600 text-lg"></i>
                    </div>

                    <div class="p-3 bg-slate-900/60 border border-slate-800/40 rounded-xl flex items-center justify-between">
                        <div class="space-y-0.5">
                            <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">House Rent Allowance (HRA)</span>
                            <span class="text-lg font-bold font-mono text-slate-200">₹{{ number_format($employee->hra, 2) }}</span>
                        </div>
                        <i class="fa-solid fa-house-chimney text-slate-600 text-lg"></i>
                    </div>
                </div>
            </div>

            <!-- Documents Repository Manager -->
            <div class="glass-card rounded-2xl p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                    <h3 class="text-sm font-bold text-slate-200 tracking-wide uppercase">Employee Documents Manager</h3>
                    <i class="fa-solid fa-folder-open text-slate-500"></i>
                </div>

                <!-- Document Upload Form -->
                <form action="{{ route('employees.documents.upload', $employee) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-4 gap-3 p-3 bg-slate-900/40 border border-slate-800/50 rounded-xl items-end">
                    @csrf
                    <div class="sm:col-span-1">
                        <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">Doc Type</label>
                        <select name="document_type" required class="block w-full bg-slate-950 border border-slate-800 rounded-lg px-2 py-1.5 text-[11px] text-slate-300 focus:outline-none">
                            <option value="Aadhaar Card">Aadhaar Card</option>
                            <option value="PAN Card">PAN Card</option>
                            <option value="Resume">Resume</option>
                            <option value="Education Certificate">Education Cert</option>
                            <option value="Experience Certificate">Experience Cert</option>
                            <option value="Offer Letter">Offer Letter</option>
                            <option value="Joining Letter">Joining Letter</option>
                            <option value="Other">Other Document</option>
                        </select>
                    </div>

                    <div class="sm:col-span-1">
                        <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">Doc Title</label>
                        <input type="text" name="document_name" required placeholder="e.g. Rohits_Aadhaar" class="block w-full bg-slate-950 border border-slate-800 rounded-lg px-2 py-1.5 text-[11px] text-slate-200 placeholder-slate-600 focus:outline-none">
                    </div>

                    <div class="sm:col-span-1">
                        <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">Choose File</label>
                        <input type="file" name="document_file" required class="block w-full text-[10px] text-slate-500 bg-slate-950 border border-slate-800 rounded-lg py-1 px-2 focus:outline-none file:hidden">
                    </div>

                    <button type="submit" class="sm:col-span-1 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-1.5 px-3 rounded-lg text-xs transition-all duration-200 flex items-center justify-center gap-1.5 shadow">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Upload
                    </button>
                </form>

                <!-- Document List Grid -->
                <div class="space-y-2.5">
                    @if($employee->documents->isEmpty())
                    <div class="h-32 flex items-center justify-center text-slate-500 text-xs border border-dashed border-slate-800 rounded-xl">
                        No onboarding documents uploaded.
                    </div>
                    @else
                    @foreach($employee->documents as $doc)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900/30 border border-slate-800 hover:border-slate-800/80 transition-all duration-200">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-file-pdf"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-200">{{ $doc->document_name }}</span>
                                <span class="text-[10px] text-slate-500 font-mono">{{ $doc->document_type }} • Uploaded {{ $doc->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="p-2 rounded-lg bg-slate-900 border border-slate-800 text-indigo-400 hover:text-indigo-300 hover:bg-slate-800 transition-all" title="View/Download">
                                <i class="fa-solid fa-circle-down text-xs"></i>
                            </a>
                            <form action="{{ route('documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Delete this document?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-all" title="Delete">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
