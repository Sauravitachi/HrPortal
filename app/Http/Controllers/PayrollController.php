<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PayrollController extends Controller
{
    /**
     * Display payroll list for selected month.
     */
    public function index(Request $request): View
    {
        $month = $request->input('month', now()->format('Y-m'));

        $payrolls = Payroll::with(['employee.department', 'employee.designation'])
            ->where('salary_month', $month)
            ->get();

        // Calculate totals
        $totalGross = $payrolls->sum('gross_salary');
        $totalDeductions = $payrolls->sum('total_deductions');
        $totalNet = $payrolls->sum('net_salary');

        return view('payroll.index', compact('payrolls', 'month', 'totalGross', 'totalDeductions', 'totalNet'));
    }

    /**
     * Generate monthly payroll draft for all active employees.
     */
    public function generate(Request $request): RedirectResponse
    {
        $request->validate([
            'month' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $month = $request->input('month');
        $employees = Employee::where('employment_status', 'Active')->get();

        if ($employees->isEmpty()) {
            return back()->with('error', 'No active employees found to generate payroll.');
        }

        $generatedCount = 0;
        $skippedCount = 0;

        foreach ($employees as $emp) {
            // Check if already generated
            $exists = Payroll::where('employee_id', $emp->id)
                ->where('salary_month', $month)
                ->exists();

            if ($exists) {
                $skippedCount++;

                continue;
            }

            // Calculations
            $basic = $emp->basic_salary;
            $hra = $emp->hra;

            // Simple Auto Deduction formulas
            $pf = round($basic * 0.12, 2);  // 12% standard PF
            $esi = round($basic * 0.0175, 2); // 1.75% ESI
            $tax = 0.00;

            // Simple Tax Brackets based on Basic Salary
            if ($basic > 50000) {
                $tax = round($basic * 0.10, 2); // 10% tax
            } elseif ($basic > 30000) {
                $tax = round($basic * 0.05, 2); // 5% tax
            }

            $payroll = new Payroll([
                'employee_id' => $emp->id,
                'salary_month' => $month,
                'basic_salary' => $basic,
                'hra' => $hra,
                'pf' => $pf,
                'esi' => $esi,
                'tax' => $tax,
                'incentives' => 0.00,
                'bonuses' => 0.00,
                'allowances' => 0.00,
                'loan_deductions' => 0.00,
                'other_deductions' => 0.00,
                'status' => 'Draft',
            ]);

            $payroll->calculateSalary();
            $payroll->save();

            $generatedCount++;
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Payroll Generated',
            'description' => "Generated payroll records for {$month}. Generated: {$generatedCount}, Skipped: {$skippedCount}.",
        ]);

        return back()->with('success', "Payroll processing complete. Generated: {$generatedCount} slips. Skipped: {$skippedCount} existing slips.");
    }

    /**
     * Show a detailed salary slip (highly printable).
     */
    public function show(Payroll $payroll): View
    {
        $payroll->load(['employee.department', 'employee.designation']);

        return view('payroll.show', compact('payroll'));
    }

    /**
     * Mark a payroll as paid.
     */
    public function pay(Payroll $payroll): RedirectResponse
    {
        if ($payroll->status === 'Paid') {
            return back()->with('error', 'Payroll has already been marked as Paid.');
        }

        $payroll->status = 'Paid';
        $payroll->processed_at = now();
        $payroll->save();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Payroll Paid',
            'description' => "Marked payroll slip #{$payroll->id} as PAID for {$payroll->employee->full_name}.",
        ]);

        return back()->with('success', 'Payroll marked as Paid successfully.');
    }
}
