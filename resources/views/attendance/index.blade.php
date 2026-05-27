@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-100 tracking-tight">Shift Attendance Logs</h1>
            <p class="text-xs text-slate-400 mt-1">Track working shifts, daily roster status, and calendar events.</p>
        </div>
    </div>

    <!-- 1. HR Manager / Super Admin Roster View -->
    @if(Auth::user()->isHrManager())
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="glass-card p-4 rounded-2xl flex items-center justify-between">
            <div class="space-y-0.5">
                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wide block">Roster Roster Date</span>
                <span class="text-xs font-mono text-slate-300 font-semibold">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</span>
            </div>
            <i class="fa-regular fa-clock text-slate-600 text-lg"></i>
        </div>

        <div class="glass-card p-4 rounded-2xl flex items-center justify-between border-l border-l-emerald-500">
            <div class="space-y-0.5">
                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wide block">Present Today</span>
                <span class="text-xl font-extrabold text-slate-200">{{ $presentCount }}</span>
                <span class="text-[9px] text-slate-500 block">Out of {{ $totalEmployees }} total</span>
            </div>
            <div class="w-8 h-8 rounded bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xs">
                <i class="fa-solid fa-user-check"></i>
            </div>
        </div>

        <div class="glass-card p-4 rounded-2xl flex items-center justify-between border-l border-l-red-500">
            <div class="space-y-0.5">
                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wide block">Absent Today</span>
                <span class="text-xl font-extrabold text-slate-200">{{ $absentCount }}</span>
                <span class="text-[9px] text-slate-500 block">Pending check-ins</span>
            </div>
            <div class="w-8 h-8 rounded bg-red-500/10 text-red-400 flex items-center justify-center text-xs">
                <i class="fa-solid fa-user-xmark"></i>
            </div>
        </div>
    </div>

    <!-- Date & Dept Filter Bar -->
    <div class="glass-card p-4 rounded-xl">
        <form action="{{ route('attendance.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Roster Date</label>
                <input type="date" name="date" value="{{ $date }}" 
                    class="block w-full bg-slate-950/60 border border-slate-800 rounded-lg px-3 py-1.5 text-xs text-slate-300 focus:outline-none">
            </div>

            <div>
                <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Department</label>
                <select name="department_id" class="block w-full bg-slate-950/60 border border-slate-800 rounded-lg px-3 py-1.5 text-xs text-slate-300 focus:outline-none">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2 px-4 rounded-xl text-xs shadow shadow-indigo-600/35 transition duration-200">
                Load Roster
            </button>
        </form>
    </div>

    <!-- Attendance Roster Grid -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            @if($attendances->isEmpty())
            <div class="h-48 flex flex-col items-center justify-center text-slate-500 text-xs gap-2">
                <i class="fa-solid fa-clipboard-question text-2xl text-slate-600"></i>
                <span>No attendance logs found for this date.</span>
            </div>
            @else
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800/60 text-[9px] text-slate-500 uppercase tracking-wider font-bold bg-slate-900/10">
                        <th class="p-3.5">Employee</th>
                        <th class="p-3.5">Department</th>
                        <th class="p-3.5">Check In</th>
                        <th class="p-3.5">Check Out</th>
                        <th class="p-3.5">Total Hours</th>
                        <th class="p-3.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40 text-xs text-slate-300">
                    @foreach($attendances as $att)
                    <tr class="hover:bg-slate-900/10">
                        <td class="p-3.5 font-semibold text-slate-200">{{ $att->employee->full_name }}</td>
                        <td class="p-3.5 text-slate-400 font-mono text-[10px]">{{ $att->employee->department->name }}</td>
                        <td class="p-3.5 font-mono text-indigo-400">{{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('h:i A') : '--:--' }}</td>
                        <td class="p-3.5 font-mono text-indigo-400">{{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('h:i A') : '--:--' }}</td>
                        <td class="p-3.5 font-mono text-slate-400">{{ $att->total_hours ? $att->total_hours . ' hrs' : '--' }}</td>
                        <td class="p-3.5">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider
                                {{ $att->status === 'Present' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($att->status === 'Work from home' ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : ($att->status === 'Half day' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20')) }}">
                                {{ $att->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    <!-- 2. Employee Personal Calendar View -->
    @else
    
    @if(isset($noProfile) && $noProfile)
    <div class="glass-card p-8 rounded-3xl text-center space-y-4">
        <div class="w-16 h-16 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-2xl flex items-center justify-center mx-auto">
            <i class="fa-solid fa-user-slash"></i>
        </div>
        <h2 class="text-sm font-bold text-slate-200">No profile associated to clock shift hours.</h2>
    </div>
    @else
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Quick clock widgets -->
        <div class="glass-card p-5 rounded-2xl flex flex-col justify-between h-fit gap-6 relative overflow-hidden">
            <div class="absolute -top-10 -left-10 w-24 h-24 bg-indigo-500/5 rounded-full blur-2xl pointer-events-none"></div>

            <div class="space-y-4">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block border-b border-slate-800 pb-2">Shift Clock Terminal</span>
                
                <div class="text-center py-4 space-y-1">
                    <span class="text-3xl font-extrabold text-slate-200 tracking-tight font-mono" id="live_clock">00:00:00</span>
                    <span class="text-[10px] font-mono text-slate-500 uppercase block">Indian Standard Time</span>
                </div>
            </div>

            @if(!$todayAttendance)
            <form action="{{ route('attendance.check-in') }}" method="POST" class="space-y-4">
                @csrf
                <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-400">
                    <input type="checkbox" name="wfh" value="1" class="rounded bg-slate-900 border-slate-800 text-indigo-600 focus:ring-indigo-500/50">
                    Work From Home (WFH) Flag
                </label>
                <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold py-2.5 rounded-xl text-xs transition duration-300 flex items-center justify-center gap-2 shadow shadow-indigo-600/30">
                    <i class="fa-solid fa-right-to-bracket"></i> Clock Check-In
                </button>
            </form>
            @else
            <div class="space-y-4.5">
                <div class="p-3 bg-indigo-500/5 border border-indigo-500/15 rounded-xl flex items-center justify-between text-xs">
                    <span class="text-slate-400">Active Check-In:</span>
                    <span class="font-bold text-indigo-400 font-mono">{{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}</span>
                </div>
                <form action="{{ route('attendance.check-out') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold py-2.5 rounded-xl text-xs transition duration-300 flex items-center justify-center gap-2 shadow shadow-emerald-600/30">
                        <i class="fa-solid fa-right-from-bracket"></i> Clock Check-Out
                    </button>
                </form>
            </div>
            @endif
        </div>

        <!-- Right: Monthly calendar grid -->
        <div class="glass-card rounded-2xl p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-800/60">
                <h2 class="text-sm font-bold text-slate-200 tracking-wide uppercase">My Shift Calendar</h2>
                <form action="{{ route('attendance.index') }}" method="GET">
                    <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()"
                        class="bg-slate-950 border border-slate-800 rounded-lg px-2.5 py-1 text-xs text-indigo-400 focus:outline-none font-semibold">
                </form>
            </div>

            <!-- Calendar sheet -->
            <div class="grid grid-cols-7 gap-2.5 text-center">
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $w)
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ $w }}</span>
                @endforeach

                <!-- Add padding blank days depending on first day week index -->
                @php
                    $firstDayWeekIndex = $calendarDays[0]['date']->dayOfWeek;
                @endphp
                @for($i = 0; $i < $firstDayWeekIndex; $i++)
                <div class="h-10 rounded-lg border border-transparent"></div>
                @endfor

                @foreach($calendarDays as $day)
                <div class="h-11 rounded-xl p-1.5 flex flex-col justify-between border relative group transition duration-300
                    {{ $day['status'] === 'Present' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' : 
                      ($day['status'] === 'Work from home' ? 'bg-indigo-500/10 border-indigo-500/20 text-indigo-400' :
                      ($day['status'] === 'Half day' ? 'bg-amber-500/10 border-amber-500/20 text-amber-400' :
                      ($day['status'] === 'Holiday' ? 'bg-slate-900 border-slate-800/60 text-slate-500' : 'bg-red-500/10 border-red-500/20 text-red-400'))) }}">
                    
                    <span class="text-xs font-bold leading-none">{{ $day['date']->day }}</span>
                    
                    @if($day['attendances'] && $day['attendances']->isNotEmpty())
                    <span class="text-[7px] font-mono leading-none tracking-tighter mt-1 block group-hover:hidden">
                        {{ $day['attendances']->sum('total_hours') > 0 ? number_format($day['attendances']->sum('total_hours'), 1) . 'h' : 'Active' }}
                    </span>
                    
                    <!-- Hover shifts list -->
                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 hidden group-hover:block bg-slate-950 border border-slate-800 text-[8px] font-mono text-slate-300 rounded-lg p-2 w-28 shadow-xl z-50 pointer-events-none text-left space-y-1">
                        <span class="text-[7px] font-bold text-slate-500 uppercase block tracking-wider mb-0.5">Shifts Today:</span>
                        @foreach($day['attendances'] as $session)
                        <div class="flex flex-col border-b border-slate-800/40 pb-1 last:border-b-0 last:pb-0">
                            <div class="flex justify-between font-bold">
                                <span>In: {{ \Carbon\Carbon::parse($session->check_in)->format('H:i') }}</span>
                                <span>{{ $session->check_out ? 'Out: ' . \Carbon\Carbon::parse($session->check_out)->format('H:i') : 'Active' }}</span>
                            </div>
                            @if($session->total_hours)
                            <span class="text-[6.5px] text-indigo-400">Worked: {{ $session->total_hours }}h</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                    
                    <span class="text-[7px] font-bold uppercase tracking-wider block leading-none select-none group-hover:block {{ $day['attendances'] && $day['attendances']->isNotEmpty() ? 'hidden' : '' }}">
                        {{ substr($day['status'], 0, 3) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    <script>
        setInterval(() => {
            const clock = document.getElementById('live_clock');
            if (clock) {
                const now = new Date();
                clock.textContent = now.toLocaleTimeString('en-US', { hour12: false });
            }
        }, 1000);
    </script>
    @endif

    @endif

</div>
@endsection
