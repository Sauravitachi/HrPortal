<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LeaveController extends Controller
{
    /**
     * Display leave requests list and balance cards.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        if ($user->isSuperAdmin() || $user->role === 'hr_manager') {
            // HR View: All requests in system
            $status = $request->input('status');
            $query = LeaveRequest::with(['employee.department', 'leaveType'])->latest();

            if ($status) {
                $query->where('status', $status);
            }

            $leaves = $query->paginate(10)->withQueryString();

            return view('leaves.index', compact('leaves', 'status'));
        }

        // Employee View: Personal leave balances and request history
        $employee = $user->employee;
        if (! $employee) {
            return view('leaves.index', ['noProfile' => true]);
        }

        $leaves = LeaveRequest::with('leaveType')
            ->where('employee_id', $employee->id)
            ->latest()
            ->paginate(10);

        // Calculate balances
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
                'id' => $type->id,
                'name' => $type->name,
                'max' => $type->max_days,
                'used' => $approvedDays,
                'remaining' => max(0, $type->max_days - $approvedDays),
            ];
        }

        return view('leaves.index', compact('leaves', 'leaveBalances'));
    }

    /**
     * Show form to apply for leave.
     */
    public function create(): View
    {
        $leaveTypes = LeaveType::all();

        return view('leaves.create', compact('leaveTypes'));
    }

    /**
     * Store new leave request.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (! $employee) {
            return redirect()->route('leaves.index')->with('error', 'Employee profile required.');
        }

        $validated = $request->validate([
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,png', 'max:2048'],
        ]);

        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);

        // Calculate leave duration
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $requestedDays = $startDate->diffInDays($endDate) + 1;

        // Verify remaining balance
        $currentYear = now()->year;
        $approvedDays = LeaveRequest::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'Approved')
            ->whereYear('start_date', $currentYear)
            ->get()
            ->sum(fn ($req) => $req->getDurationInDays());

        $remaining = max(0, $leaveType->max_days - $approvedDays);

        if ($requestedDays > $remaining) {
            return back()->withInput()->with('error', "Insufficient leave balance. You requested {$requestedDays} days, but only have {$remaining} days remaining of {$leaveType->name}.");
        }

        // Handle attachment
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('leave_attachments', 'public');
            $validated['attachment'] = $path;
        }

        $validated['employee_id'] = $employee->id;
        $validated['status'] = 'Pending';

        $req = LeaveRequest::create($validated);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Leave Requested',
            'description' => "Requested {$requestedDays} days of {$leaveType->name} starting {$validated['start_date']}.",
        ]);

        return redirect()->route('leaves.index')->with('success', 'Leave application submitted successfully.');
    }

    /**
     * Approve or reject a leave request.
     */
    public function update(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->isSuperAdmin() && $user->role !== 'hr_manager') {
            abort(403);
        }

        $request->validate([
            'status' => ['required', 'string', 'in:Approved,Rejected'],
        ]);

        $status = $request->input('status');
        $leave->status = $status;
        $leave->approved_by = $user->id;
        $leave->save();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => "Leave {$status}",
            'description' => "{$status} leave request #{$leave->id} for employee {$leave->employee->full_name}.",
        ]);

        return redirect()->route('leaves.index')->with('success', "Leave request was successfully {$status}.");
    }

    /**
     * Cancel / Delete a leave request.
     */
    public function destroy(LeaveRequest $leave): RedirectResponse
    {
        if ($leave->employee->user_id !== Auth::id() && ! Auth::user()->isHrManager()) {
            abort(403);
        }

        if ($leave->status !== 'Pending' && ! Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Only pending leave requests can be cancelled.');
        }

        $leave->delete();

        return redirect()->route('leaves.index')->with('success', 'Leave application cancelled successfully.');
    }
}
