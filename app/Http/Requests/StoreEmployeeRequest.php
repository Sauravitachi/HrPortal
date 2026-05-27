<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'string', 'unique:employees,employee_id'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:employees,email'],
            'contact_number' => ['required', 'string', 'max:20'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'gender' => ['required', 'string'],
            'date_of_birth' => ['required', 'date'],
            'address' => ['required', 'string'],
            'emergency_contact' => ['required', 'string'],
            'blood_group' => ['required', 'string'],
            'joining_date' => ['required', 'date'],
            'department_id' => ['required', 'exists:departments,id'],
            'designation_id' => ['required', 'exists:designations,id'],
            'reporting_manager_id' => ['nullable', 'exists:employees,id'],
            'employee_type' => ['required', 'string'],
            'work_location' => ['required', 'string'],
            'employment_status' => ['required', 'string'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'hra' => ['required', 'numeric', 'min:0'],
            
            // Optional user account fields
            'create_user_account' => ['nullable', 'boolean'],
            'role' => ['nullable', 'string', 'in:super_admin,hr_manager,employee'],
            'password' => ['nullable', 'string', 'min:8', 'required_if:create_user_account,1'],
        ];
    }
}
