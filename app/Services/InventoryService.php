<?php

namespace App\Services;

use App\Models\Inventory\InventorySource;
use App\Models\Catalogue\Product;
use Illuminate\Support\Collection;

class InventoryService
{
    /**
     * Get the best inventory source for a product based on availability.
     * Prefers default source, then falls back to first source with sufficient stock.
     */
    public function getBestSourceForProduct(Product $product, int $quantity): ?InventorySource
    {
        // First, try the default source
        $defaultSource = InventorySource::getDefault();
        
        if ($defaultSource && $defaultSource->getAvailableStock($product) >= $quantity) {
            return $defaultSource;
        }

        // Find any source with sufficient stock
        $sources = InventorySource::getActive();
        
        foreach ($sources as $source) {
            if ($source->getAvailableStock($product) >= $quantity) {
                return $source;
            }
        }

        // If no source has sufficient stock, return the default source anyway (for partial fulfillment)
        return $defaultSource;
    }

    /**
     * Deduct stock from inventory for an order.
     * Attempts to deduct from the best available source.
     */
    public function deductStockForOrder(Product $product, int $quantity, ?InventorySource $source = null): bool
    {
        $source = $source ?? $this->getBestSourceForProduct($product, $quantity);
        
        if (!$source) {
            return false;
        }

        return $source->deductStock($product, $quantity);
    }

    /**
     * Get total available stock for a product across all sources.
     */
    public function getTotalStock(Product $product): int
    {
        $sources = InventorySource::getActive();
        $total = 0;

        foreach ($sources as $source) {
            $total += $source->getAvailableStock($product);
        }

        return $total;
    }

    /**
     * Get stock information for a product from all sources.
     */
    public function getStockBySource(Product $product): Collection
    {
        $sources = InventorySource::getActive();
        $stockInfo = collect();

        foreach ($sources as $source) {
            $pivot = $source->products()->where('product_id', $product->id)->first();
            
            $stockInfo->push([
                'source' => $source,
                'quantity' => $pivot?->pivot?->quantity ?? 0,
                'reserved_quantity' => $pivot?->pivot?->reserved_quantity ?? 0,
                'available_quantity' => $source->getAvailableStock($product),
            ]);
        }

        return $stockInfo;
    }

    /**
     * Check if a product is low on stock across all sources.
     */
    public function isLowStock(Product $product): bool
    {
        $totalStock = $this->getTotalStock($product);
        $threshold = $product->low_stock_threshold ?? 10;
        
        return $totalStock <= $threshold;
    }

    /**
     * Get all products with low stock.
     */
    public function getLowStockProducts(): Collection
    {
        $products = Product::where('published', true)->get();
        
        return $products->filter(function ($product) {
            return $this->isLowStock($product);
        });
    }

    /**
     * Check if a product is out of stock.
     */
    public function isOutOfStock(Product $product): bool
    {
        return $this->getTotalStock($product) <= 0;
    }

    /**
     * Add stock to a product at a specific source.
     */
    public function addStock(Product $product, int $quantity, ?InventorySource $source = null): bool
    {
        $source = $source ?? InventorySource::getDefault();
        
        if (!$source) {
            return false;
        }

        return $source->addStock($product, $quantity);
    }

    /**
     * Transfer stock between sources for a product.
     */
    public function transferStock(
        Product $product, 
        InventorySource $fromSource, 
        InventorySource $toSource, 
        int $quantity
    ): bool {
        // Check if source has enough stock
        if ($fromSource->getAvailableStock($product) < $quantity) {
            return false;
        }

        // Deduct from source
        if (!$fromSource->deductStock($product, $quantity)) {
            return false;
        }

        // Add to destination
        return $toSource->addStock($product, $quantity);
    }
}
