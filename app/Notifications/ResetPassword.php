<?php

namespace App\Notifications;

use App\Mail\ResetPasswordMail;
use Illuminate\Notifications\Notification;

class ResetPassword extends Notification
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Le prenom plutot que l'identifiant : `name` porte souvent le nom
        // de compte (« akhettar »), qui sonne froid en tete d'un e-mail.
        $prenom = $notifiable->firstname ?: $notifiable->name;

        return (new ResetPasswordMail($this->token, $notifiable->email, $prenom))
                    ->to($notifiable->email);
    }
}
