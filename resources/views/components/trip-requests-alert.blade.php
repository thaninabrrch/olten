@props(['count' => null, 'href' => null])

@php
    /*
     | « Vous avez 3 réservations à approuver » : demandes reçues sur les
     | trajets en validation manuelle, qui attendent la réponse du conducteur.
     | Sans réponse au départ, elles sont annulées et remboursées
     | (covoiturage:expirer-demandes) : le bandeau le rappelle.
     |
     | Affiché sur le tableau de bord et les réservations reçues ; rien ne
     | s'affiche sans demande en attente. Styles : style_connected.css.
     */
    $count ??= auth()->user()?->pendingTripRequests()->count() ?? 0;
    $href  ??= route('trips.received');
@endphp

@if ($count > 0)
    <div class="trq-alert" role="status">
        <span class="trq-alert-icon"><i class="fa-solid fa-hourglass-half"></i></span>
        <span class="trq-alert-text">
            <strong>Vous avez {{ $count }} réservation{{ $count > 1 ? 's' : '' }} à approuver</strong>
            <small>Les passagers ont déjà payé. Sans réponse de votre part au départ, leur demande est annulée et remboursée.</small>
        </span>
        <a href="{{ $href }}" class="trq-alert-btn">
            {{ $count > 1 ? 'Voir les demandes' : 'Voir la demande' }} <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
@endif
