<?php

namespace App\Http\Controllers;

use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    /**
     * Get public application settings (currency, fees, contact).
     */
    public function appSettings(): JsonResponse
    {
        $settings = $this->settingService->getAppSettings();

        return response()->json([
            'message' => 'Application settings retrieved successfully.',
            'settings' => $settings,
        ], 200);
    }

    /**
     * Get authenticated user's account & settings page data.
     */
    public function userSettings(Request $request): JsonResponse
    {
        $settings = $this->settingService->getUserSettings($request->user());

        return response()->json([
            'message' => 'User settings retrieved successfully.',
            'settings' => $settings,
        ], 200);
    }

    /**
     * Update user profile from settings page.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fullname' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:25'],
            'address' => ['nullable', 'string', 'max:500'],
            'profile_picture' => ['nullable', 'image', 'max:4096'],
        ]);

        $user = $this->settingService->updateProfile(
            $request->user(),
            $validated,
            $request->file('profile_picture')
        );

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user,
        ], 200);
    }

    /**
     * Change user password from settings page.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $this->settingService->changePassword(
            $request->user(),
            $validated['current_password'],
            $validated['password']
        );

        return response()->json([
            'message' => 'Password changed successfully.',
        ], 200);
    }
}
