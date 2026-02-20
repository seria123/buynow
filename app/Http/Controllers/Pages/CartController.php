<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\Catalogue\Product;
use App\Models\Order;
use App\Models\OrderItem;

class CartController extends Controller
{
    // -----------------------------
    // Get cart items
    // -----------------------------
    public function index(Request $request)
    {
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())
                        ->get()
                        ->map(function ($item) {
                            return [
                                'product_id' => $item->product_id,
                                'name' => $item->product_name,
                                'price' => $item->price,
                                'quantity' => $item->quantity,
                            ];
                        });
        } else {
            $cart = collect($request->session()->get('cart', []));
        }

        $cartCount = $cart->sum('quantity');

        return response()->json([
            'cart' => $cart,
            'cart_count' => $cartCount,
            'logged_in' => Auth::check(),
        ]);
    }

    public function page()
    {
        return inertia('Cart/Index');
    }

    // -----------------------------
    // Add item to cart
    // -----------------------------
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::with('category')->findOrFail($request->product_id);

        if (Auth::check()) {
            $this->addToDatabaseCart(Auth::id(), $product, $request->quantity);
        } else {
            $this->addToSessionCart($request, $product, $request->quantity);
        }

        return $this->index($request);
    }

    private function addToDatabaseCart($userId, $product, $quantity)
    {
        $cartItem = Cart::firstOrNew([
            'user_id' => $userId,
            'product_id' => $product->id,
        ]);

        $cartItem->quantity = ($cartItem->quantity ?? 0) + $quantity;
        $cartItem->product_name = $product->name;
        $cartItem->category_name = $product->category->name ?? null;
        $cartItem->price = $product->price;
        $cartItem->save();
    }

    private function addToSessionCart(Request $request, $product, $quantity)
    {
        $cart = $request->session()->get('cart', []);
        $found = false;

        foreach ($cart as &$item) {
            if ($item['product_id'] == $product->id) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $cart[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'category_name' => $product->category->name ?? null,
                'price' => $product->price,
                'quantity' => $quantity,
            ];
        }

        $request->session()->put('cart', $cart);
        $request->session()->save(); // important
    }

    // -----------------------------
    // Remove item from cart
    // -----------------------------
    public function remove($productId, Request $request)
    {
        if (Auth::check()) {
            Cart::where('user_id', Auth::id())->where('product_id', $productId)->delete();
        } else {
            $cart = $request->session()->get('cart', []);
            $cart = array_filter($cart, fn($item) => $item['product_id'] != $productId);
            $request->session()->put('cart', array_values($cart));
        }

        return $this->index($request);
    }

    // -----------------------------
    // Clear cart
    // -----------------------------
    public function clear(Request $request)
    {
        if (Auth::check()) {
            Cart::where('user_id', Auth::id())->delete();
        } else {
            $request->session()->forget('cart');
        }

        return $this->index($request);
    }

    // -----------------------------
    // Checkout
    // -----------------------------
    public function checkout(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Login required'], 401);
        }

        $cart = Cart::where('user_id', $user->id)->get();
        if ($cart->isEmpty()) {
            return response()->json(['error' => 'Cart empty'], 400);
        }

        $total = $cart->sum(fn($item) => $item->price * $item->quantity);

        try {
            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $total,
                'status' => 'pending',
                'shipping_address' => $request->shipping_address ?? null,
            ]);

            foreach ($cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'category_name' => $item->category_name,
                ]);
            }

            Cart::where('user_id', $user->id)->delete();

            return response()->json(['order_id' => $order->id]);

        } catch (\Exception $e) {
            \Log::error('Checkout failed', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Checkout failed'], 500);
        }
    }
}
