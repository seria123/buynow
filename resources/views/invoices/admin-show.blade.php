<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6 max-w-4xl">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Invoice Details</h1>
            <a href="{{ route('admin.invoices.download', $invoice->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Download PDF</a>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Invoice Info</h3>
                    <p class="text-lg font-bold">{{ $invoice->invoice_number }}</p>
                    <p class="text-gray-600">Order: {{ $invoice->order->order_number ?? 'N/A' }}</p>
                    <p class="text-gray-600">Date: {{ $invoice->invoice_date }}</p>
                    <div class="mt-2">
                        <span class="px-3 py-1 rounded-full text-sm font-medium 
                            @if($invoice->status === 'paid') bg-green-100 text-green-800
                            @elseif($invoice->status === 'pending') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Bill To</h3>
                    <p class="font-medium">{{ $invoice->order->user->name ?? 'N/A' }}</p>
                    <p class="text-gray-600">{{ $invoice->order->user->email ?? '' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($invoice->order->orderItems ?? [] as $item)
                    <tr>
                        <td class="px-6 py-4">{{ $item->product_name }}</td>
                        <td class="px-6 py-4 text-right">KES {{ number_format($item->price, 2) }}</td>
                        <td class="px-6 py-4 text-right">{{ $item->quantity }}</td>
                        <td class="px-6 py-4 text-right font-medium">KES {{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No items found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex justify-end">
            <div class="w-64">
                <div class="flex justify-between py-2">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="font-medium">KES {{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                @if($invoice->tax_amount > 0)
                <div class="flex justify-between py-2">
                    <span class="text-gray-600">Tax</span>
                    <span class="font-medium">KES {{ number_format($invoice->tax_amount, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between py-3 border-t border-gray-300">
                    <span class="text-lg font-bold">Total</span>
                    <span class="text-lg font-bold">KES {{ number_format($invoice->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('admin.invoices.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Invoices</a>
        </div>
    </div>
</body>
</html>
