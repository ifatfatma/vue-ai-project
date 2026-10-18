<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SendOtpMail;
use App\Models\FrontendUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

class AuthController extends Controller
{
    /**
     * Send OTP to frontend user's email.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = Str::lower($request->email);

        $sendKey = 'api-otp-send:' . sha1(
            $email . '|' . $request->ip()
        );

        if (RateLimiter::tooManyAttempts($sendKey, 3)) {
            $seconds = RateLimiter::availableIn($sendKey);

            return response()->json([
                'message' => "Too many OTP requests. Please try again in {$seconds} seconds.",
            ], 429);
        }

        RateLimiter::hit($sendKey, 60);

        $user = FrontendUser::firstOrCreate(
            ['email' => $email],
            [
                'name' => Str::before($email, '@'),
                'password' => Str::random(32),
            ]
        );

        $otp = (string) random_int(100000, 999999);

        $user->otp = Hash::make($otp);
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();

        try {
            Mail::to($user->email)->send(
                new SendOtpMail($otp, $user)
            );

            return response()->json([
                'message' => 'OTP sent successfully to your email.',
            ], 200);

        } catch (Throwable $e) {

            logger()->error('API OTP email sending failed.', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'OTP generated, but email could not be sent.',
            ], 500);
        }
    }

    /**
     * Login using email + OTP.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'otp' => ['required', 'digits:6'],
        ]);

        $email = Str::lower($request->email);

        $verifyKey = 'api-otp-verify:' . sha1(
            $email . '|' . $request->ip()
        );

        if (RateLimiter::tooManyAttempts($verifyKey, 5)) {
            $seconds = RateLimiter::availableIn($verifyKey);

            return response()->json([
                'message' => "Too many OTP attempts. Please try again in {$seconds} seconds.",
            ], 429);
        }

        RateLimiter::hit($verifyKey, 60);

        $user = FrontendUser::where('email', $email)->first();

        if (!$user || !$user->otp || !$user->otp_expires_at) {
            return response()->json([
                'message' => 'Invalid or expired OTP. Please request a new OTP.',
            ], 401);
        }

        if ($user->otp_expires_at->isPast()) {

            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();

            return response()->json([
                'message' => 'OTP has expired. Please request a new OTP.',
            ], 401);
        }

        if (!Hash::check($request->otp, $user->otp)) {
            return response()->json([
                'message' => 'Invalid OTP.',
            ], 401);
        }

        // OTP successfully verified
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        RateLimiter::clear($verifyKey);

        // Create Sanctum token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $user,
        ], 200);
    }

    /**
     * Logout current API user.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful.',
        ], 200);
    }

    /**
     * Get authenticated frontend user.
     */
    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ], 200);
    }
}