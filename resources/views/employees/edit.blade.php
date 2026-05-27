@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    
    <!-- Top Action bar -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-100 tracking-tight">Edit Employee Profile</h1>
            <p class="text-xs text-slate-400 mt-1">Modify core settings, department, designation, and salary logs for {{ $employee->full_name }}.</p>
        </div>
        <a href="{{ route('employees.show', $employee) }}" class="bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-200">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to Profile
        </a>
    </div>

    <!-- Edit Form -->
    <form action="{{ route('employees.update', $employee) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Section 1: Personal Details -->
        <div class="glass-card rounded-2xl p-6 space-y-4">
            <div class="border-b border-slate-800/80 pb-3">
                <h2 class="text-sm font-bold text-slate-200 uppercase tracking-wide">Personal Details</h2>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <!-- Employee ID -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Employee ID *</label>
                    <input type="text" name="employee_id" value="{{ old('employee_id', $employee->employee_id) }}" required
                        class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500/60 font-mono">
                </div>

                <!-- Full Name -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Full Name *</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $employee->full_name) }}" required
                        class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500/60">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Work Email *</label>
                    <input type="email" name="email" value="{{ old('email', $employee->email) }}" required
                        class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500/60">
                </div>

                <!-- Contact -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Contact Number *</label>
                    <input type="text" name="contact_number" value="{{ old('contact_number', $employee->contact_number) }}" required
                        class="block w-full bg-slate-955 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500/60">
                </div>

                <!-- Profile Photo -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Profile Image</label>
                    <input type="file" name="profile_image" accept="image/*"
                        class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-1.5 text-xs text-slate-400 focus:outline-none focus:border-indigo-500/60 file:bg-slate-900 file:border-none file:text-[10px] file:text-indigo-400 file:px-2.5 file:py-1 file:rounded file:mr-2">
                </div>

                <!-- Gender -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Gender *</label>
                    <select name="gender" required class="block w-full bg-slate-955 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60">
                        <option value="Male" {{ $employee->gender === 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ $employee->gender === 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ $employee->gender === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- Date of Birth -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Date of Birth *</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $employee->date_of_birth->toDateString()) }}" required
                        class="block w-full bg-slate-955 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60">
                </div>

                <!-- Blood Group -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Blood Group *</label>
                    <select name="blood_group" required class="block w-full bg-slate-955 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60">
                        <option value="A+" {{ $employee->blood_group === 'A+' ? 'selected' : '' }}>A+</option>
                        <option value="A-" {{ $employee->blood_group === 'A-' ? 'selected' : '' }}>A-</option>
                        <option value="B+" {{ $employee->blood_group === 'B+' ? 'selected' : '' }}>B+</option>
                        <option value="B-" {{ $employee->blood_group === 'B-' ? 'selected' : '' }}>B-</option>
                        <option value="O+" {{ $employee->blood_group === 'O+' ? 'selected' : '' }}>O+</option>
                        <option value="O-" {{ $employee->blood_group === 'O-' ? 'selected' : '' }}>O-</option>
                        <option value="AB+" {{ $employee->blood_group === 'AB+' ? 'selected' : '' }}>AB+</option>
                        <option value="AB-" {{ $employee->blood_group === 'AB-' ? 'selected' : '' }}>AB-</option>
                    </select>
                </div>

                <!-- Emergency Contact -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Emergency Contact *</label>
                    <input type="text" name="emergency_contact" value="{{ old('emergency_contact', $employee->emergency_contact) }}" required
                        class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500/60">
                </div>
            </div>

            <!-- Address -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Permanent Address *</label>
                <textarea name="address" required rows="2"
                    class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500/60">{{ old('address', $employee->address) }}</textarea>
            </div>
        </div>

        <!-- Section 2: Employment Details -->
        <div class="glass-card rounded-2xl p-6 space-y-4">
            <div class="border-b border-slate-800/80 pb-3">
                <h2 class="text-sm font-bold text-slate-200 uppercase tracking-wide">Employment Details</h2>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <!-- Joining Date -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Joining Date *</label>
                    <input type="date" name="joining_date" value="{{ old('joining_date', $employee->joining_date->toDateString()) }}" required
                        class="block w-full bg-slate-955 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60">
                </div>

                <!-- Department -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Department *</label>
                    <select name="department_id" required class="block w-full bg-slate-955 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60">
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $employee->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Designation -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Designation *</label>
                    <select name="designation_id" required class="block w-full bg-slate-955 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60">
                        @foreach($designations as $desig)
                        <option value="{{ $desig->id }}" {{ $employee->designation_id == $desig->id ? 'selected' : '' }}>{{ $desig->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Manager -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Reporting Manager</label>
                    <select name="reporting_manager_id" class="block w-full bg-slate-955 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60">
                        <option value="">No Reporting Manager</option>
                        @foreach($managers as $mng)
                        <option value="{{ $mng->id }}" {{ $employee->reporting_manager_id == $mng->id ? 'selected' : '' }}>{{ $mng->full_name }} ({{ $mng->employee_id }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Employee Type -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Employment Type *</label>
                    <select name="employee_type" required class="block w-full bg-slate-955 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60">
                        <option value="Full-time" {{ $employee->employee_type === 'Full-time' ? 'selected' : '' }}>Full-time</option>
                        <option value="Part-time" {{ $employee->employee_type === 'Part-time' ? 'selected' : '' }}>Part-time</option>
                        <option value="Contract" {{ $employee->employee_type === 'Contract' ? 'selected' : '' }}>Contract</option>
                        <option value="Intern" {{ $employee->employee_type === 'Intern' ? 'selected' : '' }}>Intern</option>
                    </select>
                </div>

                <!-- Work Location -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Work Location *</label>
                    <select name="work_location" required class="block w-full bg-slate-955 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60">
                        <option value="Onsite" {{ $employee->work_location === 'Onsite' ? 'selected' : '' }}>Onsite</option>
                        <option value="Remote" {{ $employee->work_location === 'Remote' ? 'selected' : '' }}>Remote</option>
                        <option value="Hybrid" {{ $employee->work_location === 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                    </select>
                </div>

                <!-- Employment Status -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Status *</label>
                    <select name="employment_status" required class="block w-full bg-slate-955 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60">
                        <option value="Active" {{ $employee->employment_status === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ $employee->employment_status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="Terminated" {{ $employee->employment_status === 'Terminated' ? 'selected' : '' }}>Terminated</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 3: Salary Components -->
        <div class="glass-card rounded-2xl p-6 space-y-4">
            <div class="border-b border-slate-800/80 pb-3">
                <h2 class="text-sm font-bold text-slate-200 uppercase tracking-wide">Compensation & Salary Parameters</h2>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Basic Salary -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Monthly Basic Salary (₹) *</label>
                    <input type="number" step="0.01" name="basic_salary" value="{{ old('basic_salary', $employee->basic_salary) }}" required
                        class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500/60 font-mono">
                </div>

                <!-- HRA -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">House Rent Allowance (HRA) (₹) *</label>
                    <input type="number" step="0.01" name="hra" value="{{ old('hra', $employee->hra) }}" required
                        class="block w-full bg-slate-955 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500/60 font-mono">
                </div>
            </div>
        </div>

        <!-- Section 4: Security Password Change -->
        @if($employee->user)
        <div class="glass-card rounded-2xl p-6 space-y-4">
            <div class="border-b border-slate-800/80 pb-3">
                <h2 class="text-sm font-bold text-slate-200 uppercase tracking-wide">Security Portal Access</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Change Password</label>
                    <input type="password" name="password" placeholder="Leave blank to keep existing password"
                        class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500/60">
                </div>
            </div>
        </div>
        @endif

        <!-- Submit Bar -->
        <div class="flex items-center justify-end gap-3.5 pt-3">
            <a href="{{ route('employees.show', $employee) }}" class="px-5 py-2.5 bg-slate-900 border border-slate-800 hover:bg-slate-800 rounded-xl text-xs font-semibold text-slate-400 transition-all duration-200">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition-all duration-300">
                Save Profile Changes
            </button>
        </div>
    </form>

</div>
@endsection
