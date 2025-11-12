<?php

use App\Http\Controllers\Auth\AuthenticateSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return inertia('Index');
});

// Email Verification Notice Route (GET only - POST and verification are handled by Fortify)
Route::get('/email/verify', function () {
    return inertia('Auth/EmailVerification');
})->middleware('auth')->name('verification.notice');

// Social Authentication Routes
Route::controller(SocialAuthController::class)->group(function () {
    Route::get('/auth/google', 'redirectToGoogle')->name('auth.google');
    Route::get('/auth/google/callback', 'handleGoogleCallback')->name('auth.google.callback');
    Route::get('/auth/facebook', 'redirectToFacebook')->name('auth.facebook');
    Route::get('/auth/facebook/callback', 'handleFacebookCallback')->name('auth.facebook.callback');
});

// Auth Routes (GET only - POST is handled by Fortify)
Route::controller(RegisteredUserController::class)->group(function () {
    Route::get('/register', 'index')->name('register');
});

Route::controller(AuthenticateSessionController::class)->group(function() {
    Route::get('/login', 'index')->name('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', function () {
        return inertia('Auth/ForgotPassword');
    })->name('password.request');

    Route::get('/reset-password/{token}', function (Request $request, string $token) {
        return inertia('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->email,
        ]);
    })->name('password.reset');
});
