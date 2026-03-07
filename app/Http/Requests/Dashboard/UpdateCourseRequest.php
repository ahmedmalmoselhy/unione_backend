<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'           => ['required', 'string', 'max:20', Rule::unique('courses', 'code')->ignore($this->route('course'))],
            'name'           => ['required', 'string', 'max:255'],
            'name_ar'        => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:2000'],
            'credit_hours'   => ['required', 'integer', 'min:1', 'max:12'],
            'lecture_hours'  => ['required', 'integer', 'min:0', 'max:12'],
            'lab_hours'      => ['required', 'integer', 'min:0', 'max:12'],
            'level'          => ['required', 'integer', 'min:1', 'max:5'],
            'is_elective'    => ['nullable', 'boolean'],
            'is_active'      => ['nullable', 'boolean'],

            // Pivot: departments
            'departments'              => ['required', 'array', 'min:1'],
            'departments.*.id'         => ['required', 'exists:departments,id'],
            'departments.*.is_owner'   => ['nullable', 'boolean'],

            // Prerequisites (cannot include self — checked in controller)
            'prerequisites'   => ['nullable', 'array'],
            'prerequisites.*' => ['exists:courses,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'departments.required' => 'At least one department must be assigned.',
            'departments.min'      => 'At least one department must be assigned.',
        ];
    }
}
