<?php

namespace App\Services;

use App\Models\Sales\Promotion;
use App\Models\Sales\Order;
use App\Models\Sales\CouponUsage;
use App\Models\Catalogue\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PromotionService
{
    /*
    |--------------------------------------------------------------------------
    | 🔹 CORE: CART TOTAL CALCULATION (SINGLE SOURCE OF TRUTH)
    |--------------------------------------------------------------------------
    */
    public function calculateCartTotals($cartItems, $user = null, $appliedPromoId = null): array
    {
        $items = $this->normalizeItems($cartItems);

        $subtotal = $this->calculateSubtotal($items);

        [$promotion, $discount] = $this->resolveDiscount(
            $subtotal,
            $items,
            $user,
            $appliedPromoId
        );

        [$shipping, $freeShipping] = $this->calculateShipping($subtotal);

        $total = max(0, $subtotal - $discount + $shipping);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'total' => $total,
            'promotion' => $promotion,
            'free_shipping' => $freeShipping,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 🔹 NORMALIZATION (avoid array/object chaos)
    |--------------------------------------------------------------------------
    */
    private function normalizeItems($cartItems): Collection
    {
        return collect($cartItems)->map(function ($item) {
            return [
                'product_id' => is_array($item) ? $item['product_id'] : $item->product_id,
                'price' => is_array($item) ? $item['price'] : $item->price,
                'quantity' => is_array($item) ? $item['quantity'] : $item->quantity,
            ];
        });
    }

    private function calculateSubtotal(Collection $items): float
    {
        return $items->sum(fn($item) => $item['price'] * $item['quantity']);
    }

    /*
    |--------------------------------------------------------------------------
    | 🔹 DISCOUNT RESOLUTION (promo vs automatic)
    |--------------------------------------------------------------------------
    */
    private function resolveDiscount(float $subtotal, Collection $items, $user, $appliedPromoId): array
    {
        // 1. Promo Code
        if ($appliedPromoId) {
            $promotion = Promotion::find($appliedPromoId);

            if ($promotion) {
                $discount = $this->calculateDiscount($promotion, $subtotal, $items);
                return [$promotion, $discount];
            }
        }

        // 2. Automatic Promotion
        $best = $this->getBestAutomaticDiscount($subtotal, $items, $user);

        if ($best) {
            return [$best['promotion'], $best['discount_amount']];
        }

        return [null, 0];
    }

    /*
    |--------------------------------------------------------------------------
    | 🔹 SHIPPING
    |--------------------------------------------------------------------------
    */
   private function calculateShipping(float $subtotal): array
{
    $freeShippingPromo = $this->getActivePromotions()
        ->where('promotion_type', 'free_shipping')
        ->first();

    $isFree = $freeShippingPromo &&
        $this->getFreeShippingEligible($freeShippingPromo, $subtotal);

    // Always return 0 for now (override hardcoded 150)
    $shipping = 0;

    return [$shipping, $isFree];
}

    /*
    |--------------------------------------------------------------------------
    | 🔹 PROMO VALIDATION
    |--------------------------------------------------------------------------
    */
    public function validatePromoCode(string $code, float $orderTotal = 0, $user = null): array
    {
        $promotion = Promotion::where('code', strtoupper(trim($code)))->first();

        if (!$promotion) return $this->invalid('Invalid promo code');
        if (!$promotion->is_active) return $this->invalid('Promo not active');
        if ($promotion->isUpcoming()) return $this->invalid('Not started yet');
        if ($promotion->isExpired()) return $this->invalid('Expired');
        if ($promotion->isUsageLimitReached()) return $this->invalid('Usage limit reached');

        if ($user && $promotion->max_uses_per_user > 0) {
            $used = Order::where('promotion_id', $promotion->id)
                ->where('customer_id', $user->id)
                ->count();

            if ($used >= $promotion->max_uses_per_user) {
                return $this->invalid('Already used');
            }
        }

        if ($promotion->minimum_order_amount &&
            $orderTotal < $promotion->minimum_order_amount) {
            return $this->invalid('Minimum order not reached');
        }

        return [
            'valid' => true,
            'promotion' => $promotion
        ];
    }

    private function invalid($message): array
    {
        return ['valid' => false, 'message' => $message];
    }

    /*
    |--------------------------------------------------------------------------
    | 🔹 DISCOUNT CALCULATION
    |--------------------------------------------------------------------------
    */
    public function calculateDiscount(Promotion $promotion, float $total, Collection $items): float
    {
        if ($total < ($promotion->minimum_order_amount ?? 0)) {
            return 0;
        }

        return match ($promotion->promotion_type) {
            'percentage' => round($total * $promotion->value / 100, 2),
            'fixed' => min($promotion->value, $total),
            'buy_one_get_one' => $this->bogo($items, $promotion),
            'bundle' => $this->bundle($items, $promotion),
            default => 0,
        };
    }

    private function bogo(Collection $items, Promotion $promo): float
    {
        $discount = 0;

        foreach ($items as $item) {
            $sets = floor($item['quantity'] / 2);
            $discount += $sets * $item['price'];
        }

        return $discount;
    }

    private function bundle(Collection $items, Promotion $promo): float
    {
        if (!$promo->bundle_product_ids) return 0;

        $total = collect($items)
            ->whereIn('product_id', $promo->bundle_product_ids)
            ->sum(fn($i) => $i['price'] * $i['quantity']);

        return round($total * ($promo->bundle_discount_percentage ?? 0) / 100, 2);
    }

    /*
    |--------------------------------------------------------------------------
    | 🔹 AUTOMATIC PROMOS
    |--------------------------------------------------------------------------
    */
    public function getBestAutomaticDiscount(float $total, Collection $items, $user): ?array
    {
        return $this->getAutomaticPromotions()
            ->map(fn($promo) => [
                'promotion' => $promo,
                'discount_amount' => $this->calculateDiscount($promo, $total, $items)
            ])
            ->sortByDesc('discount_amount')
            ->first();
    }

    public function getAutomaticPromotions(): Collection
    {
        return Promotion::valid()
            ->whereNull('code')
            ->orderByDesc('priority')
            ->get();
    }

    public function getActivePromotions(): Collection
    {
        return Promotion::valid()->get();
    }

    public function getFreeShippingEligible(Promotion $promotion, float $total): bool
    {
        return $total >= ($promotion->minimum_order_amount ?? 0);
    }

    /*
    |--------------------------------------------------------------------------
    | 🔹 GET ALL APPLICABLE PROMOTIONS
    |--------------------------------------------------------------------------
    */
    public function getAllApplicablePromotions(float $subtotal, $cartItems, $user = null): Collection
    {
        $items = $this->normalizeItems($cartItems);
        $promotions = collect();

        // Get automatic promotions
        $automaticPromotions = $this->getAutomaticPromotions();
        foreach ($automaticPromotions as $promo) {
            $discount = $this->calculateDiscount($promo, $subtotal, $items);
            if ($discount > 0) {
                $promotions->push([
                    'promotion' => $promo,
                    'discount_amount' => $discount,
                ]);
            }
        }

        return $promotions->sortByDesc('discount_amount');
    }

    /*
    |--------------------------------------------------------------------------
    | 🔹 RECORD USAGE
    |--------------------------------------------------------------------------
    */
    public function recordCouponUsage(Promotion $promotion, $userId, $orderId, float $discount): void
    {
        DB::transaction(function () use ($promotion, $userId, $orderId, $discount) {
            CouponUsage::create([
                'promotion_id' => $promotion->id,
                'user_id' => $userId,
                'order_id' => $orderId,
                'discount_amount' => $discount,
            ]);

            $promotion->incrementUsage();
        });
    }
}