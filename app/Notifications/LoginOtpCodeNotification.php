<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginOtpCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $code,
        public int $ttlMinutes
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode Masuk Cakrawala')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Gunakan kode OTP berikut untuk masuk ke akun Anda:')
            ->line('Kode OTP: '.$this->code)
            ->line('Kode berlaku selama '.$this->ttlMinutes.' menit.')
            ->line('Jika Anda tidak meminta kode ini, abaikan email ini.');
    }
}

