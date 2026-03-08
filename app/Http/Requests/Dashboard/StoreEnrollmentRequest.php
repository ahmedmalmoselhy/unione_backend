<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id'       => ['required', 'exists:students,id'],
            'section_id'       => [
                'required',
                'exists:sections,id',
                Rule::unique('enrollments')->where(function ($query) {
                    return $query->where('student_id', $this->student_id);
                }),
            ],
            'academic_term_id' => ['required', 'exists:academic_terms,id'],
            'status'           => ['required', 'in:registered,dropped,completed,failed,incomplete'],
            'registered_at'    => ['required', 'date'],
            'dropped_at'       => ['nullable', 'date', 'after_or_equal:registered_at'],
        ];
    }

    public function messages(): array
    {
        return [
            'section_id.unique' => 'This student is already enrolled in this section.',
        ];
    }
}
