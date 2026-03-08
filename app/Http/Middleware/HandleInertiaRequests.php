<?php

namespace App\Http\Middleware;

use App\Models\Catalogue\Category;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
                'status' => fn () => $request->session()->get('status'),
            ],
            'auth' => [
                'user' => fn () => $request->user() ? $request->user()->load('media') : null,
            ],
            'categories' => fn () => $this->navigationCategories(),
        ];
    }

    private function navigationCategories(): array
    {
        try {
            return Cache::remember(
                'navigation.categories',
                now()->addMinutes(10),
                fn () => Category::query()
                    ->select(['id', 'name', 'slug', 'sort_order', 'icon', 'parent_id'])
                    ->root()
                    ->active()
                    ->ordered()
                    ->with(['navProducts.variants'])
                    ->tap(fn ($query) => $this->withNestedChildren($query))
                    ->get()
                    ->map(fn (Category $category) => $this->transformCategoryForNav($category))
                    ->toArray()
            );
        } catch (\Exception $e) {
            \Log::error('Error loading categories in HandleInertiaRequests: '.$e->getMessage());

            return [];
        }
    }

    private function transformCategoryForNav(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'icon' => $category->icon,
            'sort_order' => $category->sort_order,
            'children' => $category->activeChildren
                ->map(fn (Category $child) => $this->transformCategoryForNav($child))
                ->values()
                ->all(),
            'products' => $category->navProducts
                ->map(fn (Product $product) => $this->transformProductForNav($product))
                ->values()
                ->all(),
        ];
    }

    private function transformProductForNav(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'compare_price' => $product->compare_price,
            'thumbnail_url' => $product->thumbnail_url,
            'variants' => $product->variants
                ->map(fn (ProductVariant $variant) => [
                    'id' => $variant->id,
                    'name' => $variant->display_name,
                    'price' => $variant->effective_price,
                    'compare_price' => $variant->effective_compare_price,
                    'thumbnail_url' => $variant->thumbnail_url,
                ])
                ->values()
                ->all(),
        ];
    }

    private function withNestedChildren($query, int $depth = 3): void
    {
        if ($depth <= 0) {
            return;
        }

        $query->with(['activeChildren' => function ($childQuery) use ($depth) {
            $childQuery->select(['id', 'name', 'slug', 'sort_order', 'icon', 'parent_id'])
                ->active()
                ->ordered()
                ->with(['navProducts.variants']);

            $this->withNestedChildren($childQuery, $depth - 1);
        }]);
    }
}
