<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class StoreFacultyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'name_ar'          => ['required', 'string', 'max:255'],
            'code'             => ['required', 'string', 'max:20', 'unique:faculties,code'],
            'enrollment_type'  => ['required', 'in:immediate,none,deferred'],
            'dean_id'          => ['nullable', 'exists:users,id', function ($attribute, $value, $fail) {
                if ($value && !\App\Models\User::find($value)?->hasActiveRole('professor')) {
                    $fail('The selected dean must have an active professor role.');
                }
            }],
            'is_active'        => ['boolean'],
        ];
    }
}
