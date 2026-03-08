<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Sales\OrderItem;
use App\Models\Catalogue\Product;
use App\Models\Sales\Order;

class OrderItemController extends Controller
{
    // List all order items
    public function index()
    {
        $items = OrderItem::with('order', 'product')->latest()->get();
        return Inertia::render('Pages/OrderItems/Index', [
            'items' => $items,
        ]);
    }

    // Show a single order item
    public function show(OrderItem $orderItem)
    {
        $orderItem->load('order', 'product');
        return Inertia::render('Pages/OrderItems/Show', [
            'item' => $orderItem,
        ]);
    }

    // Create form
    public function create()
    {
        $products = Product::all();
        $orders = Order::all();
        return Inertia::render('Pages/OrderItems/Create', [
            'products' => $products,
            'orders' => $orders,
        ]);
    }

    // Store a new order item
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric',
            'subtotal' => 'required|numeric',
        ]);

        $item = OrderItem::create($validated);

        return redirect()->route('order-items.show', $item)->with('success', 'Order item added!');
    }

    // Edit form
    public function edit(OrderItem $orderItem)
    {
        $products = Product::all();
        $orders = Order::all();
        return Inertia::render('Pages/OrderItems/Edit', [
            'item' => $orderItem,
            'products' => $products,
            'orders' => $orders,
        ]);
    }

    // Update order item
    public function update(Request $request, OrderItem $orderItem)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric',
            'subtotal' => 'required|numeric',
        ]);

        $orderItem->update($validated);

        return redirect()->route('order-items.show', $orderItem)->with('success', 'Order item updated!');
    }

    // Delete
    public function destroy(OrderItem $orderItem)
    {
        $orderItem->delete();
        return redirect()->route('order-items.index')->with('success', 'Order item deleted!');
    }
}