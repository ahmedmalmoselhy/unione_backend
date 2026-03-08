<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enrollment_id' => [
                'required',
                'exists:enrollments,id',
                Rule::unique('grades', 'enrollment_id'),
            ],
            'midterm'       => ['nullable', 'numeric', 'min:0', 'max:100'],
            'final'         => ['nullable', 'numeric', 'min:0', 'max:100'],
            'coursework'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'total'         => ['nullable', 'numeric', 'min:0', 'max:100'],
            'letter_grade'  => ['nullable', 'string', 'max:3'],
            'grade_points'  => ['nullable', 'numeric', 'min:0', 'max:4'],
        ];
    }

    public function messages(): array
    {
        return [
            'enrollment_id.unique' => 'A grade already exists for this enrollment.',
        ];
    }
}
