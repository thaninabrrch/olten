{{--
    En-tête d'une étape de l'assistant : la progression, dessinée comme une
    ligne d'arrêts (une par section), puis le titre de l'étape.

    $section : rang de la section, de 1 à 7
    $title, $lede (facultatif)
    $tag : h1 pour la première étape, h2 sinon
--}}
@php
    $sections = ['Itinéraire', 'Horaires', 'Passagers', 'Réservation', 'Prix', 'Retour', 'Profil'];
    $tag = $tag ?? 'h2';

    // Espace insécable avant ? ! : ; pour qu'ils ne partent pas seuls à la ligne
    $fr = fn ($text) => preg_replace('/ ([?!:;])/u', '&nbsp;$1', e($text));
@endphp
<header class="wz-head">
    <div class="wz-progress">
        <ol class="wz-line" aria-label="Étape {{ $section }} sur {{ count($sections) }}">
            @foreach ($sections as $i => $name)
                <li @class(['is-done' => $i + 1 < $section, 'is-current' => $i + 1 === $section])
                    @if ($i + 1 === $section) aria-current="step" @endif title="{{ $name }}">
                    <span class="sr-only">{{ $name }}</span>
                </li>
            @endforeach
        </ol>
        <span class="wz-count" aria-hidden="true">{{ $section }}<span>/{{ count($sections) }}</span></span>
    </div>
    <p class="wz-eyebrow">{{ $sections[$section - 1] }}</p>
    <{{ $tag }} class="wz-title">{!! $fr($title) !!}</{{ $tag }}>
    @isset($lede)
        <p class="wz-lede">{!! $fr($lede) !!}</p>
    @endisset
</header>
