<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'password' => ['sometimes', 'confirmed', Rules\Password::defaults()],
            'avatar' => ['nullable', 'string', 'max:255'],
            'address' => ['sometimes', 'string', 'max:255'],
            'phone_number' => ['sometimes', 'digits:10'],
            'is_marketing' => ['nullable']
        ];
    }
}
