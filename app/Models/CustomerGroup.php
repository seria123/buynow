<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'discount_rate',
        'is_active',
    ];

    protected $casts = [
        'discount_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the users belonging to this group
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'customer_group_id');
    }

    /**
     * Check if the group is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get the discount rate as a percentage
     */
    public function getDiscountPercentageAttribute(): float
    {
        return (float) $this->discount_rate;
    }

    /**
     * Apply discount to a price
     */
    public function applyDiscount(float $price): float
    {
        return $price - ($price * ($this->discount_rate / 100));
    }

    /**
     * Scope to get only active groups
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
