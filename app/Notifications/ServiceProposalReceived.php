<?php

namespace App\Notifications;

use App\Models\ServiceProposal;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Un prestataire a répondu à une demande de prestation : le client est
 * prévenu dans la cloche et retrouve la proposition sur la page de
 * comparaison. Pas d'e-mail : une demande peut recevoir de nombreuses
 * propositions, une par message serait du bruit.
 *
 * Envoyée immédiatement, comme TripScheduleChanged.
 */
class ServiceProposalReceived extends Notification
{
    public function __construct(public ServiceProposal $proposal)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $who   = $this->proposal->provider?->public_name ?? 'Un prestataire';
        $price = number_format((float) $this->proposal->price, 2, ',', ' ') . ' €';

        return [
            'icon'  => 'fa-hand-holding-dollar',
            'title' => 'Nouvelle proposition · ' . Str::limit($this->proposal->ad->title, 40),
            'text'  => $who . ' vous propose ses services pour ' . $price . '. Comparez les propositions reçues.',
            'url'   => route('ads.proposals', $this->proposal->ad_id) . '#proposition-' . $this->proposal->id,
        ];
    }
}
