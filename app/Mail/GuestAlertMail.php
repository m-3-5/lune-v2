<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GuestAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $alertTitle,
        public string $alertBody,
        public ?string $actionUrl = null,
        public ?string $attachmentStoragePath = null,
        public ?string $attachmentName = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Jlune — '.$this->alertTitle,
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.guest-alert',
        );
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        if (! $this->attachmentStoragePath) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('local', $this->attachmentStoragePath)
                ->as($this->attachmentName ?? basename($this->attachmentStoragePath))
                ->withMime('application/pdf'),
        ];
    }
}
