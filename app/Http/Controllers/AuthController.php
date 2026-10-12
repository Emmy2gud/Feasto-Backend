<?php
namespace App\Http\Controllers;

use App\Http\Requests\EmailVerificationRequest;
use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;

use App\Http\Requests\ResetPasswordRequest;
use App\Mail\ForgotPasswordMail;
use App\Mail\OtpVerificationMail;
use App\Mail\UserRegistrationMail;
use App\Models\OtpVerification;
use App\Models\PasswordResetTokens;
use App\Models\User;
use App\Notifications\OtpVerifiedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Resend\Laravel\Facades\Resend;

class AuthController
{
    // public function __construct(private OtpService $otpService) {}

    /**
     * Request a new OTP code for the given email and purpose
     *
     * Available purposes: registration, password_reset, phone_verification
     */
    // public function requestOtp(RequestOtpRequest $request)
    // {
    //     $validated = $request->validated();

    //     $result = $this->otpService->requestOtp(
    //         email: $validated['email'],
    //         purpose: $validated['purpose'],
    //         additionalData: $request->except(['email', 'purpose'])
    //     );

    //     $statusCode = $result['success'] ? 200 : 400;

    //     return response()->json($result, $statusCode);
    // }

    public function register(RegisterRequest $request)
    {

        $validatedData = $request->validated();

        $otpusers = OtpVerification::where('email', $validatedData['email'])->first();

        if ($otpusers) {

            if ($otpusers->first()->expires_at < now()) {
                return response()->json(['message' => 'OTP has expired. Please request a new one.'], 400);
            }
            // Check if attempts exceed limit (e.g., 3 attempts)
            if ($otpusers->attempts >= 3) {
                return response()->json(['message' => 'Maximum OTP attempts exceeded. Please request a new one.'], 400);
            }
            $otpusers->increment('attempts');
        } else {

            $otpCode = rand(100000, 999999); // Generate a random 6-digit OTP

            // Create the user record
            $otpRecord = OtpVerification::create([
                'fullname'     => $validatedData['fullname'],
                'email'        => $validatedData['email'],
                'phone_number' => $validatedData['phone_number'] ?? null,
                'password'     => Hash::make($validatedData['password']),
                'otp_hash'     => Hash::make($otpCode),
                'purpose'      => 'registration',
                'expires_at'   => now()->addMinutes(20), // Set OTP expiration time
                'attempts'     => +1,
            ]);

            // Send a simple registration notification to the provided email.
            Resend::emails()->send([
                'from'    => env('MAIL_FROM_ADDRESS'),
                'to'      => [$otpRecord->email],
                'subject' => 'Password reset link',
                'html'    => (new OtpVerificationMail($otpCode, $otpRecord))->render(),
            ]);

            return response()->json([
                'message'  => 'an otp code has been sent to your email',
                'otp_code' => $otpCode,
            ]);
        }
    }
    public function verifyOtp(EmailVerificationRequest $request)
    {

        $validatedData   = $request->validated();
        $otpVerification = OtpVerification::where('email', $validatedData['email'])->first();

        if (! $otpVerification) {
            return response()->json(['message' => 'OTP verification record not found.'], 404);
        }

        if ($otpVerification->expires_at->isPast()) {
            return response()->json(['message' => 'OTP has expired. Please request a new one.'], 400);
        }

        if (! Hash::check($validatedData['otp'], $otpVerification->otp_hash)) {
          $otpVerification->delete();
          return response()->json(['message' => 'Invalid OTP.'], 422);
        }
        $user = User::create([
            'fullname'     => $otpVerification->fullname,
            'email'        => $otpVerification->email,
            'phone_number' => $otpVerification->phone_number,
            'password'     => $otpVerification->password,
            'email_verified_at'  => now(), // Mark the user as verified
        ]);

        // Send a notification to the user that their account has been created
        Notification::route('mail', $user->email)->notify(new OtpVerifiedNotification($user));
        //welcome the user to the platform
        Resend::emails()->send([
            'from'    => env('MAIL_FROM_ADDRESS'),
            'to'      => [$user->email],
            'subject' => 'Password reset link',
            'html'    => (new UserRegistrationMail($user))->render(),
        ]);

        // Delete the OTP verification record after successful verification
        $otpVerification->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => 'OTP verified successfully. User account created.', 'user' => $user,'access_token' => $token,'token_type'   => 'Bearer'], 201);
    }
    public function login(LoginRequest $request)
    {
        $validatedData = $request->validated();
        $user          = User::where('email', $validatedData['email'])->first();

        if (! $user || ! Hash::check($validatedData['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // dd($token);

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
    public function forgotPassword(ForgetPasswordRequest $request)
    {
        $validatedData = $request->validated();
        $token         = Str::random(64);
        // collect with the provided email
        $user = User::where('email', $validatedData['email'])->first();
        if ($user->email !== $validatedData['email']) {
            return response()->json(['message' => 'Email does not exists.'], 404);
        }

        // store in the password_reset_table
        DB::table('password_reset_tokens')->insert([

            'email'      => $validatedData['email'],

            'token'      => $token,

            'created_at' => Carbon::now(),

        ]);
        Resend::emails()->send([
            'from'    => env('MAIL_FROM_ADDRESS'),
            'to'      => [$user->email],
            'subject' => 'Password reset link',
            'html'    => (new ForgotPasswordMail($user, $token))->render(),
        ]);

        return response()->json(['message' => 'Password reset link sent successfully'], 200);

    }
    public function resetPassword(ResetPasswordRequest $request)
    {


        $validatedData = $request->validated();

        // find the password_reset_table row with the provided token
        $passwordReset =PasswordResetTokens::where('token', $validatedData['token'])->first();
        // if not found
        if (!$passwordReset) {
            return response()->json(['message' => 'Invalid token.'], 401);
        }
        // update the user password

        User::where('email', $passwordReset->email)->update([
            'password' => Hash::make($validatedData['password']),
        ]);
        // delete the password_reset_tokens row

        PasswordResetTokens::where('token', $validatedData['token'])->delete();

        return response()->json(['message' => 'Password reset successfully']);
    }
}
