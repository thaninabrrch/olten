<?php

namespace App\Notifications;

use App\Models\TripBooking;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Un passager a payé une place sur un trajet en validation manuelle : le
 * conducteur doit accepter ou refuser sa demande. Il est prévenu dans la
 * cloche et par e-mail, car sans réponse au départ la demande est
 * remboursée (covoiturage:expirer-demandes).
 *
 * Envoyée immédiatement, comme TripScheduleChanged : aucun worker de file
 * d'attente n'est nécessaire.
 */
class TripBookingRequested extends Notification
{
    public function __construct(public TripBooking $booking)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon'  => 'fa-hourglass-half',
            'title' => 'Réservation à approuver · ' . $this->route(),
            'text'  => $this->who() . ' demande ' . $this->booking->seatsLabel() . '. Acceptez ou refusez sa demande.',
            'url'   => $this->url(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Réservation à approuver · ' . $this->route())
            ->greeting('Bonjour ' . ($notifiable->firstname ?: $notifiable->name) . ',')
            ->line($this->who() . ' demande ' . $this->booking->seatsLabel() . ' sur votre trajet ' . $this->route() . '.')
            ->line('Le paiement est encaissé et la place bloquée : il ne manque que votre accord.')
            ->action('Accepter ou refuser', $this->url())
            ->line('Sans réponse de votre part au moment du départ, la demande est annulée et le passager remboursé.');
    }

    private function who(): string
    {
        return $this->booking->passenger?->public_name ?? 'Un passager';
    }

    private function route(): string
    {
        $trip = $this->booking->trip;

        return $trip->depart_ville . ' → ' . $trip->destination_ville;
    }

    /** La carte du trajet dans les réservations reçues, où sont les boutons. */
    private function url(): string
    {
        return route('trips.received') . '#trajet-' . $this->booking->trip_id;
    }
}
