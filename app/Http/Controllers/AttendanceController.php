<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Display attendance history.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $date = $request->input('date', now()->toDateString());

        if ($user->isHrManager()) {
            // HR / Admin View: Daily Attendance Roster
            $query = Attendance::with('employee.department')->where('date', $date);

            if ($deptId = $request->input('department_id')) {
                $query->whereHas('employee', function ($q) use ($deptId) {
                    $q->where('department_id', $deptId);
                });
            }

            $attendances = $query->get();
            $departments = Department::all();

            // Stats today
            $totalEmployees = Employee::where('employment_status', 'Active')->count();
            $presentCount = Attendance::where('date', $date)->whereIn('status', ['Present', 'Half day', 'Work from home', 'Late'])->count();
            $absentCount = $totalEmployees - $presentCount;

            return view('attendance.index', compact('attendances', 'departments', 'date', 'presentCount', 'absentCount', 'totalEmployees'));
        }

        // Employee View: Personal Monthly Attendance Log
        $employee = $user->employee;
        if (! $employee) {
            return view('attendance.index', ['noProfile' => true]);
        }

        $month = $request->input('month', now()->format('Y-m'));
        $startOfMonth = Carbon::parse($month.'-01')->startOfMonth();
        $endOfMonth = Carbon::parse($month.'-01')->endOfMonth();

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->groupBy(fn ($item) => $item->date->toDateString());

        // Generate full calendar days
        $calendarDays = [];
        $tempDate = $startOfMonth->copy();
        while ($tempDate->lte($endOfMonth)) {
            $dateString = $tempDate->toDateString();
            $dayAtts = $attendances->get($dateString, collect());

            $status = 'Absent';
            if ($tempDate->isWeekend()) {
                $status = 'Holiday';
            }

            if ($dayAtts->isNotEmpty()) {
                $hasWfh = $dayAtts->contains('status', 'Work from home');
                $hasPresent = $dayAtts->contains('status', 'Present');
                $hasHalfDay = $dayAtts->contains('status', 'Half day');
                $hasLate = $dayAtts->contains('status', 'Late');

                if ($hasWfh) {
                    $status = 'Work from home';
                } elseif ($hasPresent) {
                    $status = 'Present';
                } elseif ($hasLate) {
                    $status = 'Late';
                } elseif ($hasHalfDay) {
                    $status = 'Half day';
                } else {
                    $status = $dayAtts->first()->status;
                }
            }

            $calendarDays[] = [
                'date' => $tempDate->copy(),
                'attendances' => $dayAtts,
                'status' => $status,
            ];
            $tempDate->addDay();
        }

        // Today's/Active Check-in Log (represents the currently active open shift)
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereNull('check_out')
            ->latest()
            ->first();

        return view('attendance.index', compact('calendarDays', 'month', 'todayAttendance'));
    }

    /**
     * Perform daily check-in.
     */
    public function checkIn(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $this->ensureEmployeeProfileExists($user);
        $employee = $user->employee;

        if (! $employee) {
            return back()->with('error', 'No employee profile associated with your user.');
        }

        // Find active open check-in
        $active = Attendance::where('employee_id', $employee->id)
            ->whereNull('check_out')
            ->first();

        if ($active) {
            return back()->with('error', 'You have already checked in.');
        }

        $today = now()->toDateString();
        $currentTime = now();
        $checkInTime = $currentTime->toTimeString();

        if ($request->boolean('wfh')) {
            $status = 'Work from home';
        } else {
            $time1015 = Carbon::today()->setTime(10, 15);
            $time1145 = Carbon::today()->setTime(11, 45);

            if ($currentTime->greaterThan($time1145)) {
                $status = 'Half day';
            } elseif ($currentTime->greaterThan($time1015)) {
                $status = 'Late';
            } else {
                $status = 'Present';
            }
        }

        Attendance::create([
            'employee_id' => $employee->id,
            'date' => $today,
            'check_in' => $checkInTime,
            'status' => $status,
        ]);

        return back()->with('success', 'Checked in successfully at '.now()->format('H:i A').'.');
    }

    /**
     * Perform daily check-out.
     */
    public function checkOut(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $this->ensureEmployeeProfileExists($user);
        $employee = $user->employee;

        if (! $employee) {
            return back()->with('error', 'No employee profile associated.');
        }

        // Find latest open shift (even if checked in yesterday!)
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereNull('check_out')
            ->latest()
            ->first();

        if (! $attendance) {
            return back()->with('error', 'You have not checked in yet.');
        }

        $checkOutTime = now()->toTimeString();
        $attendance->check_out = $checkOutTime;

        // Calculate hours accurately, handling shifts crossing midnight!
        $checkIn = Carbon::parse($attendance->date->toDateString().' '.$attendance->check_in);
        $checkOut = now();
        $totalHours = round($checkIn->diffInMinutes($checkOut) / 60, 2);

        $attendance->total_hours = $totalHours;

        // Auto half-day marking if worked less than 4 hours
        if ($totalHours < 4 && $attendance->status !== 'Work from home') {
            $attendance->status = 'Half day';
        }

        $attendance->save();

        return back()->with('success', 'Checked out successfully at '.now()->format('H:i A').'. Total working hours: '.$totalHours.' hrs.');
    }
}
