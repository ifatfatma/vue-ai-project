<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\SeoSetting;
use App\Models\Setting;

class SettingsController extends Controller
{
    /**
     * Display Account & System Settings page.
     */
    public function index()
    {
        $user = auth()->user();

        $seo = SeoSetting::first() ?? new SeoSetting();

        $logo = $user->logo ?? null;

        return view('pages.admin.setting', compact(
            'user',
            'seo',
            'logo'
        ));
    }

    /**
     * Update Admin Profile Details.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        return back()->with(
            'success',
            'Profile information updated successfully!'
        );
    }

    /**
     * Update Website Logo.
     */
    public function updateLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = auth()->user();

        if ($request->hasFile('logo')) {

            $destinationPath = public_path('uploads/logos');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $imageName = time() . '_' .
                uniqid() . '.' .
                $request->file('logo')->extension();

            $request->file('logo')->move(
                $destinationPath,
                $imageName
            );

            $user->logo = 'uploads/logos/' . $imageName;
            $user->save();
        }

        return back()->with(
            'success',
            'Website logo updated successfully!'
        );
    }

    /**
     * Update Admin Password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check(
            $request->current_password,
            $user->password
        )) {
            return back()->withErrors([
                'current_password' => 'Current password does not match.'
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with(
            'success',
            'Password changed successfully!'
        );
    }

    /**
     * Update Admin Login Background.
     */
    public function updateLoginBackground(Request $request)
    {
        $request->validate([
            'login_background' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('login_background')) {

            $imagePath = $request->file('login_background')
                ->store('settings', 'public');

            Setting::updateOrCreate(
                ['key' => 'login_background'],
                ['value' => $imagePath]
            );
        }

        return back()->with(
            'success',
            'Admin login background updated successfully!'
        );
    }
}