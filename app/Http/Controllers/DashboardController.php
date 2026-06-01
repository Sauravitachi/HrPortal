<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\JobPost;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dynamic dashboard.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $this->ensureEmployeeProfileExists($user);

        if ($user->isHrManager()) {
            return $this->adminDashboard();
        }

        return $this->employeeDashboard($user);
    }

    /**
     * Load metrics and view for HR/Super Admin.
     */
    protected function adminDashboard(): View
    {
        $totalEmployees = Employee::where('employment_status', 'Active')->count();

        $presentToday = Attendance::where('date', now()->toDateString())
            ->whereIn('status', ['Present', 'Half day', 'Work from home', 'Late'])
            ->count();

        $pendingLeaves = LeaveRequest::where('status', 'Pending')->count();
        $activeJobs = JobPost::where('status', 'Active')->count();

        $currentMonth = now()->format('Y-m');
        $payrollSpent = Payroll::where('salary_month', $currentMonth)
            ->where('status', 'Paid')
            ->sum('net_salary');

        // Recent activity lists
        $recentLeaves = LeaveRequest::with(['employee', 'leaveType'])
            ->latest()
            ->take(5)
            ->get();

        $recentEmployees = Employee::with(['department', 'designation'])
            ->latest()
            ->take(5)
            ->get();

        $employee = Auth::user()->employee;
        $todayAttendance = $employee ? Attendance::where('employee_id', $employee->id)
            ->whereNull('check_out')
            ->latest()
            ->first() : null;

        return view('dashboard', compact(
            'totalEmployees',
            'presentToday',
            'pendingLeaves',
            'activeJobs',
            'payrollSpent',
            'recentLeaves',
            'recentEmployees',
            'todayAttendance'
        ));
    }

    /**
     * Load metrics and view for individual Employees.
     */
    protected function employeeDashboard($user): View
    {
        $employee = $user->employee;

        if (! $employee) {
            return view('dashboard', [
                'noProfile' => true,
                'totalEmployees' => 0,
                'presentToday' => 0,
                'pendingLeaves' => 0,
                'activeJobs' => 0,
                'payrollSpent' => 0,
            ]);
        }

        // Today's Check-in Log
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereNull('check_out')
            ->latest()
            ->first();

        // Leave Balance Tracking
        $leaveTypes = LeaveType::all();
        $leaveBalances = [];
        $currentYear = now()->year;

        foreach ($leaveTypes as $type) {
            $approvedDays = LeaveRequest::where('employee_id', $employee->id)
                ->where('leave_type_id', $type->id)
                ->where('status', 'Approved')
                ->whereYear('start_date', $currentYear)
                ->get()
                ->sum(fn ($req) => $req->getDurationInDays());

            $leaveBalances[] = [
                'name' => $type->name,
                'max' => $type->max_days,
                'used' => $approvedDays,
                'remaining' => max(0, $type->max_days - $approvedDays),
            ];
        }

        // Attendance Percentage (Last 30 Days)
        $totalDays = 30;
        $presentDays = Attendance::where('employee_id', $employee->id)
            ->where('date', '>=', now()->subDays($totalDays)->toDateString())
            ->whereIn('status', ['Present', 'Half day', 'Work from home', 'Late'])
            ->count();
        $attendancePercentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 100;

        // Upcoming Holidays
        $upcomingHolidays = Holiday::where('date', '>=', now()->toDateString())
            ->orderBy('date', 'asc')
            ->take(5)
            ->get();

        // Recent Salary Slips
        $recentPayrolls = Payroll::where('employee_id', $employee->id)
            ->where('status', 'Paid')
            ->orderBy('salary_month', 'desc')
            ->take(3)
            ->get();

        return view('dashboard', compact(
            'employee',
            'todayAttendance',
            'leaveBalances',
            'attendancePercentage',
            'upcomingHolidays',
            'recentPayrolls'
        ));
    }
}
