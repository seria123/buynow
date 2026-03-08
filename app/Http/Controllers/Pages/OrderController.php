<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use Inertia\Inertia;

class OrderController extends Controller
{
    // -----------------------------
    // List all orders for the user
    // -----------------------------
  public function index()
{
    $orders = Order::with('orderItems.product')
        ->where('user_id', auth()->id()) // ← make sure auth()->id() matches the current logged-in user
        ->orderBy('created_at', 'desc')
        ->get();
  $orders->each(function ($order) {
        $order->orderItems->each(function ($item) {
            if ($item->product) {
                $item->product->thumbnail_url =
                    $item->product->getFirstMediaUrl('thumbnail');
            }
        });
    });

    return Inertia::render('Orders/Index', [
        'orders' => $orders,
    ]);
}


    // -----------------------------
    // Show a single order
    // -----------------------------
 public function show(Order $order)
{
  $order->load('orderItems.product');

    $itemsTotal = $order->orderItems->sum(function ($item) {
        return (float) $item->subtotal;
    });

    $vat = $itemsTotal * 0.16;
    $discount = 0;
    $shipping = 150;

    $grandTotal = $itemsTotal + $vat + $shipping - $discount;

    return Inertia::render('Orders/Show', [
        'order' => $order,
        'summary' => [
            'itemsTotal' => $itemsTotal,
            'vat' => $vat,
            'shipping' => $shipping,
            'discount' => $discount,
            'grandTotal' => $grandTotal,
        ]
    ]);
}

    // -----------------------------
    // Checkout the cart
  public function checkout(Request $request)
{
    $user = Auth::user();
    $cartItems = $user->cartItems; // assumes User->cartItems relationship

    if ($cartItems->isEmpty()) {
        return redirect()->back()->with('error', 'Your cart is empty.');
    }

    // Group cart items by store_id
    $stores = $cartItems->groupBy(fn($item) => $item->product->store_id);

    foreach ($stores as $storeId => $itemsForStore) {
        // Create order for this store
        $order = Order::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'store_id' => $storeId,
            'total_amount' => $itemsForStore->sum(fn($item) => $item->product->price * $item->quantity),
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        // Generate human-friendly order number
        $datePart = now()->format('Ymd');
        $randomPart = strtoupper(Str::random(4));
        $order->order_number = "ORD-{$datePart}-{$randomPart}";
        $order->save();

        // Create order items
        foreach ($itemsForStore as $cartItem) {
            $product = $cartItem->product;
            OrderItem::create([
                'id' => (string) Str::uuid(),
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $cartItem->quantity,
                'price' => $product->price,
                'subtotal' => $product->price * $cartItem->quantity,
            ]);
        }
    }

    // Clear the cart
    $user->cartItems()->delete();

    return redirect()->route('orders.index')->with('success', 'Orders placed successfully!');
}

    // -----------------------------
    // Pay an order
    // -----------------------------
    public function pay(Request $request, Order $order)
{
    if ($order->payment_status === 'paid') {
        return response()->json(['message' => 'Already paid']);
    }

    // Call Mpesa STK Push API here

    return response()->json([
        'message' => 'Mpesa prompt sent. Please check your phone.'
    ]);
}

public function callback(Request $request)
{
    $data = $request->all();

    $resultCode = $data['Body']['stkCallback']['ResultCode'];

    if ($resultCode == 0) {

        $checkoutRequestID = $data['Body']['stkCallback']['CheckoutRequestID'];

        $order = Order::where('checkout_request_id', $checkoutRequestID)->first();

        if ($order) {
            $order->payment_status = 'paid';
            $order->status = 'processing';
            $order->save();
        }
    }
}
    // -----------------------------
    // Delete any order
    // -----------------------------
   public function destroy(Order $order)
{
    $user = auth()->user();

    // Make sure user owns this order
    if ($order->user_id !== $user->id) {
        abort(403, 'Unauthorized');
    }

    $order->delete(); // orderItems will cascade if your migration has onDelete('cascade')

    return response()->json([
        'message' => 'Order deleted successfully'
    ]);
}
public function status(Order $order)
{
    // Make sure the authenticated user owns the order
    if ($order->user_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    return response()->json([
        'payment_status' => $order->payment_status,
        'status' => $order->status,
    ]);
}
public function trackForm()
    {
        return Inertia::render('Orders/TrackForm');
    }

    // Handle tracking
    public function track(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string|exists:orders,order_number',
        ]);

        $order = Order::where('order_number', $request->order_number)
            ->with('orderItems.product')
            ->first();

        if (!$order) {
            return back()->with('error', 'Order not found.');
        }

        $statusSteps = ['pending', 'processing', 'shipped', 'delivered'];

        return Inertia::render('Orders/TrackResult', [
            'order' => $order,
            'statusSteps' => $statusSteps,
        ]);
    }

    // -----------------------------
    // Request a return for a delivered order
    // -----------------------------
    public function requestReturn(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if ($order->status !== 'delivered') {
            return response()->json(['message' => 'Only delivered orders can be returned.'], 422);
        }

        if ($order->return_status) {
            return response()->json(['message' => 'A return has already been requested for this order.'], 422);
        }

        $order->update(['return_status' => 'requested']);

        return response()->json([
            'message' => 'Return request submitted successfully. Our team will review it within 24 hours.',
            'return_status' => 'requested',
        ]);
    }
}