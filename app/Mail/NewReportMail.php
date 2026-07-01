<?php

namespace App\Mail;

use App\Models\Report;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewReportMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public User $user,
        public Report $report,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->user->role === 'ketua'
                ? 'Informasi Laporan Baru - Segera Ditindaklanjuti'
                : 'Informasi Laporan Baru',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-report',
            with: [
                'user' => $this->user,
                'report' => $this->report,
                'loginUrl' => route('login'),
                'isKetua' => $this->user->role === 'ketua',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
