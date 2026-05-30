<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\RecruitmentController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

// Authentication
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Public Careers Portal (Scoped per resolved tenant subdomain)
Route::get('careers', [RecruitmentController::class, 'careersPortal'])->name('careers.index');
Route::get('careers/{job}', [RecruitmentController::class, 'careersJob'])->name('careers.show');
Route::post('careers/{job}/apply', [RecruitmentController::class, 'apply'])->name('careers.apply');

// Core Dashboard & Modules (Restricted to logged-in users and locked to resolved tenant boundaries)
Route::middleware(['auth', 'tenant.security'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Attendance Log & Check-in Check-out
    Route::post('attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');

    // Leave requests
    Route::resource('leaves', LeaveController::class)->parameters([
        'leaves' => 'leave',
    ]);

    // HR and Super Admin Restricted Modules
    Route::middleware('role:super_admin,hr_manager')->group(function () {

        // Employee Management
        Route::get('employees/export', [EmployeeController::class, 'export'])->name('employees.export');
        Route::resource('employees', EmployeeController::class);
        Route::post('employees/{employee}/documents', [EmployeeController::class, 'uploadDocument'])->name('employees.documents.upload');
        Route::delete('documents/{document}', [EmployeeController::class, 'deleteDocument'])->name('documents.destroy');

        // Payroll Operations
        Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
        Route::post('payroll/generate', [PayrollController::class, 'generate'])->name('payroll.generate');
        Route::get('payroll/{payroll}', [PayrollController::class, 'show'])->name('payroll.show');
        Route::post('payroll/{payroll}/pay', [PayrollController::class, 'pay'])->name('payroll.pay');

        // Recruitment Board & Applicant status pipelines
        Route::resource('jobs', RecruitmentController::class);
        Route::post('jobs/{job}/apply', [RecruitmentController::class, 'apply'])->name('jobs.apply');
        Route::post('applications/{application}/status', [RecruitmentController::class, 'updateStatus'])->name('applications.status');
        Route::post('applications/{application}/interview', [RecruitmentController::class, 'scheduleInterview'])->name('applications.interview');
    });

    // Super Admin Restricted Tenancy & Platform Management Modules
    Route::middleware('role:super_admin')->group(function () {
        Route::get('admin/tenants', [TenantController::class, 'index'])->name('admin.tenants.index');
        Route::post('admin/tenants', [TenantController::class, 'store'])->name('admin.tenants.store');
        Route::post('admin/tenants/{tenant}/plan', [TenantController::class, 'updatePlan'])->name('admin.tenants.plan');
        Route::delete('admin/tenants/{tenant}', [TenantController::class, 'destroy'])->name('admin.tenants.destroy');
    });
});
