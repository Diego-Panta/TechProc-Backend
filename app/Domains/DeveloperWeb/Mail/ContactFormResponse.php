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
            ->subject("Respuesta a tu consulta: {$this->originalSubject}")
            ->view('emails.contact-form-response')
            ->with([
                'fullName' => $this->fullName,
                'originalSubject' => $this->originalSubject,
                'response' => $this->response,
                'responseDate' => now()->format('d/m/Y H:i'),
            ]);
    }
}