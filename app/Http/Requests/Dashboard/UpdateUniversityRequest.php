<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUniversityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:255'],
            'name_ar'        => ['required', 'string', 'max:255'],
            'address'        => ['required', 'string', 'max:500'],
            'established_at' => ['nullable', 'date'],
            'president_id'   => ['nullable', 'exists:professors,id'],
            'logo'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'remove_logo'    => ['nullable', 'boolean'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'email'          => ['nullable', 'email', 'max:255'],
            'website'        => ['nullable', 'url', 'max:255'],
        ];
    }
}
