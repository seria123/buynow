<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Sales\Promotion;
use Inertia\Inertia;

class PromotionPageController extends Controller
{
    // Display all active promotions for customers
    public function index()
    {
        $promotions = Promotion::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->where(function ($query) {
                $query->whereNull('usage_limit')
                    ->orWhereRaw('used_count < usage_limit');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Promotions/Index', [
            'promotions' => $promotions,
        ]);
    }
}
