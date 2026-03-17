<?php

namespace App\Services;

use App\Models\Sales\Promotion;
use Illuminate\Support\Facades\Validator;

class PromotionService
{
    /**
     * Validate a promo code
     */
    public function validatePromoCode(string $code, float $orderTotal = 0): array
    {
        $code = strtoupper(trim($code));

        // Find the promotion by code
        $promotion = Promotion::where('code', $code)->first();

        if (!$promotion) {
            return [
                'valid' => false,
                'message' => 'Invalid promo code',
                'promotion' => null,
            ];
        }

        // Check if promotion is active
        if (!$promotion->is_active) {
            return [
                'valid' => false,
                'message' => 'This promo code is not active',
                'promotion' => $promotion,
            ];
        }

        // Check if promotion has started
        if ($promotion->isUpcoming()) {
            return [
                'valid' => false,
                'message' => 'This promo code is not yet active',
                'promotion' => $promotion,
            ];
        }

        // Check if promotion has expired
        if ($promotion->isExpired()) {
            return [
                'valid' => false,
                'message' => 'This promo code has expired',
                'promotion' => $promotion,
            ];
        }

        // Check usage limit
        if ($promotion->isUsageLimitReached()) {
            return [
                'valid' => false,
                'message' => 'This promo code has reached its usage limit',
                'promotion' => $promotion,
            ];
        }

        // Check minimum order amount
        if ($promotion->minimum_order_amount && $orderTotal < $promotion->minimum_order_amount) {
            return [
                'valid' => false,
                'message' => 'Minimum order amount of $' . number_format($promotion->minimum_order_amount, 2) . ' required',
                'promotion' => $promotion,
            ];
        }

        return [
            'valid' => true,
            'message' => 'Promo code is valid',
            'promotion' => $promotion,
        ];
    }

    /**
     * Calculate discount for a given order total
     */
    public function calculateDiscount(Promotion $promotion, float $orderTotal): float
    {
        return $promotion->calculateDiscount($orderTotal);
    }

    /**
     * Apply a promo code to an order
     */
    public function applyPromoCode(string $code, float $orderTotal): array
    {
        $validation = $this->validatePromoCode($code, $orderTotal);

        if (!$validation['valid']) {
            return $validation;
        }

        $promotion = $validation['promotion'];
        $discountAmount = $this->calculateDiscount($promotion, $orderTotal);

        return [
            'valid' => true,
            'message' => 'Promo code applied successfully',
            'promotion' => $promotion,
            'discount_amount' => $discountAmount,
            'promotion_code' => $promotion->code,
        ];
    }

    /**
     * Get all active and valid promotions
     */
    public function getActivePromotions(): \Illuminate\Database\Eloquent\Collection
    {
        return Promotion::valid()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get the best available discount for an order total
     */
    public function getBestAvailableDiscount(float $orderTotal): ?array
    {
        $promotions = Promotion::valid()->get();

        if ($promotions->isEmpty()) {
            return null;
        }

        $bestDiscount = 0;
        $bestPromotion = null;

        foreach ($promotions as $promotion) {
            $discount = $this->calculateDiscount($promotion, $orderTotal);
            if ($discount > $bestDiscount) {
                $bestDiscount = $discount;
                $bestPromotion = $promotion;
            }
        }

        if (!$bestPromotion) {
            return null;
        }

        return [
            'promotion' => $bestPromotion,
            'discount_amount' => $bestDiscount,
        ];
    }

    /**
     * Record usage of a promotion (call after order is completed)
     */
    public function recordUsage(Promotion $promotion): void
    {
        $promotion->incrementUsage();
    }
}
