<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>OTP Verification</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; }
        .email-container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .email-header { background: #0d6efd; padding: 20px; text-align: center; color: #ffffff; }
        .email-header img { max-height: 40px; }
        .email-body { padding: 30px; color: #333333; line-height: 1.6; }
        .otp-box { background: #f8f9fa; border: 2px dashed #0d6efd; font-size: 28px; font-weight: bold; color: #0d6efd; text-align: center; padding: 15px; margin: 20px 0; letter-spacing: 5px; border-radius: 6px; }
        .email-footer { background: #f1f3f5; padding: 15px; text-align: center; font-size: 12px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header with Logo -->
        <div class="email-header">
            <h2>{{ config('app.name', 'AI Prompt Hub') }}</h2>
        </div>

        <!-- Body with Information & Bold OTP -->
        <div class="email-body">
            <h3>Hello, {{ $user->name ?? 'User' }}</h3>
            <p>You have requested to login to your account. Please use the One-Time Password (OTP) below to complete your authentication process. Do not share this code with anyone.</p>
            
            <div class="otp-box">
                {{ $otp }}
            </div>

            <p>This OTP is valid for a limited time. If you did not request this, please ignore this email.</p>
            <p>Best Regards,<br><strong>Team {{ config('app.name') }}</strong></p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
            This is an automated message, please do not reply.
        </div>
    </div>
</body>
</html>