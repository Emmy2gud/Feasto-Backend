<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Api\PaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        protected ?PaymentGatewayService $gatewayService = null
    ) {}

    /**
     * Initiate payment for an order.
     */
    public function initiatePayment(Order $order, ?string $paymentMethod = 'card'): array
    {
        $txRef = 'PAY-' . strtoupper(uniqid()) . '-' . $order->id;

        $payment = Payment::create([
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'amount' => $order->total,
            'currency' => 'NGN',
            'payment_method' => $paymentMethod,
            'provider' => 'korapay',
            'provider_reference' => $txRef,
            'status' => 'pending',
        ]);

        $gatewayResponse = [];
        if ($this->gatewayService) {
            try {
                $customer = $order->customer;
                $gatewayResponse = $this->gatewayService->initializePayment([
                    'amount' => $order->total,
                    'customer' => [
                        'email' => $customer?->email ?? 'customer@example.com',
                        'name' => $customer?->fullname ?? 'Customer',
                        'phone' => $customer?->phone_number ?? '08000000000',
                    ],
                    'metadata' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                    ],
                ], $txRef);
            } catch (\Throwable $e) {
                Log::warning('Gateway initialization deferred or failed: ' . $e->getMessage());
            }
        }

        return [
            'payment' => $payment,
            'reference' => $txRef,
            'gateway' => $gatewayResponse,
        ];
    }

    /**
     * Handle Korapay/Flutterwave webhook callback.
     */
    public function handleKorapayWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $data = $payload['data'] ?? $payload;

        $status = strtolower($data['status'] ?? $payload['event'] ?? '');
        $txRef = $data['tx_ref'] ?? $data['reference'] ?? null;

        if (! $txRef) {
            return response()->json(['message' => 'Transaction reference missing'], 400);
        }

        $payment = Payment::where('provider_reference', $txRef)->first();

        if (! $payment) {
            return response()->json(['message' => 'Payment record not found'], 404);
        }

        if (in_array($status, ['successful', 'success', 'charge.success'], true)) {
            DB::transaction(function () use ($payment) {
                $payment->update([
                    'status' => 'success',
                    'paid_at' => now(),
                ]);

                if ($payment->order) {
                    $payment->order->update([
                        'status' => 'paid',
                        'payment_status' => 'paid',
                    ]);
                }
            });

            return response()->json(['message' => 'Payment marked as successful'], 200);
        }

        if (in_array($status, ['failed', 'charge.failed'], true)) {
            DB::transaction(function () use ($payment, $data) {
                $payment->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'failure_reason' => $data['reason'] ?? 'Payment failed',
                ]);

                if ($payment->order) {
                    $payment->order->update([
                        'payment_status' => 'failed',
                    ]);
                }
            });

            return response()->json(['message' => 'Payment marked as failed'], 200);
        }

        return response()->json(['message' => 'Webhook received'], 200);
    }
}
