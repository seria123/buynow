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

    // -----------------------------
    // Get cart items
    // -----------------------------
    public function index(Request $request)
    {
        $cartItems = null;
        $cartCollection = null;

        if (Auth::check()) {
            $cartCollection = Cart::where('user_id', Auth::id())
                        ->get()
                        ->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'product_id' => $item->product_id,
                                'variant_id' => $item->variant_id,
                                'name' => $item->product_name,
                                'price' => $item->price,
                                'quantity' => $item->quantity,
                            ];
                        });
            $cartItems = Cart::where('user_id', Auth::id())->get();
        } else {
            $cartCollection = collect($request->session()->get('cart', []));
            $cartItems = $request->session()->get('cart', []);
        }

        $cartCount = $cartCollection->sum('quantity');

        // Calculate subtotal
        $subtotal = $cartCollection->sum(fn($item) => $item['price'] * $item['quantity']);

        // Get applied promo from session
        $appliedPromoCode = $request->session()->get('applied_promo_code');
        $appliedPromoId = $request->session()->get('applied_promo_id');
        $promoDiscount = $request->session()->get('promo_discount', 0);

        // Check for automatic promotions
        $automaticPromo = null;
        $automaticDiscount = 0;
        $automaticPromotions = $this->promotionService->getAllApplicablePromotions(
            $subtotal,
            $cartCollection,
            Auth::user()
        );

        if ($automaticPromotions->isNotEmpty()) {
            $bestAutomatic = $automaticPromotions->first();
            $automaticPromo = $bestAutomatic['promotion'];
            $automaticDiscount = $bestAutomatic['discount_amount'];
        }

        // Check for free shipping
        $freeShippingEligible = false;
        $freeShippingPromotion = $this->promotionService->getActivePromotions()
            ->where('promotion_type', 'free_shipping')
            ->first();

        if ($freeShippingPromotion) {
            $freeShippingEligible = $this->promotionService->getFreeShippingEligible($freeShippingPromotion, $subtotal);
        }

        return response()->json([
            'cart' => $cartCollection,
            'cart_count' => $cartCount,
            'subtotal' => $subtotal,
            'logged_in' => Auth::check(),
            'applied_promo_code' => $appliedPromoCode,
            'applied_promo_id' => $appliedPromoId,
            'promo_discount' => $promoDiscount,
            'automatic_promo' => $automaticPromo ? [
                'id' => $automaticPromo->id,
                'name' => $automaticPromo->name,
                'promotion_type' => $automaticPromo->promotion_type,
                'description' => $this->promotionService->getDiscountDescription($automaticPromo),
            ] : null,
            'automatic_discount' => $automaticDiscount,
            'free_shipping_eligible' => $freeShippingEligible,
            'automatic_promotions' => $automaticPromotions->map(fn($p) => [
                'id' => $p['promotion']->id,
                'name' => $p['promotion']->name,
                'promotion_type' => $p['promotion']->promotion_type,
                'discount_amount' => $p['discount_amount'],
            ])->values(),
        ]);
    }

    public function page(Request $request)
    {
        // Calculate totals
        $subtotal = 0;
        $promoDiscount = 0;
        $automaticDiscount = 0;
        $total = 0;
        $freeShippingEligible = false;

        $cartItems = Auth::check() 
            ? Cart::where('user_id', Auth::id())->get()
            : collect($request->session()->get('cart', []));

        $subtotal = $cartItems->sum(fn($item) => $item['price'] * $item['quantity'] ?? $item->price * $item->quantity);

        // Get promo code discount
        $appliedPromoId = $request->session()->get('applied_promo_id');
        if ($appliedPromoId) {
            $promotion = Promotion::find($appliedPromoId);
            if ($promotion) {
                $promoDiscount = $this->promotionService->calculateDiscount($promotion, $subtotal, $cartItems);
            }
        }

        // Get automatic discounts
        $automaticPromotions = $this->promotionService->getAllApplicablePromotions(
            $subtotal,
            $cartItems,
            Auth::user()
        );

        if ($automaticPromotions->isNotEmpty()) {
            $bestAutomatic = $automaticPromotions->first();
            $automaticDiscount = $bestAutomatic['discount_amount'];
        }

        // Check free shipping
        $freeShippingPromo = $this->promotionService->getActivePromotions()
            ->where('promotion_type', 'free_shipping')
            ->first();

        if ($freeShippingPromo) {
            $freeShippingEligible = $this->promotionService->getFreeShippingEligible($freeShippingPromo, $subtotal);
        }

        // Calculate total
        // ✅ Only ONE discount allowed
$totalDiscount = $promoDiscount > 0 ? $promoDiscount : $automaticDiscount;
       $total = max(0, $subtotal - $totalDiscount);

        return inertia('Cart/Index', [
            'subtotal' => $subtotal,
            'promo_discount' => $promoDiscount,
            'automatic_discount' => $automaticDiscount,
            'total' => $total,
            'free_shipping_eligible' => $freeShippingEligible,
            'automatic_promotions' => $automaticPromotions,
        ]);
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
    // Update item quantity in cart
    // -----------------------------
    public function updateQuantity(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'variant_id' => 'nullable|integer',
        ]);

        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');
        $variantId = $request->input('variant_id');

        if (Auth::check()) {
            $cartItem = Cart::where('user_id', Auth::id())
                ->where('product_id', $productId)
                ->where('variant_id', $variantId ?? null)
                ->first();

            if ($cartItem) {
                $cartItem->quantity = $quantity;
                $cartItem->save();
            }
        } else {
            $cart = $request->session()->get('cart', []);
            
            foreach ($cart as &$item) {
                if ($item['product_id'] == $productId && ($item['variant_id'] ?? null) == $variantId) {
                    $item['quantity'] = $quantity;
                    break;
                }
            }
            
            $request->session()->put('cart', $cart);
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
        $request->session()->forget('applied_promo_code');
        $request->session()->forget('applied_promo_id');
        $request->session()->forget('promo_discount');
        $request->session()->save();

        return $this->index($request);
    }

    // -----------------------------
    // Apply promo code
    // -----------------------------
   public function applyPromo(Request $request)
{
    $request->validate([
        'code' => 'required|string|max:50',
    ]);

    $promoCode = $request->input('code');

    // Get cart items
    $cartItems = Auth::check() 
        ? Cart::where('user_id', Auth::id())->get()
        : collect($request->session()->get('cart', []));

    // 🔥 Calculate total from backend (NOT frontend)
    $orderTotal = $cartItems->sum(fn($item) => $item->price * $item->quantity);

    // Apply promo
    $result = $this->promotionService->applyPromoCode(
        $promoCode, 
        $orderTotal, 
        $cartItems,
        Auth::user()
    );

    if ($result['valid']) {
        // Store in session
        $request->session()->put('applied_promo_code', $result['promotion_code']);
        $request->session()->put('applied_promo_id', $result['promotion']->id);
        $request->session()->put('promo_discount', $result['discount_amount']);
        $request->session()->save();

        return response()->json([
            'valid' => true,
            'message' => 'Promo code applied successfully',
            'promotion_code' => $result['promotion_code'],
            'promotion_type' => $result['promotion_type'] ?? 'percentage',
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
        $request->session()->forget('applied_promo_code');
        $request->session()->forget('applied_promo_id');
        $request->session()->forget('promo_discount');
        $request->session()->save();

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

    // -----------------------------
    // 1. Subtotal
    // -----------------------------
    $subtotal = $cartItems->sum(fn($item) => $item->price * $item->quantity);

    // -----------------------------
    // 2. Promo Code Discount
    // -----------------------------
    $promoDiscount = 0;
    $automaticDiscount = 0;
    $promotion = null;
    $automaticPromotion = null;

    $appliedPromoId = $request->session()->get('applied_promo_id');
    $appliedPromoCode = $request->session()->get('applied_promo_code');

    if ($appliedPromoId) {
        $promotion = Promotion::find($appliedPromoId);

        if ($promotion) {
            $promoDiscount = $this->promotionService->calculateDiscount(
                $promotion,
                $subtotal,
                $cartItems
            );
        }
    }

    // -----------------------------
    // 3. Automatic Promotion (ONLY if no promo code)
    // -----------------------------
    if (!$promotion) {
        $automaticPromotions = $this->promotionService->getAllApplicablePromotions(
            $subtotal,
            $cartItems,
            Auth::user()
        );

        if ($automaticPromotions->isNotEmpty()) {
            $bestAutomatic = $automaticPromotions->first();
            $automaticDiscount = $bestAutomatic['discount_amount'];
            $automaticPromotion = $bestAutomatic['promotion'];
        }
    }

    // -----------------------------
    // 4. Choose ONE discount
    // -----------------------------
    $totalDiscount = $promotion ? $promoDiscount : $automaticDiscount;

    // -----------------------------
    // 5. Shipping (backend controlled)
    // -----------------------------
    $freeShippingEligible = false;

    $freeShippingPromo = $this->promotionService->getActivePromotions()
        ->where('promotion_type', 'free_shipping')
        ->first();

    if ($freeShippingPromo) {
        $freeShippingEligible = $this->promotionService->getFreeShippingEligible(
            $freeShippingPromo,
            $subtotal
        );
    }

    $shippingCost = $freeShippingEligible ? 0 : 150; // 🔥 FIXED (no frontend input)

    // -----------------------------
    // 6. Final Total
    // -----------------------------
    $total = max(0, $subtotal - $totalDiscount + $shippingCost);

    DB::beginTransaction();

    try {
        // -----------------------------
        // 7. Create Order
        // -----------------------------
        $order = Order::create([
            'id' => Str::uuid(),
            'user_id' => $userId,
            'total_amount' => $total,
            'subtotal' => $subtotal,
            'discount_amount' => $totalDiscount,
            'shipping_cost' => $shippingCost,
            'promotion_id' => $promotion?->id ?? $automaticPromotion?->id,
            'promotion_code' => $appliedPromoCode ?? null,
            'status' => 'pending',
        ]);

        // -----------------------------
        // 8. Create Order Items
        // -----------------------------
        foreach ($cartItems as $item) {
            OrderItem::create([
                'id' => Str::uuid(),
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'variant_id' => $item->variant_id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->price * $item->quantity,
            ]);
        }

        // -----------------------------
        // 9. Record Promotion Usage
        // -----------------------------
        if ($promotion) {
            $this->promotionService->recordCouponUsage(
                $promotion,
                $userId,
                $order->id,
                $promoDiscount
            );
        }

        // -----------------------------
        // 10. Cleanup
        // -----------------------------
        Cart::where('user_id', $userId)->delete();

        $request->session()->forget([
            'applied_promo_code',
            'applied_promo_id',
            'promo_discount'
        ]);

        DB::commit();

        // Redirect to success page
        return redirect()->route('orders.success', $order->id);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'message' => 'Checkout failed: ' . $e->getMessage()
        ], 500);
    }
}
}