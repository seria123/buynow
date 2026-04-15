<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'image',
        'mobile_image',
        'link',
        'position',
        'type',
        'active',
        'sort_order',
        'start_date',
        'end_date',
        'clicks',
        'impressions',
        'product_id',
        'category_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'clicks' => 'integer',
        'impressions' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ad) {
            if (empty($ad->image)) {
                $ad->image = 'ads/' . Str::random(40) . '.jpg';
            }
        });
    }

    /**
     * Get the product associated with the ad.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalogue\Product::class);
    }

    /**
     * Get the category associated with the ad.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalogue\Category::class);
    }

    /**
     * Scope to get active ads.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get ads by position.
     */
    public function scopeByPosition($query, string $position)
    {
        return $query->where('position', $position);
    }

    /**
     * Scope to get valid ads (within date range).
     */
    public function scopeValid($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('start_date')
                ->orWhere('start_date', '<=', now());
        })->where(function ($query) {
            $query->whereNull('end_date')
                ->orWhere('end_date', '>=', now());
        });
    }

    /**
     * Scope to get ads ordered by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    /**
     * Increment the clicks count.
     */
    public function incrementClicks(): void
    {
        $this->increment('clicks');
    }

    /**
     * Increment the impressions count.
     */
    public function incrementImpressions(): void
    {
        $this->increment('impressions');
    }

    /**
     * Check if the ad is currently valid.
     */
    public function isValid(): bool
    {
        $now = now();
        
        if ($this->start_date && $this->start_date > $now) {
            return false;
        }
        
        if ($this->end_date && $this->end_date < $now) {
            return false;
        }
        
        return true;
    }

    /**
     * Get the display image based on device type.
     */
    public function getDisplayImage(?string $deviceType = null): string
    {
        if ($deviceType === 'mobile' && $this->mobile_image) {
            return $this->mobile_image;
        }
        
        return $this->image;
    }
}
