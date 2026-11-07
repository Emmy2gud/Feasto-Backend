<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmailVerificationRequest;
use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Request an OTP code.
     */
    public function requestOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'purpose' => ['nullable', 'string'],
        ]);

        $result = $this->authService->requestOtp(
            $validated['email'],
            $validated['purpose'] ?? 'registration'
        );

        return response()->json($result, $result['status']);
    }

    /**
     * Register a new user with OTP verification.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json($result, $result['status']);
    }

    /**
     * Verify OTP code and activate account.
     */
    public function verifyOtp(EmailVerificationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->authService->verifyOtp($validated['email'], $validated['otp']);

        return response()->json($result, $result['status']);
    }

    /**
     * Login user and issue access token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->authService->login($validated['email'], $validated['password']);

        return response()->json($result, $result['status']);
    }

    /**
     * Invalidate current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.'], 200);
    }

    /**
     * Invalidate all access tokens for the authenticated user.
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out from all sessions successfully.'], 200);
    }

    /**
     * Request password reset token.
     */
    public function forgotPassword(ForgetPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->authService->forgotPassword($validated['email']);

        return response()->json($result, $result['status']);
    }

    /**
     * Reset password using token.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->authService->resetPassword($validated['token'], $validated['password']);

        return response()->json($result, $result['status']);
    }
}
