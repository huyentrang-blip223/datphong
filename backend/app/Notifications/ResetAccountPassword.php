<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;

class ResetAccountPassword extends ResetPassword
{
    protected function resetUrl($notifiable)
    {
        // Use the configured origin, never the untrusted request Host header.
        return rtrim(config('app.url'), '/').route('password.reset', ['token' => $this->token, 'email' => $notifiable->email], false);
    }

    public function toMail($notifiable)
    {
        $message = parent::toMail($notifiable);
        if (app()->environment('local') && config('mail.default') === 'smtp' && ! config('mail.mailers.smtp.username')) {
            $message->mailer('log');
        }

        return $message;
    }
}
