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

        // Get featured products
        $featuredProducts = Product::where('status', ProductStatus::Approved)
            ->where('published', true)
            ->where('is_featured', true)
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->with('category.parent')
            ->limit(12)
            ->get();

        // Get latest products for general display
        $latestProducts = Product::where('status', ProductStatus::Approved)
            ->where('published', true)
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->with('category.parent')
            ->latest()
            ->limit(20)
            ->get();

        // Get products on sale (with compare_price set - this acts as special price)
        $saleProducts = Product::where('status', ProductStatus::Approved)
            ->where('published', true)
            ->whereNotNull('compare_price')
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->with('category.parent')
            ->limit(12)
            ->get();

        // Extract unique parent categories from featured products
        $featuredCategories = $featuredProducts
            ->pluck('category')
            ->filter()
            ->map(function ($category) {
                return $category->parent ?? $category;
            })
            ->filter(function ($category) {
                return $category->parent_id === null && $category->is_active;
            })
            ->unique('id')
            ->sortBy('sort_order')
            ->values();

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
}
