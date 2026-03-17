<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Catalogue\Product;

class InventorySource extends Model
{
    use HasFactory;

    protected $table = 'inventory_sources';

    protected $fillable = [
        'code',
        'name',
        'description',
        'address',
        'city',
        'country',
        'postal_code',
        'contact_name',
        'contact_email',
        'contact_phone',
        'is_default',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Get the products associated with this inventory source.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_inventory_source')
            ->withPivot('quantity', 'reserved_quantity')
            ->withTimestamps();
    }

    /**
     * Get the default inventory source.
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all active inventory sources ordered by priority.
     */
    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get total stock for a specific product at this source.
     */
    public function getProductStock(Product $product): int
    {
        $pivot = $this->products()->where('product_id', $product->id)->first();
        return $pivot?->pivot?->quantity ?? 0;
    }

    /**
     * Get available stock (quantity - reserved) for a product at this source.
     */
    public function getAvailableStock(Product $product): int
    {
        $pivot = $this->products()->where('product_id', $product->id)->first();
        if (!$pivot || !$pivot->pivot) {
            return 0;
        }
        return $pivot->pivot->quantity - $pivot->pivot->reserved_quantity;
    }

    /**
     * Deduct stock for a product at this source.
     */
    public function deductStock(Product $product, int $quantity): bool
    {
        $pivot = $this->products()->where('product_id', $product->id)->first();
        
        if (!$pivot || !$pivot->pivot) {
            return false;
        }

        $available = $pivot->pivot->quantity - $pivot->pivot->reserved_quantity;
        
        if ($available < $quantity) {
            return false;
        }

        $pivot->pivot->decrement('quantity', $quantity);
        
        return true;
    }

    /**
     * Add stock for a product at this source.
     */
    public function addStock(Product $product, int $quantity): bool
    {
        $pivot = $this->products()->where('product_id', $product->id)->first();
        
        if (!$pivot || !$pivot->pivot) {
            // Create the pivot record if it doesn't exist
            $this->products()->attach($product->id, [
                'quantity' => $quantity,
                'reserved_quantity' => 0,
            ]);
            return true;
        }

        $pivot->pivot->increment('quantity', $quantity);
        
        return true;
    }

    /**
     * Reserve stock for a product at this source.
     */
    public function reserveStock(Product $product, int $quantity): bool
    {
        $pivot = $this->products()->where('product_id', $product->id)->first();
        
        if (!$pivot || !$pivot->pivot) {
            return false;
        }

        $available = $pivot->pivot->quantity - $pivot->pivot->reserved_quantity;
        
        if ($available < $quantity) {
            return false;
        }

        $pivot->pivot->increment('reserved_quantity', $quantity);
        
        return true;
    }

    /**
     * Release reserved stock for a product at this source.
     */
    public function releaseStock(Product $product, int $quantity): bool
    {
        $pivot = $this->products()->where('product_id', $product->id)->first();
        
        if (!$pivot || !$pivot->pivot) {
            return false;
        }

        $reserved = $pivot->pivot->reserved_quantity;
        
        if ($reserved < $quantity) {
            $quantity = $reserved;
        }

        $pivot->pivot->decrement('reserved_quantity', $quantity);
        
        return true;
    }
}
