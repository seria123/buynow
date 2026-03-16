<?php

namespace App\Notifications;

use App\Models\Sales\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;
    protected string $oldStatus;
    protected string $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, string $oldStatus, string $newStatus)
    {
        $this->order = $order->load(['orderItems.product', 'orderItems.variant', 'invoice']);
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $orderUrl = route('orders.show', ['order' => $this->order->id]);
        
        $subject = $this->getSubject();
        $message = $this->getMessage();
        
        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($message)
            ->line('**Order Number:** ' . $this->order->order_number)
            ->line('**Previous Payment Status:** ' . ucfirst($this->oldStatus))
            ->line('**Current Payment Status:** ' . ucfirst($this->newStatus))
            ->line('**Order Total:** KSh ' . number_format($this->order->total_amount, 2));
            
        if ($this->newStatus === 'paid' && $this->order->invoice) {
            $mail->line('**Invoice Number:** ' . $this->order->invoice->invoice_number);
        }
        
        return $mail
            ->line('')
            ->action('View Order Details', $orderUrl)
            ->line('')
            ->line('Thank you for shopping with us!');
    }

    /**
     * Get the subject line based on payment status.
     */
    protected function getSubject(): string
    {
        return match ($this->newStatus) {
            'paid' => 'Payment Confirmed - ' . $this->order->order_number,
            'pending' => 'Payment Pending - ' . $this->order->order_number,
            'failed' => 'Payment Failed - ' . $this->order->order_number,
            'refunded' => 'Payment Refunded - ' . $this->order->order_number,
            default => 'Payment Status Updated - ' . $this->order->order_number,
        };
    }

    /**
     * Get the message based on payment status.
     */
    protected function getMessage(): string
    {
        return match ($this->newStatus) {
            'paid' => 'Great news! Your payment has been confirmed and your order is now being processed.',
            'pending' => 'Your payment is still being processed. Please complete your payment to proceed with the order.',
            'failed' => 'Unfortunately, your payment could not be processed. Please try again or contact support.',
            'refunded' => 'Your payment has been refunded to your original payment method.',
            default => 'The payment status for your order has been updated.',
        };
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'url' => route('orders.show', ['order' => $this->order->id]),
            'message' => 'Payment status for order ' . $this->order->order_number . ' changed from ' . $this->oldStatus . ' to ' . $this->newStatus,
        ];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }
}
