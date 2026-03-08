<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employee = $this->route('employee');
        $userId = $employee->user_id;

        return [
            // User fields
            'national_id'      => ['required', 'string', 'max:30', Rule::unique('users', 'national_id')->ignore($userId)],
            'first_name'       => ['required', 'string', 'max:255'],
            'last_name'        => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password'         => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone'            => ['nullable', 'string', 'max:30'],
            'gender'           => ['required', 'in:male,female'],
            'date_of_birth'    => ['nullable', 'date'],

            // Employee fields
            'staff_number'     => ['required', 'string', 'max:50', Rule::unique('employees', 'staff_number')->ignore($employee->id)],
            'department_id'    => ['required', 'exists:departments,id', function ($attribute, $value, $fail) {
                if ($value && Department::where('id', $value)->where('type', 'managerial')->doesntExist()) {
                    $fail('The selected department must be a managerial department.');
                }
            }],
            'job_title'        => ['required', 'string', 'max:255'],
            'employment_type'  => ['required', 'in:full_time,part_time,contract'],
            'salary'           => ['nullable', 'numeric', 'min:0'],
            'hired_at'         => ['required', 'date'],
            'terminated_at'    => ['nullable', 'date', 'after_or_equal:hired_at'],
            'is_active'        => ['boolean'],
            'avatar'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar'    => ['nullable', 'boolean'],
        ];
    }
}
