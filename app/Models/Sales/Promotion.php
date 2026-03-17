<?php

namespace App\Models\Sales;

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
        'value',
        'minimum_order_amount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'value' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
    ];

    protected $attributes = [
        'is_active' => true,
        'used_count' => 0,
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

        if ($this->type === 'percentage') {
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
}

