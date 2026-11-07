<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductAddons;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_to_cart_update_quantity_and_view(): void
    {
        $user = User::factory()->create();
        $vendorUser = User::factory()->create(['role' => 'vendor']);

        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Pizza Palace',
            'status' => 'active',
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'name' => 'Pepperoni Pizza',
            'slug' => 'pepperoni-pizza',
            'price' => 4000.00,
            'is_available' => true,
            'status' => 'active',
        ]);

        $addon = ProductAddons::create([
            'product_id' => $product->id,
            'name' => 'Extra Mozzarella',
            'price' => 500.00,
            'is_available' => true,
        ]);

        // 1. Add to cart with addon
        $addResponse = $this->actingAs($user, 'sanctum')->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'addon_ids' => [$addon->id],
        ]);
        $addResponse->assertOk();

        // 2. View cart
        $cartResponse = $this->actingAs($user, 'sanctum')->getJson('/api/cart');
        $cartResponse->assertOk();
        $this->assertEquals(2, $cartResponse->json('total_items'));
        // (4000 + 500) * 2 = 9000
        $this->assertEquals(9000.00, $cartResponse->json('subtotal'));

        // 3. Update quantity
        $cartItemId = $cartResponse->json('items.0.id');
        $updateResponse = $this->actingAs($user, 'sanctum')->patchJson("/api/cart/items/{$cartItemId}", [
            'quantity' => 3,
        ]);
        $updateResponse->assertOk();

        $cartResponse2 = $this->actingAs($user, 'sanctum')->getJson('/api/cart');
        $this->assertEquals(3, $cartResponse2->json('total_items'));
        $this->assertEquals(13500.00, $cartResponse2->json('subtotal'));

        // 4. Remove item
        $deleteResponse = $this->actingAs($user, 'sanctum')->deleteJson("/api/cart/items/{$cartItemId}");
        $deleteResponse->assertOk();

        $cartResponse3 = $this->actingAs($user, 'sanctum')->getJson('/api/cart');
        $this->assertEquals(0, $cartResponse3->json('total_items'));
    }
}

