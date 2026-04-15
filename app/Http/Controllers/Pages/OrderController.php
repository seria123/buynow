<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Models\Sales\Invoice;
use App\Models\Sales\Promotion;
use App\Services\PromotionService;
use Inertia\Inertia;

class OrderController extends Controller
{
    // -----------------------------
    // List all orders for the user
    // -----------------------------
  public function index()
{
    $orders = Order::with(['orderItems.product', 'refunds', 'transactions'])
        ->where('user_id', auth()->id())
        ->orderBy('created_at', 'desc')
        ->get();

    return Inertia::render('Orders/Index', [
        'orders' => $orders,
    ]);
}


    // -----------------------------
    // Show a single order
    // -----------------------------
 public function show(Order $order)
 {
    // Ensure user owns the order
    if ($order->user_id !== auth()->id()) {
        abort(403, 'Unauthorized');
    }

    // Load relationships including refunds
    $order->load(['orderItems.product', 'refunds', 'transactions']);

   $itemsTotal = $order->orderItems->sum(function ($item) {
       return (float) $item->subtotal;
   });

   $vat = $itemsTotal * 0.16;
   $discount = 0;
   $shipping = 150;

   $grandTotal = $itemsTotal + $vat + $shipping - $discount;

   return Inertia::render('Orders/Show', [
       'order' => $order,
       'invoice' => $order->invoice,
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
    // -----------------------------
 public function checkout(Request $request)
{
    $user = Auth::user();
    $cartItems = $user->cartItems;

    if ($cartItems->isEmpty()) {
        return back()->with('error', 'Your cart is empty.');
    }

    $promotionService = app(PromotionService::class);

    $appliedPromoId = $request->session()->get('applied_promo_id');
    $appliedPromoCode = $request->session()->get('applied_promo_code');

    $cartTotals = $promotionService->calculateCartTotals(
        $cartItems,
        $user,
        $appliedPromoId
    );

    $subtotal = $cartTotals['subtotal'];
    $discount = $cartTotals['discount'];
    $shipping = $cartTotals['shipping'];
    $promotion = $cartTotals['promotion'];
    $freeShipping = $cartTotals['free_shipping'];

    $stores = $cartItems->groupBy(fn($item) => $item->product->store_id);

    $orders = [];

    foreach ($stores as $storeId => $itemsForStore) {

        $orderItemsTotal = $itemsForStore->sum(
            fn($item) => $item->product->price * $item->quantity
        );

        $ratio = $subtotal > 0 ? $orderItemsTotal / $subtotal : 0;

        $orderDiscount = round($discount * $ratio, 2);
        $orderShipping = $freeShipping ? 0 : round($shipping * $ratio, 2);

        $orderTotal = max(0, $orderItemsTotal - $orderDiscount + $orderShipping);

        $order = Order::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'store_id' => $storeId,
            'total_amount' => $orderTotal,
            'subtotal' => $orderItemsTotal,
            'discount_amount' => $orderDiscount,
            'shipping_cost' => $orderShipping,
            'promotion_id' => $promotion?->id,
            'promotion_code' => $appliedPromoCode,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $order->update([
            'order_number' => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4))
        ]);

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

        Invoice::create([
            'order_id' => $order->id,
            'subtotal' => $orderItemsTotal,
            'tax_amount' => 0,
            'total_amount' => $orderTotal,
            'status' => 'pending',
            'invoice_date' => now()->toDateString(),
        ]);

        if ($promotion) {
            $promotionService->recordCouponUsage(
                $promotion,
                $user->id,
                $order->id,
                $orderDiscount
            );
        }

        $orders[] = $order;
    }

    // 🧹 cleanup
    $user->cartItems()->delete();

    $request->session()->forget([
        'applied_promo_code',
        'applied_promo_id',
    ]);

    $firstOrder = $orders[0];

    return redirect()->route('orders.success', $firstOrder->id);
}
   

// -----------------------------
// Order success page
// -----------------------------
public function success(Order $order)
{
    // Ensure user owns the order
    if ($order->user_id !== auth()->id()) {
        abort(403, 'Unauthorized');
    }

    // Load relationships
    $order->load(['orderItems.product', 'transactions']);

    // Calculate summary
    $itemsTotal = $order->orderItems->sum(function ($item) {
        return (float) $item->subtotal;
    });

    $vat = $itemsTotal * 0.16;
    $discount = $order->discount ?? 0;
    $shipping = $order->shipping_fee ?? 150;
    $grandTotal = $itemsTotal + $vat + $shipping - $discount;

    return Inertia::render('Orders/Success', [
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
    // Pay an order
    // -----------------------------
    public function pay(Request $request, Order $order)
    {
        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'Already paid']);
        }

        // Validate phone number
        $request->validate([
            'phone' => 'required|string|min:10|max:12',
        ]);

        // Call Mpesa STK Push API via PaymentController
        $paymentController = new PaymentController();
        $mpesaResponse = $paymentController->mpesaPay($request, $order);

        return $mpesaResponse;
    }

public function callback(Request $request)
{
    $data = $request->all();

    $resultCode = $data['Body']['stkCallback']['ResultCode'];

    if ($resultCode == 0) {

        $checkoutRequestID = $data['Body']['stkCallback']['CheckoutRequestID'];

        $order = Order::where('checkout_request_id', $checkoutRequestID)->first();

        if ($order) {
            // Extract M-Pesa details from callback
            $callbackItems = $data['Body']['stkCallback']['CallbackMetadata']['Item'] ?? [];
            $mpesaTransactionId = null;
            $mpesaPhoneNumber = null;

            foreach ($callbackItems as $item) {
                if ($item['Name'] === 'MpesaReceiptNumber') {
                    $mpesaTransactionId = $item['Value'];
                }
                if ($item['Name'] === 'PhoneNumber') {
                    $mpesaPhoneNumber = $item['Value'];
                }
            }

            // Create transaction record
            \App\Models\Sales\Transaction::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'amount' => $order->total_amount,
                'currency' => 'KES',
                'type' => 'payment',
                'status' => 'completed',
                'payment_method' => 'mpesa',
                'gateway' => 'mpesa',
                'gateway_transaction_id' => $checkoutRequestID,
                'mpesa_transaction_id' => $mpesaTransactionId,
                'mpesa_phone_number' => $mpesaPhoneNumber,
                'gateway_response_code' => (string) $resultCode,
                'gateway_response_message' => 'Payment successful',
                'gateway_response_data' => $data,
                'customer_email' => $order->user?->email,
                'customer_phone' => $mpesaPhoneNumber,
                'processed_at' => now(),
            ]);

            // Use the notification method to update payment status and send notification
            $order->updatePaymentStatus('paid');
            $order->updateStatus('processing');

            // Create invoice if it doesn't exist, or update existing one
            if (!$order->invoice) {
                // Calculate totals from order items
                $subtotal = $order->orderItems->sum(function ($item) {
                    return $item->price * $item->quantity;
                });

                $taxAmount = $subtotal * 0.16; // 16% VAT
                $totalAmount = $subtotal + $taxAmount;

                Invoice::create([
                    'order_id' => $order->id,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'status' => 'paid',
                    'invoice_date' => now()->toDateString(),
                ]);
            } else {
                // Update existing invoice status to paid
                $order->invoice->update(['status' => 'paid']);
            }
        }
    }
}
    // -----------------------------
    // Delete any order
    // -----------------------------
   public function destroy(Order $order)
   {
    $user = auth()->user();

    // Make sure user owns the order
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

    // Also load transactions and invoice for more info
    $order->load(['transactions', 'invoice']);

    return response()->json([
        'payment_status' => $order->payment_status,
        'status' => $order->status,
        'transactions' => $order->transactions,
        'checkout_request_id' => $order->checkout_request_id,
        'invoice' => $order->invoice,
        'receipt_url' => $order->receipt_path ? asset('storage/' . $order->receipt_path) : null,
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

        $order = Order::with(['orderItems.product'])
            ->where('order_number', $request->order_number)
            ->first();

        if (!$order) {
            return back()->with('error', 'Order not found.');
        }

        $statusSteps = ['pending', 'processing', 'shipped', 'delivered'];

        return Inertia::render('Orders/TrackResult', [
            'order' => $order->load('orderItems.product'),
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
            'return_status' => 'requested'
        ]);
    }
}
