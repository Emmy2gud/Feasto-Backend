<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SettingService
{
    /**
     * Get platform/app settings for MVP clients.
     */
    public function getAppSettings(): array
    {
        return [
            'app_name' => config('app.name', 'Feasto'),
            'currency' => 'NGN',
            'currency_symbol' => '₦',
            'support_email' => config('mail.from.address', 'support@feasto.app'),
            'support_phone' => '+2348000000000',
            'delivery_base_fee' => (float) config('delivery.base_fee', 500),
            'delivery_rate_per_km' => (float) config('delivery.rate_per_km', 100),
            'service_fee_percentage' => 5.0,
            'operating_hours' => [
                'opens_at' => '08:00',
                'closes_at' => '22:00',
            ],
        ];
    }

    /**
     * Get user profile and account settings.
     */
    public function getUserSettings(User $user): array
    {
        return [
            'profile' => [
                'id' => $user->id,
                'fullname' => $user->fullname,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'address' => $user->address,
                'role' => $user->role,
                'profile_picture' => $user->profile_picture,
                'email_verified' => ! is_null($user->email_verified_at),
            ],
            'preferences' => [
                'order_notifications_email' => true,
                'order_notifications_sms' => true,
                'promotional_notifications' => false,
            ],
            'vendor_profile' => $user->vendor ? $user->vendor->only(['id', 'business_name', 'is_accepting_orders', 'status']) : null,
            'rider_profile' => $user->rider ? $user->rider->only(['id', 'vehicle_type', 'is_online', 'is_available', 'approval_status']) : null,
        ];
    }

    /**
     * Update user profile information.
     */
    public function updateProfile(User $user, array $data, ?UploadedFile $profilePicture = null): User
    {
        $updateData = [];

        if (isset($data['fullname'])) {
            $updateData['fullname'] = $data['fullname'];
        }

        if (isset($data['phone_number'])) {
            $updateData['phone_number'] = $data['phone_number'];
        }

        if (isset($data['address'])) {
            $updateData['address'] = $data['address'];
        }

        if ($profilePicture) {
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $updateData['profile_picture'] = $profilePicture->store('users/profiles', 'public');
        }

        if (! empty($updateData)) {
            $user->update($updateData);
        }

        return $user->fresh();
    }

    /**
     * Securely update password from settings page.
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'The current password provided does not match our records.',
            ]);
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        return true;
    }
}
