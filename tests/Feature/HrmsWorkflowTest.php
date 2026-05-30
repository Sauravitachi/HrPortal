<?php

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow(Carbon::today()->setTime(9, 0, 0));

    // Seed essential roles, departments, designations
    $this->department = Department::create(['name' => 'Tech', 'description' => 'Tech Team']);
    $this->designation = Designation::create(['department_id' => $this->department->id, 'name' => 'Coder']);
    $this->leaveType = LeaveType::create(['name' => 'Sick Leave', 'max_days' => 10]);

    // Create Super Admin User
    $this->admin = User::create([
        'name' => 'Saurav Admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
        'role' => 'super_admin',
    ]);

    // Create Employee User
    $this->employeeUser = User::create([
        'name' => 'Karan Dev',
        'email' => 'employee@example.com',
        'password' => Hash::make('password'),
        'role' => 'employee',
    ]);

    // Create Employee Profile
    $this->employee = Employee::create([
        'user_id' => $this->employeeUser->id,
        'employee_id' => 'EMP-1111',
        'full_name' => 'Karan Dev',
        'email' => 'employee@example.com',
        'contact_number' => '+91 9999999999',
        'gender' => 'Male',
        'date_of_birth' => '1995-09-22',
        'address' => 'Chandigarh HQ',
        'emergency_contact' => 'Father',
        'blood_group' => 'B+',
        'joining_date' => '2024-05-15',
        'department_id' => $this->department->id,
        'designation_id' => $this->designation->id,
        'employee_type' => 'Full-time',
        'work_location' => 'Onsite',
        'basic_salary' => 50000.00,
        'hra' => 20000.00,
    ]);
});

afterEach(function () {
    Carbon::setTestNow(null);
});

test('unauthenticated users are redirected to login', function () {
    $this->get('/')
        ->assertRedirect('/login');
});

test('employees cannot access restricted admin routes', function () {
    $this->actingAs($this->employeeUser)
        ->get('/employees')
        ->assertForbidden();

    $this->actingAs($this->employeeUser)
        ->get('/payroll')
        ->assertForbidden();
});

test('admin can access employees management roster', function () {
    $this->actingAs($this->admin)
        ->get('/employees')
        ->assertSuccessful();
});

test('employees can clock check-in shift logs cleanly', function () {
    $this->actingAs($this->employeeUser)
        ->post('/attendance/check-in', ['wfh' => 0])
        ->assertRedirect();

    $this->assertDatabaseHas('attendance', [
        'employee_id' => $this->employee->id,
        'date' => now()->toDateString().' 00:00:00',
        'status' => 'Present',
    ]);
});

test('employees can clock in and out multiple times in a single day', function () {
    $this->actingAs($this->employeeUser);

    // 1st Check-In
    $this->post('/attendance/check-in', ['wfh' => 0])
        ->assertRedirect();

    // 1st Check-Out
    $this->post('/attendance/check-out')
        ->assertRedirect();

    // 2nd Check-In
    $this->post('/attendance/check-in', ['wfh' => 1])
        ->assertRedirect();

    // 2nd Check-Out
    $this->post('/attendance/check-out')
        ->assertRedirect();

    $attendances = Attendance::where('employee_id', $this->employee->id)->get();
    expect($attendances)->toHaveCount(2);

    expect($attendances[0]->status)->toEqual('Half day');
    expect($attendances[1]->status)->toEqual('Work from home');
});

test('employees can submit leave requests', function () {
    $this->actingAs($this->employeeUser)
        ->post('/leaves', [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(6)->toDateString(),
            'reason' => 'Annual checkup.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('leave_requests', [
        'employee_id' => $this->employee->id,
        'leave_type_id' => $this->leaveType->id,
        'status' => 'Pending',
    ]);
});

test('payroll arithmetic computes net salary accurately', function () {
    $payroll = new Payroll([
        'employee_id' => $this->employee->id,
        'salary_month' => '2026-05',
        'basic_salary' => 50000.00,
        'hra' => 20000.00,
        'incentives' => 1500.00,
        'bonuses' => 2000.00,
        'allowances' => 500.00,
        'pf' => 6000.00,
        'esi' => 875.00,
        'tax' => 2500.00,
        'loan_deductions' => 0.00,
        'other_deductions' => 0.00,
    ]);

    $payroll->calculateSalary();

    // Gross = Basic (50000) + HRA (20000) + Incentives (1500) + Bonuses (2000) + Allowances (500) = 74000
    expect($payroll->gross_salary)->toEqual(74000.00);

    // Deductions = PF (6000) + ESI (875) + Tax (2500) = 9375
    expect($payroll->total_deductions)->toEqual(9375.00);

    // Net = Gross (74000) - Deductions (9375) = 64625
    expect($payroll->net_salary)->toEqual(64625.00);
});

test('employees checking in after 10:15 are marked as Late', function () {
    Carbon::setTestNow(Carbon::today()->setTime(10, 30, 0));

    $this->actingAs($this->employeeUser)
        ->post('/attendance/check-in', ['wfh' => 0])
        ->assertRedirect();

    $this->assertDatabaseHas('attendance', [
        'employee_id' => $this->employee->id,
        'date' => Carbon::today()->toDateString().' 00:00:00',
        'status' => 'Late',
    ]);
});

test('employees checking in after 11:45 are marked as Half day', function () {
    Carbon::setTestNow(Carbon::today()->setTime(12, 00, 0));

    $this->actingAs($this->employeeUser)
        ->post('/attendance/check-in', ['wfh' => 0])
        ->assertRedirect();

    $this->assertDatabaseHas('attendance', [
        'employee_id' => $this->employee->id,
        'date' => Carbon::today()->toDateString().' 00:00:00',
        'status' => 'Half day',
    ]);
});

test('employees checking in WFH after 10:15 or 11:45 are still marked as Work from home', function () {
    Carbon::setTestNow(Carbon::today()->setTime(12, 15, 0));

    $this->actingAs($this->employeeUser)
        ->post('/attendance/check-in', ['wfh' => 1])
        ->assertRedirect();

    $this->assertDatabaseHas('attendance', [
        'employee_id' => $this->employee->id,
        'date' => Carbon::today()->toDateString().' 00:00:00',
        'status' => 'Work from home',
    ]);
});
