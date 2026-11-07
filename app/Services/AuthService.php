<?php

namespace App\Services;

use App\Mail\ForgotPasswordMail;
use App\Mail\OtpVerificationMail;
use App\Mail\UserRegistrationMail;
use App\Models\OtpVerification;
use App\Models\PasswordResetTokens;
use App\Models\User;
use App\Notifications\OtpVerifiedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Resend\Laravel\Facades\Resend;

class AuthService
{
    /**
     * Request a new OTP for an email.
     */
    public function requestOtp(string $email, string $purpose = 'registration'): array
    {
        $existing = OtpVerification::where('email', $email)->first();

        if ($existing && $existing->attempts >= 3 && $existing->expires_at > now()) {
            return [
                'success' => false,
                'status' => 400,
                'message' => 'Maximum OTP attempts exceeded. Please wait before requesting another.',
            ];
        }

        $otpCode = (string) rand(100000, 999999);

        OtpVerification::updateOrCreate(
            ['email' => $email],
            [
                'fullname' => $existing?->fullname ?? 'New User',
                'otp_hash' => Hash::make($otpCode),
                'purpose' => $purpose,
                'expires_at' => now()->addMinutes(20),
                'attempts' => 1,
            ]
        );

        $this->sendEmailSafely($email, 'Your Verification Code', new OtpVerificationMail($otpCode, (object) ['email' => $email]));

        return [
            'success' => true,
            'status' => 200,
            'message' => 'An OTP code has been sent to your email.',
            'otp_code' => app()->environment('local', 'testing') ? $otpCode : null,
        ];
    }

    /**
     * Register a new user intent by storing OTP verification record.
     */
    public function register(array $data): array
    {
        $existing = OtpVerification::where('email', $data['email'])->first();

        if ($existing) {
            if ($existing->expires_at < now()) {
                $existing->delete();
            } elseif ($existing->attempts >= 3) {
                return [
                    'success' => false,
                    'status' => 400,
                    'message' => 'Maximum OTP attempts exceeded. Please wait or request a new one.',
                ];
            } else {
                $existing->increment('attempts');
            }
        }

        $otpCode = (string) rand(100000, 999999);

        $otpRecord = OtpVerification::updateOrCreate(
            ['email' => $data['email']],
            [
                'fullname' => $data['fullname'],
                'phone_number' => $data['phone_number'] ?? null,
                'password' => Hash::make($data['password']),
                'otp_hash' => Hash::make($otpCode),
                'purpose' => 'registration',
                'expires_at' => now()->addMinutes(20),
                'attempts' => 1,
            ]
        );

        $this->sendEmailSafely(
            $otpRecord->email,
            'Verify Your Email',
            new OtpVerificationMail($otpCode, $otpRecord)
        );

        return [
            'success' => true,
            'status' => 200,
            'message' => 'An OTP code has been sent to your email.',
            'otp_code' => app()->environment('local', 'testing') ? $otpCode : null,
        ];
    }

    /**
     * Verify OTP and create the user account.
     */
    public function verifyOtp(string $email, string $otp): array
    {
        $record = OtpVerification::where('email', $email)->first();

        if (! $record) {
            return [
                'success' => false,
                'status' => 404,
                'message' => 'OTP verification record not found.',
            ];
        }

        if ($record->expires_at->isPast()) {
            return [
                'success' => false,
                'status' => 400,
                'message' => 'OTP has expired. Please request a new one.',
            ];
        }

        if (! Hash::check($otp, $record->otp_hash)) {
            return [
                'success' => false,
                'status' => 422,
                'message' => 'Invalid OTP.',
            ];
        }

        $user = User::create([
            'fullname' => $record->fullname,
            'name' => $record->fullname,
            'email' => $record->email,
            'phone_number' => $record->phone_number,
            'password' => $record->password,
            'email_verified_at' => now(),
            'role' => 'user',
            'status' => 'active',
        ]);

        // Clean up OTP record
        $record->delete();

        // Create default wallet
        $user->wallet()->firstOrCreate([], [
            'balance' => 0.00,
            'currency' => 'NGN',
            'status' => 'active',
        ]);

        try {
            Notification::route('mail', $user->email)->notify(new OtpVerifiedNotification($user));
            $this->sendEmailSafely($user->email, 'Welcome to Feasto', new UserRegistrationMail($user));
        } catch (\Throwable $e) {
            Log::warning('Email dispatch after registration failed: ' . $e->getMessage());
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'success' => true,
            'status' => 201,
            'message' => 'OTP verified successfully. User account created.',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Authenticate user and issue API token.
     */
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return [
                'success' => false,
                'status' => 401,
                'message' => 'Invalid credentials.',
            ];
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Logged in successfully.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ];
    }

    /**
     * Send password reset email token.
     */
    public function forgotPassword(string $email): array
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            return [
                'success' => false,
                'status' => 404,
                'message' => 'No user found with this email address.',
            ];
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $token,
                'created_at' => Carbon::now(),
            ]
        );

        $this->sendEmailSafely(
            $user->email,
            'Password Reset Request',
            new ForgotPasswordMail($user, $token)
        );

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Password reset link sent successfully.',
        ];
    }

    /**
     * Reset password using token.
     */
    public function resetPassword(string $token, string $password): array
    {
        $resetRecord = PasswordResetTokens::where('token', $token)->first();

        if (! $resetRecord) {
            return [
                'success' => false,
                'status' => 401,
                'message' => 'Invalid or expired password reset token.',
            ];
        }

        User::where('email', $resetRecord->email)->update([
            'password' => Hash::make($password),
        ]);

        PasswordResetTokens::where('token', $token)->delete();

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Password reset successfully.',
        ];
    }

    /**
     * Helper to send email via Resend safely without breaking flows if API is unavailable.
     */
    protected function sendEmailSafely(string $to, string $subject, $mailable): void
    {
        try {
            if (config('services.resend.key') || env('RESEND_API_KEY')) {
                Resend::emails()->send([
                    'from' => config('mail.from.address', 'noreply@feasto.app'),
                    'to' => [$to],
                    'subject' => $subject,
                    'html' => $mailable->render(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Resend email error: ' . $e->getMessage());
        }
    }
}

