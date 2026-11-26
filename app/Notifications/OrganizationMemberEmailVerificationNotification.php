<?php

namespace App\Notifications;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class OrganizationMemberEmailVerificationNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Organization $organization)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $verificationUrl = URL::temporarySignedRoute(
            'organizations.member.verify',
            now()->addMinutes(60),
            [
                'organization' => $this->organization->getKey(),
                'member' => $notifiable->getKey(),
                'hash' => sha1($notifiable->email),
            ],
        );

        return (new MailMessage)
            ->subject('Verify your organization account email')
            ->greeting('Welcome to '.$this->organization->name)
            ->line('Please verify your email address to activate your organization user account.')
            ->action('Verify email address', $verificationUrl)
            ->line('This link expires in 60 minutes. You can also verify your email with the one-time code sent when you sign in.');
    }
}
