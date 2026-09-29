<?php

namespace App\Notifications;

use App\Models\TripBooking;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Réponse à une demande de réservation (trajet en validation manuelle) :
 * acceptée par le conducteur, refusée, ou restée sans réponse au départ.
 * Dans les deux derniers cas le passager a été remboursé. Cloche et
 * e-mail : il doit savoir s'il part, même sans se reconnecter.
 *
 * Envoyée immédiatement, comme TripScheduleChanged.
 */
class TripBookingAnswered extends Notification
{
    /** @param string $answer accepted | refused | expired */
    public function __construct(public TripBooking $booking, public string $answer)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        [$icon, $title, $text] = match ($this->answer) {
            'accepted' => ['fa-circle-check', 'Réservation acceptée', 'Le conducteur a accepté votre demande' . $this->departure() . '.'],
            'refused'  => ['fa-circle-xmark', 'Réservation refusée', "Le conducteur n'a pas accepté votre demande. Vous êtes intégralement remboursé."],
            default    => ['fa-clock-rotate-left', 'Demande sans réponse', "Le conducteur n'a pas répondu avant le départ. Vous êtes intégralement remboursé."],
        };

        return [
            'icon'  => $icon,
            'title' => $title . ' · ' . $this->route(),
            'text'  => $text,
            'url'   => route('trips.myBookings.show', $this->booking),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)->greeting('Bonjour ' . ($notifiable->firstname ?: $notifiable->name) . ',');

        if ($this->answer === 'accepted') {
            return $mail
                ->subject('Réservation acceptée · ' . $this->route())
                ->line('Bonne nouvelle : le conducteur a accepté votre demande sur le trajet ' . $this->route() . $this->departure() . '.')
                ->line('Il vous joindra au numéro que vous avez renseigné.')
                ->action('Voir ma réservation', route('trips.myBookings.show', $this->booking));
        }

        $trip = $this->booking->trip;

        return $mail
            ->subject(($this->answer === 'refused' ? 'Réservation refusée' : 'Demande sans réponse') . ' · ' . $this->route())
            ->line($this->answer === 'refused'
                ? "Le conducteur n'a pas accepté votre demande sur le trajet " . $this->route() . '.'
                : "Le conducteur n'a pas répondu avant le départ à votre demande sur le trajet " . $this->route() . '.')
            ->line('Vous êtes intégralement remboursé : selon votre banque, le montant apparaît sur votre compte sous 5 à 10 jours ouvrés.')
            ->action('Trouver un autre trajet', route('covoiturage.trips', ['from' => $trip->depart_ville, 'to' => $trip->destination_ville]));
    }

    private function route(): string
    {
        $trip = $this->booking->trip;

        return $trip->depart_ville . ' → ' . $trip->destination_ville;
    }

    /** « : départ le sam. 04 oct. à 08:30 » */
    private function departure(): string
    {
        $at = $this->booking->departsAt();

        return $at ? ' : départ le ' . $at->translatedFormat('D d M') . ' à ' . $at->format('H:i') : '';
    }
}
