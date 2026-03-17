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
            'phone' => 'nullable|string|min:10|max:20',
            'avatar' => 'nullable|image|max:2048', // max 2MB
        ]);

        // Handle avatar upload via Spatie Media Library
        if ($request->hasFile('avatar')) {
            try {
                // Delete old avatar if it exists
                $media = $user->getFirstMedia('avatar');
                if ($media && $media->exists) {
                    $user->clearMediaCollection('avatar');
                }

                // Add new avatar media
                $user->addMediaFromRequest('avatar')
                    ->toMediaCollection('avatar');
            } catch (\Exception $e) {
                // Log error but continue with other updates
                \Log::error('Avatar upload error: ' . $e->getMessage());
            }
        }

        // Update user fields (excluding avatar - handled by Spatie)
        $user->update([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        // Refresh the user to get latest data including avatar
        $user->refresh();

        // Redirect to overview page
        return to_route('profile.index');
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