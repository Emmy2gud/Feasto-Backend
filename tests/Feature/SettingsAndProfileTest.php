<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingsAndProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_retrieve_app_settings(): void
    {
        $response = $this->getJson('/api/settings/app');

        $response->assertOk();
        $response->assertJsonPath('settings.currency', 'NGN');
        $response->assertJsonPath('settings.delivery_base_fee', 500);
    }

    public function test_user_can_view_and_update_settings_and_password(): void
    {
        $user = User::factory()->create([
            'fullname' => 'John Original',
            'phone_number' => '08011112222',
            'password' => Hash::make('secret123'),
        ]);

        // 1. Get user settings
        $settingsResponse = $this->actingAs($user, 'sanctum')->getJson('/api/settings/user');
        $settingsResponse->assertOk();
        $settingsResponse->assertJsonPath('settings.profile.fullname', 'John Original');

        // 2. Update profile
        $updateResponse = $this->actingAs($user, 'sanctum')->postJson('/api/settings/profile', [
            'fullname' => 'John Updated',
            'phone_number' => '08099998888',
            'address' => '25 Lekki Phase 1',
        ]);
        $updateResponse->assertOk();

        $user->refresh();
        $this->assertSame('John Updated', $user->fullname);
        $this->assertSame('08099998888', $user->phone_number);

        // 3. Change password
        $passwordResponse = $this->actingAs($user, 'sanctum')->postJson('/api/settings/password', [
            'current_password' => 'secret123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        $passwordResponse->assertOk();

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }
}

