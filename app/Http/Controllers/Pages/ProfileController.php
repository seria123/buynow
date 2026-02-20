<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileController extends Controller
{

  public function index()
    {
        $user = Auth::user();

        return Inertia::render('Profile/Index', [
            'user' => $user,  // pass authenticated user to Vue
        ]);
    }


     // Overview page
    
    public function edit()
    {
        $user = Auth::user();

        return Inertia::render('Profile/Edit', [
            'user' => $user,  // pass authenticated user to Vue
        ]);
    }
      public function update(Request $request)
    {
        $user = Auth::user();

        // Validate form fields
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
           'phone_number' => 'nullable|string|digits_between:10,15',
            'avatar' => 'nullable|image|max:2048', // max 2MB
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if it exists
            if ($user->avatar) {
                Storage::delete($user->avatar);
            }

            // Store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        // Update user
        $user->update($validated);
        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully!');
    }
    public function updatePassword(Request $request)
{
    $user = $request->user();

    // Validate input
    $request->validate([
        'current_password' => ['required'],
        'new_password' => [
            'required',
            'string',
            'min:8',
            'confirmed', // requires new_password_confirmation
        ],
    ]);

    // Check current password
    if (!Hash::check($request->current_password, $user->password)) {
        return back()->withErrors([
            'current_password' => 'The current password is incorrect.',
        ]);
    }

    // Update password
    $user->update([
        'password' => Hash::make($request->new_password),
    ]);

    return back()->with('success', 'Password updated successfully.');
}
public function security()
{
    return inertia('Profile/Security');
}
      }