<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('customer_id') && auth()->check()) {
            $this->merge(['customer_id' => auth()->id()]);
        }

        if (! $this->has('order_number')) {
            $this->merge(['order_number' => 'ORD-' . strtoupper(uniqid())]);
        }

        if (! $this->has('idempotency_key')) {
            $this->merge(['idempotency_key' => (string) Str::uuid()]);
        }
    }

    public function rules(): array
    {
        return [
            'order_number' => ['nullable', 'string', 'max:255'],
            'customer_id' => ['nullable', 'exists:users,id'],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'rider_id' => ['nullable', 'exists:users,id'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'service_fee' => ['nullable', 'numeric', 'min:0'],
            'tip' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'delivery_status' => ['nullable', 'string', 'max:50'],
            'delivery_address' => ['nullable', 'string'],
            'delivery_latitude' => ['nullable', 'numeric'],
            'delivery_longitude' => ['nullable', 'numeric'],
            'distance_km' => ['nullable', 'numeric'],
            'dropoff_preference' => ['nullable', 'string'],
            'recipient_phone' => ['nullable', 'string', 'max:25'],
            'notes' => ['nullable', 'string'],
            'idempotency_key' => ['nullable', 'string', 'max:255'],

            // Single item or array of items
            'dish_id' => ['nullable', 'exists:products,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'name' => ['nullable', 'string', 'max:255'],

            'items' => ['nullable', 'array'],
            'items.*.dish_id' => ['nullable', 'exists:products,id'],
            'items.*.name' => ['required_with:items', 'string', 'max:255'],
            'items.*.price' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'items.*.addons' => ['nullable', 'array'],

            // Addons
            'addon_id' => ['nullable', 'exists:product_addons,id'],
            'addon_name' => ['nullable', 'string', 'max:255'],
            'addon_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
