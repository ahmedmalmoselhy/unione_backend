<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id'        => ['required', 'exists:courses,id'],
            'professor_id'     => ['required', 'exists:professors,id'],
            'academic_term_id' => ['required', 'exists:academic_terms,id'],
            'capacity'         => ['required', 'integer', 'min:1', 'max:999'],
            'room'             => ['nullable', 'string', 'max:100'],
            'schedule'         => ['nullable', 'array'],
            'schedule.*.day'        => ['required_with:schedule', 'in:sunday,monday,tuesday,wednesday,thursday,friday,saturday'],
            'schedule.*.start_time' => ['required_with:schedule', 'date_format:H:i'],
            'schedule.*.end_time'   => ['required_with:schedule', 'date_format:H:i', 'after:schedule.*.start_time'],
            'schedule.*.type'       => ['required_with:schedule', 'in:lecture,lab'],
            'is_active'        => ['nullable', 'boolean'],
        ];
    }
}
