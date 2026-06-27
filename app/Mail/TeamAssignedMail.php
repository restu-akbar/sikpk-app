<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamAssignedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $name,
        public string $teamNumber,
        public string $reportTitle,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Penugasan Tim Penanganan',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.team-assigned',
            with: [
                'name' => $this->name,
                'teamNumber' => $this->teamNumber,
                'reportTitle' => $this->reportTitle,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
