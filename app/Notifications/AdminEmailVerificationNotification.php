<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class AdminEmailVerificationNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $verificationUrl = URL::temporarySignedRoute(
            'admin.email.verify',
            now()->addMinutes(60),
            ['user' => $notifiable->getKey(), 'hash' => sha1($notifiable->email)],
        );

        return (new MailMessage)
            ->subject('Verify your admin account email')
            ->greeting('Welcome to Northstar')
            ->line('Please verify your email address to complete your admin account setup.')
            ->action('Verify email address', $verificationUrl)
            ->line('This link expires in 60 minutes. If you did not expect this email, you can ignore it.');
    }
}
