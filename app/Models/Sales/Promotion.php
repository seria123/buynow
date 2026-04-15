<?php

namespace App\Models\Sales;

use App\Models\Catalogue\Category;
use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Promotion extends Model
{
    protected $table = 'promotions';

    // UUID setup
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'code',
        'type',
        'promotion_type',
        'value',
        'minimum_order_amount',
        'usage_limit',
        'used_count',
        'per_user_limit',
        'max_uses_per_user',
        'starts_at',
        'expires_at',
        'is_active',
        // Rule-based application
        'apply_to',
        'category_id',
        'product_ids',
        'customer_group_id',
        // Priority
        'priority',
        // Buy 1 Get 1
        'buy_quantity',
        'get_quantity',
        'minimum_quantity',
        // Flash sale
        'is_flash_sale',
        'flash_sale_duration_minutes',
        // Bundle
        'bundle_product_ids',
        'bundle_discount_percentage',
        // Description
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'value' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'product_ids' => 'array',
        'bundle_product_ids' => 'array',
        'is_flash_sale' => 'boolean',
        'per_user_limit' => 'integer',
        'max_uses_per_user' => 'integer',
        'priority' => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
        'used_count' => 0,
        'apply_to' => 'all',
        'promotion_type' => 'percentage',
        'priority' => 0,
        'max_uses_per_user' => 0,
    ];

    // Auto-generate UUID on creation
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($promotion) {
            if (!$promotion->id) {
                $promotion->id = (string) Str::uuid();
            }
            // Auto-uppercase the promo code
            if ($promotion->code) {
                $promotion->code = strtoupper($promotion->code);
            }
        });

        static::updating(function ($promotion) {
            // Auto-upcase the promo code on updates
            if ($promotion->isDirty('code') && $promotion->code) {
                $promotion->code = strtoupper($promotion->code);
            }
        });
    }

    /**
     * Check if promotion is currently valid
     */
    public function isValid(): bool
    {
        return $this->is_active
            && (!$this->starts_at || now()->gte($this->starts_at))
            && (!$this->expires_at || now()->lte($this->expires_at))
            && (!$this->usage_limit || $this->used_count < $this->usage_limit);
    }

    /**
     * Check if promotion has expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && now()->gt($this->expires_at);
    }

    /**
     * Check if promotion has not started yet
     */
    public function isUpcoming(): bool
    {
        return $this->starts_at && now()->lt($this->starts_at);
    }

    /**
     * Check if promotion usage limit is reached
     */
    public function isUsageLimitReached(): bool
    {
        return $this->usage_limit && $this->used_count >= $this->usage_limit;
    }

    /**
     * Calculate discount amount for a given order total
     */
    public function calculateDiscount(float $orderTotal): float
    {
        if ($orderTotal < ($this->minimum_order_amount ?? 0)) {
            return 0;
        }

        if ($this->promotion_type === 'percentage') {
            return round(($orderTotal * $this->value) / 100, 2);
        }

        // Fixed discount - cannot exceed order total
        return min($this->value, $orderTotal);
    }

    /**
     * Increment the used count
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }

    /**
     * Scope to get only active promotions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get currently valid promotions (within date range)
     */
    public function scopeValid($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            });
    }

    /**
     * Get the status label for display
     */
    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactive';
        }
        if ($this->isExpired()) {
            return 'Expired';
        }
        if ($this->isUpcoming()) {
            return 'Upcoming';
        }
        if ($this->isUsageLimitReached()) {
            return 'Limit Reached';
        }
        return 'Active';
    }

    /**
     * Get the promotion type label
     */
    public function getPromotionTypeLabelAttribute(): string
    {
        return match($this->promotion_type) {
            'percentage' => 'Percentage Discount',
            'fixed' => 'Fixed Amount Discount',
            'buy_one_get_one' => 'Buy 1 Get 1',
            'free_shipping' => 'Free Shipping',
            'bundle' => 'Bundle Deal',
            'flash_sale' => 'Flash Sale',
            default => 'Unknown',
        };
    }

    /**
     * Get the apply_to label
     */
    public function getApplyToLabelAttribute(): string
    {
        return match($this->apply_to) {
            'all' => 'All Products',
            'category' => 'Specific Category',
            'products' => 'Specific Products',
            'customer_group' => 'Customer Group',
            default => 'All Products',
        };
    }

    /**
     * Check if promotion is automatic (no code required)
     */
    public function isAutomatic(): bool
    {
        return empty($this->code);
    }

    /**
     * Check if promotion requires a code
     */
    public function requiresCode(): bool
    {
        return !empty($this->code);
    }

    /**
     * Check if promotion applies to a specific product
     */
    public function appliesToProduct($productId): bool
    {
        return match($this->apply_to) {
            'all' => true,
            'category' => $this->category_id && $productId->category_id === $this->category_id,
            'products' => in_array($productId, $this->product_ids ?? []),
            'customer_group' => true, // Check on user level
            default => true,
        };
    }

    /**
     * Check if promotion applies to a specific category
     */
    public function appliesToCategory($categoryId): bool
    {
        return $this->apply_to === 'all' || $this->apply_to === 'category' && $this->category_id === $categoryId;
    }

    /**
     * Scope to get automatic promotions (no code required)
     */
    public function scopeAutomatic($query)
    {
        return $query->whereNull('code')->orWhere('code', '');
    }

    /**
     * Scope to get coupon-based promotions (requires code)
     */
    public function scopeCouponBased($query)
    {
        return $query->whereNotNull('code')->where('code', '!=', '');
    }

    /**
     * Scope to order by priority (highest first)
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    /**
     * Scope to get flash sales
     */
    public function scopeFlashSales($query)
    {
        return $query->where('is_flash_sale', true);
    }

    /**
     * Get the category relationship
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the customer group relationship
     */
    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }

    /**
     * Get the products associated with this promotion
     */
    public function products()
    {
        return $this->belongsToMany(
            \App\Models\Catalogue\Product::class,
            'product_promotions',
            'promotion_id',
            'product_id'
        )->withPivot(['discounted_price', 'stock_limit', 'sold_count'])
         ->withTimestamps();
    }

    /**
     * Get the coupon usage records
     */
    public function couponUsages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Get users who have used this coupon
     */
    public function users()
    {
        return $this->belongsToMany(
            \App\Models\User::class,
            'coupon_usage',
            'promotion_id',
            'user_id'
        )->withPivot(['order_id', 'discount_amount'])
         ->withTimestamps();
    }

    /**
     * Calculate discount for a specific product
     */
    public function calculateProductDiscount(float $productPrice, int $quantity = 1): float
    {
        return match($this->promotion_type) {
            'percentage' => round(($productPrice * $this->value) / 100, 2) * $quantity,
            'fixed' => min($this->value, $productPrice) * $quantity,
            default => 0,
        };
    }

    /**
     * Check if user has reached per-user limit
     */
    public function userHasReachedLimit($userId): bool
    {
        if ($this->max_uses_per_user <= 0) {
            return false;
        }
        
        $userUsage = $this->couponUsages()->where('user_id', $userId)->count();
        return $userUsage >= $this->max_uses_per_user;
    }

    /**
     * Get remaining uses for a specific user
     */
    public function getRemainingUsesForUser($userId): int
    {
        if ($this->max_uses_per_user <= 0) {
            return PHP_INT_MAX;
        }
        
        $userUsage = $this->couponUsages()->where('user_id', $userId)->count();
        return max(0, $this->max_uses_per_user - $userUsage);
    }

    /**
     * Get remaining stock for flash sale products
     */
    public function getRemainingStock(): int
    {
        if (!$this->is_flash_sale) {
            return PHP_INT_MAX;
        }
        
        return $this->products()
            ->wherePivot('stock_limit', '>', 0)
            ->sum(
                \DB::raw('COALESCE(product_promotions.stock_limit, 0) - COALESCE(product_promotions.sold_count, 0)')
            );
    }
}

