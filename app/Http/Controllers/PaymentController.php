<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * List user payments.
     */
    public function index(Request $request): JsonResponse
    {
        $payments = Payment::where('customer_id', $request->user()->id)
            ->with('order')
            ->latest()
            ->paginate(15);

        return response()->json([
            'message' => 'Payments retrieved successfully.',
            'payments' => $payments,
        ], 200);
    }

    /**
     * Show payment details.
     */
    public function show(int $id): JsonResponse
    {
        $payment = Payment::with('order')->findOrFail($id);

        return response()->json([
            'message' => 'Payment details retrieved.',
            'payment' => $payment,
        ], 200);
    }

    /**
     * Initiate payment for an order.
     */
    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'payment_method' => ['nullable', 'string', 'in:card,bank_transfer,wallet'],
        ]);

        $order = Order::findOrFail($validated['order_id']);

        $result = $this->paymentService->initiatePayment(
            $order,
            $validated['payment_method'] ?? 'card'
        );

        return response()->json([
            'message' => 'Payment initiated successfully.',
            'data' => $result,
        ], 200);
    }

    /**
     * Handle incoming payment gateway webhook (e.g. Korapay).
     */
    public function koraPayWebhook(Request $request): JsonResponse
    {
        return $this->paymentService->handleKorapayWebhook($request);
    }
}
