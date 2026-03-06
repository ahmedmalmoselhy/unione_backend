<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'faculty_id'     => ['required', 'exists:faculties,id'],
            'name'           => ['required', 'string', 'max:255'],
            'name_ar'        => ['required', 'string', 'max:255'],
            'code'           => ['required', 'string', 'max:20', 'unique:departments,code'],
            'type'           => ['required', 'in:academic,managerial'],
            'is_preparatory' => ['boolean'],
            'head_id'        => ['nullable', 'exists:users,id', function ($attribute, $value, $fail) {
                if (! $value) {
                    return;
                }
                $type = $this->input('type');
                $user = \App\Models\User::find($value);
                if ($type === 'academic' && ! $user?->hasActiveRole('professor')) {
                    $fail('The head of an academic department must have an active professor role.');
                }
                if ($type === 'managerial' && ! $user?->hasActiveRole('employee')) {
                    $fail('The head of a managerial department must have an active employee role.');
                }
            }],
            'is_active'      => ['boolean'],
        ];
    }
}
