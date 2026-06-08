<?php

namespace App\Http\Requests\Api\Profile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'level' => 'nullable|string|in:beginner,intermediate,advanced',
            'goal' => 'nullable|string|max:1000',
        ];
    }
}
