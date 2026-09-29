<?php

namespace App\Notifications;

use App\Models\TripBooking;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Le conducteur a décalé l'horaire d'un sens que le passager a réservé
 * (dans la marge autorisée, voir Covoiturage::bookedTimeShift()). Le
 * passager est prévenu dans la cloche du header et par e-mail : il peut
 * ne pas se reconnecter avant de partir.
 *
 * Envoyée immédiatement : un passager ne doit pas rester sans nouvelles
 * parce qu'aucun worker de file d'attente ne tourne.
 */
class TripScheduleChanged extends Notification
{
    /**
     * @param  array<string, array{0: string, 1: string}>  $changes  sens => [ancien horaire, nouvel horaire]
     */
    public function __construct(public TripBooking $booking, public array $changes)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon'  => 'fa-clock',
            'title' => 'Horaire modifié · ' . $this->route(),
            'text'  => ucfirst(implode(' ; ', $this->lines())) . '.',
            'url'   => route('trips.myBookings.show', $this->booking),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Votre trajet ' . $this->route() . ' change d\'horaire')
            ->greeting('Bonjour ' . ($notifiable->firstname ?: $notifiable->name) . ',')
            ->line('Le conducteur a ajusté l\'horaire de votre trajet ' . $this->route() . '.');

        foreach ($this->lines() as $line) {
            $message->line(ucfirst($line) . '.');
        }

        return $message
            ->action('Voir ma réservation', route('trips.myBookings.show', $this->booking))
            ->line('Si ce nouvel horaire ne vous convient plus, vous pouvez annuler votre réservation depuis cette page : vous êtes intégralement remboursé.');
    }

    private function route(): string
    {
        $trip = $this->booking->trip;

        return $trip->depart_ville . ' → ' . $trip->destination_ville;
    }

    /** « l'aller du mar. 12 oct. part à 09:00 au lieu de 08:30 », un par sens. */
    private function lines(): array
    {
        $trip = $this->booking->trip;

        return collect($this->changes)->map(function (array $change, string $leg) use ($trip) {
            [$before, $after] = $change;
            $date = $leg === 'retour' ? $trip->return_date : $trip->date_depart;

            return ($leg === 'retour' ? 'le retour' : 'l\'aller')
                . ($date ? ' du ' . $date->translatedFormat('D d M') : '')
                . ' part à ' . $after . ' au lieu de ' . $before;
        })->values()->all();
    }
}
