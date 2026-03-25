<?php

namespace App\Models\Sales;

use App\Models\Catalogue\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Warranty extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'warranties';

    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'warranty_number',
        'warranty_type',
        'start_date',
        'end_date',
        'status',
        'terms',
        'claim_instructions',
        'document_path',
        'coverage_details',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'coverage_details' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($warranty) {
            if (!$warranty->warranty_number) {
                $warranty->warranty_number = static::generateWarrantyNumber();
            }
        });
    }

    /**
     * Generate a unique warranty number.
     */
    public static function generateWarrantyNumber(): string
    {
        $prefix = 'WRT';
        $timestamp = now()->format('ymd');
        $random = strtoupper(Str::random(6));
        
        $warrantyNumber = $prefix . $timestamp . $random;
        
        // Ensure uniqueness
        $exists = static::where('warranty_number', $warrantyNumber)->exists();
        if ($exists) {
            return static::generateWarrantyNumber();
        }
        
        return $warrantyNumber;
    }

    /**
     * Check if the warranty is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->end_date >= now()->toDateString();
    }

    /**
     * Check if the warranty has expired.
     */
    public function isExpired(): bool
    {
        return $this->end_date < now()->toDateString();
    }

    /**
     * Check if the warranty can be claimed.
     */
    public function canBeClaimed(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    /**
     * Get the remaining days of the warranty.
     */
    public function getRemainingDaysAttribute(): int
    {
        if ($this->isExpired()) {
            return 0;
        }
        
        return now()->diffInDays($this->end_date, false);
    }

    /**
     * Get the product associated with this warranty.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user associated with this warranty.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order associated with this warranty.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the creator of this warranty.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get only active warranties.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get only expired warranties.
     */
    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now()->toDateString());
    }

    /**
     * Scope to get warranties for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get the status label for display.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => $this->isExpired() ? 'Expired' : 'Active',
            'expired' => 'Expired',
            'claimed' => 'Claimed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get the warranty type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->warranty_type) {
            'standard' => 'Standard Warranty',
            'extended' => 'Extended Warranty',
            'lifetime' => 'Lifetime Warranty',
            'manufacturer' => 'Manufacturer Warranty',
            default => ucfirst($this->warranty_type),
        };
    }
}