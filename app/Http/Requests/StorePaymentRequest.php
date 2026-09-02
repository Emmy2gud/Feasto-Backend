<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'exists:orders,id'],
            'customer_id' => ['required', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'provider' => ['nullable', 'string', 'max:100'],
            'provider_reference' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'paid_at' => ['nullable', 'date'],
            'failed_at' => ['nullable', 'date'],
            'failure_reason' => ['nullable', 'string'],
            'refund_amount' => ['nullable', 'numeric', 'min:0'],
            'refunded_at' => ['nullable', 'date'],
            'refund_reason' => ['nullable', 'string'],
        ];
    }
}
