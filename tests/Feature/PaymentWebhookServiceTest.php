<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class PaymentWebhookServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_the_payment_and_order_for_a_successful_korapay_webhook(): void
    {
        putenv('FLW_SECRET_HASH=valid-secret');

        $order = Order::create([
            'order_number' => 'ORD-1001',
            'customer_id' => 1,
            'vendor_id' => 1,
            'subtotal' => 1000,
            'delivery_fee' => 0,
            'discount' => 0,
            'total' => 1000,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'customer_id' => 1,
            'amount' => 1000,
            'currency' => 'NGN',
            'payment_method' => 'card',
            'provider' => 'korapay',
            'provider_reference' => 'tx_ref_123',
            'status' => 'pending',
        ]);

        $request = Request::create('/webhooks/korapay', 'POST', [
            'data' => [
                'status' => 'successful',
                'tx_ref' => 'tx_ref_123',
            ],
        ], [], [], ['HTTP_VERIF' => 'valid-secret']);

        $response = (new PaymentService)->handleKorapayWebhook($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('success', $payment->fresh()->status);
        $this->assertSame('paid', $order->fresh()->status);
    }
}
