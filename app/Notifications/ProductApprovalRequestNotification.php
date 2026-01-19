<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductApprovalRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $product;

    protected $creator;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\Catalogue\Product  $product
     * @param  \App\Models\User|null  $creator
     */
    public function __construct($product, $creator = null)
    {
        $this->product = $product;
        $this->creator = $creator;
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
        $productUrl = route('filament.admin.resources.products.edit', ['record' => $this->product->id]);
        $creatorInfo = $this->creator
            ? ' by '.$this->creator->name.' ('.$this->creator->email.')'
            : '';

        return (new MailMessage)
            ->subject('Product Pending Approval: '.$this->product->name)
            ->greeting('Hello Admin,'.$notifiable->name.'!')
            ->line('A new product'.$creatorInfo.' has been added to the catalogue and is awaiting your review for approval.')
            ->line('**Product Name:** '.$this->product->name)
            ->line('**Category:** '.($this->product->category->name ?? 'N/A'))
            ->line('**Brand:** '.($this->product->brand->name ?? 'N/A'))
            ->action('Review Product', $productUrl)
            ->line('Please check all information and publish if everything is correct.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'creator_id' => $this->creator?->id,
            'creator_name' => $this->creator?->name,
            'category' => $this->product->category->name ?? 'N/A',
            'brand' => $this->product->brand->name ?? 'N/A',
            'url' => route('filament.admin.resources.products.edit', ['record' => $this->product->id]),
        ];
    }

    /**
     * Get the database representation of the notification for Filament.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }
}
