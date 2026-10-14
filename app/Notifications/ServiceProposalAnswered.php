<?php

namespace App\Notifications;

use App\Models\ServiceProposal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Réponse du client à une proposition : retenue, ou non retenue parce qu'il
 * a choisi un autre prestataire.
 *
 * Le prestataire retenu est aussi prévenu par e-mail : c'est à lui de
 * prendre contact pour convenir des modalités, il doit le savoir même sans
 * se reconnecter. Les autres ne le sont que dans la cloche.
 *
 * Envoyée immédiatement, comme TripBookingAnswered.
 */
class ServiceProposalAnswered extends Notification
{
    public const ACCEPTED = 'accepted';
    public const DECLINED = 'declined';

    /** @param string $answer accepted | declined */
    public function __construct(public ServiceProposal $proposal, public string $answer)
    {
    }

    public function via(object $notifiable): array
    {
        return $this->answer === self::ACCEPTED ? ['database', 'mail'] : ['database'];
    }

    public function toArray(object $notifiable): array
    {
        [$icon, $title, $text] = $this->answer === self::ACCEPTED
            ? ['fa-circle-check', 'Proposition retenue', 'Le client vous a choisi pour ' . $this->price() . '. Écrivez-lui pour convenir des modalités.']
            : ['fa-circle-xmark', 'Proposition non retenue', 'Le client a choisi un autre prestataire pour cette demande.'];

        return [
            'icon'  => $icon,
            'title' => $title . ' · ' . Str::limit($this->proposal->ad->title, 40),
            'text'  => $text,
            'url'   => route('proposals.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ad = $this->proposal->ad;

        return (new MailMessage)
            ->subject('Proposition retenue · ' . Str::limit($ad->title, 60))
            ->greeting('Bonjour ' . ($notifiable->firstname ?: $notifiable->name) . ',')
            ->line('Bonne nouvelle : ' . ($ad->user?->public_name ?? 'le client') . ' a retenu votre proposition à ' . $this->price()
                . ' pour sa demande « ' . $ad->title . ' ».')
            ->line('Prenez contact avec lui pour convenir ensemble de la date, du lieu et des détails de la prestation.')
            // La fiche de la demande, popin « Message » ouverte (?contacter=1)
            ->action('Écrire au client', route('ads.show', ['ad' => $ad->id, 'contacter' => 1]));
    }

    private function price(): string
    {
        return number_format((float) $this->proposal->price, 2, ',', ' ') . ' €';
    }
}
