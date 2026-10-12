<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'business_name' => ['required', 'string', 'max:255'],
            'business_description' => ['nullable', 'string'],
            'business_phone' => ['nullable', 'string', 'max:50'],
            'business_email' => ['nullable', 'email', 'max:255'],
            'business_address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_accepting_orders' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', 'max:50'],
            'verification_status' => ['nullable', 'string', 'max:50'],
           
         'day_of_week'=> ['required', 'string', 'max:20'],
        'opening_time'=> ['required', 'date_format:H:i'],
        'closing_time'=> ['required', 'date_format:H:i'],
        'is_closed'=> ['boolean'],

        ];
    }
}
