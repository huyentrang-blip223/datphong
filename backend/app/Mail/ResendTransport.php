<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;

class ResendTransport extends AbstractTransport
{
    protected function doSend(SentMessage $message): void
    {
        $email = $message->getOriginalMessage();
        if (! $email instanceof Email || ! config('services.resend.key')) {
            throw new TransportException('Resend is not configured.');
        }
        $body = ['from' => $email->getFrom()[0]->toString(), 'to' => array_map(fn ($address) => $address->getAddress(), $message->getEnvelope()->getRecipients()),
            'subject' => $email->getSubject(), 'html' => $email->getHtmlBody(), 'text' => $email->getTextBody()];
        $response = Http::withToken(config('services.resend.key'))->timeout(15)->post('https://api.resend.com/emails', array_filter($body, fn ($value) => $value !== null));
        if (! $response->successful() || ! $response->json('id')) {
            throw new TransportException('Email provider rejected the message.');
        }
    }

    public function __toString(): string
    {
        return 'resend';
    }
}
