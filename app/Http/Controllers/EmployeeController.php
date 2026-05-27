<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the employees with search and filters.
     */
    public function index(Request $request): View
    {
        $query = Employee::with(['department', 'designation', 'reportingManager']);

        // Search Filter
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Department Filter
        if ($deptId = $request->input('department_id')) {
            $query->where('department_id', $deptId);
        }

        // Designation Filter
        if ($desigId = $request->input('designation_id')) {
            $query->where('designation_id', $desigId);
        }

        // Status Filter
        if ($status = $request->input('status')) {
            $query->where('employment_status', $status);
        }

        $employees = $query->paginate(10)->withQueryString();
        $departments = Department::all();
        $designations = Designation::all();

        return view('employees.index', compact('employees', 'departments', 'designations'));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create(): View
    {
        $departments = Department::all();
        $designations = Designation::all();
        $managers = Employee::where('employment_status', 'Active')->get();

        return view('employees.create', compact('departments', 'designations', 'managers'));
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('profile_images', 'public');
            $validated['profile_image'] = $imagePath;
        }

        // Create User Account if requested
        if ($request->boolean('create_user_account')) {
            $user = User::create([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'] ?? 'employee',
            ]);
            $validated['user_id'] = $user->id;
        }

        $employee = Employee::create($validated);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Employee Created',
            'description' => "Created employee profile for {$employee->full_name} ({$employee->employee_id})."
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee profile created successfully.');
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee): View
    {
        $employee->load(['department', 'designation', 'reportingManager', 'documents', 'attendance' => function($q) {
            $q->latest()->take(10);
        }, 'leaveRequests' => function($q) {
            $q->latest()->take(10);
        }, 'payrolls' => function($q) {
            $q->latest()->take(10);
        }]);

        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Employee $employee): View
    {
        $departments = Department::all();
        $designations = Designation::all();
        $managers = Employee::where('employment_status', 'Active')->where('id', '!=', $employee->id)->get();

        return view('employees.edit', compact('employee', 'departments', 'designations', 'managers'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validated();

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            if ($employee->profile_image) {
                Storage::disk('public')->delete($employee->profile_image);
            }
            $imagePath = $request->file('profile_image')->store('profile_images', 'public');
            $validated['profile_image'] = $imagePath;
        }

        $employee->update($validated);

        // Update password if specified and user account exists
        if (!empty($validated['password']) && $employee->user) {
            $employee->user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Employee Updated',
            'description' => "Updated employee profile for {$employee->full_name} ({$employee->employee_id})."
        ]);

        return redirect()->route('employees.show', $employee)->with('success', 'Employee profile updated successfully.');
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(Employee $employee): RedirectResponse
    {
        // Delete profile image
        if ($employee->profile_image) {
            Storage::disk('public')->delete($employee->profile_image);
        }

        // Delete documents
        foreach ($employee->documents as $doc) {
            Storage::disk('public')->delete($doc->file_path);
            $doc->delete();
        }

        // Delete user account if it exists
        if ($employee->user) {
            $employee->user->delete();
        }

        $employeeName = $employee->full_name;
        $employeeId = $employee->employee_id;
        $employee->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Employee Deleted',
            'description' => "Deleted employee profile for {$employeeName} ({$employeeId})."
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee profile deleted successfully.');
    }

    /**
     * Upload an employee document.
     */
    public function uploadDocument(Request $request, Employee $employee): RedirectResponse
    {
        $request->validate([
            'document_type' => ['required', 'string'],
            'document_name' => ['required', 'string', 'max:255'],
            'document_file' => ['required', 'file', 'mimes:pdf,jpg,png,docx', 'max:5120'],
        ]);

        $filePath = $request->file('document_file')->store('employee_documents', 'public');

        EmployeeDocument::create([
            'employee_id' => $employee->id,
            'document_type' => $request->input('document_type'),
            'document_name' => $request->input('document_name'),
            'file_path' => $filePath,
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Document Uploaded',
            'description' => "Uploaded {$request->input('document_type')} for {$employee->full_name}."
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    /**
     * Delete an employee document.
     */
    public function deleteDocument(EmployeeDocument $document): RedirectResponse
    {
        $employee = $document->employee;
        
        Storage::disk('public')->delete($document->file_path);
        
        $docType = $document->document_type;
        $document->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Document Deleted',
            'description' => "Deleted document {$docType} for {$employee->full_name}."
        ]);

        return back()->with('success', 'Document deleted successfully.');
    }

    /**
     * Export all employee records to CSV.
     */
    public function export(): StreamedResponse
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=employees_export_' . now()->format('YmdHis') . '.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'Employee ID', 'Full Name', 'Email', 'Contact', 'Gender', 'DOB',
            'Department', 'Designation', 'Joining Date', 'Type', 'Location', 'Status', 'Basic Salary', 'HRA'
        ];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $employees = Employee::with(['department', 'designation'])->get();

            foreach ($employees as $emp) {
                fputcsv($file, [
                    $emp->employee_id,
                    $emp->full_name,
                    $emp->email,
                    $emp->contact_number,
                    $emp->gender,
                    $emp->date_of_birth->toDateString(),
                    $emp->department->name,
                    $emp->designation->name,
                    $emp->joining_date->toDateString(),
                    $emp->employee_type,
                    $emp->work_location,
                    $emp->employment_status,
                    $emp->basic_salary,
                    $emp->hra
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
