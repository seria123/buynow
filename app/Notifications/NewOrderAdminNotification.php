<?php

namespace App\Notifications;

use App\Models\Sales\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order->load(['orderItems.product', 'orderItems.variant', 'user', 'store']);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $orderUrl = route('filament.admin.resources.orders.view', ['record' => $this->order->id]);
        $customerName = $this->order->user->name ?? 'N/A';
        $customerEmail = $this->order->user->email ?? 'N/A';
        
        return (new MailMessage)
            ->subject('New Order Received - ' . $this->order->order_number)
            ->greeting('Hello Admin!')
            ->line('A new order has been placed on the platform.')
            ->line('**Order Number:** ' . $this->order->order_number)
            ->line('**Order Total:** KSh ' . number_format($this->order->total_amount, 2))
            ->line('**Payment Status:** ' . ucfirst($this->order->payment_status))
            ->line('')
            ->line('**Customer Information:**')
            ->line('Name: ' . $customerName)
            ->line('Email: ' . $customerEmail)
            ->line('Phone: ' . ($this->order->user->phone ?? 'N/A'))
            ->line('')
            ->line('**Order Items:**')
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
            ->line('**Store:** ' . ($this->order->store->name ?? 'N/A'))
            ->line('')
            ->action('View Order', $orderUrl)
            ->line('')
            ->line('Please process this order accordingly.');
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
            'customer_name' => $this->order->user->name ?? 'N/A',
            'customer_email' => $this->order->user->email ?? 'N/A',
            'item_count' => $this->order->orderItems->count(),
            'url' => route('filament.admin.resources.orders.view', ['record' => $this->order->id]),
            'message' => 'New order ' . $this->order->order_number . ' from ' . ($this->order->user->name ?? 'N/A'),
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
