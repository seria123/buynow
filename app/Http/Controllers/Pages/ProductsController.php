<?php

namespace App\Http\Controllers\Pages;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Catalogue\Attribute;
use App\Models\Catalogue\Brand;
use App\Models\Catalogue\Category;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductAttributeValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class ProductsController extends Controller
{
    public function index(Request $request): Response
    {
        [$filters, $category] = $this->resolveFilters($request);

        $productsQuery = $this->baseProductsQuery();

        $this->applyFilters($productsQuery, $filters);

        $products = $productsQuery
            ->orderBy('name')
            ->get()
            ->flatMap(fn (Product $product) => $this->transformProduct($product))
            ->values()
            ->toArray();

        $priceRange = $this->priceRange($category, $filters['category_ids'] ?? []);

        return Inertia::render('Products/Index', [
            'products' => $products,
            'filterCategories' => $this->categoryFilters(),
            'brands' => $this->brandFilters(),
            'attributes' => $this->filterableAttributes($category, $filters['category_ids'] ?? []),
            'priceRange' => $priceRange,
            'filters' => $this->presentFilters($filters, $priceRange, $category),
        ]);
    }

    /**
     * Transform the product into the array expected by the frontend.
     */
    private function transformProduct(Product $product): Collection
    {
        $basePayload = [
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
            'variant_badges' => [],
            'is_variant' => false,
        ];

        $variantCards = $product->variants->map(function ($variant) use ($product) {
            return [
                'id' => $variant->id,
                'name' => $variant->display_name,
                'slug' => $product->slug,
                'price' => $variant->price ?? $product->price,
                'compare_price' => $variant->compare_price ?? $product->compare_price,
                'thumbnail_url' => $variant->thumbnail_url ?? $product->thumbnail_url,
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
                    'total_stock' => $variant->quantity ?? 0,
                    'variant_count' => 0,
                    'has_variants' => false,
                ],
                'variant_badges' => $variant->variantOptions
                    ->map(fn ($option) => [
                        'attribute' => $option->attribute?->name,
                        'value' => $option->value,
                    ])
                    ->filter(fn ($option) => $option['attribute'] && $option['value'])
                    ->values()
                    ->toArray(),
                'is_variant' => true,
            ];
        });

        return collect([$basePayload])->merge($variantCards)->values();
    }

    private function baseProductsQuery(): Builder
    {
        return Product::query()
            ->with([
                'category:id,name,slug',
                'brand:id,name',
                'variants.variantOptions.attribute',
            ])
            ->tap(function (Builder $query) {
                $this->applyVisibilityConstraints($query);
            });
    }

    private function applyVisibilityConstraints(Builder $query): void
    {
        $query->where('status', ProductStatus::Approved)
            ->where('published', true);
    }

    private function resolveFilters(Request $request): array
    {
        $filters = [
            'categories' => [],
            'category_ids' => [],
            'brands' => [],
            'price' => [
                'min' => null,
                'max' => null,
            ],
            'attributes' => [],
        ];

        // Support both single 'category' param (backward compatibility) and 'categories[]' array param
        $categorySlugs = collect(Arr::wrap($request->input('categories', [])))
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Backward compatibility: also check single 'category' param
        if ($singleCategorySlug = $request->string('category')->trim()->toString()) {
            if (! in_array($singleCategorySlug, $categorySlugs)) {
                $categorySlugs[] = $singleCategorySlug;
            }
        }

        // Resolve category slugs to category IDs
        $categories = Category::query()
            ->whereIn('slug', $categorySlugs)
            ->where('is_active', true)
            ->get();

        $filters['categories'] = $categories->pluck('slug')->values()->all();
        $filters['category_ids'] = $categories->pluck('id')->values()->all();

        // For backward compatibility, set the first category as the primary one
        $category = $categories->first();

        $filters['brands'] = collect(Arr::wrap($request->input('brands', [])))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $filters['price']['min'] = $this->sanitizePrice($request->input('price.min'));
        $filters['price']['max'] = $this->sanitizePrice($request->input('price.max'));

        $filters['attributes'] = collect($request->input('attributes', []))
            ->map(fn ($values) => array_values(array_filter(Arr::wrap($values), fn ($value) => filled($value))))
            ->filter()
            ->toArray();

        return [$filters, $category];
    }

    private function sanitizePrice($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $numeric = (float) $value;

        return $numeric >= 0 ? $numeric : null;
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        // Apply category filters with OR logic (products in any of the selected categories)
        if (! empty($filters['category_ids'])) {
            $query->whereIn('category_id', $filters['category_ids']);
        }

        if (! empty($filters['brands'])) {
            $query->whereIn('brand_id', $filters['brands']);
        }

        $minPrice = $filters['price']['min'];
        $maxPrice = $filters['price']['max'];

        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }

        $this->applyAttributeFilters($query, $filters['attributes']);
    }

    private function applyAttributeFilters(Builder $query, array $attributeFilters): void
    {
        foreach ($attributeFilters as $attributeId => $values) {
            $valueList = Arr::wrap($values);

            if (empty($valueList)) {
                continue;
            }

            $query->whereHas('attributeValues', function (Builder $attributeQuery) use ($attributeId, $valueList) {
                $attributeQuery->where('attribute_id', $attributeId)
                    ->whereIn('value', $valueList);
            });
        }
    }

    private function categoryFilters(): array
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->with(['activeChildren' => function ($query) {
                $query->with(['activeChildren']);
            }])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'icon', 'parent_id']);

        return $categories->map(function (Category $category) {
            return $this->formatCategoryWithChildren($category);
        })->toArray();
    }

    private function formatCategoryWithChildren(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'icon' => $category->icon,
            'children' => $category->activeChildren->map(fn (Category $child) => $this->formatCategoryWithChildren($child))->values()->toArray(),
        ];
    }

    private function brandFilters(): array
    {
        return Brand::query()
            ->whereHas('products', fn (Builder $query) => $this->applyVisibilityConstraints($query))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Brand $brand) => [
                'id' => $brand->id,
                'name' => $brand->name,
            ])
            ->toArray();
    }

    private function priceRange(?Category $category = null, array $categoryIds = []): array
    {
        $query = Product::query()
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->tap(function (Builder $builder) use ($category, $categoryIds) {
                $this->applyVisibilityConstraints($builder);

                // Support both single category and multiple categories
                if (! empty($categoryIds)) {
                    $builder->whereIn('category_id', $categoryIds);
                } elseif ($category) {
                    $builder->where('category_id', $category->id);
                }
            });

        $result = $query->first();

        $min = $result?->min_price ?? 0;
        $max = $result?->max_price ?? $min;

        return [
            'min' => (float) $min,
            'max' => (float) $max,
        ];
    }

    private function filterableAttributes(?Category $category = null, array $categoryIds = []): array
    {
        $attributeValueGroups = ProductAttributeValue::query()
            ->select('attribute_id', 'value')
            ->whereNotNull('value')
            ->whereHas('product', function (Builder $query) use ($category, $categoryIds) {
                $this->applyVisibilityConstraints($query);

                // Support both single category and multiple categories
                if (! empty($categoryIds)) {
                    $query->whereIn('category_id', $categoryIds);
                } elseif ($category) {
                    $query->where('category_id', $category->id);
                }
            })
            ->whereHas('attribute', fn (Builder $query) => $query->where('is_active', true))
            ->distinct()
            ->get()
            ->groupBy('attribute_id');

        if ($attributeValueGroups->isEmpty()) {
            return [];
        }

        $attributes = Attribute::query()
            ->whereIn('id', $attributeValueGroups->keys())
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        return $attributeValueGroups
            ->map(function (Collection $values, string $attributeId) use ($attributes) {
                $attribute = $attributes->get($attributeId);

                if (! $attribute) {
                    return null;
                }

                return [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'slug' => $attribute->slug,
                    'type' => $attribute->type?->value,
                    'values' => $values->pluck('value')->filter()->unique()->values()->toArray(),
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }

    private function presentFilters(array $filters, array $priceRange, ?Category $category = null): array
    {
        return [
            'categories' => $filters['categories'] ?? [],
            'brands' => $filters['brands'],
            'price' => [
                'min' => $filters['price']['min'] ?? $priceRange['min'],
                'max' => $filters['price']['max'] ?? $priceRange['max'],
            ],
            'attributes' => $filters['attributes'],
        ];
    }
}
