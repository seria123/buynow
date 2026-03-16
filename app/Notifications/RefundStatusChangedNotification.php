<?php

namespace App\Notifications;

use App\Models\Sales\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Refund $refund;
    protected string $oldStatus;
    protected string $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Refund $refund, string $oldStatus, string $newStatus)
    {
        $this->refund = $refund->load(['order', 'order.user']);
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
        $orderUrl = route('orders.show', ['order' => $this->refund->order_id]);
        
        $subject = $this->getSubject();
        $message = $this->getMessage();
        
        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($message)
            ->line('**Order Number:** ' . $this->refund->order->order_number)
            ->line('**Refund Amount:** KSh ' . number_format($this->refund->amount, 2))
            ->line('**Previous Status:** ' . $this->formatStatus($this->oldStatus))
            ->line('**Current Status:** ' . $this->formatStatus($this->newStatus));
            
        if ($this->refund->reason) {
            $mail->line('**Reason:** ' . $this->refund->reason);
        }
        
        if ($this->refund->notes) {
            $mail->line('**Notes:** ' . $this->refund->notes);
        }
        
        return $mail
            ->line('')
            ->action('View Order Details', $orderUrl)
            ->line('')
            ->line('If you have any questions, please contact our support team.');
    }

    /**
     * Get the subject line based on refund status.
     */
    protected function getSubject(): string
    {
        return match ($this->newStatus) {
            'pending' => 'Refund Request Received - ' . $this->refund->order->order_number,
            'processing' => 'Refund Being Processed - ' . $this->refund->order->order_number,
            'completed' => 'Refund Completed - ' . $this->refund->order->order_number,
            'failed' => 'Refund Failed - ' . $this->refund->order->order_number,
            'rejected' => 'Refund Request Declined - ' . $this->refund->order->order_number,
            default => 'Refund Status Updated - ' . $this->refund->order->order_number,
        };
    }

    /**
     * Get the message based on refund status.
     */
    protected function getMessage(): string
    {
        return match ($this->newStatus) {
            'pending' => 'Your refund request has been received and is awaiting review.',
            'processing' => 'Your refund is being processed. The amount will be refunded to your original payment method.',
            'completed' => 'Your refund has been completed successfully! The amount should reflect in your account within 1-5 business days.',
            'failed' => 'Unfortunately, the refund could not be processed. Please contact our support team for assistance.',
            'rejected' => 'Your refund request has been declined. Please contact our support team for more information.',
            default => 'The status of your refund has been updated.',
        };
    }

    /**
     * Format status for display.
     */
    protected function formatStatus(string $status): string
    {
        return match ($status) {
            'pending' => 'Pending',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'rejected' => 'Rejected',
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
            'refund_id' => $this->refund->id,
            'order_id' => $this->refund->order_id,
            'order_number' => $this->refund->order->order_number,
            'refund_amount' => $this->refund->amount,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'url' => route('orders.show', ['order' => $this->refund->order_id]),
            'message' => 'Refund status for order ' . $this->refund->order->order_number . ' changed from ' . $this->formatStatus($this->oldStatus) . ' to ' . $this->formatStatus($this->newStatus),
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
