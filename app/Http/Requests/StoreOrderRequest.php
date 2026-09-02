<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_number' => ['required', 'string', 'max:255', 'unique:orders,order_number'],
            'customer_id' => ['required', 'exists:users,id'],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'rider_id' => ['nullable', 'exists:users,id'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'delivery_fee' => ['required', 'numeric', 'min:0'],
            'service_fee' => ['required', 'numeric', 'min:0'],
            'discount' => ['required', 'numeric', 'min:0'],
            'tax' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'status' => ['nullable', 'string', 'max:50'],
            'payment_status' => ['nullable', 'string', 'max:50'],
            'delivery_status' => ['nullable', 'string', 'max:50'],
            'pickup_address' => ['nullable', 'string'],
            'pickup_latitude' => ['nullable', 'numeric'],
            'pickup_longitude' => ['nullable', 'numeric'],
            'delivery_address' => ['nullable', 'string'],
            'delivery_latitude' => ['nullable', 'numeric'],
            'delivery_longitude' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
