<?php

namespace App\Models\Catalogue;

use App\Enums\ProductStatus;
use App\Models\User;
use App\Models\Inventory\InventorySource;
use App\Notifications\ProductApprovalRequestNotification;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use App\Models\Sales\Store;

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
        'store_id',
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
    protected $appends = [
    'thumbnail_url',
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
    $this->addMediaCollection('thumbnail')
         ->useDisk('media')
         ->singleFile();

    $this->addMediaCollection('images')
         ->useDisk('media');
}

       public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
             ->width(300)
             ->height(300)
             ->sharpen(10);
           
    }

    /**
     * Get the thumbnail media instance.
     */
    public function getThumbnailMediaAttribute(): ?Media
{
    return $this->getFirstMedia('thumbnail');
}

    /**
     * Get the thumbnail URL attribute.
     * Returns the thumbnail conversion URL if available, otherwise the original thumbnail URL,
     * or falls back to the first product image if no thumbnail exists.
     */
 public function getThumbnailUrlAttribute(): string
{
    // Get converted thumbnail if exists
    $url = $this->getFirstMediaUrl('thumbnail', 'thumb');

    // If no conversion, get original file
    if (!$url) {
        $url = $this->getFirstMediaUrl('thumbnail');
    }

    // Final fallback placeholder
    return $url ?: asset('images/placeholder.svg');
}
    /**
     * Check if the product has a thumbnail.
     */
    public function getGalleryImagesAttribute()
{
    return $this->getMedia('images')->map(function ($media) {
        return [
            'url' => $media->getUrl(),
            'thumb_url' => $media->getUrl('thumb') ?: $media->getUrl(),
        ];
    });
}

    public function hasThumbnail(): bool
    {
        return $this->getFirstMedia('thumbnail') !== null;
    }

    /**
     * Get all product images for the gallery.
     * Returns an array with thumbnail as first image (if exists) followed by gallery images.
     */
    public function getAllImages(): array
    {
        $images = [];
        
        // Add thumbnail as first image if exists
        $thumbnail = $this->getFirstMedia('thumbnail');
        if ($thumbnail) {
            $images[] = [
                'url' => $thumbnail->getUrl(),
                'thumb_url' => $thumbnail->getUrl('thumb') ?: $thumbnail->getUrl(),
                'is_thumbnail' => true,
            ];
        }
        
        // Add gallery images
        $galleryImages = $this->getMedia('images');
        foreach ($galleryImages as $image) {
            $images[] = [
                'url' => $image->getUrl(),
                'thumb_url' => $image->getUrl('thumb') ?: $image->getUrl(),
                'is_thumbnail' => false,
            ];
        }
        
        return $images;
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
    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function wishlistedBy()
{
    return $this->belongsToMany(\App\Models\User::class, 'user_product_wishlist', 'product_id', 'user_id');
}
public function store()
{
    return $this->belongsTo(Store::class);
}

public function ratings()
{
    return $this->hasMany(ProductRating::class);
}

public function comments()
{
    return $this->hasMany(ProductComment::class);
}

/**
 * Get the inventory sources associated with this product.
 */
public function inventorySources()
{
    return $this->belongsToMany(InventorySource::class, 'product_inventory_source')
        ->withPivot('quantity', 'reserved_quantity')
        ->withTimestamps();
}

/**
 * Get total stock from all inventory sources.
 */
public function getInventoryStock(): int
{
    return $this->inventorySources()
        ->get()
        ->sum(fn($source) => $source->pivot->quantity - $source->pivot->reserved_quantity);
}

/**
 * Get available stock from all inventory sources.
 */
public function getAvailableStock(): int
{
    return $this->inventorySources()
        ->get()
        ->sum(fn($source) => $source->pivot->quantity - $source->pivot->reserved_quantity);
}

/**
 * Check if product is low on stock.
 */
public function isLowStock(): bool
{
    return $this->getAvailableStock() <= ($this->low_stock_threshold ?? 10);
}

/**
 * Check if product is out of stock.
 */
public function isOutOfStock(): bool
{
    return $this->getAvailableStock() <= 0;
}
}
