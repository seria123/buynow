<?php

namespace App\Http\Controllers\Pages;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
    // M-Pesa Callback
    // -----------------------------
    public function mpesaCallback(Request $request)
    {
        $data = $request->all();
        Log::info('M-Pesa callback received', $data);

        $checkoutRequestID = $data['Body']['stkCallback']['CheckoutRequestID'] ?? null;
        $resultCode = $data['Body']['stkCallback']['ResultCode'] ?? null;

        if ($checkoutRequestID && $resultCode == 0) {
            $callbackItems = $data['Body']['stkCallback']['CallbackMetadata']['Item'] ?? [];
            $orderRef = null;

            foreach ($callbackItems as $item) {
                if ($item['Name'] === 'BillRefNumber') {
                    $orderRef = $item['Value'];
                    break;
                }
            }

            if ($orderRef) {
                $order = Order::where('order_number', $orderRef)->first();
                if ($order) {
                    $order->status = 'processing';
                    $order->payment_status = 'paid';
                    $order->payment_method = 'mpesa';
                    $order->save();
                    Log::info("Order {$order->id} marked as paid via M-Pesa");
                }
            }
        } else {
            Log::warning('M-Pesa payment failed or canceled', ['data' => $data]);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
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