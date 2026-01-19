<?php

namespace App\Observers;

use App\Models\Catalogue\Product;
use App\Services\ProductSkuGenerator;

class ProductObserver
{
    public function __construct(
        protected ProductSkuGenerator $skuGenerator
    ) {}

    public function creating(Product $product): void
    {
        if (filled($product->sku)) {
            return;
        }

        $product->sku = $this->skuGenerator->generate($product->category_id);
    }
}
