<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailCustom extends VerifyEmail
{
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Confirmez votre adresse e-mail - Olten')
            ->greeting('Bienvenue sur Olten 👋')
            ->line('Merci pour votre inscription sur Olten.fr.')
            ->line('Afin d’activer votre compte, veuillez confirmer votre adresse e-mail en cliquant sur le bouton ci-dessous.')
            ->action('Confirmer mon adresse e-mail', $verificationUrl)
            ->line('Si vous n’êtes pas à l’origine de cette inscription, aucune action supplémentaire n’est requise.')
            ->salutation('L’équipe Olten');
    }
}