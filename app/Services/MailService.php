<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class MailService
{
    public function send(
        string|array $to,
        Mailable $mailable
    ): void {
        Mail::to($to)->send($mailable);
    }
}
