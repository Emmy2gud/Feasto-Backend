<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_as_rider(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->postJson('/api/riders', [
            'vehicle_type' => 'bike',
            'plate_number' => 'ABC-123',
            'phone_number' => '08012345678',
            'email' => 'rider@example.com',
            'emergency_name' => 'Jane Doe',
            'emergency_phone' => '08087654321',
            'is_online' => true,
            'latitude' => 6.5244,
            'longitude' => 3.3792,
        ]);

        $response->assertStatus(201);
        $user->refresh();

        $this->assertSame('rider', $user->role);
        $this->assertDatabaseHas('riders', [
            'user_id' => $user->id,
            'vehicle_type' => 'bike',
            'plate_number' => 'ABC-123',
        ]);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'currency' => 'NGN',
            'status' => 'active',
        ]);
    }
}
