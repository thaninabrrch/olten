<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Support\DescriptionWriter;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * « Rédiger avec l'IA » dans les formulaires d'annonce et de produit
 * (<x-ai-description>) : la page envoie ce que le membre a deja saisi, et
 * recoit une accroche et une description a relire avant de les utiliser.
 *
 * Repond en JSON au fetch de assets/js/ai-description.js.
 */
class AiDescriptionController extends Controller
{
    public function suggest(Request $request, DescriptionWriter $writer)
    {
        abort_unless(DescriptionWriter::isEnabled(), 404);

        $data = $request->validate([
            'target'      => 'required|in:annonce,produit',
            'mode'        => 'required|in:write,improve',
            'category_id' => 'nullable|integer|exists:categories,id',
            'title'       => 'nullable|string|max:255',
            'price'       => 'nullable|numeric|min:0|max:100000000',
            'notes'       => 'nullable|string|max:1000',
            'current'     => 'nullable|string|max:20000',
        ], [
            'notes.max'   => 'Votre consigne ne peut pas dépasser :max caractères.',
            'current.max' => 'Votre texte est trop long pour être amélioré d\'un coup.',
        ]);

        // Le texte actuel arrive de l'editeur, en HTML : l'IA n'a besoin que
        // de son contenu.
        $current = trim(html_entity_decode(strip_tags((string) ($data['current'] ?? '')), ENT_QUOTES | ENT_HTML5));

        if ($data['mode'] === 'improve' && $current === '') {
            return response()->json(['message' => 'Écrivez d\'abord un texte : l\'assistant pourra ensuite l\'améliorer.'], 422);
        }

        if (blank($data['title'] ?? null) && blank($data['notes'] ?? null) && $current === '') {
            return response()->json(['message' => 'Indiquez au moins un titre, ou dites en quelques mots ce que vous proposez.'], 422);
        }

        // Le type d'annonce se deduit de la categorie, cote serveur : c'est
        // elle qui fait d'une annonce une location, une vente ou une demande
        // de prestation (voir assets/js/ad-form.js).
        $category = isset($data['category_id']) ? Category::with('service')->find($data['category_id']) : null;

        $kind = match (true) {
            $data['target'] === 'produit'           => 'produit',
            (bool) $category?->isPrestation()       => 'prestation',
            (bool) $category?->isVente()            => 'vente',
            default                                 => 'location',
        };

        try {
            $suggestion = $writer->suggest([
                'kind'     => $kind,
                'mode'     => $data['mode'],
                'title'    => $data['title'] ?? null,
                'category' => $category?->nom,
                'price'    => isset($data['price']) ? (float) $data['price'] : null,
                'notes'    => $data['notes'] ?? null,
                'current'  => $current,
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }

        return response()->json($suggestion);
    }
}
