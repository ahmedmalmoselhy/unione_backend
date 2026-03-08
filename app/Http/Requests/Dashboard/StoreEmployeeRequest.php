<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Department;
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
            // User fields
            'national_id'      => ['required', 'string', 'max:30', 'unique:users,national_id'],
            'first_name'       => ['required', 'string', 'max:255'],
            'last_name'        => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
            'phone'            => ['nullable', 'string', 'max:30'],
            'gender'           => ['required', 'in:male,female'],
            'date_of_birth'    => ['nullable', 'date'],

            // Employee fields
            'staff_number'     => ['required', 'string', 'max:50', 'unique:employees,staff_number'],
            'department_id'    => ['required', 'exists:departments,id', function ($attribute, $value, $fail) {
                if ($value && Department::where('id', $value)->where('type', 'managerial')->doesntExist()) {
                    $fail('The selected department must be a managerial department.');
                }
            }],
            'job_title'        => ['required', 'string', 'max:255'],
            'employment_type'  => ['required', 'in:full_time,part_time,contract'],
            'salary'           => ['nullable', 'numeric', 'min:0'],
            'hired_at'         => ['required', 'date'],
            'avatar'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
