<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public $contact;

    /**
     * Create a new message instance.
     */
    public function __construct($contact)
    {
        $this->contact = $contact;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        // Le controleur envoie ce message a l'auteur du formulaire
        // (Mail::to($validated['email'])) : c'est donc un accuse de
        // reception, pas la notification interne que laissait croire
        // l'ancien objet « Nouveau message de contact ».
        return $this->subject('Nous avons bien reçu votre message - Olten')
                    ->view('emails.contact_message')
                    ->with([
                        'contact' => $this->contact
                    ]);
    }
}
