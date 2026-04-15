<?php

namespace App\Http\Controllers\Pages;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReceiptMail;

class PaymentController extends Controller
{
    // M-Pesa API base URLs
    private const SANDBOX_URL = 'https://sandbox.safaricom.co.ke';
    private const PRODUCTION_URL = 'https://api.safaricom.co.ke';

    private function getMpesaBaseUrl(): string
    {
        $environment = env('MPESA_ENVIRONMENT', 'sandbox');
        return $environment === 'production' ? self::PRODUCTION_URL : self::SANDBOX_URL;
    }

    public function mpesaPay(Request $request, Order $order)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:12',
        ]);

        $phone = $request->input('phone');
        $amount = max(1, intval($order->total_amount));
        $environment = env('MPESA_ENVIRONMENT', 'sandbox');
        $baseUrl = $this->getMpesaBaseUrl();

        // Normalize phone number to 254 format
        $phone = $this->normalizePhoneNumber($phone);

        // In sandbox, validate and fallback to test numbers
        if ($environment === 'sandbox') {
            if (!preg_match('/^254[71]\d{8}$/', $phone)) {
                $sandboxNumbers = ['254708374149', '254708374147', '254708374145'];
                $phone = $sandboxNumbers[0];
                Log::info("Sandbox mode: using default test number $phone");
            } else {
                Log::info("Sandbox mode: using user phone $phone (ensure it's added to Daraja Simulator)");
            }
        }

        Log::info('Starting M-Pesa payment', [
            'order_id' => $order->id,
            'phone' => $phone,
            'amount' => $amount,
            'environment' => $environment,
            'base_url' => $baseUrl
        ]);

        try {
            $accessToken = $this->generateAccessToken($baseUrl);
            if (!$accessToken) {
                throw new \Exception('Failed to generate M-Pesa access token. Check your consumer key and secret in .env');
            }

            $timestamp = now()->format('YmdHis');
            $password = base64_encode(env('MPESA_SHORTCODE') . env('MPESA_PASSKEY') . $timestamp);

            $stkRequest = [
                "BusinessShortCode" => env('MPESA_SHORTCODE'),
                "Password" => $password,
                "Timestamp" => $timestamp,
                "TransactionType" => "CustomerPayBillOnline",
                "Amount" => $amount,
                "PartyA" => $phone,
                "PartyB" => env('MPESA_SHORTCODE'),
                "PhoneNumber" => $phone,
                "CallBackURL" => env('MPESA_CALLBACK_URL'),
                "AccountReference" => $order->order_number,
                "TransactionDesc" => 'Payment for order ' . $order->order_number,
            ];

            $stkUrl = $baseUrl . '/mpesa/stkpush/v1/processrequest';
            
            Log::info('Sending STK Push request', ['url' => $stkUrl, 'request' => $stkRequest]);

            $response = Http::withToken($accessToken)
                ->timeout(120)
                ->connectTimeout(30)
                ->retry(3, 1000)
                ->post($stkUrl, $stkRequest);

            $responseJson = $response->json() ?? [];
            Log::info('STK Push Response', [
                'status' => $response->status(),
                'json' => $responseJson
            ]);

            // Check for HTTP errors
            if ($response->failed()) {
                Log::error('STK Push HTTP error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'json' => $responseJson
                ]);
                return response()->json([
                    'error' => 'Payment initiation failed.',
                    'details' => "HTTP error: " . $response->status() . ". " . ($responseJson['errorMessage'] ?? $responseJson['error'] ?? 'Unknown error'),
                    'hint' => $environment === 'sandbox' ? 'Make sure your IP is whitelisted in the Safaricom developer portal' : 'Check your M-Pesa credentials and try again'
                ], 500);
            }

            // Check for M-Pesa API errors
            if (!isset($responseJson['ResponseCode']) || $responseJson['ResponseCode'] !== '0') {
                $errorMsg = $responseJson['errorMessage'] ?? $responseJson['ResponseDesc'] ?? 'Unknown error';
                Log::warning('STK Push failed', ['response' => $responseJson]);
                return response()->json([
                    'message' => 'STK Push failed: ' . $errorMsg,
                    'stk_response' => $responseJson,
                    'used_phone' => $phone,
                    'hint' => 'For sandbox, add your phone number to the Daraja Simulator'
                ], 400);
            }

            // STK Push initiated successfully
            Log::info('STK Push initiated successfully', [
                'order_id' => $order->id,
                'checkout_request_id' => $responseJson['CheckoutRequestID'] ?? 'N/A'
            ]);

            // Store checkout request ID in order for later reference
            $order->update(['checkout_request_id' => $responseJson['CheckoutRequestID'] ?? null]);

            return response()->json([
                'message' => 'STK Push initiated. Check your phone to complete payment.',
                'stk_response' => $responseJson,
                'used_phone' => $phone,
                'checkout_request_id' => $responseJson['CheckoutRequestID'] ?? null
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('M-Pesa Connection Error', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
                'hint' => 'Cannot connect to M-Pesa API. Check: 1) Network connectivity, 2) Firewall allows outbound to ' . $baseUrl
            ]);
            return response()->json([
                'error' => 'Payment initiation failed.',
                'details' => 'Cannot connect to M-Pesa server. ' . $e->getMessage(),
                'hint' => 'Check your network connectivity or firewall settings'
            ], 500);
        } catch (\Exception $e) {
            Log::error('MPESA Payment error', [
                'order_id' => $order->id,
                'phone' => $phone,
                'amount' => $amount,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Payment initiation failed.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate receipt PDF and save to storage
     */
    private function generateReceipt(Order $order): ?string
    {
        try {
            // Load necessary relationships
            $order->load(['orderItems.product', 'store', 'transactions', 'user']);
            
            // Get or create invoice
            $invoice = $order->invoice;
            if (!$invoice) {
                // Calculate totals from order items
                $subtotal = $order->orderItems->sum(function ($item) {
                    return $item->price * $item->quantity;
                });

                $taxAmount = $subtotal * 0.16; // 16% VAT
                $totalAmount = $subtotal + $taxAmount;

                $invoice = \App\Models\Sales\Invoice::create([
                    'order_id' => $order->id,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'status' => 'paid',
                    'invoice_date' => now()->toDateString(),
                ]);
            }
            
            // Generate PDF
            $pdf = Pdf::loadView('invoices.receipt', [
                'order' => $order,
                'invoice' => $invoice,
            ]);
            
            // Save to storage
            $path = 'receipts/receipt-' . $order->id . '.pdf';
            Storage::put($path, $pdf->output());
            
            // Save path in DB
            $order->receipt_path = $path;
            $order->save();
            
            Log::info("Receipt generated for order {$order->id}", ['path' => $path]);
            
            return $path;
        } catch (\Exception $e) {
            Log::error('Failed to generate receipt', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * Send receipt email to customer
     */
    private function sendReceiptEmail(Order $order, string $path): void
    {
        try {
            if ($order->user && $order->user->email) {
                Mail::to($order->user->email)
                    ->send(new ReceiptMail($order, $path));
                
                Log::info("Receipt email sent for order {$order->id}", ['email' => $order->user->email]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send receipt email', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    // Normalize phone number to 254 format
    private function normalizePhoneNumber(string $phone): string
    {
        // Remove any spaces or dashes
        $phone = preg_replace('/[\s\-]/', '', $phone);
        
        // If starts with 0, replace with 254
        if (preg_match('/^0[71]\d{8}$/', $phone)) {
            return '254' . substr($phone, 1);
        }
        
        // If starts with 254, keep it
        if (preg_match('/^254[71]\d{8}$/', $phone)) {
            return $phone;
        }
        
        // If starts with 7 or 1, add 254
        if (preg_match('/^[71]\d{8}$/', $phone)) {
            return '254' . $phone;
        }
        
        // Return as is if already in some other format
        return $phone;
    }

 // Called by Safaricom to validate the transaction
    public function validation(Request $request)
    {
        Log::info('M-Pesa Validation:', $request->all());

        return response()->json([
            "ResultCode" => 0,
            "ResultDesc" => "Accepted"
        ]);
    }

    // Called by Safaricom after payment is complete
    public function confirmation(Request $request)
    {
        Log::info('M-Pesa Confirmation:', $request->all());

        // Here you can mark the order as paid in your database

        return response()->json([
            "ResultCode" => 0,
            "ResultDesc" => "Accepted"
        ]);
    }

    // -----------------------------
    // M-Pesa Callback (handles both success and cancellation)
    // -----------------------------
    public function mpesaCallback(Request $request)
    {
        $data = $request->all();
        
        // Log EVERYTHING that comes in
        Log::info('=== M-PESA CALLBACK RECEIVED ===', [
            'all_data' => $data,
            'full_url' => $request->fullUrl(),
            'method' => $request->method(),
            'headers' => $request->headers->all(),
        ]);

        // Also log to a separate file for easier debugging
        Log::channel('single')->info('M-Pesa callback raw', $data);

        $checkoutRequestID = $data['Body']['stkCallback']['CheckoutRequestID'] ?? null;
        $resultCode = $data['Body']['stkCallback']['ResultCode'] ?? null;

        Log::info('M-Pesa callback check', [
            'checkoutRequestID' => $checkoutRequestID,
            'resultCode' => $resultCode,
            'is_success' => ($resultCode == 0),
            'is_cancelled' => in_array($resultCode, [1037, 1038, 1039, 9999]),
        ]);

        if ($checkoutRequestID && $resultCode == 0) {
            // SUCCESS CASE
            $callbackItems = $data['Body']['stkCallback']['CallbackMetadata']['Item'] ?? [];
            $mpesaTransactionId = null;
            $mpesaPhoneNumber = null;

            Log::info('M-Pesa callback - success case', [
                'checkoutRequestID' => $checkoutRequestID,
                'callbackItems' => $callbackItems,
            ]);

            foreach ($callbackItems as $item) {
                if ($item['Name'] === 'MpesaReceiptNumber') {
                    $mpesaTransactionId = $item['Value'];
                }
                if ($item['Name'] === 'PhoneNumber') {
                    $mpesaPhoneNumber = $item['Value'];
                }
            }

            // Use checkout_request_id as primary lookup (BillRefNumber is not always sent by M-Pesa)
            $order = Order::where('checkout_request_id', $checkoutRequestID)->first();
            
            if ($order) {
                // Create transaction record
                $transaction = \App\Models\Sales\Transaction::create([
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
                $order->payment_method = 'mpesa';
                $order->save();
                
                // Create invoice if it doesn't exist, or update existing one
                if (!$order->invoice) {
                    // Calculate totals from order items
                    $subtotal = $order->orderItems->sum(function ($item) {
                        return $item->price * $item->quantity;
                    });

                    $taxAmount = $subtotal * 0.16; // 16% VAT
                    $totalAmount = $subtotal + $taxAmount;

                    \App\Models\Sales\Invoice::create([
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
                
                // Generate receipt and send email
                $receiptPath = $this->generateReceipt($order);
                if ($receiptPath) {
                    $this->sendReceiptEmail($order, $receiptPath);
                }
                
                Log::info("Order {$order->id} marked as paid via M-Pesa. Transaction ID: {$transaction->id}");
            } else {
                // Order not found by checkout_request_id
                Log::error('M-Pesa callback: Order not found for checkout_request_id', [
                    'checkout_request_id' => $checkoutRequestID,
                ]);
            }
        } elseif ($checkoutRequestID && in_array($resultCode, ['1037', 1037, '1038', 1038, '1039', 1039, '9999', 9999])) {
            // CANCELLED CASE - This handles ResultCode 1037 (Request cancelled by user)
            Log::warning('M-Pesa payment cancelled by user', ['data' => $data]);

            $resultDesc = $data['Body']['stkCallback']['ResultDesc'] ?? 'Payment canceled by user';

            $order = Order::where('checkout_request_id', $checkoutRequestID)->first();
            Log::info('Looking for order with checkout_request_id', ['checkout_request_id' => $checkoutRequestID, 'found' => $order ? 'yes' : 'no', 'order_id' => $order?->id]);
            
            if ($order) {
                // Create a cancelled transaction record
                $transaction = \App\Models\Sales\Transaction::create([
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'amount' => $order->total_amount,
                    'currency' => 'KES',
                    'type' => 'payment',
                    'status' => 'cancelled',
                    'payment_method' => 'mpesa',
                    'gateway' => 'mpesa',
                    'gateway_transaction_id' => $checkoutRequestID,
                    'gateway_response_code' => (string) $resultCode,
                    'gateway_response_message' => $resultDesc,
                    'gateway_response_data' => $data,
                    'customer_email' => $order->user?->email,
                    'customer_phone' => $order->user?->phone ?? null,
                ]);

                // Update order payment status
                $order->updatePaymentStatus('cancelled');

                Log::info("Order {$order->id} payment cancelled via M-Pesa. Transaction ID: {$transaction->id}");
            } else {
                Log::warning('M-Pesa callback: Order not found for cancelled payment', [
                    'checkout_request_id' => $checkoutRequestID,
                ]);
            }
        } else {
            Log::warning('M-Pesa payment failed or canceled', ['data' => $data]);

            // Handle cancelled/failed payments - find the order by checkout request ID
            $checkoutRequestID = $data['Body']['stkCallback']['CheckoutRequestID'] ?? null;
            $resultDesc = $data['Body']['stkCallback']['ResultDesc'] ?? 'Payment canceled or failed';
            $resultCode = $data['Body']['stkCallback']['ResultCode'] ?? null;

            Log::info('M-Pesa callback - cancelled/failed case', [
                'checkoutRequestID' => $checkoutRequestID,
                'resultCode' => $resultCode,
                'resultDesc' => $resultDesc,
            ]);

            if ($checkoutRequestID) {
                $order = Order::where('checkout_request_id', $checkoutRequestID)->first();
                Log::info('Looking for order with checkout_request_id', ['checkout_request_id' => $checkoutRequestID, 'found' => $order ? 'yes' : 'no', 'order_id' => $order?->id]);
                
                if ($order) {
                    // Create a failed transaction record
                    $transaction = \App\Models\Sales\Transaction::create([
                        'order_id' => $order->id,
                        'user_id' => $order->user_id,
                        'amount' => $order->total_amount,
                        'currency' => 'KES',
                        'type' => 'payment',
                        'status' => 'cancelled',
                        'payment_method' => 'mpesa',
                        'gateway' => 'mpesa',
                        'gateway_transaction_id' => $checkoutRequestID,
                        'gateway_response_code' => (string) $resultCode,
                        'gateway_response_message' => $resultDesc,
                        'gateway_response_data' => $data,
                        'customer_email' => $order->user?->email,
                        'customer_phone' => $order->user?->phone ?? null,
                    ]);

                    // Update order payment status
                    $order->updatePaymentStatus('cancelled');

                    // Optionally cancel the order if payment was cancelled
                    // Uncomment if you want to cancel the entire order when payment is cancelled
                    // $order->updateStatus('cancelled');

                    Log::info("Order {$order->id} payment cancelled via M-Pesa. Transaction ID: {$transaction->id}");
                } else {
                    // Order not found - log this for debugging
                    Log::warning('M-Pesa callback: Order not found for checkout_request_id', [
                        'checkout_request_id' => $checkoutRequestID,
                        'available_orders' => \App\Models\Sales\Order::whereNotNull('checkout_request_id')
                            ->pluck('checkout_request_id', 'order_number')
                    ]);
                }
            }
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /**
     * Manual endpoint to record a cancelled payment
     * Use this if the M-Pesa callback was not received
     */
    public function recordCancelledPayment(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:cancelled,failed',
        ]);

        $status = $request->input('status', 'cancelled');

        // Check if transaction already exists
        $existingTransaction = $order->transactions()
            ->where('gateway_transaction_id', $order->checkout_request_id)
            ->first();

        if ($existingTransaction) {
            // Update existing transaction
            $existingTransaction->update([
                'status' => $status,
                'gateway_response_message' => $request->input('message', 'Manually marked as ' . $status),
            ]);
            
            $order->updatePaymentStatus($status);
            
            return response()->json([
                'message' => 'Transaction updated to ' . $status,
                'transaction' => $existingTransaction,
                'payment_status' => $order->payment_status
            ]);
        }

        // Create new transaction
        $transaction = \App\Models\Sales\Transaction::create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'amount' => $order->total_amount,
            'currency' => 'KES',
            'type' => 'payment',
            'status' => $status,
            'payment_method' => 'mpesa',
            'gateway' => 'mpesa',
            'gateway_transaction_id' => $order->checkout_request_id,
            'gateway_response_message' => $request->input('message', 'Manually marked as ' . $status),
            'customer_email' => $order->user?->email,
            'customer_phone' => $order->user?->phone,
        ]);

        // Update order payment status
        $order->updatePaymentStatus($status);

        Log::info("Order {$order->id} manually marked as {$status}. Transaction ID: {$transaction->id}");

        return response()->json([
            'message' => 'Transaction created with status: ' . $status,
            'transaction' => $transaction,
            'payment_status' => $order->payment_status
        ]);
    }

    /**
     * Manual endpoint to record a SUCCESS payment
     * Use this when user has paid but callback was not received
     */
    public function recordPaymentReceived(Request $request, Order $order)
    {
        $request->validate([
            'mpesa_receipt' => 'nullable|string',
        ]);

        // Load invoice for response
        $order->load('invoice');
        
        // Check if transaction already exists
        $existingTransaction = $order->transactions()
            ->whereIn('status', ['completed', 'pending'])
            ->first();

        if ($existingTransaction) {
            return response()->json([
                'message' => 'Payment already recorded',
                'transaction' => $existingTransaction,
                'payment_status' => $order->payment_status,
                'invoice' => $order->invoice,
            ]);
        }

        // Create new completed transaction
        $transaction = \App\Models\Sales\Transaction::create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'amount' => $order->total_amount,
            'currency' => 'KES',
            'type' => 'payment',
            'status' => 'completed',
            'payment_method' => 'mpesa',
            'gateway' => 'mpesa',
            'gateway_transaction_id' => $order->checkout_request_id ?? 'manual-' . now()->timestamp,
            'mpesa_transaction_id' => $request->input('mpesa_receipt'),
            'mpesa_phone_number' => $order->user?->phone,
            'gateway_response_message' => 'Manually marked as paid (user confirmed)',
            'customer_email' => $order->user?->email,
            'customer_phone' => $order->user?->phone,
            'processed_at' => now(),
        ]);

        // Update order payment status and status
        $order->updatePaymentStatus('paid');
        $order->updateStatus('processing');
        $order->payment_method = 'mpesa';
        $order->save();

        // Create invoice if it doesn't exist, or update existing one
        if (!$order->invoice) {
            // Calculate totals from order items
            $subtotal = $order->orderItems->sum(function ($item) {
                return $item->price * $item->quantity;
            });

            $taxAmount = $subtotal * 0.16; // 16% VAT
            $totalAmount = $subtotal + $taxAmount;

            \App\Models\Sales\Invoice::create([
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

        // Generate receipt and send email
        $receiptPath = $this->generateReceipt($order);
        if ($receiptPath) {
            $this->sendReceiptEmail($order, $receiptPath);
        }

        Log::info("Order {$order->id} manually marked as paid. Transaction ID: {$transaction->id}");

        return response()->json([
            'message' => 'Payment recorded successfully',
            'transaction' => $transaction,
            'payment_status' => $order->payment_status,
            'status' => $order->status,
            'invoice' => $order->invoice,
            'receipt_url' => $receiptPath ? asset('storage/' . $receiptPath) : null,
        ]);
    }

    /**
     * Endpoint to force-check payment status for an order
     * This checks if there's any successful transaction that wasn't recorded
     */
    public function checkPaymentStatus(Request $request, Order $order)
    {
        // Load invoice for response
        $order->load('invoice');
        
        // If already paid, return current status
        if ($order->payment_status === 'paid') {
            return response()->json([
                'payment_status' => $order->payment_status,
                'status' => $order->status,
                'transactions' => $order->transactions,
                'invoice' => $order->invoice,
                'receipt_url' => $order->receipt_path ? asset('storage/' . $order->receipt_path) : null,
                'message' => 'Order is already paid'
            ]);
        }

        // If there's a checkout_request_id and no successful transaction, try to record payment
        if ($order->checkout_request_id && $order->payment_status !== 'paid') {
            // Check if there's any transaction
            $hasTransaction = $order->transactions()->exists();
            
            if (!$hasTransaction) {
                // No transaction exists - record payment as completed
                $transaction = \App\Models\Sales\Transaction::create([
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'amount' => $order->total_amount,
                    'currency' => 'KES',
                    'type' => 'payment',
                    'status' => 'completed',
                    'payment_method' => 'mpesa',
                    'gateway' => 'mpesa',
                    'gateway_transaction_id' => $order->checkout_request_id,
                    'gateway_response_message' => 'Payment confirmed via status check (no callback received)',
                    'customer_email' => $order->user?->email,
                    'customer_phone' => $order->user?->phone,
                    'processed_at' => now(),
                ]);

                $order->updatePaymentStatus('paid');
                $order->updateStatus('processing');
                $order->payment_method = 'mpesa';
                $order->save();

                // Generate receipt and send email
                $receiptPath = $this->generateReceipt($order);
                if ($receiptPath) {
                    $this->sendReceiptEmail($order, $receiptPath);
                }

                Log::info("Order {$order->id} marked as paid via status check. Transaction ID: {$transaction->id}");

                return response()->json([
                    'payment_status' => $order->payment_status,
                    'status' => $order->status,
                    'transaction' => $transaction,
                    'invoice' => $order->invoice,
                    'receipt_url' => $receiptPath ? asset('storage/' . $receiptPath) : null,
                    'message' => 'Payment recorded - callback was not received but order has checkout_request_id'
                ]);
            }
        }

        return response()->json([
            'payment_status' => $order->payment_status,
            'status' => $order->status,
            'transactions' => $order->transactions,
            'checkout_request_id' => $order->checkout_request_id,
            'invoice' => $order->invoice,
            'message' => 'Unable to auto-confirm payment. Please click "I Have Already Paid" button.'
        ]);
    }

    public function transactionStatus($receipt)
{
    $baseUrl = $this->getMpesaBaseUrl();
    $accessToken = $this->generateAccessToken($baseUrl);

    $response = Http::withToken($accessToken)->post(
        $baseUrl . '/mpesa/transactionstatus/v1/query',
        [
            "Initiator" => env('MPESA_INITIATOR'),
            "SecurityCredential" => env('MPESA_SECURITY_CREDENTIAL'),
            "CommandID" => "TransactionStatusQuery",
            "TransactionID" => $receipt,
            "PartyA" => env('MPESA_SHORTCODE'),
            "IdentifierType" => "4",
            "ResultURL" => env('MPESA_CALLBACK_URL'),
            "QueueTimeOutURL" => env('MPESA_CALLBACK_URL'),
            "Remarks" => "Check transaction",
            "Occasion" => "Status"
        ]
    );

    return $response->json();
}

public function generateAccessToken(?string $baseUrl = null)
{
    $baseUrl = $baseUrl ?? $this->getMpesaBaseUrl();
    $consumerKey = env('MPESA_CONSUMER_KEY');
    $consumerSecret = env('MPESA_CONSUMER_SECRET');

    if (empty($consumerKey) || empty($consumerSecret)) {
        \Log::error('M-Pesa consumer key or secret is missing in .env');
        return null;
    }

    // Encode credentials as required by Daraja
    $credentials = base64_encode($consumerKey . ':' . $consumerSecret);

    $url = $baseUrl . '/oauth/v1/generate?grant_type=client_credentials';

    try {
        \Log::info('Generating M-Pesa access token', ['url' => $url, 'environment' => env('MPESA_ENVIRONMENT')]);
        
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials
        ])
        ->timeout(60)
        ->connectTimeout(15)
        ->retry(3, 1000)
        ->get($url);

        \Log::info('M-Pesa Access Token Response', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        $data = $response->json() ?? [];

        if (isset($data['access_token'])) {
            return $data['access_token'];
        } else {
            \Log::error('Failed to get access token', ['response' => $data]);
            return null;
        }
    } catch (\Exception $e) {
        \Log::error('Error generating M-Pesa access token', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return null;
    }
}
}