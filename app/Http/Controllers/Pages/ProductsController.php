<?php

namespace App\Http\Controllers\Pages;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Catalogue\Attribute;
use App\Models\Catalogue\Brand;
use App\Models\Catalogue\Category;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductVariant;
use App\Models\Catalogue\ProductAttributeValue;
use App\Models\Catalogue\ProductRating;
use App\Models\Catalogue\ProductComment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;

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
            'images' => $product->getMedia('images')->map(fn ($media) => [
                'url' => $media->getUrl(),
                'thumb_url' => $media->getUrl('thumb'),
            ])->toArray(),
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

    return collect([$basePayload])->merge(
        $product->variants->map(fn ($variant) => [
            'id' => $variant->id,
            'name' => $variant->display_name,
            'slug' => $product->slug,
            'price' => $variant->price ?? $product->price,
            'compare_price' => $variant->compare_price ?? $product->compare_price,
            'thumbnail_url' => $variant->getFirstMediaUrl('thumbnail', 'thumb') 
                               ?: $product->thumbnail_url
                               ?: asset('images/placeholder.svg'),
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
        ])
    )->values();
}
    private function baseProductsQuery(): Builder
    {
        return Product::query()
            ->with([
                'category:id,name,slug',
                'brand:id,name',
                'variants.variantOptions.attribute',
                'media',
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
    public function show(string $slug)
    {
        // Find product by slug since getRouteKeyName returns 'slug'
        $product = Product::where('slug', $slug)->first();
        
        if (!$product) {
            abort(404);
        }
        
        // Load all necessary relations
        $product->load(['category', 'brand', 'variants.variantOptions.attribute', 'media']);
        
        // Check if product is in user's wishlist
        $isInWishlist = false;
        if (auth()->check()) {
            $isInWishlist = \App\Models\Sales\Wishlist::where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->exists();
        }
        
        // Get product ratings
        $ratings = $product->ratings()->with('user:id,name,avatar')->get();
        $averageRating = $ratings->avg('rating') ?? 0;
        $ratingCount = $ratings->count();
        
        // Get user's own rating if logged in
        $userRating = null;
        if (auth()->check()) {
            $userRating = $product->ratings()->where('user_id', auth()->id())->first();
        }
        
        // Get product comments
        $comments = $product->comments()->with('user:id,name,avatar')->latest()->get();
        
        // Transform product for frontend - similar to transformProduct but for single product
        $transformedProduct = $this->transformSingleProduct($product);
        $transformedProduct['is_in_wishlist'] = $isInWishlist;
        $transformedProduct['rating'] = round($averageRating, 1);
        $transformedProduct['rating_count'] = $ratingCount;
        $transformedProduct['user_rating'] = $userRating ? [
            'id' => $userRating->id,
            'rating' => $userRating->rating,
            'comment' => $userRating->comment,
        ] : null;
        $transformedProduct['ratings'] = $ratings->map(fn($r) => [
            'id' => $r->id,
            'rating' => $r->rating,
            'comment' => $r->comment,
            'user' => $r->user ? [
                'id' => $r->user->id,
                'name' => $r->user->name,
                'avatar' => $r->user->avatar,
            ] : null,
            'created_at' => $r->created_at,
        ])->toArray();
        $transformedProduct['comments'] = $comments->map(fn($c) => [
            'id' => $c->id,
            'comment' => $c->comment,
            'user' => $c->user ? [
                'id' => $c->user->id,
                'name' => $c->user->name,
                'avatar' => $c->user->avatar,
            ] : null,
            'created_at' => $c->created_at,
        ])->toArray();
        
        return Inertia::render('Products/Show', [
            'product' => $transformedProduct,
        ]);
    }
    
    /**
     * Transform a single product for the frontend show page.
     */
    private function transformSingleProduct(Product $product): array
    {
        // Get thumbnail from 'thumbnail' collection or fallback to first image from 'images' collection
        $thumbnailUrl = $product->thumbnail_url;
        
        // If no thumbnail from 'thumbnail' collection, try getting first image from 'images' collection
        if (!$thumbnailUrl || $thumbnailUrl === asset('images/placeholder.svg')) {
            $firstImage = $product->getMedia('images')->first();
            if ($firstImage) {
                $thumbnailUrl = $firstImage->getUrl('thumb') ?: $firstImage->getUrl();
            }
        }
        
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'compare_price' => $product->compare_price,
            'thumbnail_url' => $thumbnailUrl,
            'images' => $product->getMedia('images')->map(fn ($media) => [
                'url' => $media->getUrl(),
                'thumb_url' => $media->getUrl('thumb'),
            ])->toArray(),
            'description' => $product->description,
            'short_description' => $product->short_description,
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
            // Rating and reviews
            'rating' => $product->rating ?? 4.0,
            'rating_count' => $product->rating_count ?? rand(10, 100),
            // Shipping info - calculate as percentage of product price
            // 5% of price, minimum KSh 100, maximum KSh 500, free for orders >= KSh 1000
            'shipping_fee' => $product->price >= 1000 
                ? 0 
                : min(max(round($product->price * 0.05), 100), 500),
            'variant_badges' => [],
            'is_variant' => false,
            'variants' => $product->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'name' => $variant->display_name,
                'price' => $variant->price ?? $product->price,
                'compare_price' => $variant->compare_price ?? $product->compare_price,
                'thumbnail_url' => $variant->getFirstMediaUrl('thumbnail', 'thumb') 
                                   ?: $product->thumbnail_url
                                   ?: asset('images/placeholder.svg'),
                'quantity' => $variant->quantity ?? 0,
                'variantOptions' => $variant->variantOptions->map(fn ($option) => [
                    'attribute' => $option->attribute?->name,
                    'value' => $option->value,
                ])->filter(fn ($option) => $option['attribute'] && $option['value'])->values()->toArray(),
            ])->toArray(),
        ];
    }
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'slug' => 'nullable|string|max:255|unique:products,slug',
        'sku' => 'nullable|string|max:100|unique:products,sku',
        'brand_id' => 'nullable|exists:brands,id',
        'category_id' => 'nullable|exists:categories,id',
        'price' => 'required|numeric|min:0',
        'compare_price' => 'nullable|numeric|min:0',
        'cost' => 'nullable|numeric|min:0',
        'quantity' => 'nullable|integer|min:0',
        'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        // add other fields as needed
    ]);

    // Create product with fillable fields
    $product = Product::create($request->only([
        'name', 'slug', 'sku', 'brand_id', 'category_id', 
        'price', 'compare_price', 'cost', 'quantity', 
        'short_description', 'description', 
        'status', 'published', 'is_featured'
    ]));

    // Handle thumbnail upload
    if ($request->hasFile('thumbnail')) {
        $product->addMedia($request->file('thumbnail'))
                ->toMediaCollection('thumbnail', 'media'); // store in public disk
    }

    return redirect()->route('products.index')
                     ->with('success', 'Product created successfully.');
}
public function update(Request $request, Product $product)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
        'sku' => 'nullable|string|max:100|unique:products,sku,' . $product->id,
        'brand_id' => 'nullable|exists:brands,id',
        'category_id' => 'nullable|exists:categories,id',
        'price' => 'required|numeric|min:0',
        'compare_price' => 'nullable|numeric|min:0',
        'cost' => 'nullable|numeric|min:0',
        'quantity' => 'nullable|integer|min:0',
        'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        // add other fields as needed
    ]);

    // Update fillable fields
    $product->update($request->only([
        'name', 'slug', 'sku', 'brand_id', 'category_id', 
        'price', 'compare_price', 'cost', 'quantity', 
        'short_description', 'description', 
        'status', 'published', 'is_featured'
    ]));

    // Replace old thumbnail if new file uploaded
    if ($request->hasFile('thumbnail')) {
        $product->clearMediaCollection('thumbnail'); // deletes old thumbnail
        $product->addMedia($request->file('thumbnail'))
                ->toMediaCollection('thumbnail', 'media');
    }

    return redirect()->route('products.index')
                     ->with('success', 'Product updated successfully.');
}

/**
 * Store a rating for a product.
 */
public function storeRating(Request $request, Product $product)
{
    $request->validate([
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string|max:500',
    ]);

    // Check if user already rated this product
    $existingRating = ProductRating::where('product_id', $product->id)
        ->where('user_id', auth()->id())
        ->first();

    if ($existingRating) {
        // Update existing rating
        $existingRating->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);
        
        return response()->json([
            'message' => 'Rating updated successfully',
            'rating' => $existingRating,
        ]);
    }

    // Create new rating
    $rating = ProductRating::create([
        'product_id' => $product->id,
        'user_id' => auth()->id(),
        'rating' => $request->rating,
        'comment' => $request->comment,
    ]);

    return response()->json([
        'message' => 'Rating submitted successfully',
        'rating' => $rating,
    ]);
}

/**
 * Delete a rating for a product.
 */
public function deleteRating(Product $product)
{
    $rating = ProductRating::where('product_id', $product->id)
        ->where('user_id', auth()->id())
        ->first();

    if (!$rating) {
        return response()->json(['message' => 'Rating not found'], 404);
    }

    $rating->delete();

    return response()->json(['message' => 'Rating deleted successfully']);
}

/**
 * Store a comment for a product.
 */
public function storeComment(Request $request, Product $product)
{
    $request->validate([
        'comment' => 'required|string|max:1000',
    ]);

    $comment = ProductComment::create([
        'product_id' => $product->id,
        'user_id' => auth()->id(),
        'comment' => $request->comment,
    ]);

    $comment->load('user');

    return response()->json([
        'message' => 'Comment added successfully',
        'comment' => $comment,
    ]);
}
}
