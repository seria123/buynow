<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background-color: #28a745;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px;
        }
        .order-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .order-info h2 {
            margin-top: 0;
            color: #28a745;
            font-size: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        .info-value {
            color: #333;
        }
        .message {
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 10px;
        }
        .button:hover {
            background-color: #218838;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .attachment-note {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Payment Receipt</h1>
            <p>Thank you for your purchase!</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="message">
                <p>Dear {{ $order->user->name ?? 'Valued Customer' }},</p>
                <p>Your payment has been successfully processed. Please find your receipt attached to this email.</p>
            </div>

            <!-- Order Information -->
            <div class="order-info">
                <h2>Order Details</h2>
                <div class="info-row">
                    <span class="info-label">Order Number:</span>
                    <span class="info-value">{{ $order->order_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Order Date:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($order->created_at)->format('F d, Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment Method:</span>
                    <span class="info-value">{{ ucfirst($order->payment_method ?? 'M-Pesa') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Amount:</span>
                    <span class="info-value">KES {{ number_format($order->total_amount, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment Status:</span>
                    <span class="info-value" style="color: #28a745; font-weight: bold;">PAID</span>
                </div>
            </div>

            <!-- Attachment Note -->
            <div class="attachment-note">
                <strong>📎 Attachment:</strong> Your receipt PDF is attached to this email for your records.
            </div>

            <!-- Call to Action -->
            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ route('orders.receipt.view', $order->id) }}" class="button">
                    View Receipt Online
                </a>
            </div>

            <!-- Additional Info -->
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                <p><strong>Need Help?</strong></p>
                <p>If you have any questions about your order, please don't hesitate to contact our support team.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This is an automated email. Please do not reply directly to this message.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'BuyNow') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
