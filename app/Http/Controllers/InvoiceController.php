<?php

namespace App\Http\Controllers;

use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices (Admin)
     */
    public function index()
    {
        $invoices = Invoice::with('order.user', 'order')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('invoices.admin-index', [
            'invoices' => $invoices,
        ]);
    }

    /**
     * Show invoice details (Admin)
     */
    public function show($id)
    {
        $invoice = Invoice::with(['order.user', 'order.orderItems'])
            ->findOrFail($id);

        return view('invoices.admin-show', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Generate PDF for invoice
     */
    public function downloadPdf($id)
    {
        $invoice = Invoice::with(['order.user', 'order.orderItems', 'order.store'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'order' => $invoice->order,
        ]);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * View invoice in browser (Admin)
     */
    public function viewPdf($id)
    {
        $invoice = Invoice::with(['order.user', 'order.orderItems.product', 'order.orderItems.variant', 'order.store'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'order' => $invoice->order,
        ]);

        return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Customer: View their invoice for an order
     */
    public function customerView(Order $order)
    {
        // Ensure the user owns this order
        if (Auth::id() !== $order->user_id && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized access to this order.');
        }

        $invoice = Invoice::with(['order.orderItems', 'order.store'])
            ->where('order_id', $order->id)
            ->firstOrFail();

        return inertia('Orders/Invoice', [
            'invoice' => $invoice,
            'order' => $order->load(['orderItems', 'store']),
        ]);
    }

    /**
     * Customer: Download their invoice PDF
     */
    public function customerDownloadPdf(Order $order)
    {
        // Ensure the user owns this order
        if (Auth::id() !== $order->user_id && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized access to this order.');
        }

        $invoice = Invoice::with(['order.user', 'order.orderItems', 'order.store'])
            ->where('order_id', $order->id)
            ->firstOrFail();

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'order' => $invoice->order,
        ]);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Customer: Download their receipt PDF (proof of payment)
     */
    public function customerDownloadReceipt(Order $order)
    {
        // Ensure the user owns this order
        if (Auth::id() !== $order->user_id && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized access to this order.');
        }

        // Only allow receipt download for paid orders
        if ($order->payment_status !== 'paid') {
            abort(403, 'Receipt is only available for paid orders.');
        }

        $invoice = Invoice::with(['order.user', 'order.orderItems.product', 'order.orderItems.variant', 'order.store', 'order.transactions'])
            ->where('order_id', $order->id)
            ->firstOrFail();

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'order' => $invoice->order,
        ]);

        return $pdf->download("receipt-{$invoice->invoice_number}.pdf");
    }

    /**
     * Customer: View their receipt PDF in browser
     */
    public function viewReceipt(Order $order)
    {
        // Ensure the user owns this order
        if (Auth::id() !== $order->user_id && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized access to this order.');
        }

        // Only allow receipt view for paid orders
        if ($order->payment_status !== 'paid') {
            abort(403, 'Receipt is only available for paid orders.');
        }

        $invoice = Invoice::with(['order.user', 'order.orderItems.product', 'order.orderItems.variant', 'order.store', 'order.transactions'])
            ->where('order_id', $order->id)
            ->firstOrFail();

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'order' => $invoice->order,
        ]);

        return $pdf->stream("receipt-{$invoice->invoice_number}.pdf");
    }

    /**
     * Customer: View receipt page (user-facing)
     */
    public function viewReceiptPage(Order $order)
    {
        // Ensure the user owns this order
        if (Auth::id() !== $order->user_id && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized access to this order.');
        }

        // Only allow receipt view for paid orders
        if ($order->payment_status !== 'paid') {
            abort(403, 'Receipt is only available for paid orders.');
        }

        $invoice = Invoice::with(['order.user', 'order.orderItems.product', 'order.orderItems.variant', 'order.store', 'order.transactions'])
            ->where('order_id', $order->id)
            ->firstOrFail();

        return inertia('Orders/Receipt', [
            'invoice' => $invoice,
            'order' => $order->load(['orderItems', 'store', 'transactions']),
        ]);
    }

    /**
     * Create invoice for an order (Admin)
     */
    public function createForOrder(Order $order)
    {
        if ($order->invoice) {
            return redirect()->route('admin.invoices.show', $order->invoice->id)
                ->with('info', 'Invoice already exists for this order.');
        }

        // Calculate totals from order items
        $subtotal = $order->orderItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $taxAmount = 0; // Can be calculated based on tax rates
        $totalAmount = $subtotal + $taxAmount;

        $invoice = Invoice::create([
            'order_id' => $order->id,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'invoice_date' => now()->toDateString(),
        ]);

        return redirect()->route('admin.invoices.show', $invoice->id)
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Update invoice status (Admin)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,cancelled',
        ]);

        $invoice = Invoice::findOrFail($id);
        $invoice->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Invoice status updated.');
    }

    /**
     * Generate receipt by order number (API endpoint)
     */
    public function generateReceiptByOrderNumber(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
        ]);

        $orderNumber = $request->input('order_number');

        // Find the order by order number
        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found with the provided order number.',
            ], 404);
        }

        // Ensure the user owns this order
        if (Auth::id() !== $order->user_id && !Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this order.',
            ], 403);
        }

        // Only allow receipt generation for paid orders
        if ($order->payment_status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Receipt is only available for paid orders.',
            ], 403);
        }

        // Check if receipt already exists
        if ($order->receipt_path && Storage::disk('public')->exists($order->receipt_path)) {
            return response()->json([
                'success' => true,
                'message' => 'Receipt generated successfully.',
                'receipt_url' => asset('storage/' . $order->receipt_path),
                'order' => [
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'total_amount' => $order->total_amount,
                ],
            ]);
        }

        // Generate new receipt
        $invoice = Invoice::with(['order.user', 'order.orderItems.product', 'order.store', 'order.transactions'])
            ->where('order_id', $order->id)
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found for this order.',
            ], 404);
        }

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'order' => $invoice->order,
        ]);

        // Save the PDF to storage
        $filename = 'receipt-' . $order->order_number . '-' . time() . '.pdf';
        $path = 'receipts/' . $filename;
        
        Storage::disk('public')->put($path, $pdf->output());

        // Update order with receipt path
        $order->update(['receipt_path' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Receipt generated successfully.',
            'receipt_url' => asset('storage/' . $path),
            'order' => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'total_amount' => $order->total_amount,
            ],
        ]);
    }
}
