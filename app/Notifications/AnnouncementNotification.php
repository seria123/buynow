<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnnouncementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Announcement $announcement
    ) {}

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
        $mail = (new MailMessage)
            ->subject($this->announcement->title)
            ->greeting('Hello ' . $notifiable->name . '!');

        // Add different content based on announcement type
        $mail = match ($this->announcement->type) {
            'promotional' => $this->buildPromotionalMail($mail),
            'order' => $this->buildOrderMail($mail),
            'account' => $this->buildAccountMail($mail),
            'newsletter' => $this->buildNewsletterMail($mail),
            default => $this->buildGeneralMail($mail),
        };

        return $mail
            ->salutation('Best regards, The Buynow Team');
    }

    /**
     * Build a general announcement email
     */
    protected function buildGeneralMail(MailMessage $mail): MailMessage
    {
        return $mail
            ->line($this->announcement->content)
            ->action('View Details', url('/dashboard'));
    }

    /**
     * Build a promotional announcement email
     */
    protected function buildPromotionalMail(MailMessage $mail): MailMessage
    {
        return $mail
            ->line('🎉 ' . $this->announcement->content)
            ->action('Shop Now', url('/products'))
            ->line('This offer is exclusive to you as a valued customer.');
    }

    /**
     * Build an order-related announcement email
     */
    protected function buildOrderMail(MailMessage $mail): MailMessage
    {
        return $mail
            ->line('📦 ' . $this->announcement->content)
            ->action('View Orders', url('/orders'));
    }

    /**
     * Build an account-related announcement email
     */
    protected function buildAccountMail(MailMessage $mail): MailMessage
    {
        return $mail
            ->line('🔐 ' . $this->announcement->content)
            ->action('Manage Account', url('/profile'));
    }

    /**
     * Build a newsletter announcement email
     */
    protected function buildNewsletterMail(MailMessage $mail): MailMessage
    {
        return $mail
            ->line('📰 ' . $this->announcement->content)
            ->action('Read More', url('/blog'))
            ->line('Thank you for subscribing to our newsletter!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'announcement_id' => $this->announcement->id,
            'title' => $this->announcement->title,
            'content' => $this->announcement->content,
            'type' => $this->announcement->type,
            'target' => $this->announcement->target,
        ];
    }
}
