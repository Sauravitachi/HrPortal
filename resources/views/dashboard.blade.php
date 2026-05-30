@extends('layouts.app')

@section('content')
<div class="space-y-6">
    
    <!-- Dashboard Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-100 tracking-tight">
                {{ Auth::user()->isHrManager() ? 'HR Administration Control' : 'Welcome back, ' . (Auth::user()->employee ? Auth::user()->employee->full_name : Auth::user()->name) }}
            </h1>
            <p class="text-xs text-slate-400 mt-1">Today is {{ now()->format('l, d F Y') }}</p>
        </div>
        
        @if(Auth::user()->employee)
        <!-- Quick Attendance Widget -->
        <div class="glass-card px-4 py-2.5 rounded-2xl flex items-center gap-3">
            @if(!$todayAttendance)
            <span class="text-xs text-slate-400">Not checked in today:</span>
            <div class="flex gap-2">
                <form action="{{ route('attendance.check-in') }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    <label class="text-[10px] text-slate-500 flex items-center gap-1 cursor-pointer hover:text-slate-300">
                        <input type="checkbox" name="wfh" value="1" class="rounded bg-slate-900 border-slate-800 text-indigo-600 focus:ring-indigo-500/50">
                        WFH
                    </label>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow shadow-indigo-600/30 transition-all duration-200">
                        Check In
                    </button>
                </form>
            </div>
            @else
            <span class="text-xs text-slate-300 flex items-center gap-1.5">
                Checked in at <span class="font-mono text-indigo-400 font-semibold">{{ \Illuminate\Support\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}</span>
                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider
                    {{ $todayAttendance->status === 'Present' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($todayAttendance->status === 'Work from home' ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : ($todayAttendance->status === 'Late' ? 'bg-orange-500/10 text-orange-400 border border-orange-500/20' : ($todayAttendance->status === 'Half day' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20'))) }}">
                    {{ $todayAttendance->status }}
                </span>
            </span>
            <form action="{{ route('attendance.check-out') }}" method="POST">
                @csrf
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow shadow-emerald-600/30 transition-all duration-200">
                    Check Out
                </button>
            </form>
            @endif
        </div>
        @endif
    </div>

    <!-- 1. HR Manager / Super Admin Dashboard -->
    @if(Auth::user()->isHrManager())
    
    <!-- Admin Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="glass-card p-5 rounded-2xl flex items-center justify-between relative overflow-hidden">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Active Employees</span>
                <span class="text-3xl font-extrabold text-slate-100 tracking-tight">{{ $totalEmployees }}</span>
            </div>
            <div class="bg-indigo-500/10 text-indigo-400 p-4 rounded-xl border border-indigo-500/15">
                <i class="fa-solid fa-users text-2xl"></i>
            </div>
        </div>

        <div class="glass-card p-5 rounded-2xl flex items-center justify-between relative overflow-hidden">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Present Today</span>
                <span class="text-3xl font-extrabold text-slate-100 tracking-tight">{{ $presentToday }}</span>
                <span class="text-[10px] text-slate-500 block">Active roster status</span>
            </div>
            <div class="bg-emerald-500/10 text-emerald-400 p-4 rounded-xl border border-emerald-500/15">
                <i class="fa-regular fa-calendar-check text-2xl"></i>
            </div>
        </div>

        <div class="glass-card p-5 rounded-2xl flex items-center justify-between relative overflow-hidden">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Pending Leaves</span>
                <span class="text-3xl font-extrabold text-slate-100 tracking-tight">{{ $pendingLeaves }}</span>
            </div>
            <div class="bg-amber-500/10 text-amber-400 p-4 rounded-xl border border-amber-500/15">
                <i class="fa-solid fa-umbrella-beach text-2xl"></i>
            </div>
        </div>

        <div class="glass-card p-5 rounded-2xl flex items-center justify-between relative overflow-hidden">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Job Postings</span>
                <span class="text-3xl font-extrabold text-slate-100 tracking-tight">{{ $activeJobs }}</span>
            </div>
            <div class="bg-purple-500/10 text-purple-400 p-4 rounded-xl border border-purple-500/15">
                <i class="fa-solid fa-briefcase text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Admin Lists Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Recent Leave Requests -->
        <div class="glass-card rounded-2xl p-5 flex flex-col">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-800/60">
                <h2 class="text-sm font-bold text-slate-200 tracking-wide uppercase">Pending Leave Applications</h2>
                <a href="{{ route('leaves.index') }}" class="text-[10px] font-semibold text-indigo-400 hover:text-indigo-300">View Roster <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="flex-1 overflow-x-auto">
                @if($recentLeaves->isEmpty())
                <div class="h-48 flex items-center justify-center text-slate-500 text-xs">
                    No recent leave applications.
                </div>
                @else
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-[10px] text-slate-500 uppercase tracking-wider font-bold">
                            <th class="py-2.5">Employee</th>
                            <th>Type</th>
                            <th>Dates</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/40 text-xs text-slate-300">
                        @foreach($recentLeaves as $leave)
                        <tr>
                            <td class="py-3 font-semibold text-slate-200">{{ $leave->employee->full_name }}</td>
                            <td>{{ $leave->leaveType->name }}</td>
                            <td class="font-mono text-[10px]">
                                {{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d') }}
                                <span class="text-slate-500 font-sans ml-1">({{ $leave->getDurationInDays() }}d)</span>
                            </td>
                            <td class="text-right">
                                @if($leave->status === 'Pending')
                                <div class="flex items-center justify-end gap-1.5">
                                    <form action="{{ route('leaves.update', $leave) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="Approved">
                                        <button type="submit" class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/15 text-[10px] px-2 py-0.5 rounded hover:bg-emerald-500 hover:text-white transition-all duration-200">
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('leaves.update', $leave) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="Rejected">
                                        <button type="submit" class="bg-red-500/10 text-red-400 border border-red-500/15 text-[10px] px-2 py-0.5 rounded hover:bg-red-500 hover:text-white transition-all duration-200">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                                @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold tracking-wider font-sans uppercase inline-block
                                    {{ $leave->status === 'Approved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' }}">
                                    {{ $leave->status }}
                                </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>

        <!-- Recent Employee Joinings -->
        <div class="glass-card rounded-2xl p-5 flex flex-col">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-800/60">
                <h2 class="text-sm font-bold text-slate-200 tracking-wide uppercase">Recently Onboarded Employees</h2>
                <a href="{{ route('employees.index') }}" class="text-[10px] font-semibold text-indigo-400 hover:text-indigo-300">Roster Index <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="flex-1">
                @if($recentEmployees->isEmpty())
                <div class="h-48 flex items-center justify-center text-slate-500 text-xs">
                    No onboarding history recorded.
                </div>
                @else
                <div class="space-y-3.5">
                    @foreach($recentEmployees as $emp)
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-900/40 border border-slate-800/40 hover:border-slate-800 transition-all duration-200">
                        <div class="flex items-center gap-3">
                            @if($emp->profile_image)
                            <img src="{{ asset('storage/' . $emp->profile_image) }}" class="w-8 h-8 rounded-lg object-cover">
                            @else
                            <div class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/25 flex items-center justify-center text-indigo-400 text-xs font-bold">
                                {{ substr($emp->full_name, 0, 2) }}
                            </div>
                            @endif
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-200">{{ $emp->full_name }}</span>
                                <span class="text-[10px] text-slate-500 font-mono">{{ $emp->employee_id }} • {{ $emp->designation->name }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold text-slate-400 block">{{ $emp->department->name }}</span>
                            <span class="text-[9px] text-slate-500 block font-mono">Joined {{ $emp->joining_date->format('M d, Y') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 2. Employee Dashboard -->
    @else
    
    @if(isset($noProfile) && $noProfile)
    <div class="glass-card p-8 rounded-3xl text-center space-y-4">
        <div class="w-16 h-16 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-2xl flex items-center justify-center mx-auto">
            <i class="fa-solid fa-user-slash"></i>
        </div>
        <div class="space-y-1">
            <h2 class="text-lg font-bold text-slate-200">No Profile Associated</h2>
            <p class="text-xs text-slate-400 max-w-sm mx-auto">Please contact your system Super Admin or HR Manager to configure an Employee profile associated with your user email.</p>
        </div>
    </div>
    @else
    <!-- Employee Statistics & Balance cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-5">
        
        <!-- Leave Balance Cards -->
        @foreach($leaveBalances as $bal)
        <div class="glass-card p-4.5 rounded-2xl flex items-center justify-between border-l-2
            @if($loop->index % 3 == 0) border-l-indigo-500 @elseif($loop->index % 3 == 1) border-l-emerald-500 @else border-l-purple-500 @endif">
            <div class="space-y-0.5">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">{{ $bal['name'] }}</span>
                <span class="text-2xl font-extrabold text-slate-100">{{ $bal['remaining'] }}</span>
                <span class="text-[9px] text-slate-500 block">Available out of {{ $bal['max'] }} days</span>
            </div>
            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-sm
                @if($loop->index % 3 == 0) bg-indigo-500/10 text-indigo-400 @elseif($loop->index % 3 == 1) bg-emerald-500/10 text-emerald-400 @else bg-purple-500/10 text-purple-400 @endif">
                <i class="fa-solid fa-umbrella-beach"></i>
            </div>
        </div>
        @endforeach

        <!-- Attendance Stats Card -->
        <div class="glass-card p-4.5 rounded-2xl flex items-center justify-between border-l-2 border-l-emerald-500">
            <div class="space-y-0.5">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Attendance Rate</span>
                <span class="text-2xl font-extrabold text-slate-100">{{ $attendancePercentage }}%</span>
                <span class="text-[9px] text-slate-500 block">Based on last 30 working days</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-sm">
                <i class="fa-regular fa-calendar-check"></i>
            </div>
        </div>
    </div>

    <!-- Employee Widgets Column layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Upcoming Holidays List -->
        <div class="glass-card rounded-2xl p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-800/60">
                <h2 class="text-sm font-bold text-slate-200 tracking-wide uppercase">Upcoming Official Holidays</h2>
                <span class="text-[10px] font-semibold text-slate-500 font-mono">2026 CALENDAR</span>
            </div>
            <div class="space-y-3">
                @if($upcomingHolidays->isEmpty())
                <div class="h-48 flex items-center justify-center text-slate-500 text-xs">
                    No upcoming holidays scheduled for the rest of the year.
                </div>
                @else
                @foreach($upcomingHolidays as $hol)
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900/40 border border-slate-800/40 hover:border-slate-800/80 transition-all duration-200">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex flex-col items-center justify-center font-mono">
                            <span class="text-xs font-bold leading-none">{{ $hol->date->format('d') }}</span>
                            <span class="text-[8px] uppercase tracking-wider leading-none mt-1">{{ $hol->date->format('M') }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-slate-200">{{ $hol->name }}</span>
                            <span class="text-[10px] text-slate-500 capitalize">{{ $hol->type }} Holiday</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-mono text-indigo-400 px-2 py-0.5 rounded bg-indigo-500/10 border border-indigo-500/20">
                        {{ $hol->date->format('l') }}
                    </span>
                </div>
                @endforeach
                @endif
            </div>
        </div>

        <!-- Right: Recent Payslips -->
        <div class="glass-card rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-800/60">
                <h2 class="text-sm font-bold text-slate-200 tracking-wide uppercase">Recent Salary Slips</h2>
                <i class="fa-solid fa-receipt text-slate-500"></i>
            </div>
            <div class="space-y-3.5">
                @if($recentPayrolls->isEmpty())
                <div class="h-48 flex items-center justify-center text-slate-500 text-xs text-center px-4">
                    Your salary slips for generated months will appear here once processed.
                </div>
                @else
                @foreach($recentPayrolls as $pay)
                <div class="p-3.5 rounded-xl bg-slate-900/40 border border-slate-800/40 hover:border-slate-800/80 flex flex-col gap-3 transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-slate-200">Month: {{ \Carbon\Carbon::parse($pay->salary_month . '-01')->format('F Y') }}</span>
                            <span class="text-[9px] text-slate-500 font-mono uppercase tracking-wider">Status: PAID</span>
                        </div>
                        <span class="text-xs font-bold text-emerald-400 font-mono">₹{{ number_format($pay->net_salary, 2) }}</span>
                    </div>
                    <a href="{{ route('payroll.show', $pay) }}" target="_blank" class="w-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 text-[10px] font-bold py-1.5 px-3 rounded-lg hover:bg-indigo-600 hover:text-white transition-all duration-300 text-center flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-print"></i> Print Salary Slip
                    </a>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>
    @endif

    @endif
</div>
@endsection
