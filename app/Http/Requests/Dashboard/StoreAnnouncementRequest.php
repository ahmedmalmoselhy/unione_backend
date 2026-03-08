<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:255'],
            'body'         => ['required', 'string'],
            'type'         => ['required', 'in:general,academic,administrative,urgent'],
            'visibility'   => ['required', 'in:university,faculty,department,section'],
            'target_id'    => ['nullable', 'integer'],
            'published_at' => ['nullable', 'date'],
            'expires_at'   => ['nullable', 'date', 'after_or_equal:published_at'],
        ];
    }
}
