<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('agent');

        return [
            'user_id' => 'required|exists:users,id|unique:agents,user_id,' . $id,
            'agency_name' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:255|unique:agents,license_number,' . $id,
            'bio' => 'nullable|string',
            'website' => 'nullable|url|max:255',
        ];
    }
}
