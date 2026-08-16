<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordUpdatedByAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre mot de passe a été modifié'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password_updated'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}