<?php

namespace App\Http\Controllers;

use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    /**
     * Display current authenticated user profile.
     */
    public function show(Request $request): JsonResponse
    {
        $settings = $this->settingService->getUserSettings($request->user());

        return response()->json([
            'message' => 'Profile retrieved successfully.',
            'data' => $settings,
        ], 200);
    }

    /**
     * Update current authenticated user profile.
     */
    public function update(Request $request): JsonResponse
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
}
