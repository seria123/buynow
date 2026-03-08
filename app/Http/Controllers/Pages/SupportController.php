<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales\SupportMessage;

class SupportController extends Controller
{
    // -----------------------------
    // Fetch all messages (for chat)
    // -----------------------------
    public function fetchMessages()
    {
        $messages = SupportMessage::with('user')->latest()->get();
        return response()->json($messages);
    }

    // -----------------------------
    // Store a new user message
    // -----------------------------
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMessage = strtolower(trim($request->message));

        $response = $this->buildResponse($userMessage);

        $supportMessage = SupportMessage::create([
            'user_id' => auth()->id(),
            'message' => $request->message,
            'reply'   => $response['reply'],
            'is_read' => false,
        ]);

        $supportMessage->load('user');

        return response()->json(array_merge(
            $supportMessage->toArray(),
            [
                'followUps' => $response['followUps'],
                'actions'   => $response['actions'],
            ]
        ));
    }

    // -----------------------------
    // Build structured bot response
    // -----------------------------
    private function buildResponse(string $msg): array
    {
        // ── Order flow ──
        if (str_contains($msg, 'order status') || str_contains($msg, 'track') || $msg === 'track my order') {
            return [
                'reply'     => 'You can track your order directly from the Orders page. Click below to view your orders.',
                'followUps' => ['My order is delayed', 'I never received my order', 'Back to main menu'],
                'actions'   => [['label' => '📦 View My Orders', 'url' => '/orders']],
            ];
        }

        if (str_contains($msg, 'cancel') && str_contains($msg, 'order')) {
            return [
                'reply'     => 'To cancel an order go to your Orders page, find the order and click "Cancel Order". If the order has already shipped, cancellation may not be possible.',
                'followUps' => ['My order can\'t be cancelled', 'I want a refund instead', 'Back to main menu'],
                'actions'   => [['label' => '📦 View My Orders', 'url' => '/orders']],
            ];
        }

        if (str_contains($msg, 'delayed') || str_contains($msg, 'never received') || str_contains($msg, 'not arrived')) {
            return [
                'reply'     => 'We\'re sorry for the inconvenience! Deliveries typically take 2–5 business days. If your order is significantly late, please share your order number and we\'ll escalate it immediately.',
                'followUps' => ['I want a refund', 'Contact human support', 'Back to main menu'],
                'actions'   => [['label' => '📦 View My Orders', 'url' => '/orders']],
            ];
        }

        if (str_contains($msg, 'order')) {
            return [
                'reply'     => 'Sure, I can help with your order! What specifically do you need help with?',
                'followUps' => ['Track my order', 'Cancel an order', 'My order is delayed', 'I never received my order'],
                'actions'   => [],
            ];
        }

        // ── Payment flow ──
        if (str_contains($msg, 'charged') || str_contains($msg, 'double') || str_contains($msg, 'not confirmed')) {
            return [
                'reply'     => 'If you were charged but your order was not confirmed, please share your M-Pesa transaction code (e.g. RGX1234567) and we\'ll resolve it within 24 hours.',
                'followUps' => ['I need a refund', 'Back to main menu'],
                'actions'   => [],
            ];
        }

        if (str_contains($msg, 'payment') || str_contains($msg, 'mpesa') || str_contains($msg, 'pay')) {
            return [
                'reply'     => "No worries! What's your payment issue?",
                'followUps' => ['I was charged but order not confirmed', 'I was charged twice', 'Payment failed', 'Back to main menu'],
                'actions'   => [],
            ];
        }

        // ── Return / Refund flow ──
        if (str_contains($msg, 'how') && (str_contains($msg, 'return') || str_contains($msg, 'refund'))) {
            return [
                'reply'     => "To start a return: \n1. Go to your Orders page.\n2. Click the order.\n3. Select \"Request Return\".\nReturns are accepted within 7 days of delivery.",
                'followUps' => ['What items can be returned?', 'How long does a refund take?', 'Back to main menu'],
                'actions'   => [['label' => '📦 View My Orders', 'url' => '/orders']],
            ];
        }

        if (str_contains($msg, 'refund') && str_contains($msg, 'long')) {
            return [
                'reply'     => 'Refunds are processed within 5–7 business days after we receive the returned item. M-Pesa refunds may take an extra 1–2 days.',
                'followUps' => ['I haven\'t received my refund', 'Back to main menu'],
                'actions'   => [],
            ];
        }

        if (str_contains($msg, 'return') || str_contains($msg, 'refund')) {
            return [
                'reply'     => 'We accept returns within 7 days of delivery. What do you need help with?',
                'followUps' => ['How do I return an item?', 'How long does a refund take?', 'I haven\'t received my refund', 'Back to main menu'],
                'actions'   => [],
            ];
        }

        // ── Delivery flow ──
        if (str_contains($msg, 'delivery') || str_contains($msg, 'shipping') || str_contains($msg, 'ship')) {
            return [
                'reply'     => 'Delivery typically takes 2–5 business days depending on your location. You can track your order from the Orders page.',
                'followUps' => ['My delivery is late', 'Change delivery address', 'Back to main menu'],
                'actions'   => [['label' => '📦 Track My Order', 'url' => '/orders']],
            ];
        }

        // ── Account flow ──
        if (str_contains($msg, 'password') || str_contains($msg, 'reset')) {
            return [
                'reply'     => 'You can reset your password from the login page or update it in your Profile settings.',
                'followUps' => ['I can\'t access my email', 'Back to main menu'],
                'actions'   => [['label' => '👤 Go to Profile', 'url' => '/profile']],
            ];
        }

        if (str_contains($msg, 'account') || str_contains($msg, 'profile') || str_contains($msg, 'login')) {
            return [
                'reply'     => 'I can help with your account. What do you need?',
                'followUps' => ['Reset my password', 'Update my profile', 'Back to main menu'],
                'actions'   => [['label' => '👤 Go to Profile', 'url' => '/profile']],
            ];
        }

        // ── Product flow ──
        if (str_contains($msg, 'product') || str_contains($msg, 'item') || str_contains($msg, 'stock') || str_contains($msg, 'available')) {
            return [
                'reply'     => 'For product inquiries you can search our catalogue or contact us with the product name/ID.',
                'followUps' => ['Product is out of stock', 'Product is damaged', 'Wrong item received', 'Back to main menu'],
                'actions'   => [['label' => '🛒 Browse Products', 'url' => '/products']],
            ];
        }

        // ── Greetings ──
        if (str_contains($msg, 'hello') || str_contains($msg, 'hi') || str_contains($msg, 'hey') || $msg === 'start' || str_contains($msg, 'back to main menu') || str_contains($msg, 'main menu')) {
            return [
                'reply'     => 'Hello! 👋 Welcome to BuyNow Support. What can I help you with today?',
                'followUps' => ['Order Issue', 'Payment Issue', 'Return or Refund', 'Delivery & Shipping', 'Account & Profile', 'Product Inquiry'],
                'actions'   => [],
            ];
        }

        // ── Human escalation ──
        if (str_contains($msg, 'human') || str_contains($msg, 'agent') || str_contains($msg, 'speak to someone') || str_contains($msg, 'real person')) {
            return [
                'reply'     => "I'll connect you with a support agent shortly. In the meantime, please describe your issue in detail so we can help faster. Our agents are available Mon–Fri 8am–6pm EAT.",
                'followUps' => ['Back to main menu'],
                'actions'   => [],
            ];
        }

        // ── Default fallback ──
        return [
            'reply'     => "Thanks for reaching out! I didn't quite catch that. Please select a topic below or describe your issue.",
            'followUps' => ['Order Issue', 'Payment Issue', 'Return or Refund', 'Delivery & Shipping', 'Account & Profile', 'Speak to a human agent'],
            'actions'   => [],
        ];
    }

    // -----------------------------
    // Admin reply to a message
    // -----------------------------
    public function reply(Request $request, SupportMessage $supportMessage)
    {
        $request->validate([
            'reply' => 'required|string|max:1000',
        ]);

        $supportMessage->update([
            'reply'   => $request->reply,
            'is_read' => true,
        ]);

        return response()->json([
            'message'        => 'Reply sent successfully',
            'supportMessage' => $supportMessage,
        ]);
    }
}
