<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminTeamAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $alertTitle,
        public string $alertBody,
        public ?string $actionUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->alertTitle,
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.admin-team-alert',
        );
    }
}
