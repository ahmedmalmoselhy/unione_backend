<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfessorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $professor = $this->route('professor');
        $userId = $professor->user_id;

        return [
            // User fields
            'national_id'     => ['required', 'string', 'max:30', Rule::unique('users', 'national_id')->ignore($userId)],
            'first_name'      => ['required', 'string', 'max:255'],
            'last_name'       => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password'        => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone'           => ['nullable', 'string', 'max:30'],
            'gender'          => ['required', 'in:male,female'],
            'date_of_birth'   => ['nullable', 'date'],

            // Professor fields
            'staff_number'    => ['required', 'string', 'max:50', Rule::unique('professors', 'staff_number')->ignore($professor->id)],
            'department_id'   => ['required', 'exists:departments,id', function ($attribute, $value, $fail) {
                if ($value && Department::where('id', $value)->where('type', 'academic')->doesntExist()) {
                    $fail('The selected department must be an academic department.');
                }
            }],
            'specialization'  => ['required', 'string', 'max:255'],
            'academic_rank'   => ['required', 'in:lecturer,assistant_professor,associate_professor,professor'],
            'office_location' => ['nullable', 'string', 'max:255'],
            'hired_at'        => ['required', 'date'],
            'is_active'       => ['boolean'],
            'avatar'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar'   => ['nullable', 'boolean'],
        ];
    }
}
