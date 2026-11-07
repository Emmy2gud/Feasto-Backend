<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('user_id') && auth()->check()) {
            $this->merge(['user_id' => auth()->id()]);
        }

        if ($this->has('open_time') && ! $this->has('opening_time')) {
            $this->merge(['opening_time' => $this->input('open_time')]);
        }

        if ($this->has('close_time') && ! $this->has('closing_time')) {
            $this->merge(['closing_time' => $this->input('close_time')]);
        }
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,id'],
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
            'day_of_week' => ['nullable', 'string', 'max:20'],
            'opening_time' => ['nullable', 'date_format:H:i'],
            'closing_time' => ['nullable', 'date_format:H:i'],
            'open_time' => ['nullable', 'date_format:H:i'],
            'close_time' => ['nullable', 'date_format:H:i'],
            'is_closed' => ['nullable', 'boolean'],
        ];
    }
}
