<?php

namespace App\Services\WebhookSubclass;

use App\Models\Payment;
use DB;

class ChargeService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function handleChargeSuccess(array $data, $status, $txRef)
    {
        if (! $status || ! $txRef) {
            return response()->json(['message' => 'Invalid webhook payload.'], 422);
        }

        if ($status === 'success') {
            $transaction = Payment::where('provider_reference', $txRef)->first();

            if (! $transaction) {
                return response()->json(['message' => 'Payment not found.'], 404);
            }

            DB::transaction(function () use ($transaction, $status) {
                $transaction->update(['status' => $status]);
                $transaction->order->update(['payment_status' => 'paid']);
                $transaction->order->update(['status' => 'pending']); // Update the order status to pending until the payment is confirmed which will done by thevendor
            });

        }
    }

    public function handleChargeFailed(array $data, $status, $txRef)
    {
        if (! $status || ! $txRef) {
            return response()->json(['message' => 'Invalid webhook payload.'], 422);
        }

        if ($status === 'failed') {
            $transaction = Payment::where('provider_reference', $txRef)->first();

            if (! $transaction) {
                return response()->json(['message' => 'Payment not found.'], 404);
            }

            DB::transaction(function () use ($transaction, $status) {
                $transaction->update(['status' => $status]);
                $transaction->order->update(['payment_status' => 'failed']);
                $transaction->order->update(['status' => 'pending']); // Update the order status to pending until the payment is confirmed which will done by thevendor
            });

        }
    }
}
