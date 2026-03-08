<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return Inertia::render('Settings/Index', [
            'settings' => [
                'email_notifications' => $user->email_notifications ?? true,
                'sms_notifications'   => $user->sms_notifications ?? false,
                'order_updates'       => $user->order_updates ?? true,
                'promo_emails'        => $user->promo_emails ?? true,
                'newsletter'          => $user->newsletter ?? false,
                'two_factor'          => $user->two_factor_confirmed_at !== null,
                'language'            => $user->language ?? 'en',
                'currency'            => $user->currency ?? 'KES',
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'sms_notifications'   => 'boolean',
            'order_updates'       => 'boolean',
            'promo_emails'        => 'boolean',
            'newsletter'          => 'boolean',
            'language'            => 'string|in:en,sw,fr',
            'currency'            => 'string|in:KES,USD,EUR,GBP',
        ]);

        $user->fill($validated)->save();

        return redirect()->route('settings.index')->with('success', 'Settings saved successfully.');
    }

    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Your account has been deleted.');
    }
}
