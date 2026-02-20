<?php

namespace App\Models\Catalogue;

use App\Enums\ProductStatus;
use App\Models\User;
use App\Notifications\ProductApprovalRequestNotification;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements AuditableContract, HasMedia
{
    use Auditable, HasFactory, HasUuids, InteractsWithMedia;

    

    protected $table = 'products';

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'brand_id',
        'category_id',
        'attribute_family_id',
        'short_description',
        'description',
        'price',
        'compare_price',
        'cost',
        'quantity',
        'low_stock_threshold',
        'status',
        'published',
        'is_featured',
        'creator_id',
        'reviewer_id',
        'reviewed_at',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'published' => 'boolean',
            'is_featured' => 'boolean',
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'cost' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function attributeFamily()
    {
        return $this->belongsTo(AttributeFamily::class, 'attribute_family_id');
    }

    public function attributeValues()
    {
        return $this->hasMany(ProductAttributeValue::class, 'product_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id')->orderBy('sort_order');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
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
     * or falls back to the first product image if no thumbnail exists.
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

        // Fallback to first product image if no thumbnail exists
        $firstImage = $this->getFirstMedia('images');
        if ($firstImage) {
            return $firstImage->getUrl();
        }

        return null;
    }

    /**
     * Check if the product has a thumbnail.
     */
    public function hasThumbnail(): bool
    {
        return $this->getFirstMedia('thumbnail') !== null;
    }

    /**
     * Check if the product has variants.
     */
    public function hasVariants(): bool
    {
        return $this->variants()->exists();
    }

    /**
     * Get the total stock (sum of variant quantities or product quantity).
     */
    public function getTotalStock(): int
    {
        if ($this->hasVariants()) {
            return $this->variants()->sum('quantity');
        }

        return $this->quantity ?? 0;
    }

    /**
     * Get the default variant.
     */
    public function getDefaultVariant(): ?ProductVariant
    {
        return $this->variants()->where('is_default', true)->first()
            ?? $this->variants()->orderBy('sort_order')->first();
    }

    /**
     * Send approval request notifications to Admin users (excluding the creator).
     */
    public function notifyAdminsForApproval(): void
    {
        // Get all Admin users, excluding the creator
        $adminUsers = User::role('Admin')
            ->when($this->creator_id, function ($query) {
                $query->where('id', '!=', $this->creator_id);
            })
            ->get();

        // Send notification to each Admin user
        foreach ($adminUsers as $admin) {
            $admin->notify(new ProductApprovalRequestNotification($this, $this->creator));
        }
    }
}
