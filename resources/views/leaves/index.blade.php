@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-100 tracking-tight">Leave Operations Desk</h1>
            <p class="text-xs text-slate-400 mt-1">Track employee leave balances, workflows, and historical requests.</p>
        </div>
        @if(!Auth::user()->isHrManager())
        <a href="{{ route('leaves.create') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-200 flex items-center gap-2 shadow shadow-indigo-600/35">
            <i class="fa-solid fa-plane-departure"></i> Apply for Leave
        </a>
        @endif
    </div>

    <!-- 1. HR Manager / Super Admin Requests View -->
    @if(Auth::user()->isHrManager())
    
    <!-- Filter bar -->
    <div class="glass-card p-4 rounded-xl flex items-center justify-between">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Workforce Leave Requests</span>
        <div class="flex gap-2">
            <a href="{{ route('leaves.index') }}" class="px-3 py-1.5 rounded-lg border text-xs font-semibold {{ !$status ? 'bg-indigo-500/10 border-indigo-500/20 text-indigo-400' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-slate-200' }}">
                All
            </a>
            <a href="{{ route('leaves.index', ['status' => 'Pending']) }}" class="px-3 py-1.5 rounded-lg border text-xs font-semibold {{ $status === 'Pending' ? 'bg-indigo-500/10 border-indigo-500/20 text-indigo-400' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-slate-200' }}">
                Pending
            </a>
            <a href="{{ route('leaves.index', ['status' => 'Approved']) }}" class="px-3 py-1.5 rounded-lg border text-xs font-semibold {{ $status === 'Approved' ? 'bg-indigo-500/10 border-indigo-500/20 text-indigo-400' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-slate-200' }}">
                Approved
            </a>
            <a href="{{ route('leaves.index', ['status' => 'Rejected']) }}" class="px-3 py-1.5 rounded-lg border text-xs font-semibold {{ $status === 'Rejected' ? 'bg-indigo-500/10 border-indigo-500/20 text-indigo-400' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-slate-200' }}">
                Rejected
            </a>
        </div>
    </div>

    <!-- Requests list -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            @if($leaves->isEmpty())
            <div class="h-48 flex flex-col items-center justify-center text-slate-500 text-xs gap-2">
                <i class="fa-solid fa-umbrella-beach text-2xl text-slate-600"></i>
                <span>No leave applications recorded in system.</span>
            </div>
            @else
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800/60 text-[9px] text-slate-500 uppercase tracking-widest font-bold bg-slate-900/10">
                        <th class="p-3.5">Employee</th>
                        <th class="p-3.5">Leave Type</th>
                        <th class="p-3.5">Dates & Duration</th>
                        <th class="p-3.5">Reason</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40 text-xs text-slate-300">
                    @foreach($leaves as $leave)
                    <tr class="hover:bg-slate-900/10">
                        <td class="p-3.5">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-200">{{ $leave->employee->full_name }}</span>
                                <span class="text-[9px] text-slate-500 font-mono">{{ $leave->employee->employee_id }}</span>
                            </div>
                        </td>
                        <td class="p-3.5 font-semibold text-indigo-400">{{ $leave->leaveType->name }}</td>
                        <td class="p-3.5 font-mono">
                            {{ $leave->start_date->format('M d, Y') }} - {{ $leave->end_date->format('M d, Y') }}
                            <span class="text-indigo-400 font-sans block text-[10px] mt-0.5">({{ $leave->getDurationInDays() }} calendar days)</span>
                        </td>
                        <td class="p-3.5 text-slate-400 max-w-xs truncate" title="{{ $leave->reason }}">{{ $leave->reason }}</td>
                        <td class="p-3.5">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider
                                {{ $leave->status === 'Approved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($leave->status === 'Pending' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20') }}">
                                {{ $leave->status }}
                            </span>
                        </td>
                        <td class="p-3.5 text-right">
                            @if($leave->status === 'Pending')
                            <div class="flex items-center justify-end gap-1.5">
                                <form action="{{ route('leaves.update', $leave) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="Approved">
                                    <button type="submit" class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/15 text-[10px] px-2.5 py-1 rounded hover:bg-emerald-600 hover:text-white transition duration-200">
                                        Approve
                                    </button>
                                </form>
                                <form action="{{ route('leaves.update', $leave) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="Rejected">
                                    <button type="submit" class="bg-red-500/10 text-red-400 border border-red-500/15 text-[10px] px-2.5 py-1 rounded hover:bg-red-600 hover:text-white transition duration-200">
                                        Reject
                                    </button>
                                </form>
                            </div>
                            @else
                            <span class="text-[10px] text-slate-500 font-mono">Processed</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

        @if($leaves->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-900/10">
            {{ $leaves->links() }}
        </div>
        @endif
    </div>

    <!-- 2. Employee Leaves View -->
    @else
    
    @if(isset($noProfile) && $noProfile)
    <div class="glass-card p-8 rounded-3xl text-center space-y-4">
        <div class="w-16 h-16 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-2xl flex items-center justify-center mx-auto">
            <i class="fa-solid fa-user-slash"></i>
        </div>
        <h2 class="text-sm font-bold text-slate-200">Configure Employee Profile to apply for leave.</h2>
    </div>
    @else
    
    <!-- Balance Progress Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
        @foreach($leaveBalances as $bal)
        <div class="glass-card p-5 rounded-2xl flex items-center justify-between border-l-2
            @if($loop->index % 3 == 0) border-l-indigo-500 @elseif($loop->index % 3 == 1) border-l-emerald-500 @else border-l-purple-500 @endif">
            <div class="space-y-0.5">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wide block">{{ $bal['name'] }}</span>
                <span class="text-2xl font-extrabold text-slate-100 tracking-tight">{{ $bal['remaining'] }}</span>
                <span class="text-[10px] text-slate-500 block">Available out of {{ $bal['max'] }} days</span>
            </div>
            <div class="text-right space-y-1">
                <span class="text-[10px] font-semibold text-slate-400 font-mono block">Used: {{ $bal['used'] }}d</span>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Personal Requests History -->
    <div class="glass-card rounded-2xl p-5 flex flex-col">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-800/60">
            <h2 class="text-xs font-bold text-slate-200 tracking-wide uppercase">My Leave Requests History</h2>
            <i class="fa-solid fa-umbrella-beach text-slate-500"></i>
        </div>

        <div class="overflow-x-auto">
            @if($leaves->isEmpty())
            <div class="h-48 flex items-center justify-center text-slate-500 text-xs border border-dashed border-slate-800 rounded-xl">
                No leave requests submitted yet.
            </div>
            @else
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800/60 text-[9px] text-slate-500 uppercase tracking-wider font-bold bg-slate-900/10">
                        <th class="py-2.5">Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40 text-xs text-slate-300">
                    @foreach($leaves as $leave)
                    <tr>
                        <td class="py-3 font-semibold text-slate-200">{{ $leave->leaveType->name }}</td>
                        <td class="font-mono text-slate-300">{{ $leave->start_date->format('M d, Y') }}</td>
                        <td class="font-mono text-slate-300">{{ $leave->end_date->format('M d, Y') }}</td>
                        <td class="font-semibold text-indigo-400">{{ $leave->getDurationInDays() }} days</td>
                        <td>
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider
                                {{ $leave->status === 'Approved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($leave->status === 'Pending' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20') }}">
                                {{ $leave->status }}
                            </span>
                        </td>
                        <td class="text-right">
                            @if($leave->status === 'Pending')
                            <form action="{{ route('leaves.destroy', $leave) }}" method="POST" onsubmit="return confirm('Cancel this leave application?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500/10 hover:bg-red-500 hover:text-white text-red-400 border border-red-500/15 text-[10px] px-2.5 py-1 rounded transition duration-200">
                                    Cancel
                                </button>
                            </form>
                            @else
                            <span class="text-[10px] text-slate-500 font-mono">Completed</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
        
        @if($leaves->hasPages())
        <div class="mt-4">
            {{ $leaves->links() }}
        </div>
        @endif
    </div>

    @endif

    @endif

</div>
@endsection
