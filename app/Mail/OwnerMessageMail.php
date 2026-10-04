<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Previent le proprietaire d'une offre qu'un membre lui a ecrit depuis la
 * popin « Message » de la fiche. Le texte est aussi dans la messagerie : le
 * bouton de l'e-mail y renvoie, pour que la reponse reste sur Olten.
 */
class OwnerMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    // Pas de propriete `$message` : ce nom est reserve dans les vues d'e-mail
    // (Laravel y injecte l'objet Illuminate\Mail\Message).
    public function __construct(
        public User $sender,
        public User $owner,
        public string $listingTitle,
        public string $listingUrl,
        public string $messageText,
    ) {
    }

    public function build()
    {
        return $this->subject('Nouveau message à propos de « ' . $this->listingTitle . ' »')
                    ->view('emails.owner_message');
    }
}
