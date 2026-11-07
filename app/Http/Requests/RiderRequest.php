<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RiderRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vehicle_type' => ['nullable', 'string'],
            'plate_number' => ['nullable', 'string'],
            'documents' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf'],
            'phone_number' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'emergency_name' => ['nullable', 'string'],
            'emergency_phone' => ['nullable', 'string'],
            'nin' => ['nullable', 'string', 'max:255'],
            'bvn' => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
        ];
    }
}
