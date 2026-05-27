@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-100 tracking-tight">Payroll Processing Hub</h1>
            <p class="text-xs text-slate-400 mt-1">Generate monthly payroll statements, calculate deductions, and print salary slips.</p>
        </div>

        <!-- Monthly Generator Form -->
        <form action="{{ route('payroll.generate') }}" method="POST" class="glass-card p-2 rounded-xl flex items-center gap-2">
            @csrf
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2 font-mono">Process Month:</span>
            <input type="month" name="month" value="{{ $month }}" required
                class="bg-slate-950 border border-slate-800 rounded-lg px-2 py-1 text-xs text-slate-300 focus:outline-none font-semibold">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-1 px-3 rounded-lg text-xs transition duration-200">
                Generate Drafts
            </button>
        </form>
    </div>

    <!-- Stats Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="glass-card p-4 rounded-2xl flex items-center justify-between border-l-2 border-l-indigo-500">
            <div class="space-y-0.5">
                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wide block">Total Gross Salary</span>
                <span class="text-xl font-extrabold text-slate-200 font-mono">₹{{ number_format($totalGross, 2) }}</span>
                <span class="text-[9px] text-slate-500 block">Earnings this month</span>
            </div>
            <i class="fa-solid fa-calculator text-slate-600 text-lg"></i>
        </div>

        <div class="glass-card p-4 rounded-2xl flex items-center justify-between border-l-2 border-l-red-500">
            <div class="space-y-0.5">
                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wide block">Total Deductions</span>
                <span class="text-xl font-extrabold text-slate-200 font-mono text-red-400">₹{{ number_format($totalDeductions, 2) }}</span>
                <span class="text-[9px] text-slate-500 block">PF, Tax, ESI combined</span>
            </div>
            <i class="fa-solid fa-shield-halved text-slate-600 text-lg"></i>
        </div>

        <div class="glass-card p-4 rounded-2xl flex items-center justify-between border-l-2 border-l-emerald-500">
            <div class="space-y-0.5">
                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wide block">Total Net Disbursed</span>
                <span class="text-xl font-extrabold text-slate-200 font-mono text-emerald-400">₹{{ number_format($totalNet, 2) }}</span>
                <span class="text-[9px] text-slate-500 block">Net bank transfers</span>
            </div>
            <i class="fa-solid fa-wallet text-slate-600 text-lg"></i>
        </div>
    </div>

    <!-- Month Filter Bar -->
    <div class="glass-card p-4 rounded-xl flex items-center justify-between">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Payroll Index Sheet</span>
        <form action="{{ route('payroll.index') }}" method="GET" class="flex items-center gap-2">
            <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()"
                class="bg-slate-950 border border-slate-800 rounded-lg px-2 py-1 text-xs text-indigo-400 focus:outline-none font-semibold">
        </form>
    </div>

    <!-- Payroll Statements Table -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            @if($payrolls->isEmpty())
            <div class="h-48 flex flex-col items-center justify-center text-slate-500 text-xs gap-2">
                <i class="fa-solid fa-file-invoice-dollar text-2xl text-slate-600"></i>
                <span>No payroll records exist for {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}. Generate drafts to process.</span>
            </div>
            @else
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800/60 text-[9px] text-slate-500 uppercase tracking-wider font-bold bg-slate-900/10">
                        <th class="p-3.5">Employee</th>
                        <th class="p-3.5">Basic Salary</th>
                        <th class="p-3.5">Allowances</th>
                        <th class="p-3.5">Deductions</th>
                        <th class="p-3.5">Net Salary</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40 text-xs text-slate-300">
                    @foreach($payrolls as $pay)
                    <tr class="hover:bg-slate-900/10">
                        <td class="p-3.5">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-200">{{ $pay->employee->full_name }}</span>
                                <span class="text-[9px] text-slate-500 font-mono">{{ $pay->employee->employee_id }}</span>
                            </div>
                        </td>
                        <td class="p-3.5 font-mono">₹{{ number_format($pay->basic_salary, 2) }}</td>
                        <td class="p-3.5 font-mono text-slate-400">₹{{ number_format($pay->hra + $pay->incentives + $pay->bonuses + $pay->allowances, 2) }}</td>
                        <td class="p-3.5 font-mono text-red-400">₹{{ number_format($pay->total_deductions, 2) }}</td>
                        <td class="p-3.5 font-bold font-mono text-slate-200">₹{{ number_format($pay->net_salary, 2) }}</td>
                        <td class="p-3.5">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider
                                {{ $pay->status === 'Paid' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                                {{ $pay->status }}
                            </span>
                        </td>
                        <td class="p-3.5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('payroll.show', $pay) }}" target="_blank" class="p-2 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-700 text-indigo-400 hover:text-indigo-300 transition-all" title="Print Salary Slip">
                                    <i class="fa-solid fa-print text-xs"></i>
                                </a>
                                @if($pay->status === 'Draft')
                                <form action="{{ route('payroll.pay', $pay) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-emerald-600/10 hover:bg-emerald-600 border border-emerald-500/20 text-emerald-400 hover:text-white text-[10px] font-bold py-1 px-3 rounded-lg transition duration-200">
                                        Disburse
                                    </button>
                                </form>
                                @else
                                <span class="text-[10px] text-slate-500 font-mono mr-2">Disbursed</span>
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

</div>
@endsection
