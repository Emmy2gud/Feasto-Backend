<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use App\Notifications\VendorApprovedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class VendorCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_be_created_with_profile_picture_and_business_hours(): void
    {
        $user = User::factory()->create([
            'role' => 'vendor',
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/vendors', [
            'business_name' => 'Acme Foods',
            'business_description' => 'Fresh local groceries',
            'business_phone' => '08012345678',
            'business_email' => 'vendor@example.com',
            'business_address' => '12 Main Road',
            'city' => 'Lagos',
            'state' => 'Lagos',
            'country' => 'NG',
            'is_accepting_orders' => true,
            'status' => 'active',
            'verification_status' => 'pending',
            'day_of_week' => 'monday',
            'open_time' => '09:00',
            'close_time' => '17:00',
            'profile_picture' => UploadedFile::fake()->image('profile.jpg', 600, 400),
        ]);

        $response->assertCreated();
        $response->assertJsonPath('vendor.business_name', 'Acme Foods');
        $this->assertDatabaseHas('vendors', [
            'user_id' => $user->id,
            'business_name' => 'Acme Foods',
        ]);
        $this->assertDatabaseHas('vendor_business_hours', [
            'day_of_week' => 'monday',
            'open_time' => '09:00',
            'close_time' => '17:00',
        ]);
    }

    public function test_vendor_approval_sends_vendor_approved_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => 'vendor',
        ]);

        $vendor = Vendor::query()->create([
            'user_id' => $user->id,
            'business_name' => 'Test Market',
            'status' => 'active',
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/vendors/'.$vendor->id, [
            'verification_status' => 'approved',
        ]);

        $response->assertOk();
        Notification::assertSentTo(
            $user,
            VendorApprovedNotification::class,
            function ($notification, $channels) use ($vendor) {
                return $notification->vendor->id === $vendor->id
                    && in_array('mail', $channels, true)
                    && in_array('database', $channels, true);
            }
        );
    }
}
