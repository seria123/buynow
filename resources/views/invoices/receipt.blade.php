<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            background-color: #fff;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #28a745;
            padding-bottom: 20px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 5px;
        }
        .receipt-title {
            font-size: 32px;
            font-weight: bold;
            color: #28a745;
            margin-top: 10px;
        }
        .receipt-subtitle {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 5px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 15px;
            font-size: 14px;
        }
        .status-paid {
            background-color: #d4edda;
            color: #155724;
            border: 2px solid #28a745;
        }
        .receipt-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 20px;
        }
        .receipt-info, .customer-info {
            width: 48%;
        }
        .receipt-info h3, .customer-info h3 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #28a745;
            border-bottom: 1px solid #28a745;
            padding-bottom: 5px;
        }
        .info-row {
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        .info-value {
            color: #333;
            text-align: right;
        }
        .payment-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
            border-left: 4px solid #28a745;
        }
        .payment-info h3 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #28a745;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #28a745;
            color: white;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            margin-top: 20px;
            margin-left: auto;
            width: 300px;
        }
        .totals table {
            margin-bottom: 0;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }
        .totals-row.total {
            font-weight: bold;
            font-size: 18px;
            border-top: 3px solid #28a745;
            margin-top: 10px;
            padding-top: 10px;
            color: #28a745;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
            font-size: 11px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .thank-you {
            font-size: 16px;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 10px;
        }
        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f99;
            border-left: 4px solid #28a745;
        }
        .notes h4 {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #28a745;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(40, 167, 69, 0.1);
            font-weight: bold;
            pointer-events: none;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="watermark">PAID</div>
    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                {{ $order->store->name ?? config('app.name', 'BuyNow') }}
            </div>
            <div class="receipt-title">PAYMENT RECEIPT</div>
            <div class="receipt-subtitle">Proof of Payment</div>
            <div class="status-badge status-paid">
                ✓ PAID
            </div>
        </div>

        <!-- Receipt Details -->
        <div class="receipt-details">
            <div class="receipt-info">
                <h3>Receipt Information</h3>
                <div class="info-row">
                    <span class="info-label">Receipt Number:</span>
                    <span class="info-value">{{ $invoice->invoice_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Order Number:</span>
                    <span class="info-value">{{ $order->order_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment Date:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('F d, Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment Method:</span>
                    <span class="info-value">{{ ucfirst($order->payment_method ?? 'M-Pesa') }}</span>
                </div>
            </div>
            <div class="customer-info">
                <h3>Customer Information</h3>
                @if($order->user)
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value">{{ $order->user->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $order->user->email }}</span>
                </div>
                @if($order->user->phone)
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">{{ $order->user->phone }}</span>
                </div>
                @endif
                @endif
            </div>
        </div>

        <!-- Payment Transaction Info -->
        @if($order->transactions && $order->transactions->where('status', 'completed')->count() > 0)
        <div class="payment-info">
            <h3>Payment Transaction Details</h3>
            @foreach($order->transactions->where('status', 'completed') as $transaction)
            <div class="info-row">
                <span class="info-label">Transaction ID:</span>
                <span class="info-value">{{ $transaction->transaction_number ?? 'N/A' }}</span>
            </div>
            @if($transaction->mpesa_transaction_id)
            <div class="info-row">
                <span class="info-label">M-Pesa Receipt:</span>
                <span class="info-value">{{ $transaction->mpesa_transaction_id }}</span>
            </div>
            @endif
            @if($transaction->mpesa_phone_number)
            <div class="info-row">
                <span class="info-label">Phone Number:</span>
                <span class="info-value">{{ $transaction->mpesa_phone_number }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Amount Paid:</span>
                <span class="info-value">KES {{ number_format($transaction->amount, 2) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Payment Time:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($transaction->processed_at ?? $transaction->created_at)->format('F d, Y h:i A') }}</span>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Order Items Table -->
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>SKU</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                <tr>
                    <td>
                        {{ $item->product->name ?? 'Product' }}
                        @if($item->variant && $item->variant->name)
                        <br><small>{{ $item->variant->name }}</small>
                        @endif
                    </td>
                    <td>{{ $item->product->sku ?? 'N/A' }}</td>
                    <td class="text-right">KES {{ number_format($item->price, 2) }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">KES {{ number_format($item->price * $item->quantity, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            <div class="totals-row">
                <span>Subtotal:</span>
                <span>KES {{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            @if($invoice->tax_amount > 0)
            <div class="totals-row">
                <span>Tax (VAT):</span>
                <span>KES {{ number_format($invoice->tax_amount, 2) }}</span>
            </div>
            @endif
            @if($order->shipping_cost > 0)
            <div class="totals-row">
                <span>Shipping:</span>
                <span>KES {{ number_format($order->shipping_cost, 2) }}</span>
            </div>
            @endif
            @if($order->discount_amount > 0)
            <div class="totals-row">
                <span>Discount:</span>
                <span>- KES {{ number_format($order->discount_amount, 2) }}</span>
            </div>
            @endif
            <div class="totals-row total">
                <span>Total Paid:</span>
                <span>KES {{ number_format($invoice->total_amount, 2) }}</span>
            </div>
        </div>

        <!-- Notes -->
        @if($invoice->notes)
        <div class="notes">
            <h4>Notes</h4>
            <p>{{ $invoice->notes }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="thank-you">Thank you for your purchase!</div>
            <p>This receipt confirms that your payment has been successfully processed.</p>
            <p>For any inquiries, please contact our support team.</p>
            <p style="margin-top: 15px;">Generated on {{ now()->format('F d, Y h:i A') }}</p>
        </div>
    </div>
</body>
</html>
