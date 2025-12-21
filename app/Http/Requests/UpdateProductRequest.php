<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => [
                'sometimes',
                'max:255',
                'string',
                Rule::unique('products', 'name')->ignore($this->product)
            ],
            'quantity' => ['sometimes', 'integer', 'min:0'],
            'price' => ['sometimes', 'decimal:0,2', 'min:0'],
        ];
    }
}
