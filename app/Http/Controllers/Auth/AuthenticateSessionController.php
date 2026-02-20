<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use Inertia\Inertia;

class AuthenticateSessionController extends Controller
{
    public function index()
    {
        return Inertia::render('Auth/Login');
    }



 public function store(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {

        // 1️⃣ Grab guest cart from session BEFORE regenerating
        $sessionCart = $request->session()->get('cart', []);

        // 2️⃣ Merge into database if any items exist
        if (!empty($sessionCart)) {
            $userId = Auth::id();
            foreach ($sessionCart as $item) {
                $cartItem = Cart::firstOrNew([
                    'user_id' => $userId,
                    'product_id' => $item['product_id'],
                ]);

                $cartItem->quantity = ($cartItem->quantity ?? 0) + $item['quantity'];
                $cartItem->product_name = $item['product_name'];
                $cartItem->category_name = $item['category_name'] ?? null;
                $cartItem->price = $item['price'];
                $cartItem->save();
            }

            // 3️⃣ Clear the session cart AFTER merging
            $request->session()->forget('cart');
        }

        // 4️⃣ Now regenerate the session
        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    return back()->withErrors([
        'email' => 'Invalid credentials.',
    ]);
}

      protected function mergeGuestCart(Request $request)
{
    $userId = $request->user()->id;
    $guestCart = $request->session()->get('cart', []);

    if (empty($guestCart)) return;

    foreach ($guestCart as $item) {
        $existing = Cart::where('user_id', $userId)
                        ->where('product_id', $item['product_id'])
                        ->first();

        if ($existing) {
            $existing->quantity += $item['quantity'];
            $existing->save();
        } else {
            Cart::create([
                'user_id' => $userId,
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'category_name' => $item['category_name'] ?? null,
                'price' => $item['price'],
                'quantity' => $item['quantity'],
            ]);
        }
    }

    // Clear session cart
    $request->session()->forget('cart');
}   
}
