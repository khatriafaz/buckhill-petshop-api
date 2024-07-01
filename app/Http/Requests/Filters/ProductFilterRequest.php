<?php

namespace App\Http\Requests\Filters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductFilterRequest extends FormRequest
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
        return [
            'sort_by.field' => ['required_with:sort_by', Rule::in(['id', 'title', 'price'])],
            'sort_by.direction' => ['required_with:sort_by', Rule::in(['asc', 'desc'])],
        ];
    }
}
