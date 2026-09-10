{{--
    Un produit vient d'etre mis en vente dans une categorie suivie.
    Envoye par App\Mail\NewProductNotification, qui expose `product`.
--}}
@php
    $photo = $product->images->first()?->image
        ? asset('storage/' . $product->images->first()->image)
        : null;
@endphp

<x-email.layout
    title="Nouveau produit"
    :preheader="($product->name) . ' — ' . (number_format($product->price, 2, ',', ' ')) . ' €'"
    :eyebrow="$product->category?->nom ?? 'Nouveau produit'"
    heading="Un nouveau produit pour vous"
    subheading="Il vient d'être mis en vente dans une catégorie que vous suivez.">

    @if($photo)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 22px;">
            <tr>
                <td class="ol-photo" align="center" style="border-radius:12px; overflow:hidden;">
                    <img src="{{ $photo }}" width="536" alt="{{ $product->name }}"
                         style="display:block; width:100%; max-width:536px; height:auto; border-radius:12px;" />
                </td>
            </tr>
        </table>
    @endif

    <x-email.text tone="lead">{{ $product->name }}</x-email.text>

    @if($product->description)
        <x-email.text>{{ \Illuminate\Support\Str::limit(strip_tags($product->description), 200) }}</x-email.text>
    @endif

    <x-email.panel title="En résumé">
        @if($product->category)
            <x-email.row label="Catégorie" :value="$product->category->nom" />
        @endif

        @if($product->address)
            <x-email.row label="Localisation" :value="$product->address" />
        @endif

        <x-email.row label="Stock" :value="$product->stock > 0 ? $product->stock . ' disponible' . ($product->stock > 1 ? 's' : '') : 'Rupture'" />

        @if($product->delivery_available)
            <x-email.row label="Livraison" value="Possible" />
        @endif

        <x-email.row label="Prix" :value="(number_format($product->price, 2, ',', ' ')) . ' €'" total />
    </x-email.panel>

    <x-email.button :url="route('products.show', $product)">
        Voir le produit
    </x-email.button>

    <x-email.note>
        Vous recevez cet e-mail parce que vous suivez cette catégorie sur Olten.
        Vous pouvez couper ces notifications depuis
        <a href="{{ route('profile') }}" style="color:#ff3c00; text-decoration:underline;">votre profil</a>.
    </x-email.note>

</x-email.layout>
