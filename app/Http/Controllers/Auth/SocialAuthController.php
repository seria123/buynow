<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

        // If not found by provider ID, check by email
        if (!$user) {
            $user = User::where('email', $socialEmail)->first();
        }

        if ($user) {
            // Update provider ID if not set
            if (!$user->{"{$provider}_id"}) {
                $user->{"{$provider}_id"} = $socialId;
            }

            // Update avatar if available and not set
            if ($socialUser->getAvatar() && !$user->avatar) {
                $user->avatar = $socialUser->getAvatar();
            }

            // Update name if changed
            if ($socialUser->getName() && $user->name !== $socialUser->getName()) {
                $user->name = $socialUser->getName();
            }

            // Mark email as verified if from social provider
            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            $user->save();
        } else {
            // Create new user
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialEmail,
                'email' => $socialEmail,
                "{$provider}_id" => $socialId,
                'avatar' => $socialUser->getAvatar(),
                'password' => Hash::make(Str::random(32)), // Random password for social users
                'email_verified_at' => now(), // Social providers verify emails
            ]);
        }

        // Log the user in
        Auth::login($user, true);

        // Redirect to home (or intended page)
        return redirect()->intended('/');
    }
}
