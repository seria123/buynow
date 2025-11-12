<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Build the mail representation of the notification.
     */
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject('Reset your Buynow password')
            ->greeting('Hello!')
            ->line('We received a request to reset the password for your Buynow account.')
            ->action('Reset password', $url)
            ->line('This link will expire in 60 minutes for your security.')
            ->line('If you did not request a password reset, no further action is required.')
            ->salutation('Stay secure, The Buynow Team');
    }
}

