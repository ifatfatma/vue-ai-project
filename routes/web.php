<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PromptController;
use App\Http\Controllers\Admin\SeoSettingController;
use App\Http\Controllers\Admin\RunCommandController;

use App\Http\Controllers\FrontendController;
use App\Http\Controllers\FrontendAuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SavedPromptController;
use App\Http\Controllers\UserAccountController;

use App\Models\Prompt;
use App\Models\PromptCopy;
use Illuminate\Support\Facades\Storage;

Route::get('/prompt-images/{path}', function ($path) {
    $path = 'prompts/' . basename($path);

    abort_unless(Storage::disk('public')->exists($path), 404);

    return response()->file(
        Storage::disk('public')->path($path),
        [
            'Content-Type' => mime_content_type(
                Storage::disk('public')->path($path)
            ),
            'Cache-Control' => 'public, max-age=86400',
        ]
    );
})->where('path', '.*')->name('prompt-images.show');


/*
|--------------------------------------------------------------------------
| CLEAR / STORAGE LINK
|--------------------------------------------------------------------------
*/

Route::get('/clear', function () {
    Artisan::call('storage:link');
    Artisan::call('optimize:clear');

    return Artisan::output();
});


/*
|--------------------------------------------------------------------------
| PUBLIC / FRONTEND ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', [FrontendController::class, 'index'])
    ->name('home');

Route::get('/search-suggestions', [FrontendController::class, 'searchSuggestions'])
    ->name('search.suggestions');

Route::get('/user-prompts/suggestions', [FrontendController::class, 'userSearchSuggestions'])
    ->name('user.prompts.suggestions');

Route::get('/prompts/{id}', [FrontendController::class, 'show'])
    ->name('prompts.show');


/*
|--------------------------------------------------------------------------
| FRONTEND EMAIL OTP LOGIN
|--------------------------------------------------------------------------
*/

Route::get('/login', [FrontendAuthController::class, 'showLoginForm'])
    ->name('frontend.login');

Route::post('/send-otp', [FrontendAuthController::class, 'sendOtp'])
    ->name('frontend.send.otp');

Route::get('/verify-otp', [FrontendAuthController::class, 'showVerifyForm'])
    ->name('frontend.otp.verify.form');

Route::post('/verify-otp', [FrontendAuthController::class, 'verifyOtp'])
    ->name('frontend.otp.verify');

Route::post('/logout', [FrontendAuthController::class, 'logout'])
    ->name('frontend.logout')
    ->middleware('auth:frontend');


/*
|--------------------------------------------------------------------------
| FRONTEND PROMPT STORE
|--------------------------------------------------------------------------
*/

Route::post('/prompts/store', [FrontendController::class, 'storePrompt'])
    ->name('prompts.store')
    ->middleware('auth:frontend');


/*
|--------------------------------------------------------------------------
| FRONTEND COPY TRACKING
|--------------------------------------------------------------------------
*/

Route::post('/prompts/{id}/copy-track', function ($id) {

    $user = auth('frontend')->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Please login to copy and track prompts.',
        ], 401);
    }

    $prompt = Prompt::find($id);

    if (!$prompt) {
        return response()->json([
            'success' => false,
            'message' => 'Prompt not found.',
        ], 404);
    }

    $userEmail = $user->email;
    $today = now()->toDateString();

    $copyRecord = PromptCopy::firstOrCreate([
        'prompt_id' => $prompt->id,
        'email' => $userEmail,
        'copied_date' => $today,
    ]);

    if ($copyRecord->wasRecentlyCreated) {
        $prompt->increment('copies_count');

        return response()->json([
            'success' => true,
            'counted' => true,
            'message' => 'Copy count updated for today!',
            'total_copies' => (int) $prompt->copies_count,
        ]);
    }

    return response()->json([
        'success' => true,
        'counted' => false,
        'message' => 'Already counted for today.',
        'total_copies' => (int) $prompt->copies_count,
    ]);

})->middleware('auth:frontend')
    ->name('prompts.copy.track');


/*
|--------------------------------------------------------------------------
| AUTHENTICATED FRONTEND USER ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:frontend'])->group(function () {

    // My Prompts
    Route::get('/my-prompts', [FrontendController::class, 'myPrompts'])
        ->name('user.prompts');

    Route::get('/my-prompts/{id}/edit', [FrontendController::class, 'editPrompt'])
        ->name('user.prompts.edit');

    Route::put('/my-prompts/{id}', [FrontendController::class, 'updatePrompt'])
        ->name('user.prompts.update');

    Route::delete('/my-prompts/{id}', [FrontendController::class, 'destroyPrompt'])
        ->name('user.prompts.destroy');


    // Saved Prompts
    Route::post('/prompts/{prompt}/save', [SavedPromptController::class, 'toggle'])
        ->name('prompts.save');

    Route::get('/my-saved-prompts', [SavedPromptController::class, 'index'])
        ->name('user.saved-prompts');


    // Frontend User Profile
    Route::get('/my-profile', [UserAccountController::class, 'edit'])
        ->name('user.profile.edit');

    Route::patch('/my-profile', [UserAccountController::class, 'update'])
        ->name('user.profile.update');
});


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {

    // Admin Login
    Route::get('/', [AuthController::class, 'showLoginForm'])
        ->name('login');

    Route::get('/login', [AuthController::class, 'showLoginForm'])
        ->name('admin.login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('admin.login.submit');


    /*
    |--------------------------------------------------------------------------
    | ADMIN AUTHENTICATED ROUTES
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth')->group(function () {

        // Dashboard
        Route::get('/dashboard', [HomeController::class, 'index'])
            ->name('admin.dashboard');

        Route::get('/analytics/copies', [HomeController::class, 'copyAnalytics'])
            ->name('admin.analytics.copies');

        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('admin.logout');


        // Admin Profile
        Route::get('/profile', [ProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::put('/profile/update', [ProfileController::class, 'update'])
            ->name('profile.update');

        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])
            ->name('profile.password');

        Route::post('/profile/image', [ProfileController::class, 'updateProfileImage'])
            ->name('profile.image.update');


        /*
        |--------------------------------------------------------------------------
        | ADMIN SETTINGS
        |--------------------------------------------------------------------------
        */

        Route::get('/settings', [SettingsController::class, 'index'])
            ->name('admin.settings.index');

        Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])
            ->name('admin.settings.profile.update');

        Route::post('/settings/logo', [SettingsController::class, 'updateLogo'])
            ->name('admin.settings.logo.update');

        Route::post('/settings/password', [SettingsController::class, 'updatePassword'])
            ->name('admin.settings.password.update');

        Route::post('/settings/login-background', [SettingsController::class, 'updateLoginBackground'])
            ->name('admin.settings.login.bg.update');


        /*
        |--------------------------------------------------------------------------
        | RUN COMMAND SQL
        |--------------------------------------------------------------------------
        */

        Route::get('/run-commands', [RunCommandController::class, 'index'])
            ->name('admin.commands.index');

        Route::post('/commands/store', [RunCommandController::class, 'store'])
            ->name('admin.commands.store');


        /*
        |--------------------------------------------------------------------------
        | SEO SETTINGS
        |--------------------------------------------------------------------------
        */

        Route::get('/seo-settings', [SeoSettingController::class, 'edit'])
            ->name('admin.seo.edit');

        Route::post('/seo-settings', [SeoSettingController::class, 'update'])
            ->name('admin.seo.update');


        /*
        |--------------------------------------------------------------------------
        | ADMIN CATEGORIES
        |--------------------------------------------------------------------------
        */

        Route::resource('categories', CategoryController::class)->names([
            'index'   => 'admin.categories.index',
            'store'   => 'admin.categories.store',
            'update'  => 'admin.categories.update',
            'destroy' => 'admin.categories.destroy',
        ]);


        /*
        |--------------------------------------------------------------------------
        | ADMIN PROMPTS
        |--------------------------------------------------------------------------
        */

        Route::get('/prompts', [PromptController::class, 'index'])
            ->name('admin.prompts.index');

        Route::get('/prompts/create', [PromptController::class, 'create'])
            ->name('admin.prompts.create');

        Route::post('/prompts/store', [PromptController::class, 'store'])
            ->name('admin.prompts.store');

        Route::get('/prompts/{id}', [PromptController::class, 'show'])
            ->name('admin.prompts.show');

        Route::get('/prompts/{id}/edit', [PromptController::class, 'edit'])
            ->name('admin.prompts.edit');

        Route::put('/prompts/{id}', [PromptController::class, 'update'])
            ->name('admin.prompts.update');

        Route::delete('/prompts/{id}', [PromptController::class, 'destroy'])
            ->name('admin.prompts.destroy');

        Route::post('/prompts/{id}/approve', [PromptController::class, 'approve'])
            ->name('admin.prompts.approve');

        Route::post('/prompts/{id}/reject', [PromptController::class, 'reject'])
            ->name('admin.prompts.reject');
    });


});


Route::get('/welcome', [AuthController::class, 'welcome'])
        ->name('admin.welcome.submit');

