<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Rider;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderAndDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_order_and_delivery_lifecycle_for_rider(): void
    {
        // 1. Setup Customer, Vendor, and Rider
        $customer = User::factory()->create(['role' => 'user']);
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $riderUser = User::factory()->create(['role' => 'rider']);

        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Feasto Burger joint',
            'status' => 'active',
            'latitude' => 6.5244,
            'longitude' => 3.3792,
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'name' => 'Cheeseburger',
            'slug' => 'cheeseburger-' . uniqid(),
            'price' => 2500.00,
            'is_available' => true,
            'status' => 'active',
        ]);

        $rider = Rider::create([
            'user_id' => $riderUser->id,
            'vehicle_type' => 'bike',
            'approval_status' => 'approved',
            'is_online' => true,
            'is_available' => true,
            'latitude' => 6.5250,
            'longitude' => 3.3800,
        ]);

        // 2. Customer places an order
        $orderResponse = $this->actingAs($customer, 'sanctum')->postJson('/api/orders', [
            'vendor_id' => $vendor->id,
            'subtotal' => 2500.00,
            'delivery_fee' => 700.00,
            'service_fee' => 125.00,
            'total' => 3325.00,
            'delivery_address' => '10 Admiralty Way, Lekki',
            'delivery_latitude' => 6.4500,
            'delivery_longitude' => 3.5000,
            'dish_id' => $product->id,
            'name' => 'Cheeseburger',
            'price' => 2500.00,
            'quantity' => 1,
        ]);

        $orderResponse->assertCreated();
        $orderId = $orderResponse->json('order.id');

        // 3. Vendor accepts order and marks as ready
        $this->actingAs($vendorUser, 'sanctum')->patchJson("/api/orders/{$orderId}/accept")
            ->assertOk();

        $this->actingAs($vendorUser, 'sanctum')->patchJson("/api/orders/{$orderId}/ready")
            ->assertOk();

        // 4. Rider checks available deliveries
        $availableResponse = $this->actingAs($riderUser, 'sanctum')->getJson('/api/rider/available-deliveries');
        $availableResponse->assertOk();
        $this->assertNotEmpty($availableResponse->json('deliveries'));

        // 5. Rider accepts delivery
        $acceptResponse = $this->actingAs($riderUser, 'sanctum')->postJson('/api/deliveries/accept', [
            'order_id' => $orderId,
        ]);
        $acceptResponse->assertOk();

        $rider->refresh();
        $this->assertFalse((bool) $rider->is_available);

        // 6. Rider picks up order
        $pickupResponse = $this->actingAs($riderUser, 'sanctum')->patchJson("/api/deliveries/{$orderId}/pickup");
        $pickupResponse->assertOk();
        $this->assertSame('picked_up', Order::find($orderId)->status);

        // 7. Rider completes delivery -> earnings credited to wallet
        $completeResponse = $this->actingAs($riderUser, 'sanctum')->patchJson("/api/deliveries/{$orderId}/complete");
        $completeResponse->assertOk();

        $order = Order::find($orderId);
        $this->assertSame('delivered', $order->status);

        $rider->refresh();
        $this->assertTrue((bool) $rider->is_available);

        // Check wallet was credited
        $wallet = $riderUser->wallet;
        $this->assertNotNull($wallet);
        $this->assertEquals(700.00, (float) $wallet->balance);

        // Check rider earnings summary endpoint
        $earningsResponse = $this->actingAs($riderUser, 'sanctum')->getJson('/api/rider/earnings');
        $earningsResponse->assertOk();
        $this->assertEquals(700.00, $earningsResponse->json('data.today_earnings'));
        $this->assertEquals(1, $earningsResponse->json('data.total_completed_deliveries'));
    }
}

