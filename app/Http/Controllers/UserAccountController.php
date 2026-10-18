<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Storage;
use App\Models\FrontendUser;

class UserAccountController extends Controller
{
    public function edit()
    {
        
        $user = Auth::guard('frontend')->user(); 
        
        return view('pages.front.edit-account', compact('user'));
    }

    public function update(Request $request)
    {
        
        $user = Auth::guard('frontend')->user();

        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:frontend_users,email,' . $user->id], 
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        
        if ($request->hasFile('profile_image')) {
            
            if (!empty($user->profile_image) && file_exists(public_path($user->profile_image))) {
                @unlink(public_path($user->profile_image));
            }

            $image = $request->file('profile_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            
           
            $image->move(public_path('uploads/profile'), $imageName);

           //set path for database
            $user->profile_image = 'uploads/profile/' . $imageName;
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        return back()->with('success', 'Profile updated successfully!');
    }
}