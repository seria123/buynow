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
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Models\Sales\Promotion;
use App\Services\PromotionService;

class CartController extends Controller
{
    protected $promotionService;

    public function __construct()
    {
        $this->promotionService = app(PromotionService::class);
    }

    public function page()
{
    $cartItems = Auth::check()
        ? Cart::where('user_id', Auth::id())->get()
        : collect(session()->get('cart', []));

    $subtotal = $cartItems->sum(fn($item) => $item['price'] * $item['quantity']);

    return inertia('Cart/Index', [
        'cart' => $cartItems,
        'subtotal' => $subtotal,
        'cart_count' => $cartItems->sum('quantity'),
    ]);
}

    // -----------------------------
    // Show cart page
    // -----------------------------
    public function index(Request $request)
    {
        // Get cart items (DB for logged in, session for guest)
        $cartItems = Auth::check()
            ? Cart::where('user_id', Auth::id())->get()->map(fn($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'variant_id' => $item->variant_id,
                'name' => $item->product_name,
                'price' => $item->price,
                'quantity' => $item->quantity,
            ])
            : collect($request->session()->get('cart', []));

        // Cart subtotal
        $subtotal = $cartItems->sum(fn($item) => $item['price'] * $item['quantity']);

        // Applied promo from session
        $appliedPromoId = $request->session()->get('applied_promo_id');
        $appliedPromoCode = $request->session()->get('applied_promo_code');
        $promoDiscount = 0;

        $promotion = $appliedPromoId ? Promotion::find($appliedPromoId) : null;
        if ($promotion) {
            $promoDiscount = $this->promotionService->calculateDiscount($promotion, $subtotal, $cartItems);
        }

        // Automatic promotions (only if no manual promo)
        $automaticPromotions = $this->promotionService->getAllApplicablePromotions($subtotal, $cartItems, Auth::user());
        $automaticDiscount = 0;
        $bestAutomatic = null;
        if (!$promotion && $automaticPromotions->isNotEmpty()) {
            $bestAutomatic = $automaticPromotions->first();
            $automaticDiscount = $bestAutomatic['discount_amount'];
        }

        // Free shipping
        $freeShippingPromo = $this->promotionService->getActivePromotions()
            ->where('promotion_type', 'free_shipping')
            ->first();
        $freeShippingEligible = $freeShippingPromo
            ? $this->promotionService->getFreeShippingEligible($freeShippingPromo, $subtotal)
            : false;
        $shippingCost = $freeShippingEligible ? 0 : 0.00;

        // Total after discounts
        $totalDiscount = $promoDiscount > 0 ? $promoDiscount : $automaticDiscount;
        $total = max(0, $subtotal - $totalDiscount + $shippingCost);

        // Return JSON for AJAX requests, Inertia response for page loads
        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'cart' => $cartItems,
                'cart_count' => $cartItems->sum('quantity'),
                'subtotal' => $subtotal,
                'promo_discount' => $promoDiscount,
                'automatic_discount' => $automaticDiscount,
                'total' => $total,
                'free_shipping_eligible' => $freeShippingEligible,
                'applied_promo_code' => $appliedPromoCode,
                'automatic_promotions' => $automaticPromotions->map(fn($p) => [
                    'id' => $p['promotion']->id,
                    'name' => $p['promotion']->name,
                    'promotion_type' => $p['promotion']->promotion_type,
                    'discount_amount' => $p['discount_amount'],
                ]),
            ]);
        }

        return inertia('Cart/Index', [
            'cart_items' => $cartItems,
            'cart_count' => $cartItems->sum('quantity'),
            'subtotal' => $subtotal,
            'promo_discount' => $promoDiscount,
            'automatic_discount' => $automaticDiscount,
            'total' => $total,
            'free_shipping_eligible' => $freeShippingEligible,
            'applied_promo_code' => $appliedPromoCode,
            'automatic_promotions' => $automaticPromotions->map(fn($p) => [
                'id' => $p['promotion']->id,
                'name' => $p['promotion']->name,
                'promotion_type' => $p['promotion']->promotion_type,
                'discount_amount' => $p['discount_amount'],
            ]),
        ]);
    }

    // -----------------------------
    // Add item to cart
    // -----------------------------
    public function add(Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'product_slug' => 'required|string',
            'variant_id' => 'nullable|integer',
        ]);

        $product = Product::with('category')->where('slug', $request->product_slug)->firstOrFail();
        $variant = $request->variant_id ? ProductVariant::find($request->variant_id) : null;

        if ($variant && $variant->product_id !== $product->id) {
            return response()->json(['message' => 'Invalid variant'], 422);
        }

        if (Auth::check()) {
            $this->addToDatabaseCart(Auth::id(), $product, $request->quantity, $variant);
        } else {
            $this->addToSessionCart($request, $product, $request->quantity, $variant);
        }

        // Return JSON for AJAX requests, redirect for regular form submissions
        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Item added to cart',
                'cart_count' => Auth::check()
                    ? Cart::where('user_id', Auth::id())->sum('quantity')
                    : collect($request->session()->get('cart', []))->sum('quantity'),
            ]);
        }

        return redirect()->route('cart.index');
    }

    private function addToDatabaseCart($userId, $product, $quantity, $variant = null)
    {
        $cartItem = Cart::firstOrNew([
            'user_id' => $userId,
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
        ]);

        if (!$cartItem->exists) {
            $cartItem->price = 0.00;
        } else {
            $cartItem->price = $variant?->price ?? $product->price;
        }

        $cartItem->quantity = ($cartItem->quantity ?? 0) + $quantity;
        $cartItem->product_name = $variant?->name ?? $product->name;
        $cartItem->category_name = $product->category->name ?? null;
        $cartItem->save();
    }

    private function addToSessionCart(Request $request, $product, $quantity, $variant = null)
    {
        $cart = $request->session()->get('cart', []);
        $variantId = $variant?->id;
        $found = false;

        foreach ($cart as &$item) {
            if ($item['product_id'] === $product->id && ($item['variant_id'] ?? null) === $variantId) {
                $item['quantity'] += $quantity;
                $item['price'] = $variant?->price ?? $product->price;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $cart[] = [
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'product_name' => $variant?->name ?? $product->name,
                'category_name' => $product->category->name ?? null,
                'price' => $variant?->price ?? $product->price,
                'quantity' => $quantity,
            ];
        }

        $request->session()->put('cart', $cart);
        $request->session()->save();
    }

    // -----------------------------
    // Update cart item quantity
    // -----------------------------
    public function updateQuantity(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'variant_id' => 'nullable|integer',
        ]);

        if (Auth::check()) {
            $cartItem = Cart::where('user_id', Auth::id())
                ->where('product_id', $request->product_id)
                ->where('variant_id', $request->variant_id)
                ->first();

            if ($cartItem) {
                $cartItem->quantity = $request->quantity;
                $cartItem->save();
            }
        } else {
            $cart = $request->session()->get('cart', []);
            foreach ($cart as &$item) {
                if ($item['product_id'] == $request->product_id && ($item['variant_id'] ?? null) == $request->variant_id) {
                    $item['quantity'] = $request->quantity;
                    break;
                }
            }
            $request->session()->put('cart', $cart);
            $request->session()->save();
        }

        // Return JSON for AJAX requests, redirect for regular form submissions
        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Quantity updated',
                'cart_count' => Auth::check()
                    ? Cart::where('user_id', Auth::id())->sum('quantity')
                    : collect($request->session()->get('cart', []))->sum('quantity'),
            ]);
        }

        return redirect()->route('cart.index');
    }

    // -----------------------------
    // Remove item from cart
    // -----------------------------
    public function remove(Request $request, $productId)
    {
        if (Auth::check()) {
            Cart::where('user_id', Auth::id())->where('product_id', $productId)->delete();
        } else {
            $cart = $request->session()->get('cart', []);
            $cart = array_filter($cart, fn($item) => $item['product_id'] != $productId);
            $request->session()->put('cart', array_values($cart));
        }

        // Return JSON for AJAX requests, redirect for regular form submissions
        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart',
                'cart_count' => Auth::check()
                    ? Cart::where('user_id', Auth::id())->sum('quantity')
                    : collect($request->session()->get('cart', []))->sum('quantity'),
            ]);
        }

        return redirect()->route('cart.index');
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

        $request->session()->forget(['applied_promo_code','applied_promo_id','promo_discount']);
        $request->session()->save();

        // Return JSON for AJAX requests, redirect for regular form submissions
        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Cart cleared',
                'cart_count' => 0,
            ]);
        }

        return redirect()->route('cart.index');
    }

    // -----------------------------
    // Apply promo code
    // -----------------------------
    public function applyPromo(Request $request)
    {
        $request->validate(['code' => 'required|string|max:50']);

        $cartItems = Auth::check()
            ? Cart::where('user_id', Auth::id())->get()
            : collect($request->session()->get('cart', []));

        $orderTotal = $cartItems->sum(fn($item) => $item['price'] * $item['quantity']);
        $result = $this->promotionService->applyPromoCode($request->code, $orderTotal, $cartItems, Auth::user());

        if ($result['valid']) {
            $request->session()->put([
                'applied_promo_code' => $result['promotion_code'],
                'applied_promo_id' => $result['promotion']->id,
                'promo_discount' => $result['discount_amount']
            ]);
            $request->session()->save();

            // Return JSON for AJAX requests, redirect for regular form submissions
            if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'valid' => true,
                    'promotion_code' => $result['promotion_code'],
                    'discount_amount' => $result['discount_amount'],
                    'message' => 'Promo applied!',
                ]);
            }

            return redirect()->route('cart.index')->with('success', 'Promo applied!');
        }

        // Return JSON for AJAX requests, redirect for regular form submissions
        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'valid' => false,
                'message' => $result['message'] ?? 'Invalid promo code',
            ], 422);
        }

        return redirect()->route('cart.index')->with('error', $result['message'] ?? 'Invalid promo code');
    }

    // -----------------------------
    // Remove promo code
    // -----------------------------
    public function removePromo(Request $request)
    {
        $request->session()->forget(['applied_promo_code', 'applied_promo_id', 'promo_discount']);
        $request->session()->save();

        // Return JSON for AJAX requests, redirect for regular form submissions
        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Promo code removed',
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Promo code removed');
    }
}