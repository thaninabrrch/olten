{{--
    Une annonce vient d'etre publiee dans une categorie suivie.
    Envoye par App\Mail\NewAdNotification, qui expose `ad`.
--}}
@php
    $photo = $ad->images->first()?->path
        ? asset('storage/' . $ad->images->first()->path)
        : null;

    // `available_from` et `available_until` sont castees en date sur le
    // modele Ad, mais restent facultatives : une annonce de vente n'a pas de
    // periode de mise a disposition.
    $du = $ad->available_from?->format('d/m/Y');
    $au = $ad->available_until?->format('d/m/Y');

    $vente = $ad->category?->isVente() ?? false;
@endphp

<x-email.layout
    title="Nouvelle annonce"
    :preheader="($ad->title) . ' — ' . (number_format($ad->price_per_day, 2, ',', ' ')) . ' €' . ($vente ? '' : ' / jour')"
    :eyebrow="$ad->category?->nom ?? 'Nouvelle annonce'"
    heading="Une nouvelle annonce pour vous"
    subheading="Elle vient d'être publiée dans une catégorie que vous suivez.">

    @if($photo)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 22px;">
            <tr>
                <td class="ol-photo" align="center" style="border-radius:12px; overflow:hidden;">
                    <img src="{{ $photo }}" width="536" alt="{{ $ad->title }}"
                         style="display:block; width:100%; max-width:536px; height:auto; border-radius:12px;" />
                </td>
            </tr>
        </table>
    @endif

    <x-email.text tone="lead">{{ $ad->title }}</x-email.text>

    @if($ad->summary)
        <x-email.text>{{ $ad->summary }}</x-email.text>
    @endif

    <x-email.panel title="En résumé">
        @if($ad->category)
            <x-email.row label="Catégorie" :value="$ad->category->nom" />
        @endif

        @if($ad->address)
            <x-email.row label="Localisation" :value="$ad->address" />
        @endif

        @if($du)
            <x-email.row label="Disponible du" :value="$du" />
        @endif

        @if($au)
            <x-email.row label="Jusqu'au" :value="$au" />
        @endif

        <x-email.row
            :label="$vente ? 'Prix' : 'Tarif'"
            :value="(number_format($ad->price_per_day, 2, ',', ' ')) . ' €' . ($vente ? '' : ' / jour')"
            total />
    </x-email.panel>

    <x-email.button :url="route('ads.show', $ad)">
        Voir l'annonce
    </x-email.button>

    <x-email.note>
        Vous recevez cet e-mail parce que vous suivez cette catégorie sur Olten.
        Vous pouvez couper ces notifications depuis
        <a href="{{ route('profile') }}" style="color:#ff3c00; text-decoration:underline;">votre profil</a>.
    </x-email.note>

</x-email.layout>
