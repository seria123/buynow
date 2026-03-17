<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales\Store;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    private function adminOrAbort()
    {
        if (!Auth::check() || !Auth::user()->hasRole('admin')) {
            abort(403, 'Only admins can perform this action.');
        }
    }

    // Display a list of stores (public)
    public function index()
    {
        $stores = Store::where('is_active', true)->latest()->get();

        return Inertia::render('Stores/Index', [
            'stores'  => $stores,
            'isAdmin' => Auth::check() && Auth::user()->hasRole('admin'),
        ]);
    }

    // Show form to create a new store (admin only)
    public function create()
    {
        $this->adminOrAbort();
        return Inertia::render('Stores/Create');
    }

    // Store a new store in the database (admin only)
    public function store(Request $request)
    {
        $this->adminOrAbort();

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'email'       => 'nullable|email|unique:stores,email',
            'phone'       => 'nullable|string|max:20',
            'address'     => 'nullable|string|max:500',
            'city'        => 'nullable|string|max:100',
            'state'       => 'nullable|string|max:100',
            'country'     => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'latitude'    => 'nullable|numeric',
            'longitude'   => 'nullable|numeric',
            'is_active'   => 'nullable|boolean',
        ]);

        Store::create($validated);

        return redirect()->route('stores.index')->with('success', 'Store created successfully.');
    }

    // Display a single store (public)
    public function show(Store $store)
    {
        // Only show active stores to public users
        if (!$store->is_active) {
            abort(404);
        }

        return Inertia::render('Stores/Show', [
            'store'   => $store,
            'isAdmin' => Auth::check() && Auth::user()->hasRole('admin'),
        ]);
    }

    // Show form to edit a store (admin only)
    public function edit(Store $store)
    {
        $this->adminOrAbort();
        return Inertia::render('Stores/Edit', [
            'store' => $store
        ]);
    }

    // Update a store in the database (admin only)
    public function update(Request $request, Store $store)
    {
        $this->adminOrAbort();

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'email'       => 'nullable|email|unique:stores,email,' . $store->id,
            'phone'       => 'nullable|string|max:20',
            'address'     => 'nullable|string|max:500',
            'city'        => 'nullable|string|max:100',
            'state'       => 'nullable|string|max:100',
            'country'     => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'latitude'    => 'nullable|numeric',
            'longitude'   => 'nullable|numeric',
            'is_active'   => 'nullable|boolean',
        ]);

        $store->update($validated);

        return redirect()->route('stores.index')->with('success', 'Store updated successfully.');
    }

    // Delete a store (admin only)
    public function destroy(Store $store)
    {
        $this->adminOrAbort();
        $store->delete();
        return redirect()->route('stores.index')->with('success', 'Store deleted successfully.');
    }
}
