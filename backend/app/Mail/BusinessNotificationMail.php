<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;

class BusinessNotificationMail extends Mailable
{
    public function __construct(
        public string $title,
        public string $body
    ) {
    }

    public function build(): self
    {
        return $this->subject('[ĐT-07] ' . $this->title)
            ->text('emails.business_notification');
    }
}
