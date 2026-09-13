<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $phoneNumber,
        public string $contactMessage,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [
                new Address(
                    $this->email,
                    $this->firstName.' '.$this->lastName
                ),
            ],
            subject: 'Nowe zapytanie ze strony AmperMaks',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contactMail',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
