<?php

use App\Http\Controllers\Auth\AuthenticateSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Pages\PagesController;
use App\Http\Controllers\Pages\ProductsController;
use App\Http\Controllers\Pages\ProfileController;
use App\Http\Controllers\Pages\CartController;
use App\Models\Catalogue\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

Route::get('/', [PagesController::class, 'index']);

Route::get('/products', [ProductsController::class, 'index'])->name('products.index');

Route::get('/categories/all', function () {
    return redirect()->route('products.index');
})->name('categories.all');

Route::get('/categories/{category:slug}', function (Category $category) {
    return redirect()->route('products.index', ['category' => $category->slug]);
})->name('categories.show');

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

Route::controller(AuthenticateSessionController::class)->group(function () {
    Route::get('/login', 'index')->name('login');
    Route::post('/login', 'store');
   
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


 Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
     Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
     Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});
Route::post('/profile/password', [ProfileController::class, 'updatePassword'])
    ->middleware('auth')
    ->name('profile.password.update');
    Route::get('/profile/security', [ProfileController::class, 'security'])
    ->name('profile.security');
    Route::get('/product/{slug}', [ProductsController::class, 'show'])->name('products.show');

 // User cart page (Inertia)
Route::middleware('auth')->get('/profile/cart', [CartController::class, 'page'])->name('cart.page');

// Cart routes (public for guests, includes session + CSRF)
Route::middleware('web')->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::post('/cart/remove/{id}', [CartController::class, 'remove']);
    Route::post('/cart/clear', [CartController::class, 'clear']);
    Route::get('/cart/page', [CartController::class, 'page']);

    Route::middleware('auth')->group(function () {
        Route::post('/cart/checkout', [CartController::class, 'checkout']);
        Route::post('/cart/merge', [CartController::class, 'merge']);
    });
});

