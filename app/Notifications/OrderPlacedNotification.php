<?php

namespace App\Notifications;

use App\Models\Sales\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order->load(['orderItems.product', 'orderItems.variant', 'store']);
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
        
        return (new MailMessage)
            ->subject('Order Confirmed - ' . $this->order->order_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Thank you for your order! Your order has been successfully placed.')
            ->line('**Order Number:** ' . $this->order->order_number)
            ->line('**Order Total:** KSh ' . number_format($this->order->total_amount, 2))
            ->line('**Payment Status:** ' . ucfirst($this->order->payment_status))
            ->line('')
            ->line('**Order Details:**')
            ->line('-----------------------------------');
            
        foreach ($this->order->orderItems as $item) {
            $productName = $item->product->name ?? 'Product';
            if ($item->variant) {
                $productName .= ' - ' . $item->variant->name;
            }
            
            $mail = (new MailMessage)
                ->line($productName . ' x ' . $item->quantity . ' = KSh ' . number_format($item->subtotal, 2));
        }
        
        return $mail
            ->line('-----------------------------------')
            ->line('**Subtotal:** KSh ' . number_format($this->order->orderItems->sum('subtotal'), 2))
            ->line('**Store:** ' . ($this->order->store->name ?? 'N/A'))
            ->line('')
            ->action('View Order Details', $orderUrl)
            ->line('')
            ->line('You can track your order status at any time from your account.')
            ->line('Thank you for shopping with us!');
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
            'total_amount' => $this->order->total_amount,
            'payment_status' => $this->order->payment_status,
            'status' => $this->order->status,
            'item_count' => $this->order->orderItems->count(),
            'url' => route('orders.show', ['order' => $this->order->id]),
            'message' => 'Your order ' . $this->order->order_number . ' has been placed successfully.',
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
