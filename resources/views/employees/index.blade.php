@extends('layouts.app')

@section('content')
<div class="space-y-6">
    
    <!-- Top Action bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-100 tracking-tight">Employee Profiles</h1>
            <p class="text-xs text-slate-400 mt-1">Manage core workforce parameters, departments, and payroll inputs.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('employees.export') }}" class="bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-200 flex items-center gap-2 shadow">
                <i class="fa-solid fa-file-csv"></i> Export Roster
            </a>
            <a href="{{ route('employees.create') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-200 flex items-center gap-2 shadow shadow-indigo-600/35">
                <i class="fa-solid fa-user-plus"></i> Onboard Employee
            </a>
        </div>
    </div>

    <!-- Search and Filter Panel -->
    <div class="glass-card p-5 rounded-2xl">
        <form action="{{ route('employees.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
            
            <!-- Search Text -->
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ID, Name, Email..." 
                    class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl pl-9 pr-4 py-2 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500/60 transition-all duration-200">
            </div>

            <!-- Department -->
            <div>
                <select name="department_id" class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60 transition-all duration-200">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Designation -->
            <div>
                <select name="designation_id" class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60 transition-all duration-200">
                    <option value="">All Designations</option>
                    @foreach($designations as $desig)
                    <option value="{{ $desig->id }}" {{ request('designation_id') == $desig->id ? 'selected' : '' }}>{{ $desig->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Employment Status -->
            <div>
                <select name="status" class="block w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60 transition-all duration-200">
                    <option value="">All Statuses</option>
                    <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="Terminated" {{ request('status') == 'Terminated' ? 'selected' : '' }}>Terminated</option>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 bg-indigo-600/10 text-indigo-400 hover:bg-indigo-600 hover:text-white border border-indigo-500/15 text-xs font-semibold py-2 rounded-xl transition-all duration-300">
                    Filter
                </button>
                <a href="{{ route('employees.index') }}" class="px-3 py-2 bg-slate-900 hover:bg-slate-800 border border-slate-800 rounded-xl text-slate-400 hover:text-slate-200 transition-all text-xs" title="Reset Filters">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Employee Grid / Table -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            @if($employees->isEmpty())
            <div class="h-64 flex flex-col items-center justify-center text-slate-500 gap-3">
                <i class="fa-solid fa-user-slash text-3xl text-slate-600"></i>
                <span class="text-xs">No employees found matching the filters.</span>
            </div>
            @else
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800/60 text-[10px] text-slate-500 uppercase tracking-widest font-bold bg-slate-900/20">
                        <th class="p-4">Employee</th>
                        <th class="p-4">Contact & Type</th>
                        <th class="p-4">Department & Role</th>
                        <th class="p-4">Joining Date</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40 text-xs text-slate-300">
                    @foreach($employees as $emp)
                    <tr class="hover:bg-slate-900/10 transition-colors duration-150">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                @if($emp->profile_image)
                                <img src="{{ asset('storage/' . $emp->profile_image) }}" class="w-9 h-9 rounded-xl object-cover border border-slate-800 shadow">
                                @else
                                <div class="w-9 h-9 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 font-bold uppercase shadow">
                                    {{ substr($emp->full_name, 0, 2) }}
                                </div>
                                @endif
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-200">{{ $emp->full_name }}</span>
                                    <span class="text-[10px] font-mono text-slate-500 mt-0.5">{{ $emp->employee_id }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="flex flex-col">
                                <span class="text-slate-300">{{ $emp->email }}</span>
                                <span class="text-[10px] font-mono text-slate-500 mt-0.5">{{ $emp->employee_type }} • {{ $emp->work_location }}</span>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="flex flex-col">
                                <span class="font-semibold text-slate-300">{{ $emp->designation->name }}</span>
                                <span class="text-[10px] text-indigo-400/80 font-mono mt-0.5 uppercase">{{ $emp->department->name }}</span>
                            </div>
                        </td>
                        <td class="p-4 font-mono text-slate-400">
                            {{ $emp->joining_date->format('M d, Y') }}
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider
                                {{ $emp->employment_status === 'Active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($emp->employment_status === 'Inactive' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20') }}">
                                {{ $emp->employment_status }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('employees.show', $emp) }}" class="p-2 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-700 text-indigo-400 hover:text-indigo-300 transition-all" title="View Profile">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </a>
                                <a href="{{ route('employees.edit', $emp) }}" class="p-2 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-300 hover:text-white transition-all" title="Edit Profile">
                                    <i class="fa-solid fa-pencil text-xs"></i>
                                </a>
                                <form action="{{ route('employees.destroy', $emp) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this employee?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg bg-slate-900 border border-slate-800 hover:border-red-500/30 hover:bg-red-500/10 text-slate-400 hover:text-red-400 transition-all" title="Delete Profile">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
        
        <!-- Pagination Links -->
        @if($employees->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-900/10">
            {{ $employees->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
