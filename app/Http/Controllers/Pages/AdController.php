<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdController extends Controller
{
    /**
     * Get active ads for a specific position.
     */
    public function index(Request $request): JsonResponse
    {
        $position = $request->query('position', 'home');
        $limit = (int) $request->query('limit', 5);
        $random = $request->boolean('random', false);

        $query = Ad::query()
            ->active()
            ->valid()
            ->byPosition($position)
            ->ordered();

        if ($random) {
            $ads = $query->inRandomOrder()->take($limit)->get();
        } else {
            $ads = $query->take($limit)->get();
        }

        // Track impressions
        foreach ($ads as $ad) {
            $ad->incrementImpressions();
        }

        return response()->json([
            'ads' => $ads->map(fn ($ad) => [
                'id' => $ad->id,
                'title' => $ad->title,
                'subtitle' => $ad->subtitle,
                'description' => $ad->description,
                'image' => $ad->image,
                'mobile_image' => $ad->mobile_image,
                'link' => $ad->link,
                'type' => $ad->type,
                'position' => $ad->position,
                'product' => $ad->product ? [
                    'id' => $ad->product->id,
                    'name' => $ad->product->name,
                    'slug' => $ad->product->slug,
                    'price' => $ad->product->price,
                    'sale_price' => $ad->product->sale_price,
                ] : null,
                'category' => $ad->category ? [
                    'id' => $ad->category->id,
                    'name' => $ad->category->name,
                    'slug' => $ad->category->slug,
                ] : null,
            ]),
        ]);
    }

    /**
     * Track ad click.
     */
    public function trackClick(Ad $ad): JsonResponse
    {
        $ad->incrementClicks();

        return response()->json([
            'success' => true,
            'clicks' => $ad->clicks,
        ]);
    }

    /**
     * Track ad impression.
     */
    public function trackImpression(Ad $ad): JsonResponse
    {
        $ad->incrementImpressions();

        return response()->json([
            'success' => true,
            'impressions' => $ad->impressions,
        ]);
    }
}
