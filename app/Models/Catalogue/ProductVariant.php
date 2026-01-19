<?php

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductVariant extends Model implements AuditableContract, HasMedia
{
    use Auditable, HasFactory, HasUuids, InteractsWithMedia;

    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'price',
        'compare_price',
        'cost',
        'quantity',
        'low_stock_threshold',
        'is_default',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'cost' => 'decimal:2',
            'quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variantOptions()
    {
        return $this->hasMany(ProductVariantOption::class, 'product_variant_id');
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

        $this->addMediaCollection('thumbnail')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * Register media conversions.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(500)
            ->height(500)
            ->performOnCollections('thumbnail')
            ->nonQueued();
    }

    /**
     * Get the thumbnail media instance.
     */
    public function getThumbnailAttribute(): ?Media
    {
        return $this->getFirstMedia('thumbnail');
    }

    /**
     * Get the thumbnail URL attribute.
     * Returns the thumbnail conversion URL if available, otherwise the original thumbnail URL,
     * or falls back to the first variant image if no thumbnail exists.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        $thumbnail = $this->getFirstMedia('thumbnail');

        if ($thumbnail) {
            // Return the 'thumb' conversion if it exists, otherwise return the original
            return $thumbnail->hasGeneratedConversion('thumb')
                ? $thumbnail->getUrl('thumb')
                : $thumbnail->getUrl();
        }

        // Fallback to first variant image if no thumbnail exists
        $firstImage = $this->getFirstMedia('images');
        if ($firstImage) {
            return $firstImage->getUrl();
        }

        // Fallback to product images if variant has no images
        if ($this->product) {
            return $this->product->thumbnail_url;
        }

        return null;
    }

    /**
     * Check if the variant has a thumbnail.
     */
    public function hasThumbnail(): bool
    {
        return $this->getFirstMedia('thumbnail') !== null;
    }

    /**
     * Get the display name for the variant.
     * If name is set, return it. Otherwise, generate from options.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->name) {
            return $this->name;
        }

        return $this->getOptionsDisplayString();
    }

    /**
     * Get variant options as a display string (e.g., "Black / 128GB").
     */
    public function getOptionsDisplayString(): string
    {
        $options = $this->variantOptions()
            ->with('attribute')
            ->get()
            ->map(function ($option) {
                return $option->attribute->name.': '.$option->value;
            })
            ->join(' / ');

        return $options ?: 'Variant';
    }

    /**
     * Get the effective price (variant price or product price).
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->price ?? $this->product?->price ?? 0;
    }

    /**
     * Get the effective compare price.
     */
    public function getEffectiveComparePriceAttribute(): ?float
    {
        return $this->compare_price ?? $this->product?->compare_price;
    }

    /**
     * Check if variant is in stock.
     */
    public function isInStock(): bool
    {
        return $this->quantity > 0;
    }

    /**
     * Check if variant is low on stock.
     */
    public function isLowStock(): bool
    {
        if ($this->low_stock_threshold === null) {
            return false;
        }

        return $this->quantity <= $this->low_stock_threshold;
    }
}
