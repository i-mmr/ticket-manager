<?php

namespace App\Notifications;

use App\Models\PendingRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class CompleteRegistrationNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly PendingRegistration $pendingRegistration,
        private readonly string $token,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('会員登録を完了してください')
            ->greeting('Ticket Manager の会員登録')
            ->line('下のボタンから名前とパスワードを設定すると、会員登録が完了します。')
            ->action('登録を完了する', $this->registrationUrl())
            ->line('このリンクの有効期限は60分です。');
    }

    public function registrationUrl(): string
    {
        return URL::temporarySignedRoute(
            'register.complete',
            $this->pendingRegistration->expires_at,
            [
                'pendingRegistration' => $this->pendingRegistration,
                'hash' => $this->pendingRegistration->email_hash,
                'token' => $this->token,
            ],
        );
    }
}
