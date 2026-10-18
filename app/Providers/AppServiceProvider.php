<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // 🌟 Admin navbar aur profile ke liye global variables set kar rahe hain
        View::composer('*', function ($view) {
            $adminUser = Auth::user();
            $profileImageUrl = null;
            $avatarUrl = '';

            if ($adminUser) {
                // Agar database me profile_image hai
                if (!empty($adminUser->profile_image)) {
                    $profileImageUrl = asset($adminUser->profile_image);
                }

                // Fallback ui-avatars URL agar image na ho
                $nameEnc = urlencode($adminUser->name ?? 'Admin');
                $avatarUrl = "https://ui-avatars.com/api/?name={$nameEnc}&background=0D6EFD&color=fff";
            }

            $view->with([
                'adminUser' => $adminUser,
                'profileImageUrl' => $profileImageUrl,
                'avatarUrl' => $avatarUrl,
            ]);
        });
    }
}