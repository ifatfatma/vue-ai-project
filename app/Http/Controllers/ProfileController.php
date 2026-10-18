<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    // Profile Page View Render
    public function edit()
    {
        $user = Auth::user();
        return view('pages.admin.profile', compact('user'));
    }

    // Update Profile Info (Name & Email)
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        return back()->with('success', 'Profile details updated successfully!');
    }

    // Update Profile Picture / Avatar
    public function updateProfileImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        if ($request->hasFile('profile_image')) {
            $destinationPath = public_path('uploads/profile');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $image = $request->file('profile_image');
            $imageName = time() . '_profile.' . $image->getClientOriginalExtension();
            $image->move($destinationPath, $imageName);
            
            $user->update([
                'profile_image' => 'uploads/profile/' . $imageName
            ]);
        }

        return back()->with('success', 'Profile picture updated successfully!');
    }

    // Update Password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'      => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Current password matches no records.']);
        }

        Auth::user()->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'Password updated successfully!');
    }
}