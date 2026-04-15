<?php

namespace App\Services;

use App\Models\Catalogue\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class RecentlyViewedProducts
{
    private const SESSION_KEY = 'recently_viewed_products';
    private const MAX_ITEMS = 8;

    /**
     * Record a product as viewed.
     * For logged-in users, store in database.
     * For guests, store in session.
     */
    public function recordView(string $productId): void
    {
        if (Auth::check()) {
            $this->recordViewForUser(Auth::user(), $productId);
        } else {
            $this->recordViewForGuest($productId);
        }
    }

    /**
     * Record a product view for authenticated user (database).
     */
    private function recordViewForUser(User $user, string $productId): void
    {
        // Check if record exists
        $exists = DB::table('recently_viewed_products')
            ->where('user_id', $user->id)
            ->where('product_id', $productId)
            ->exists();

        if ($exists) {
            // Update the viewed_at timestamp
            DB::table('recently_viewed_products')
                ->where('user_id', $user->id)
                ->where('product_id', $productId)
                ->update([
                    'viewed_at' => now(),
                    'updated_at' => now(),
                ]);
        } else {
            // Insert new record with UUID
            DB::table('recently_viewed_products')->insert([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'user_id' => $user->id,
                'product_id' => $productId,
                'viewed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Keep only the last MAX_ITEMS viewed products
        $this->pruneOldViewsForUser($user->id);
    }

    /**
     * Prune old views keeping only the last MAX_ITEMS.
     */
    private function pruneOldViewsForUser(string $userId): void
    {
        // Get IDs of items to keep (most recent)
        $keepIds = DB::table('recently_viewed_products')
            ->where('user_id', $userId)
            ->orderBy('viewed_at', 'desc')
            ->limit(self::MAX_ITEMS)
            ->pluck('id');

        // Delete all except the ones to keep
        DB::table('recently_viewed_products')
            ->where('user_id', $userId)
            ->whereNotIn('id', $keepIds)
            ->delete();
    }

    /**
     * Record a product view for guest user (session).
     */
    private function recordViewForGuest(string $productId): void
    {
        $viewedProducts = Session::get(self::SESSION_KEY, []);

        // Remove if already exists (to update position)
        $viewedProducts = array_filter($viewedProducts, function ($id) use ($productId) {
            return $id != $productId;
        });

        // Add to the beginning (most recent)
        array_unshift($viewedProducts, $productId);

        // Keep only the last MAX_ITEMS
        $viewedProducts = array_slice($viewedProducts, 0, self::MAX_ITEMS);

        // Re-index array
        $viewedProducts = array_values($viewedProducts);

        Session::put(self::SESSION_KEY, $viewedProducts);
    }

    /**
     * Get recently viewed products.
     * Returns collection of Product models with id, name, price, thumbnail_url.
     */
    public function getRecentProducts(int $limit = self::MAX_ITEMS, string $excludeProductId = null): array
    {
        if (Auth::check()) {
            return $this->getRecentProductsForUser(Auth::user(), $limit, $excludeProductId);
        }

        return $this->getRecentProductsForGuest($limit, $excludeProductId);
    }

    /**
     * Get recently viewed products for authenticated user.
     */
    private function getRecentProductsForUser(User $user, int $limit, string $excludeProductId = null): array
    {
        $query = DB::table('recently_viewed_products')
            ->where('user_id', $user->id)
            ->orderBy('viewed_at', 'desc')
            ->limit($limit + 1); // Get one extra to check if we need to filter

        $productIds = $query->pluck('product_id')->toArray();

        // Filter out the excluded product if provided
        if ($excludeProductId !== null) {
            $productIds = array_filter($productIds, function ($id) use ($excludeProductId) {
                return $id != $excludeProductId;
            });
            $productIds = array_values($productIds);
            $productIds = array_slice($productIds, 0, $limit);
        }

        return $this->getProductsByIds($productIds);
    }

    /**
     * Get recently viewed products for guest user (session).
     */
    private function getRecentProductsForGuest(int $limit, string $excludeProductId = null): array
    {
        $viewedProducts = Session::get(self::SESSION_KEY, []);

        // Filter out the excluded product if provided
        if ($excludeProductId !== null) {
            $viewedProducts = array_filter($viewedProducts, function ($id) use ($excludeProductId) {
                return $id != $excludeProductId;
            });
            $viewedProducts = array_values($viewedProducts);
        }

        // Get the first $limit items
        $productIds = array_slice($viewedProducts, 0, $limit);

        return $this->getProductsByIds($productIds);
    }

    /**
     * Get products by their IDs.
     */
    private function getProductsByIds(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $products = Product::whereIn('id', $productIds)
            ->get(['id', 'name', 'slug', 'price', 'compare_price'])
            ->map(function ($product) {
                // Get thumbnail URL
                $media = $product->getFirstMedia('thumbnail');
                $thumbnailUrl = $media?->getUrl() ?? null;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->price,
                    'compare_price' => $product->compare_price,
                    'thumbnail_url' => $thumbnailUrl,
                ];
            });

        // Sort by the order of productIds
        $sortedProducts = [];
        foreach ($productIds as $productId) {
            $product = $products->firstWhere('id', $productId);
            if ($product) {
                $sortedProducts[] = $product;
            }
        }

        return $sortedProducts;
    }
}