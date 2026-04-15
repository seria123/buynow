<?php

namespace App\Services;

use App\Models\Sales\Promotion;
use App\Models\Sales\Order;
use App\Models\Sales\CouponUsage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PromotionService
{
    /*
    |--------------------------------------------------------------------------
    | 🔹 CORE: CART TOTAL CALCULATION
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

        [$shipping, $freeShipping] = $this->calculateShipping($subtotal, $items);

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
    | 🔹 NORMALIZATION
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
    | 🔹 DISCOUNT RESOLUTION
    |--------------------------------------------------------------------------
    */
    private function resolveDiscount(float $subtotal, Collection $items, $user, $appliedPromoId): array
    {
        // Promo code applied
        if ($appliedPromoId) {
            $promotion = Promotion::find($appliedPromoId);
            if ($promotion) {
                $discount = $this->calculateDiscount($promotion, $subtotal, $items);
                return [$promotion, $discount];
            }
        }

        // Automatic promotion
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
    private function calculateShipping(float $subtotal, Collection $items = null): array
    {
        $shippingFee = 0;

        // Calculate shipping per item if cart items exist
        if ($items) {
            foreach ($items as $item) {
                $shippingFee += ($item['shipping_cost'] ?? 0) * $item['quantity'];
            }
        }

        // Check if free shipping promotion is active
        $freeShippingPromo = $this->getActivePromotions()
            ->where('promotion_type', 'free_shipping')
            ->first();

        $isFree = $freeShippingPromo &&
            $this->getFreeShippingEligible($freeShippingPromo, $subtotal);

        return [$isFree ? 0 : $shippingFee, $isFree];
    }

    /*
    |--------------------------------------------------------------------------
    | 🔹 DISCOUNT CALCULATION
    |--------------------------------------------------------------------------
    */
    public function calculateDiscount(Promotion $promotion, float $total, Collection $items): float
    {
        if ($total < ($promotion->minimum_order_amount ?? 0)) return 0;

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
    public function getAllApplicablePromotions(float $subtotal, Collection $items, $user = null): Collection
{
    return $this->getAutomaticPromotions()
        ->map(fn($promo) => [
            'promotion' => $promo,
            'discount_amount' => $this->calculateDiscount($promo, $subtotal, $items),
        ])
        ->filter(fn($p) => $p['discount_amount'] > 0)
        ->values();
}
}