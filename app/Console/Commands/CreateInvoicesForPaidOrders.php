<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sales\Order;
use App\Models\Sales\Invoice;

class CreateInvoicesForPaidOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:create-invoices-for-paid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create invoices for all paid orders that don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Finding paid orders without invoices...');

        // Find all paid orders that don't have an invoice
        $orders = Order::where('payment_status', 'paid')
            ->whereDoesntHave('invoice')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No paid orders found without invoices.');
            return 0;
        }

        $this->info("Found {$orders->count()} paid order(s) without invoices.");

        $created = 0;
        foreach ($orders as $order) {
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

            $created++;
            $this->info("✓ Created invoice for order #{$order->order_number}");
        }

        $this->info("Successfully created {$created} invoice(s).");
        $this->info('The receipt download button should now appear for these orders.');

        return 0;
    }
}
