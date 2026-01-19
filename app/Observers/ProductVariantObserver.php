<?php

namespace App\Observers;

use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductVariant;
use App\Services\ProductSkuGenerator;
use Illuminate\Support\Facades\DB;

class ProductVariantObserver
{
    public function __construct(
        protected ProductSkuGenerator $skuGenerator
    ) {}

    /**
     * Handle the ProductVariant "creating" event.
     * Auto-generate SKU if not provided.
     */
    public function creating(ProductVariant $productVariant): void
    {
        if (filled($productVariant->sku)) {
            return;
        }

        // Get category_id from parent product
        $categoryId = null;
        if ($productVariant->product_id) {
            $product = $productVariant->product ?? Product::query()->find($productVariant->product_id);
            $categoryId = $product?->category_id;
        }

        $productVariant->sku = $this->skuGenerator->generateForVariant($categoryId);
    }

    /**
     * Handle the ProductVariant "saving" event.
     * Ensures only one default variant per product.
     */
    public function saving(ProductVariant $productVariant): void
    {
        // If this variant is being set as default, unset all other defaults for the same product
        if ($productVariant->is_default && $productVariant->product_id) {
            DB::table('product_variants')
                ->where('product_id', $productVariant->product_id)
                ->where('id', '!=', $productVariant->id)
                ->update(['is_default' => false]);
        }
    }

    /**
     * Handle the ProductVariant "created" event.
     */
    public function created(ProductVariant $productVariant): void
    {
        // If this is the first variant for the product and no default exists, set it as default
        if (! $productVariant->is_default && $productVariant->product_id) {
            $hasDefault = DB::table('product_variants')
                ->where('product_id', $productVariant->product_id)
                ->where('is_default', true)
                ->exists();

            if (! $hasDefault) {
                $productVariant->update(['is_default' => true]);
            }
        }
    }

    /**
     * Handle the ProductVariant "updated" event.
     */
    public function updated(ProductVariant $productVariant): void
    {
        // If this variant is being set as default, unset all other defaults
        if ($productVariant->is_default && $productVariant->isDirty('is_default')) {
            DB::table('product_variants')
                ->where('product_id', $productVariant->product_id)
                ->where('id', '!=', $productVariant->id)
                ->update(['is_default' => false]);
        }
    }

    /**
     * Handle the ProductVariant "deleted" event.
     * If the deleted variant was default, set another variant as default.
     */
    public function deleted(ProductVariant $productVariant): void
    {
        if ($productVariant->is_default && $productVariant->product_id) {
            // Find another variant for this product and set it as default
            $nextVariant = DB::table('product_variants')
                ->where('product_id', $productVariant->product_id)
                ->where('id', '!=', $productVariant->id)
                ->orderBy('sort_order')
                ->first();

            if ($nextVariant) {
                DB::table('product_variants')
                    ->where('id', $nextVariant->id)
                    ->update(['is_default' => true]);
            }
        }
    }
}
