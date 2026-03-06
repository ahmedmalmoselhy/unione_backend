<?php

namespace App\Http\Requests\Dashboard;

use App\Models\UniversityVicePresident;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVicePresidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $vp = $this->route('vice_president');

        return [
            'professor_id' => [
                'required',
                'exists:professors,id',
                function ($attribute, $value, $fail) use ($vp) {
                    if (UniversityVicePresident::where('professor_id', $value)
                        ->where('id', '!=', $vp->id)
                        ->exists()
                    ) {
                        $fail('This professor is already assigned as a vice president.');
                    }
                },
            ],
            'title'        => ['required', 'string', 'max:255'],
            'title_ar'     => ['required', 'string', 'max:255'],
            'order'        => ['required', 'integer', 'min:0'],
            'is_active'    => ['boolean'],
            'appointed_at' => ['required', 'date'],
            'ended_at'     => ['nullable', 'date', 'after_or_equal:appointed_at'],
        ];
    }
}
