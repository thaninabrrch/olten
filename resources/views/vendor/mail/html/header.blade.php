@props(['url'])
{{--
    En-tete des messages Markdown.

    La version livree par Laravel n'affichait le logo que si le slot valait
    exactement la chaine « Laravel » (@if (trim($slot) === 'Laravel')) : une
    sentinelle heritee du gabarit d'origine, qui obligeait a passer un nom de
    marque faux pour obtenir la bonne image. Le logo est desormais toujours
    affiche, et le slot ne sert plus qu'au texte de remplacement.
--}}
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ asset('assets/images/logo/olten_location.png') }}" width="132" alt="{{ trim($slot) ?: 'Olten' }}" class="logo">
</a>
</td>
</tr>
