<?php

use App\Http\Controllers\Auth\AuthenticateSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Pages\PagesController;
use App\Http\Controllers\Pages\ProductsController;
use App\Http\Controllers\Pages\ProfileController;
use App\Http\Controllers\Pages\CartController;
use App\Http\Controllers\Pages\OrderController;
use App\Http\Controllers\Pages\WishlistController;
use App\Http\Controllers\Pages\SettingsController;
use App\Http\Controllers\Pages\SupportController;
use App\Http\Controllers\Pages\PaymentController;
use App\Http\Controllers\Pages\StoreController;
use App\Models\Catalogue\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', [PagesController::class, 'index']);

// API route to check user authentication status
Route::get('/api/user', function (Request $request) {
    return $request->user() ? response()->json($request->user()) : response()->json(['message' => 'Not authenticated'], 401);
});

Route::get('/products', [ProductsController::class, 'index'])->name('products.index');

Route::get('/stores', [StoreController::class, 'index'])->name('stores.index');

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

// Order routes
Route::prefix('profile/orders')->middleware(['auth'])->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::delete('/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::post('/{order}/mpesa-pay', [OrderController::class, 'pay'])->name('orders.mpesa.pay');
    Route::get('/{order}/status', [OrderController::class, 'status'])->name('orders.status');
    Route::post('/{order}/return-request', [OrderController::class, 'requestReturn'])->name('orders.return.request');
});

// Track order routes (public - anyone can track with order number)
// Must be defined BEFORE /orders/{order} to avoid being caught by the wildcard
Route::get('/orders/track', [OrderController::class, 'trackForm'])->name('orders.track.form');
Route::post('/orders/track', [OrderController::class, 'track'])->name('orders.track');

// Direct /orders routes (alternative access)
Route::middleware(['auth'])->group(function () {
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::get('/orders/{order}/status', [OrderController::class, 'status']);
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
});

Route::post('/cart/checkout', [OrderController::class, 'checkout'])->name('checkout');

// Wishlist routes
Route::middleware('auth')->group(function () {
    Route::get('/profile/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/profile/wishlist/{product:id}', [WishlistController::class, 'add'])->name('wishlist.add');
    Route::post('/profile/wishlist-by-slug/{slug}', [WishlistController::class, 'addBySlug'])->name('wishlist.addBySlug');
    Route::delete('/profile/wishlist/{product:id}', [WishlistController::class, 'remove'])->name('wishlist.remove');
    Route::delete('/profile/wishlist-old', [WishlistController::class, 'deleteOld'])->name('wishlist.deleteOld');
});

// Settings routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/profile/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::delete('/profile/settings/account', [SettingsController::class, 'deleteAccount'])->name('settings.deleteAccount');
});

// Payment routes (M-Pesa Daraja)
Route::post('/payments/{order}/mpesa', [PaymentController::class, 'mpesaPay']);
Route::post('/mpesa/callback', [PaymentController::class, 'mpesaCallback']);

// Support routes
Route::middleware(['auth'])->group(function () {
    Route::get('/support/messages', [SupportController::class, 'fetchMessages']);
    Route::post('/support/message', [SupportController::class, 'store']);
});use App\Http\Controllers\Pages\MpesaController;

Route::post('/m-pesa/validation', [PaymentController::class, 'validation']);
Route::post('/m-pesa/confirmation', [PaymentController::class, 'confirmation']);

// Product ratings (requires auth)
Route::middleware('auth')->group(function () {
    Route::post('/products/{product:id}/rating', [\App\Http\Controllers\Pages\ProductsController::class, 'storeRating']);
    Route::delete('/products/{product:id}/rating', [\App\Http\Controllers\Pages\ProductsController::class, 'deleteRating']);
    Route::post('/products/{product:id}/comment', [\App\Http\Controllers\Pages\ProductsController::class, 'storeComment']);
});
