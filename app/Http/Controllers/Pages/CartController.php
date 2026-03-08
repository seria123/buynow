<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Sales\Cart;
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
    $userId = Auth::id();
    $cartItems = Cart::where('user_id', $userId)->get();

    if ($cartItems->isEmpty()) {
        return response()->json(['message' => 'Cart is empty'], 400);
    }

    DB::beginTransaction();
    try {
        // Create Order
        $order = Order::create([
            'id' => Str::uuid(),
            'user_id' => $userId,
            'total_amount' => 0,
            'status' => 'pending',
        ]);

        $total = 0;
        foreach ($cartItems as $item) {
            $product = Product::find($item->product_id);
            $subtotal = $product->price * $item->quantity;

            OrderItem::create([
                'id' => Str::uuid(),
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $item->quantity,
                'price' => $product->price,
                'subtotal' => $subtotal,
            ]);

            $total += $subtotal;
        }

        // Update total amount
        $order->update(['total_amount' => $total]);

        // Clear cart
        Cart::where('user_id', $userId)->delete();

        DB::commit();

        return response()->json(['order_id' => $order->id]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Checkout failed: ' . $e->getMessage()], 500);
    }
}
}
