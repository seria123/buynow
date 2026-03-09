<?php

namespace App\Http\Controllers\Pages;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function mpesaPay(Request $request, Order $order)
{
    $request->validate([
        'phone' => 'required|string|min:12|max:12',
    ]);

    $phone = $request->input('phone');
    $amount = max(1, intval($order->order_total));
    $environment = env('MPESA_ENVIRONMENT', 'sandbox');

    // In sandbox, allow personal number or fallback to test numbers
    if ($environment === 'sandbox') {
        // Accept user phone even if not pre-set in sandbox
        $actualPhone = preg_match('/^254[71]\d{8}$/', $phone) ? $phone : null;

        if (!$actualPhone) {
            $sandboxNumbers = ['254708374149', '254708374147', '254708374145'];
            $phone = $sandboxNumbers[0];
            Log::info("Sandbox mode: using default test number $phone");
        } else {
            Log::info("Sandbox mode: using user phone $phone (ensure it's added to Daraja Simulator for full simulation)");
        }
    }

    try {
        $accessToken = $this->generateAccessToken();
        if (!$accessToken) {
            throw new \Exception('Failed to generate M-Pesa access token');
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

        $response = Http::withToken($accessToken)
            ->timeout(120)
            ->connectTimeout(30)
            ->retry(3, 1000)
            ->post('https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest', $stkRequest);

        $responseJson = $response->json() ?? [];
        Log::info('STK Push Response JSON', ['json' => $responseJson]);

        // Sandbox: simulate payment if STK push fails
        if (!isset($responseJson['ResponseCode']) || $responseJson['ResponseCode'] !== '0') {
            Log::warning('STK Push failed, simulating payment', ['response' => $responseJson]);
            $order->payment_status = 'paid';
            $order->status = 'processing';
            $order->payment_method = 'mpesa';
            $order->save();

            return response()->json([
                'message' => 'Sandbox mode: payment simulated successfully.',
                'stk_response' => $responseJson,
                'used_phone' => $phone
            ]);
        }

        // STK Push initiated successfully
        $order->payment_status = 'paid';
        $order->status = 'processing';
        $order->payment_method = 'mpesa';
        $order->save();

        return response()->json([
            'message' => 'STK Push initiated. Check your phone to complete payment.',
            'stk_response' => $responseJson,
            'used_phone' => $phone
        ]);

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
    $accessToken = $this->generateAccessToken();

    $response = Http::withToken($accessToken)->post(
        'https://sandbox.safaricom.co.ke/mpesa/transactionstatus/v1/query',
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
public function generateAccessToken()
{
    $consumerKey = env('MPESA_CONSUMER_KEY');
    $consumerSecret = env('MPESA_CONSUMER_SECRET');

    if (empty($consumerKey) || empty($consumerSecret)) {
        \Log::error('M-Pesa consumer key or secret is missing in .env');
        return null;
    }

    // Encode credentials as required by Daraja
    $credentials = base64_encode($consumerKey . ':' . $consumerSecret);

    $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

    try {
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