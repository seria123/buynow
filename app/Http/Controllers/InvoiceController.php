<?php

namespace App\Http\Controllers;

use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
}
