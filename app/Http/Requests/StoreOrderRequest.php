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
            'discount' => ['nullable', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'delivery_status' => ['nullable', 'string', 'max:50'],
            'delivery_address' => ['nullable', 'string'],
            'delivery_latitude' => ['nullable', 'numeric'],
            'delivery_longitude' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
            'idempotency_key' => ['required', 'string', 'max:255'],
            //order items
            'order_id' => ['required', 'exists:orders,id'],
            'dish_id' => ['required', 'exists:dishes,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'name' => ['required', 'string', 'max:255'],
            //order item addons
            'order_item_id' => ['required', 'exists:order_items,id'],
            'addon_id' => ['required', 'exists:addons,id'],
            'addon_name' => ['required', 'string', 'max:255'],
            'addon_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
