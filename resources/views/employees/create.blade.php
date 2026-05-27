@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    
    <!-- Top breadcrumbs -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-100 tracking-tight">Onboard New Employee</h1>
            <p class="text-xs text-slate-400 mt-1">Configure profile details, documents, and salary structure settings.</p>
        </div>
        <a href="{{ route('employees.index') }}" class="bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-200">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to Roster
        </a>
    </div>

    <!-- Create Form -->
    <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Section 1: Personal Details -->
        <div class="glass-card rounded-2xl p-6 space-y-4">
            <div class="border-b border-slate-800/80 pb-3">
                <h2 class="text-sm font-bold text-slate-200 uppercase tracking-wide">Personal Details</h2>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <!-- Employee ID -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Employee ID *</label>
                    <input type="text" name="employee_id" value="{{ old('employee_id', 'EMP-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT)) }}" required
                        class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500/60 font-mono">
                </div>

                <!-- Full Name -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Full Name *</label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required placeholder="e.g. Rohit Sharma"
                        class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500/60">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Work Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="e.g. rohit@company.com"
                        class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500/60">
                </div>

                <!-- Contact -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Contact Number *</label>
                    <input type="text" name="contact_number" value="{{ old('contact_number') }}" required placeholder="+91 9876543210"
                        class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500/60">
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
                    <select name="gender" required class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <!-- Date of Birth -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Date of Birth *</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required
                        class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60">
                </div>

                <!-- Blood Group -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Blood Group *</label>
                    <select name="blood_group" required class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60">
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                    </select>
                </div>

                <!-- Emergency Contact -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Emergency Contact *</label>
                    <input type="text" name="emergency_contact" value="{{ old('emergency_contact') }}" required placeholder="Spouse/Parent details"
                        class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500/60">
                </div>
            </div>

            <!-- Address -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Permanent Address *</label>
                <textarea name="address" required rows="2" placeholder="Street, City, State, ZIP details..."
                    class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500/60">{{ old('address') }}</textarea>
            </div>
        </div>

        <!-- Section 2: Employment Details -->
        <div class="glass-card rounded-2xl p-6 space-y-4">
            <div class="border-b border-slate-800/80 pb-3">
                <h2 class="text-sm font-bold text-slate-200 uppercase tracking-wide">Employment details</h2>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <!-- Joining Date -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Joining Date *</label>
                    <input type="date" name="joining_date" value="{{ old('joining_date', now()->toDateString()) }}" required
                        class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60">
                </div>

                <!-- Department -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Department *</label>
                    <select name="department_id" required class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60">
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Designation -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Designation *</label>
                    <select name="designation_id" required class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60">
                        @foreach($designations as $desig)
                        <option value="{{ $desig->id }}">{{ $desig->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Manager -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Reporting Manager</label>
                    <select name="reporting_manager_id" class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60">
                        <option value="">No Reporting Manager</option>
                        @foreach($managers as $mng)
                        <option value="{{ $mng->id }}">{{ $mng->full_name }} ({{ $mng->employee_id }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Employee Type -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Employment Type *</label>
                    <select name="employee_type" required class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60">
                        <option value="Full-time">Full-time</option>
                        <option value="Part-time">Part-time</option>
                        <option value="Contract">Contract</option>
                        <option value="Intern">Intern</option>
                    </select>
                </div>

                <!-- Work Location -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Work Location *</label>
                    <select name="work_location" required class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60">
                        <option value="Onsite">Onsite</option>
                        <option value="Remote">Remote</option>
                        <option value="Hybrid">Hybrid</option>
                    </select>
                </div>

                <!-- Employment Status -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Status *</label>
                    <select name="employment_status" required class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Terminated">Terminated</option>
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
                    <input type="number" step="0.01" name="basic_salary" value="{{ old('basic_salary', '45000.00') }}" required
                        class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500/60 font-mono">
                </div>

                <!-- HRA -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">House Rent Allowance (HRA) (₹) *</label>
                    <input type="number" step="0.01" name="hra" value="{{ old('hra', '18000.00') }}" required
                        class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500/60 font-mono">
                </div>
            </div>
        </div>

        <!-- Section 4: System User Account -->
        <div class="glass-card rounded-2xl p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800/80 pb-3">
                <h2 class="text-sm font-bold text-slate-200 uppercase tracking-wide">Portal Access & Credentials</h2>
                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-indigo-400">
                    <input type="checkbox" name="create_user_account" id="toggle_account" value="1" onchange="toggleCreds(this)" class="rounded bg-slate-900 border-slate-800 text-indigo-600 focus:ring-indigo-500/50">
                    Create User Account
                </label>
            </div>
            
            <div id="credentials_block" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1 transition-all duration-300">
                <!-- System Role -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Security Authorization Role</label>
                    <select name="role" class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60">
                        <option value="employee">Employee (Default)</option>
                        <option value="hr_manager">HR Manager</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Security Password</label>
                    <input type="password" name="password" id="pass_field" placeholder="Minimum 8 characters"
                        class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500/60">
                </div>
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="flex items-center justify-end gap-3.5 pt-3">
            <a href="{{ route('employees.index') }}" class="px-5 py-2.5 bg-slate-900 border border-slate-800 hover:bg-slate-800 rounded-xl text-xs font-semibold text-slate-400 transition-all duration-200">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition-all duration-300">
                Complete Onboarding
            </button>
        </div>
    </form>

    <script>
        function toggleCreds(checkbox) {
            const block = document.getElementById('credentials_block');
            const pass = document.getElementById('pass_field');
            if (checkbox.checked) {
                block.classList.remove('hidden');
                pass.setAttribute('required', 'required');
            } else {
                block.classList.add('hidden');
                pass.removeAttribute('required');
            }
        }
    </script>

</div>
@endsection
