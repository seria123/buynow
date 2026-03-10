<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Sales\Wishlist;
use App\Models\Catalogue\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WishlistController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $wishlistItems = Wishlist::with('product')
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($w) {
                $product = $w->product;
                if ($product) {
                    $product->wishlist_id = $w->id;
                    $product->added_at = $w->created_at->toISOString();
                    $product->days_in_wishlist = $w->created_at->diffInDays(now());
                    $product->is_old = $w->created_at->lt(now()->subDays(30));
                }
                return $product;
            })
            ->filter()
            ->values();

        return Inertia::render('Wishlist/Index', [
            'wishlistItems' => $wishlistItems,
        ]);
    }

    public function add(Request $request, Product $product)
    {
        $user = auth()->user();

        \App\Models\Sales\Wishlist::firstOrCreate([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return redirect()->route('wishlist.index')->with('success', 'Product added to wishlist!');
    }

    public function addBySlug(Request $request, string $slug)
    {
        $user = auth()->user();
        
        // Find product by slug
        $product = Product::where('slug', $slug)->first();
        
        if (!$product) {
            return redirect()->back()->with('error', 'Product not found!');
        }

        \App\Models\Sales\Wishlist::firstOrCreate([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return redirect()->route('wishlist.index')->with('success', 'Product added to wishlist!');
    }

    public function remove(Product $product)
    {
        $user = Auth::user();

        Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->delete();

        return redirect()->route('wishlist.index')
            ->with('success', 'Product removed from wishlist.');
    }

    public function deleteOld()
    {
        $user = Auth::user();

        $deleted = Wishlist::where('user_id', $user->id)
            ->where('created_at', '<', Carbon::now()->subDays(30))
            ->delete();

        return redirect()->route('wishlist.index')
            ->with('success', "Removed {$deleted} old wishlist item(s).");
    }
}
