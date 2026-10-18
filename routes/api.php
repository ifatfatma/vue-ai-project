
<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PromptController;
use Illuminate\Support\Facades\Route;

// Public OTP authentication APIs
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/login', [AuthController::class, 'login']);

// Public prompt listing API
Route::get('/prompts', [PromptController::class, 'index']);

// Protected APIs
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});
