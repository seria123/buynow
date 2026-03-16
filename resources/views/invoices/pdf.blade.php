<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
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
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            text-align: right;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .invoice-info, .billing-info {
            width: 45%;
        }
        .invoice-info h3, .billing-info h3 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f5f5f5;
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
            padding: 5px 0;
        }
        .totals-row.total {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #333;
            margin-top: 10px;
            padding-top: 10px;
        }
        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 3px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
            font-size: 11px;
        }
        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 3px solid #333;
        }
        .notes h4 {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                {{ $order->store->name ?? config('app.name', 'BuyNow') }}
            </div>
            <div>
                <div class="invoice-title">INVOICE</div>
                <div class="status status-{{ $invoice->status }}">
                    {{ ucfirst($invoice->status) }}
                </div>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="invoice-info">
                <h3>Invoice Information</h3>
                <div class="info-row">
                    <span class="info-label">Invoice Number:</span> {{ $invoice->invoice_number }}
                </div>
                <div class="info-row">
                    <span class="info-label">Order Number:</span> {{ $order->order_number }}
                </div>
                <div class="info-row">
                    <span class="info-label">Invoice Date:</span> {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('F d, Y') }}
                </div>
                @if($invoice->due_date)
                <div class="info-row">
                    <span class="info-label">Due Date:</span> {{ \Carbon\Carbon::parse($invoice->due_date)->format('F d, Y') }}
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Payment Status:</span> {{ ucfirst($order->payment_status) }}
                </div>
            </div>
            <div class="billing-info">
                <h3>Bill To</h3>
                @if($order->user)
                <div class="info-row">
                    <span class="info-label">Name:</span> {{ $order->user->name }}
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span> {{ $order->user->email }}
                </div>
                @if($order->user->phone)
                <div class="info-row">
                    <span class="info-label">Phone:</span> {{ $order->user->phone }}
                </div>
                @endif
                @endif
            </div>
        </div>

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
                <span>Tax:</span>
                <span>KES {{ number_format($invoice->tax_amount, 2) }}</span>
            </div>
            @endif
            <div class="totals-row total">
                <span>Total:</span>
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
            <p>Thank you for your business!</p>
            <p>Generated on {{ now()->format('F d, Y h:i A') }}</p>
        </div>
    </div>
</body>
</html>
