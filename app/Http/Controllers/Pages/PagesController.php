<?php

namespace App\Http\Controllers\Pages;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Catalogue\Category;
use App\Models\Catalogue\Product;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class PagesController extends Controller
{
    public function index(): Response
    {
        // Get all active parent categories for the category icon bar
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->limit(12)
            ->get();

        // Get featured products with proper transformation
        $featuredProducts = $this->getFeaturedProducts();

        // Get latest products for general display
        $latestProducts = $this->getLatestProducts();

        // Get products on sale (with compare_price set - this acts as special price)
        $saleProducts = $this->getSaleProducts();

        // Extract unique parent categories from featured products
        $featuredCategories = Category::where('is_active', true)
            ->whereNotNull('parent_id')
            ->whereHas('products', function ($query) {
                $query->where('status', ProductStatus::Approved)
                      ->where('published', true)
                      ->where('is_featured', true);
            })
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        // Mock flash sale end time (in real app, this would come from database)
        $flashSaleEndTime = Carbon::now()->addHours(12)->toIso8601String();

        return Inertia::render('Index', [
            'categories' => $categories,
            'featuredCategories' => $featuredCategories,
            'featuredProducts' => $featuredProducts,
            'latestProducts' => $latestProducts,
            'saleProducts' => $saleProducts,
            'flashSaleEndTime' => $flashSaleEndTime,
        ]);
    }

    /**
     * Get featured products with proper transformation
     */
    private function getFeaturedProducts(): array
    {
        $products = Product::where('status', ProductStatus::Approved)
            ->where('published', true)
            ->where('is_featured', true)
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['category.parent', 'brand', 'variants', 'media'])
            ->limit(12)
            ->get();

        return $products->map(fn($product) => $this->transformProduct($product))->toArray();
    }

    /**
     * Get latest products with proper transformation
     */
    private function getLatestProducts(): array
    {
        $products = Product::where('status', ProductStatus::Approved)
            ->where('published', true)
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['category.parent', 'brand', 'variants', 'media'])
            ->latest()
            ->limit(20)
            ->get();

        return $products->map(fn($product) => $this->transformProduct($product))->toArray();
    }

    /**
     * Get sale products with proper transformation
     */
    private function getSaleProducts(): array
    {
        $products = Product::where('status', ProductStatus::Approved)
            ->where('published', true)
            ->whereNotNull('compare_price')
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['category.parent', 'brand', 'variants', 'media'])
            ->limit(12)
            ->get();

        return $products->map(fn($product) => $this->transformProduct($product))->toArray();
    }

    /**
     * Transform product for frontend display
     */
    private function transformProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'compare_price' => $product->compare_price,
            'thumbnail_url' => $product->thumbnail_url,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'brand' => $product->brand ? [
                'id' => $product->brand->id,
                'name' => $product->brand->name,
            ] : null,
            'stats' => [
                'total_stock' => $product->getTotalStock(),
                'variant_count' => $product->variants->count(),
                'has_variants' => $product->hasVariants(),
            ],
            'is_featured' => $product->is_featured,
            'is_variant' => false,
        ];
    }
}
