<?php

namespace App\Http\Controllers\Pages;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Catalogue\Product;
use Inertia\Inertia;
use Inertia\Response;

class PagesController extends Controller
{
    public function index(): Response
    {
        // Get featured products
        $featuredProducts = Product::where('status', ProductStatus::Approved)
            ->where('published', true)
            ->where('is_featured', true)
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->with('category.parent')
            ->get();

        // Extract unique parent categories from featured products
        // If a product's category has a parent, use the parent; otherwise use the category itself
        $featuredCategories = $featuredProducts
            ->pluck('category')
            ->filter()
            ->map(function ($category) {
                // Get parent category if exists, otherwise use the category itself
                return $category->parent ?? $category;
            })
            ->filter(function ($category) {
                // Only include parent categories (where parent_id is null)
                return $category->parent_id === null && $category->is_active;
            })
            ->unique('id')
            ->sortBy('sort_order')
            ->values();

        return Inertia::render('Index', [
            'featuredCategories' => $featuredCategories,
        ]);
    }
}
