<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademicTermRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                     => ['required', 'string', 'max:255'],
            'name_ar'                  => ['required', 'string', 'max:255'],
            'academic_year'            => ['required', 'integer', 'min:2000', 'max:2099'],
            'semester'                 => ['required', 'in:first,second,summer'],
            'starts_at'               => ['required', 'date'],
            'ends_at'                 => ['required', 'date', 'after:starts_at'],
            'registration_starts_at'  => ['required', 'date'],
            'registration_ends_at'    => ['required', 'date', 'after:registration_starts_at'],
            'withdrawal_deadline'     => ['nullable', 'date'],
            'exam_starts_at'          => ['nullable', 'date'],
            'exam_ends_at'            => ['nullable', 'date', 'after:exam_starts_at'],
            'grade_submission_deadline' => ['nullable', 'date'],
            'is_active'               => ['nullable', 'boolean'],
        ];
    }
}
