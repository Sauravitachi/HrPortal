<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Slip - {{ $payroll->employee->full_name }} - {{ $payroll->salary_month }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @vite(['resources/css/app.css'])

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
        }
        @media print {
            body {
                background-color: #ffffff;
                color: #000000;
            }
            .no-print {
                display: none !important;
            }
            .print-container {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body class="p-6">

    <!-- Print control bar -->
    <div class="no-print max-w-3xl mx-auto mb-6 flex justify-between items-center">
        <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider"><i class="fa-solid fa-receipt"></i> Official Payslip Preview</span>
        <div class="flex gap-2">
            <button onclick="window.close()" class="px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 shadow-sm transition">
                Close Preview
            </button>
            <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/25 transition">
                <i class="fa-solid fa-print mr-1"></i> Print Payslip
            </button>
        </div>
    </div>

    <!-- Payslip sheet -->
    <div class="print-container max-w-3xl mx-auto bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
        
        <!-- Header -->
        <div class="flex justify-between items-start border-b border-slate-200 pb-6 mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-800">HrPortal</h1>
                <span class="text-xs text-slate-500 uppercase tracking-widest block font-semibold mt-1">Human Resource Department</span>
                <span class="text-[10px] text-slate-400 block font-mono">Mohali HQ, Punjab, India</span>
            </div>
            <div class="text-right">
                <h2 class="text-base font-bold text-slate-700">Salary Slip</h2>
                <span class="text-xs font-mono font-bold text-indigo-600 block mt-1">MONTH: {{ \Carbon\Carbon::parse($payroll->salary_month . '-01')->format('F Y') }}</span>
                <span class="text-[9px] text-slate-400 block font-mono">Statement ID: #PAY-{{ str_pad($payroll->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
        </div>

        <!-- Employee Info Roster -->
        <div class="grid grid-cols-2 gap-y-4 gap-x-8 text-xs border-b border-slate-200 pb-6 mb-6">
            <div class="space-y-2">
                <div class="flex justify-between border-b border-slate-100 pb-1">
                    <span class="text-slate-500">Employee ID:</span>
                    <span class="font-bold text-slate-800 font-mono">{{ $payroll->employee->employee_id }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-100 pb-1">
                    <span class="text-slate-500">Full Name:</span>
                    <span class="font-semibold text-slate-800">{{ $payroll->employee->full_name }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-100 pb-1">
                    <span class="text-slate-500">Department:</span>
                    <span class="font-semibold text-slate-800">{{ $payroll->employee->department->name }}</span>
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex justify-between border-b border-slate-100 pb-1">
                    <span class="text-slate-500">Designation:</span>
                    <span class="font-semibold text-slate-800">{{ $payroll->employee->designation->name }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-100 pb-1">
                    <span class="text-slate-500">Joining Date:</span>
                    <span class="font-mono text-slate-700">{{ $payroll->employee->joining_date->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-100 pb-1">
                    <span class="text-slate-500">Status:</span>
                    <span class="font-bold text-emerald-600 uppercase">{{ $payroll->status }}</span>
                </div>
            </div>
        </div>

        <!-- Earnings & Deductions Tables -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start mb-6">
            
            <!-- Earnings Table -->
            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <div class="bg-slate-50 px-4 py-2 border-b border-slate-200 text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Earnings
                </div>
                <table class="w-full text-xs text-left">
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-2.5 text-slate-600">Basic Salary</td>
                            <td class="px-4 py-2.5 text-right font-mono text-slate-800">₹{{ number_format($payroll->basic_salary, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-2.5 text-slate-600">HRA Allowance</td>
                            <td class="px-4 py-2.5 text-right font-mono text-slate-800">₹{{ number_format($payroll->hra, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-2.5 text-slate-600">Incentives</td>
                            <td class="px-4 py-2.5 text-right font-mono text-slate-800">₹{{ number_format($payroll->incentives, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-2.5 text-slate-600">Bonuses</td>
                            <td class="px-4 py-2.5 text-right font-mono text-slate-800">₹{{ number_format($payroll->bonuses, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-2.5 text-slate-600">Other Allowances</td>
                            <td class="px-4 py-2.5 text-right font-mono text-slate-800">₹{{ number_format($payroll->allowances, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Deductions Table -->
            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <div class="bg-slate-50 px-4 py-2 border-b border-slate-200 text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Deductions
                </div>
                <table class="w-full text-xs text-left">
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-2.5 text-slate-600">Provident Fund (PF)</td>
                            <td class="px-4 py-2.5 text-right font-mono text-red-500">₹{{ number_format($payroll->pf, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-2.5 text-slate-600">Employees' State Insurance (ESI)</td>
                            <td class="px-4 py-2.5 text-right font-mono text-red-500">₹{{ number_format($payroll->esi, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-2.5 text-slate-600">Professional Tax</td>
                            <td class="px-4 py-2.5 text-right font-mono text-red-500">₹{{ number_format($payroll->tax, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-2.5 text-slate-600">Loan Repayments</td>
                            <td class="px-4 py-2.5 text-right font-mono text-red-500">₹{{ number_format($payroll->loan_deductions, 2) }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-2.5 text-slate-600">Other Deductions</td>
                            <td class="px-4 py-2.5 text-right font-mono text-red-500">₹{{ number_format($payroll->other_deductions, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Totals Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs font-semibold mb-6">
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex justify-between items-center">
                <span class="text-slate-600 uppercase">Gross Earnings:</span>
                <span class="font-bold font-mono text-slate-800 text-sm">₹{{ number_format($payroll->gross_salary, 2) }}</span>
            </div>

            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex justify-between items-center">
                <span class="text-slate-600 uppercase">Total Deductions:</span>
                <span class="font-bold font-mono text-red-500 text-sm">₹{{ number_format($payroll->total_deductions, 2) }}</span>
            </div>
        </div>

        <!-- Final Disbursed Net Salary Card -->
        <div class="p-5 bg-indigo-50 border border-indigo-200 rounded-2xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div class="space-y-1">
                <span class="text-[10px] font-extrabold text-indigo-500 uppercase tracking-widest block">Net Disbursed Take-home Salary</span>
                <h3 class="text-2xl font-extrabold text-slate-800 font-mono leading-none">₹{{ number_format($payroll->net_salary, 2) }}</h3>
            </div>
            <div class="text-left sm:text-right space-y-1 text-xs">
                <span class="text-slate-500 block">Bank Transfer Status: <span class="font-bold text-emerald-600 uppercase">SUCCESS</span></span>
                @if($payroll->processed_at)
                <span class="text-[10px] text-slate-400 font-mono block">Processed On: {{ $payroll->processed_at->format('d M Y, h:i A') }}</span>
                @endif
            </div>
        </div>

        <!-- Signatures & Notes -->
        <div class="grid grid-cols-2 gap-8 text-[10px] text-slate-400 pt-8 border-t border-slate-200 border-dashed">
            <div>
                <span class="font-bold text-slate-500 uppercase block mb-1">Company Seal & Authorization</span>
                <p class="leading-relaxed">This is an electronically generated salary statement. No physical signature is required for validity. All tax computations are processed dynamically under standard codes.</p>
            </div>
            <div class="text-right flex flex-col justify-end">
                <div class="h-10 border-b border-slate-200 max-w-[200px] self-end w-full"></div>
                <span class="font-bold text-slate-500 uppercase block mt-2">Authorized Signee</span>
            </div>
        </div>

    </div>

</body>
</html>
