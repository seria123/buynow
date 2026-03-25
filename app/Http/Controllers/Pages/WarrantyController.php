<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Sales\Warranty;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarrantyController extends Controller
{
    /**
     * List all warranties for the authenticated user.
     */
    public function index()
    {
        $warranties = Warranty::with(['product', 'order'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Warranties/Index', [
            'warranties' => $warranties,
        ]);
    }

    /**
     * Show a single warranty details.
     */
    public function show(Warranty $warranty)
    {
        // Ensure user owns the warranty
        if ($warranty->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $warranty->load(['product', 'order', 'product.thumbnail']);

        return Inertia::render('Warranties/Show', [
            'warranty' => $warranty,
        ]);
    }
}