<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Sales\Cart;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductVariant;
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
                                'variant_id' => $item->variant_id,
                                'name' => $item->product_name,
                                'price' => $item->price,
                                'quantity' => $item->quantity,
                            ];
                        });
        } else {
            $cart = collect($request->session()->get('cart', []));
        }

        $cartCount = $cart->sum('quantity');

        // Get applied promo from session
        $appliedPromo = $request->session()->get('applied_promo');
        $promoDiscount = $request->session()->get('promo_discount', 0);

        return response()->json([
            'cart' => $cart,
            'cart_count' => $cartCount,
            'logged_in' => Auth::check(),
            'applied_promo' => $appliedPromo,
            'promo_discount' => $promoDiscount,
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
        'quantity' => 'required|integer|min:1',
    ]);

    $productSlug = $request->input('product_slug');
    $variantId = $request->input('variant_id');

    if (!$productSlug) {
        return response()->json(['message' => 'Product slug is required'], 422);
    }

    // Load product by slug
    $product = Product::with('category')->where('slug', $productSlug)->firstOrFail();

    // Load variant if provided
    $variant = null;
    if ($variantId) {
        $variant = ProductVariant::find($variantId);
        if (!$variant || $variant->product_id !== $product->id) {
            return response()->json(['message' => 'Invalid variant'], 422);
        }
    }

    if (Auth::check()) {
        $this->addToDatabaseCart(Auth::id(), $product, $request->quantity, $variant);
    } else {
        $this->addToSessionCart($request, $product, $request->quantity, $variant);
    }

    return $this->index($request);
}
   private function addToDatabaseCart($userId, $product, $quantity, $variant = null)
{
   $price = $variant?->price ?? $product->price;
$productName = $variant?->name ?? $product->name;
    $cartItem = Cart::firstOrNew([
        'user_id' => $userId,
        'product_id' => $product->id,
        'variant_id' => $variant ? $variant->id : null,
    ]);

    $cartItem->quantity = ($cartItem->quantity ?? 0) + $quantity;
    $cartItem->product_name = $productName;
    $cartItem->category_name = $product->category->name ?? null;
    $cartItem->price = (int)$price;
    $cartItem->save();
}

   private function addToSessionCart(Request $request, $product, $quantity, $variant = null)
{
    $price = $variant && $variant->price ? $variant->price : $product->price;
        $productName = $variant ? $variant->name : $product->name;
        $variantId = $variant ? $variant->id : null;
        
        $cart = $request->session()->get('cart', []);
        $found = false;

        foreach ($cart as &$item) {
            if ($item['product_id'] == $product->id && ($item['variant_id'] ?? null) == $variantId) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $cart[] = [
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'product_name' => $productName,
                'category_name' => $product->category->name ?? null,
                'price' => $price,
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

        // Also clear promo
        $request->session()->forget('applied_promo');
        $request->session()->forget('promo_discount');

        return $this->index($request);
    }

    // -----------------------------
    // Apply promo code
    // -----------------------------
    public function applyPromo(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'order_total' => 'required|numeric|min:0',
        ]);

        $promoCode = $request->input('code');
        $orderTotal = $request->input('order_total');

        // Use the promotion service to validate and apply
        $promotionService = app(\App\Services\PromotionService::class);
        $result = $promotionService->applyPromoCode($promoCode, $orderTotal);

        if ($result['valid']) {
            // Store in session
            $request->session()->put('applied_promo', $result['promotion_code']);
            $request->session()->put('promo_discount', $result['discount_amount']);

            return response()->json([
                'valid' => true,
                'message' => 'Promo code applied successfully',
                'promotion_code' => $result['promotion_code'],
                'discount_amount' => $result['discount_amount'],
            ]);
        }

        return response()->json([
            'valid' => false,
            'message' => $result['message'] ?? 'Invalid promo code',
        ], 422);
    }

    // -----------------------------
    // Remove promo code
    // -----------------------------
    public function removePromo(Request $request)
    {
        $request->session()->forget('applied_promo');
        $request->session()->forget('promo_discount');

        return response()->json([
            'valid' => true,
            'message' => 'Promo code removed',
        ]);
    }

    // -----------------------------
    // Merge guest cart with user cart on login
    // -----------------------------
    public function merge(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $guestCart = $request->session()->get('cart', []);
        
        if (empty($guestCart)) {
            return $this->index($request);
        }

        foreach ($guestCart as $guestItem) {
            $existingItem = Cart::where('user_id', Auth::id())
                ->where('product_id', $guestItem['product_id'])
                ->where('variant_id', $guestItem['variant_id'] ?? null)
                ->first();

            if ($existingItem) {
                $existingItem->quantity += $guestItem['quantity'];
                $existingItem->save();
            } else {
                Cart::create([
                    'user_id' => Auth::id(),
                    'product_id' => $guestItem['product_id'],
                    'variant_id' => $guestItem['variant_id'] ?? null,
                    'product_name' => $guestItem['product_name'],
                    'category_name' => $guestItem['category_name'] ?? null,
                    'price' => $guestItem['price'],
                    'quantity' => $guestItem['quantity'],
                ]);
            }
        }

        // Clear guest cart
        $request->session()->forget('cart');

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
