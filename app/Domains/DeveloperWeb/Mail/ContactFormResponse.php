<?php
// app/Domains/DeveloperWeb/Mail/ContactFormResponse.php

namespace App\Domains\DeveloperWeb\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormResponse extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $fullName,
        public string $originalSubject,
        public string $response
    ) {}

    public function build(): self
    {
        return $this
            ->subject("Respuesta a: {$this->originalSubject}")
            ->view('emails.contact-form-response-simple')
            ->with([
                'fullName' => $this->fullName,
                'response' => $this->response,
            ]);
    }
}