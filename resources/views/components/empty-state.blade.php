{{--
    Etat vide unique de la plateforme.

    Utilise partout ou une liste peut etre vide (annonces, produits,
    reservations, trajets, favoris, commandes...) pour que le message
    « il n'y a rien ici » soit toujours le meme.

    Exemples :
        <x-empty-state />
        <x-empty-state title="Aucun trajet pour le moment"
                       text="Partagez votre route et rentabilisez vos déplacements."
                       :action-url="route('covoiturage.create')"
                       action-label="Publier un trajet" />
        <x-empty-state compact title="Aucune annonce trouvée" />
--}}
@props([
    'image'       => 'assets/images/pasdead.png',
    'title'       => "Aucune annonce n'a été publiée pour le moment",
    'text'        => null,
    'actionUrl'   => null,
    'actionLabel' => null,
    'actionAuth'  => false,
    'compact'     => false,
])

<div {{ $attributes->class(['olten-empty', 'olten-empty--compact' => $compact]) }}>
    <img class="olten-empty-illustration" src="{{ asset($image) }}" alt="" aria-hidden="true">

    <h3 class="olten-empty-title">{{ $title }}</h3>

    @if($text)
        <p class="olten-empty-text">{{ $text }}</p>
    @elseif(! $slot->isEmpty())
        <p class="olten-empty-text">{{ $slot }}</p>
    @endif

    @if($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="olten-empty-btn" @if($actionAuth) data-auth-required @endif>{{ $actionLabel }}</a>
    @endif
</div>
