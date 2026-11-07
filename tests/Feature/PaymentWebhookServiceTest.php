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

        $user = \App\Models\User::factory()->create();
        $vendor = \App\Models\Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Test Vendor',
            'status' => 'active',
        ]);

        $order = Order::create([
            'order_number' => 'ORD-1001',
            'customer_id' => $user->id,
            'vendor_id' => $vendor->id,
            'subtotal' => 1000,
            'delivery_fee' => 0,
            'service_fee' => 0,
            'delivery_address' => '123 Test St',
            'delivery_latitude' => 6.5,
            'delivery_longitude' => 3.3,
            'discount' => 0,
            'total' => 1000,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'customer_id' => $user->id,
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
