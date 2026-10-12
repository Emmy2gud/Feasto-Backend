<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CheckOutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        'billing_name' => 'nullable|string|max:255',
        'billing_email' => 'nullable|email|max:255',
        'billing_phone' => 'nullable|string|max:50',
        'billing_street' => 'required|string|max:255',

        'shipping_street' => 'nullable|string|max:255',
        ];
    }
}
