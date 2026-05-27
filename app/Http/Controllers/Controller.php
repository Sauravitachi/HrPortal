<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;

abstract class Controller
{
    /**
     * Ensure the authenticated user has an employee profile on-the-fly.
     */
    protected function ensureEmployeeProfileExists($user): void
    {
        if ($user && !$user->employee) {
            $dept = Department::first();
            $desig = Designation::first();

            Employee::create([
                'user_id' => $user->id,
                'employee_id' => 'EMP-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'full_name' => $user->name,
                'email' => $user->email,
                'contact_number' => '+91 9999999999',
                'gender' => 'Male',
                'date_of_birth' => '1990-01-01',
                'address' => 'Company HQ, Mohali',
                'emergency_contact' => 'Father',
                'blood_group' => 'O+',
                'joining_date' => now()->toDateString(),
                'department_id' => $dept ? $dept->id : 1,
                'designation_id' => $desig ? $desig->id : 1,
                'employee_type' => 'Full-time',
                'work_location' => 'Onsite',
                'employment_status' => 'Active',
                'basic_salary' => 50000.00,
                'hra' => 20000.00,
            ]);

            $user->load('employee');
        }
    }
}
