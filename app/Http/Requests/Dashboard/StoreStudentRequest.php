<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // User fields
            'national_id'       => ['required', 'string', 'max:30', 'unique:users,national_id'],
            'first_name'        => ['required', 'string', 'max:255'],
            'last_name'         => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'          => ['required', 'string', 'min:8', 'confirmed'],
            'phone'             => ['nullable', 'string', 'max:30'],
            'gender'            => ['required', 'in:male,female'],
            'date_of_birth'     => ['nullable', 'date'],

            // Student fields
            'student_number'    => ['required', 'string', 'max:50', 'unique:students,student_number'],
            'faculty_id'       => ['required', 'exists:faculties,id'],
            'department_id'    => ['nullable', 'exists:departments,id'],
            'academic_year'    => ['required', 'integer', 'min:1', 'max:7'],
            'semester'         => ['required', 'in:first,second,summer'],
            'enrollment_status'=> ['required', 'in:active,suspended,graduated,withdrawn'],
            'gpa'              => ['nullable', 'numeric', 'min:0', 'max:4'],
            'enrolled_at'      => ['required', 'date'],
            'graduated_at'     => ['nullable', 'date', 'after_or_equal:enrolled_at'],
            'avatar'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
