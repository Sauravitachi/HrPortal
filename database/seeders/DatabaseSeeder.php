<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\CandidateApplication;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\JobPost;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 0. Seed Roles and Permissions
        $this->call(RolesAndPermissionsSeeder::class);

        // 1. Create Core Departments
        $eng = Department::create(['name' => 'Engineering', 'description' => 'Product development, systems engineering, and technology support.']);
        $hrs = Department::create(['name' => 'Human Resources', 'description' => 'Talent acquisition, employee relations, payroll, and culture.']);
        $fin = Department::create(['name' => 'Finance', 'description' => 'Corporate accounts, taxation, budgeting, and audits.']);
        $sls = Department::create(['name' => 'Sales', 'description' => 'Business development, client relations, and marketing.']);

        // 2. Create Core Designations
        $leadEng = Designation::create(['department_id' => $eng->id, 'name' => 'Technical Lead', 'description' => 'Manages engineering sprint cycles and product architecture.']);
        $dev = Designation::create(['department_id' => $eng->id, 'name' => 'Software Engineer', 'description' => 'Builds, tests, and refactors application backends.']);
        $hrLead = Designation::create(['department_id' => $hrs->id, 'name' => 'HR Operations Lead', 'description' => 'Oversees onboarding, policies, and payroll disbursal.']);
        $recruiter = Designation::create(['department_id' => $hrs->id, 'name' => 'Talent Specialist', 'description' => 'Manages recruitment pipeline and candidate funnels.']);
        $analyst = Designation::create(['department_id' => $fin->id, 'name' => 'Senior Financial Analyst', 'description' => 'Prepares tax structures and monthly audits.']);

        // 3. Create Core Leave Types
        $cl = LeaveType::create(['name' => 'Casual Leave', 'max_days' => 12, 'description' => 'Planned personal leaves, events, or short breaks.']);
        $sl = LeaveType::create(['name' => 'Sick Leave', 'max_days' => 10, 'description' => 'Unplanned medical leaves for self or immediate family.']);
        $pl = LeaveType::create(['name' => 'Paid Leave', 'max_days' => 15, 'description' => 'Earned vacation leaves (requires advance approval).']);
        $ml = LeaveType::create(['name' => 'Maternity Leave', 'max_days' => 90, 'description' => 'Maternal rest period after delivery.']);
        $el = LeaveType::create(['name' => 'Emergency Leave', 'max_days' => 5, 'description' => 'Critical family emergencies or sudden events.']);

        // 4. Create Holidays
        Holiday::create(['name' => 'New Year Celebration', 'date' => now()->startOfYear()->toDateString(), 'type' => 'Public', 'description' => 'Welcome the new calendar year.']);
        Holiday::create(['name' => 'Independence Day', 'date' => Carbon::create(now()->year, 8, 15)->toDateString(), 'type' => 'Public', 'description' => 'National sovereignty day.']);
        Holiday::create(['name' => 'Festival of Lights (Diwali)', 'date' => Carbon::create(now()->year, 11, 4)->toDateString(), 'type' => 'Festival', 'description' => 'Official festival holiday.']);
        Holiday::create(['name' => 'Christmas Day', 'date' => Carbon::create(now()->year, 12, 25)->toDateString(), 'type' => 'Public', 'description' => 'Annual winter holiday.']);
        Holiday::create(['name' => 'Annual Strategy Day', 'date' => now()->addMonths(2)->toDateString(), 'type' => 'Company', 'description' => 'Corporate team planning session.']);

        // 5. Create Core Users & Employee Profiles

        // Super Admin User
        $superUser = User::create([
            'name' => 'Saurav Admin',
            'email' => 'super@example.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);
        $superUser->assignRole('super_admin');

        Employee::create([
            'user_id' => $superUser->id,
            'employee_id' => 'EMP-0000',
            'full_name' => 'Saurav Admin',
            'email' => 'super@example.com',
            'contact_number' => '+91 9999999999',
            'gender' => 'Male',
            'date_of_birth' => '1988-08-08',
            'address' => 'Mohali Corporate HQ',
            'emergency_contact' => 'Father',
            'blood_group' => 'A+',
            'joining_date' => '2020-01-01',
            'department_id' => $eng->id,
            'designation_id' => $leadEng->id,
            'employee_type' => 'Full-time',
            'work_location' => 'Onsite',
            'employment_status' => 'Active',
            'basic_salary' => 120000.00,
            'hra' => 45000.00,
        ]);

        // HR Manager User & Employee Profile
        $hrUser = User::create([
            'name' => 'Preeti Sharma',
            'email' => 'hr@example.com',
            'password' => Hash::make('password'),
            'role' => 'hr_manager',
        ]);
        $hrUser->assignRole('hr_manager');

        $hrEmployee = Employee::create([
            'user_id' => $hrUser->id,
            'employee_id' => 'EMP-0001',
            'full_name' => 'Preeti Sharma',
            'email' => 'hr@example.com',
            'contact_number' => '+91 9876543210',
            'gender' => 'Female',
            'date_of_birth' => '1990-04-15',
            'address' => 'Phase 3B2, Mohali, Punjab',
            'emergency_contact' => 'Father: +91 9876500000',
            'blood_group' => 'O+',
            'joining_date' => '2023-01-10',
            'department_id' => $hrs->id,
            'designation_id' => $hrLead->id,
            'employee_type' => 'Full-time',
            'work_location' => 'Onsite',
            'employment_status' => 'Active',
            'basic_salary' => 65000.00,
            'hra' => 25000.00,
        ]);

        // Standard Employee User & Profile
        $empUser = User::create([
            'name' => 'Karan Dev',
            'email' => 'employee@example.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
        ]);
        $empUser->assignRole('employee');

        $empEmployee = Employee::create([
            'user_id' => $empUser->id,
            'employee_id' => 'EMP-0002',
            'full_name' => 'Karan Dev',
            'email' => 'employee@example.com',
            'contact_number' => '+91 8876543210',
            'gender' => 'Male',
            'date_of_birth' => '1995-09-22',
            'address' => 'Sector 70, Mohali, Punjab',
            'emergency_contact' => 'Spouse: +91 8876500000',
            'blood_group' => 'A+',
            'joining_date' => '2024-05-15',
            'department_id' => $eng->id,
            'designation_id' => $dev->id,
            'reporting_manager_id' => $hrEmployee->id,
            'employee_type' => 'Full-time',
            'work_location' => 'Remote',
            'employment_status' => 'Active',
            'basic_salary' => 48000.00,
            'hra' => 18000.00,
        ]);

        // 6. Create Extra Employees to populate lists
        $emp3 = Employee::create([
            'employee_id' => 'EMP-0003',
            'full_name' => 'Amit Verma',
            'email' => 'amit@example.com',
            'contact_number' => '+91 7776543210',
            'gender' => 'Male',
            'date_of_birth' => '1993-11-05',
            'address' => 'Sector 15, Chandigarh',
            'emergency_contact' => 'Mother: +91 7776500000',
            'blood_group' => 'B+',
            'joining_date' => '2025-02-01',
            'department_id' => $eng->id,
            'designation_id' => $leadEng->id,
            'reporting_manager_id' => $hrEmployee->id,
            'employee_type' => 'Full-time',
            'work_location' => 'Hybrid',
            'employment_status' => 'Active',
            'basic_salary' => 85000.00,
            'hra' => 30000.00,
        ]);

        // 7. Seed Attendance Logs for this month
        $currentDate = now()->startOfMonth();
        $today = now();

        while ($currentDate->lte($today)) {
            if (! $currentDate->isWeekend()) {
                // Clock Preeti (HR)
                Attendance::create([
                    'employee_id' => $hrEmployee->id,
                    'date' => $currentDate->toDateString(),
                    'check_in' => '09:15:00',
                    'check_out' => '18:10:00',
                    'total_hours' => 8.92,
                    'status' => 'Present',
                ]);

                // Clock Karan (Employee)
                // WFH half of the time
                $isWfh = $currentDate->day % 2 === 0;
                Attendance::create([
                    'employee_id' => $empEmployee->id,
                    'date' => $currentDate->toDateString(),
                    'check_in' => $isWfh ? '09:00:00' : '09:45:00', // Late clock occasionally
                    'check_out' => '18:00:00',
                    'total_hours' => $isWfh ? 9.00 : 8.25,
                    'status' => $isWfh ? 'Work from home' : 'Present',
                ]);
            }
            $currentDate->addDay();
        }

        // 8. Seed Leave Applications
        LeaveRequest::create([
            'employee_id' => $empEmployee->id,
            'leave_type_id' => $cl->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(6)->toDateString(),
            'reason' => 'Family festival celebration at hometown.',
            'status' => 'Pending',
        ]);

        LeaveRequest::create([
            'employee_id' => $empEmployee->id,
            'leave_type_id' => $sl->id,
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->subDays(10)->toDateString(),
            'reason' => 'Severe seasonal flu and high temperature.',
            'status' => 'Approved',
            'approved_by' => $hrUser->id,
        ]);

        // 9. Seed Payroll history
        $pastMonth = now()->subMonth()->format('Y-m');
        $payroll = new Payroll([
            'employee_id' => $empEmployee->id,
            'salary_month' => $pastMonth,
            'basic_salary' => $empEmployee->basic_salary,
            'hra' => $empEmployee->hra,
            'pf' => 5760.00,
            'esi' => 840.00,
            'tax' => 2400.00,
            'incentives' => 1500.00,
            'bonuses' => 2000.00,
            'allowances' => 500.00,
            'status' => 'Paid',
            'processed_at' => now()->subDays(15),
        ]);
        $payroll->calculateSalary();
        $payroll->save();

        // 10. Seed Job posts
        $job1 = JobPost::create([
            'title' => 'Lead Laravel Developer',
            'department_id' => $eng->id,
            'experience_required' => '4-6 Years',
            'salary_range' => '₹10,00,000 - ₹15,00,000 P.A.',
            'description' => 'We are seeking a PHP/Laravel architect capable of building microservices, optimization, and database tuning.',
            'status' => 'Active',
        ]);

        // 11. Seed Job Applications
        CandidateApplication::create([
            'job_post_id' => $job1->id,
            'full_name' => 'Saurav Joshi',
            'email' => 'saurav@gmail.com',
            'contact_number' => '+91 9998887776',
            'resume_path' => 'resumes/saurav_resume.pdf',
            'status' => 'Applied',
        ]);

        CandidateApplication::create([
            'job_post_id' => $job1->id,
            'full_name' => 'Vikram Jit',
            'email' => 'vikram@yahoo.com',
            'contact_number' => '+91 8887776665',
            'resume_path' => 'resumes/vikram_resume.pdf',
            'status' => 'Shortlisted',
        ]);
    }
}
