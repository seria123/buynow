<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback()
    {
        try {
            $socialUser = Socialite::driver('google')->user();

            return $this->handleSocialUser($socialUser, 'google');
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Unable to login with Google. Please try again.');
        }
    }

    /**
     * Redirect the user to the Facebook authentication page.
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')
            ->scopes(['email'])
            ->redirect();
    }

    /**
     * Obtain the user information from Facebook.
     */
    public function handleFacebookCallback()
    {
        try {
            $socialUser = Socialite::driver('facebook')->user();

            return $this->handleSocialUser($socialUser, 'facebook');
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Unable to login with Facebook. Please try again.');
        }
    }

    /**
     * Handle the social user authentication.
     */
    protected function handleSocialUser($socialUser, string $provider)
    {
        $socialEmail = $socialUser->getEmail();
        $socialId = $socialUser->getId();

        if (!$socialEmail) {
            return redirect()->route('login')
                ->with('error', 'Unable to get email from ' . ucfirst($provider) . '. Please ensure your account has an email address.');
        }

        // Check if user exists by provider ID first
        $user = User::where("{$provider}_id", $socialId)->first();

        // If not found by provider ID, check by email (for account linking)
        if (!$user) {
            $user = User::where('email', $socialEmail)->first();
        }

        $needsVerification = false;

        if ($user) {
            // Existing user (login) - link the provider ID if not already linked
            if (!$user->{"{$provider}_id"}) {
                $user->{"{$provider}_id"} = $socialId;
            }

            // Update name if changed
            if ($socialUser->getName() && $user->name !== $socialUser->getName()) {
                $user->name = $socialUser->getName();
            }

            // If email is not verified, send verification email
            if (!$user->hasVerifiedEmail()) {
                $needsVerification = true;
                $user->sendEmailVerificationNotification();
            }

            $user->save();
        } else {
            // Create new user (registration) - set avatar from social provider if available
            $needsVerification = true;
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialEmail,
                'email' => $socialEmail,
                "{$provider}_id" => $socialId,
                'password' => Hash::make(Str::random(32)), // Random password for social users
                // Don't set email_verified_at - user must verify manually
            ]);

            // Add avatar from social provider URL using Spatie Media Library
            if ($socialUser->getAvatar()) {
                try {
                    $user->addMediaFromUrl($socialUser->getAvatar())
                        ->toMediaCollection('avatar');
                } catch (\Exception $e) {
                    Log::error('Failed to add avatar: ' . $e->getMessage());
                }
            }

            // Send verification email for new social users
            $user->sendEmailVerificationNotification();
        }

        // Log the user in
        Auth::login($user, true);

        // If email is not verified, redirect to verification screen
        if ($needsVerification) {
            return redirect()->route('verification.notice')
                ->with('status', 'verification-link-sent');
        }

        // Redirect to home (or intended page) if already verified
        return redirect()->intended('/');
    }
}
