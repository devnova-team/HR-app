<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployeeCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $firstName,
        public string $employeeId,
        public string $password
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your '.config('app.name').' account');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.employee-credentials');
    }
}
