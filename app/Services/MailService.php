<?php

namespace App\Services;

use App\Mail\AccountCreatedMail;
use Illuminate\Support\Facades\Mail;

class MailService
{
    public function sendAccountCreated(
        string $name,
        string $email,
        string $password
    ): void {
        Mail::to($email)->send(
            new AccountCreatedMail(
                $name,
                $email,
                $password
            )
        );
    }
}
