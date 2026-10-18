<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class BrevoMailService
{
    public function sendOtp(string $email, string $otp, $user): array
    {
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'api-key' => env('BREVO_API_KEY'),
            'content-type' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => env('BREVO_SENDER_NAME', config('app.name')),
                'email' => env('BREVO_SENDER_EMAIL'),
            ],

            'to' => [
                [
                    'email' => $email,
                    'name' => $user->name,
                ],
            ],

            'subject' => 'Your Login OTP',

            'htmlContent' => '
                <div style="font-family: Arial, sans-serif;">
                    <h2>Login Verification</h2>

                    <p>Hello ' . e($user->name) . ',</p>

                    <p>Your login OTP is:</p>

                    <h1 style="letter-spacing: 5px;">
                        ' . e($otp) . '
                    </h1>

                    <p>
                        This OTP is valid for
                        <strong>10 minutes</strong>.
                    </p>

                    <p>
                        If you did not request this OTP,
                        please ignore this email.
                    </p>
                </div>
            ',
        ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Brevo API error: ' . $response->body()
            );
        }

        return $response->json();
    }
}