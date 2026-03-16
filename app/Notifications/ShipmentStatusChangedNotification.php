<?php

namespace App\Notifications;

use App\Models\Sales\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShipmentStatusChangedNotification extends Notification implements ShouldQueue
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
        $this->order = $order->load(['orderItems.product', 'orderItems.variant']);
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
        $trackUrl = route('orders.track');
        
        $subject = $this->getSubject();
        $message = $this->getMessage();
        
        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($message)
            ->line('**Order Number:** ' . $this->order->order_number)
            ->line('**Previous Status:** ' . $this->formatStatus($this->oldStatus))
            ->line('**Current Status:** ' . $this->formatStatus($this->newStatus))
            ->line('**Order Total:** KSh ' . number_format($this->order->total_amount, 2))
            ->line('')
            ->action('View Order Details', $orderUrl)
            ->line('')
            ->action('Track Your Order', $trackUrl)
            ->line('')
            ->line('Thank you for shopping with us!');
    }

    /**
     * Get the subject line based on shipment status.
     */
    protected function getSubject(): string
    {
        return match ($this->newStatus) {
            'processing' => 'Order Processing - ' . $this->order->order_number,
            'shipped' => 'Order Shipped - ' . $this->order->order_number,
            'out_for_delivery' => 'Out for Delivery - ' . $this->order->order_number,
            'delivered' => 'Order Delivered - ' . $this->order->order_number,
            'cancelled' => 'Order Cancelled - ' . $this->order->order_number,
            default => 'Order Status Updated - ' . $this->order->order_number,
        };
    }

    /**
     * Get the message based on shipment status.
     */
    protected function getMessage(): string
    {
        return match ($this->newStatus) {
            'processing' => 'Your order has been received and is being processed. We will notify you once it\'s ready for shipment.',
            'shipped' => 'Great news! Your order has been shipped and is on its way to you.',
            'out_for_delivery' => 'Your order is out for delivery! Please be ready to receive your package.',
            'delivered' => 'Your order has been delivered. Thank you for shopping with us! We hope you enjoy your purchase.',
            'cancelled' => 'Your order has been cancelled. If you did not request this, please contact our support team.',
            default => 'The status of your order has been updated.',
        };
    }

    /**
     * Format status for display.
     */
    protected function formatStatus(string $status): string
    {
        return match ($status) {
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'pending' => 'Pending',
            default => ucfirst($status),
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
            'message' => 'Order status for ' . $this->order->order_number . ' changed from ' . $this->formatStatus($this->oldStatus) . ' to ' . $this->formatStatus($this->newStatus),
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
