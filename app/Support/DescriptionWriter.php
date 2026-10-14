<?php

namespace App\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Suggestions de texte par l'IA (Gemini, de Google) pour les formulaires
 * d'annonce et de produit : une accroche et une description, a partir de ce
 * que le membre a deja saisi (titre, categorie, prix) et de ce qu'il veut
 * mettre en avant.
 *
 * Le texte rendu n'est qu'une proposition : le membre la relit, complete les
 * reperes entre crochets que l'IA laisse a la place des informations qu'elle
 * n'a pas, puis decide de l'utiliser ou non. Rien n'est enregistre ici.
 *
 * Appel direct a l'API Interactions de Gemini, avec le client HTTP de
 * Laravel. La cle se lit dans config('services.gemini.key') : sans elle, le
 * bouton n'est pas affiche (voir isEnabled()). Au niveau gratuit de Gemini,
 * Google peut utiliser les textes envoyes pour ameliorer ses produits, et un
 * quota limite le nombre de demandes.
 */
class DescriptionWriter
{
    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/interactions';

    /**
     * Consigne commune a toutes les demandes : tout ce qui est propre a
     * l'annonce part dans le message.
     */
    private const SYSTEM = <<<'TXT'
Tu aides les membres d'Olten.fr, une plateforme française d'annonces entre particuliers (location, vente, prestations de services), à rédiger le texte de leur annonce. Tu écris en français, sur un ton simple, chaleureux et concret : celui d'un particulier soigneux, pas d'une publicité.

Le plus important : n'invente aucun fait. Tu ne sais que ce que le membre t'a donné. Quand une information manque alors qu'elle compte pour la personne qui lira l'annonce (état, dimensions, marque, année, accès, horaires…), laisse à sa place un repère entre crochets que le membre complétera, par exemple [année] ou [état : très bon / bon / correct]. Trois repères au plus : garde ceux qui comptent le plus.

N'ajoute ni coordonnées, ni lien, ni invitation à payer ou à échanger en dehors de la plateforme, ni d'autre prix que celui indiqué.

Tu rends deux textes :
- summary : une seule phrase d'accroche en texte brut, 160 caractères au plus ;
- description : 80 à 180 mots en HTML simple, en paragraphes <p>, avec au besoin une liste <ul><li> pour les points pratiques et <strong> avec parcimonie. Aucune autre balise, aucun titre.
TXT;

    /** Ce que publie le membre : le contenu attendu change avec. */
    private const KINDS = [
        'location'   => "Location entre particuliers. Décris le bien loué et ce qui est fourni, puis les conditions pratiques que le locataire voudra connaître (retrait, restitution, caution).",
        'vente'      => "Vente entre particuliers. Décris l'objet, son état, ce qui est inclus et la façon de le remettre.",
        'prestation' => "Demande de prestation de services. Le membre est un client qui cherche un prestataire, pas quelqu'un qui propose ses services : écris à la première personne (« Je recherche… »). Décris le travail à réaliser, le contexte et les contraintes, et ce que le client attend des propositions des prestataires.",
        'produit'    => "Fiche produit de la boutique d'un vendeur. Décris le produit, ses caractéristiques et ce qui est inclus.",
    ];

    /** Unite du prix, selon ce qu'il represente. */
    private const PRICE_LABELS = [
        'location'   => 'Prix de location : %s € par jour',
        'vente'      => 'Prix de vente : %s €',
        'prestation' => 'Budget indicatif du client : %s €',
        'produit'    => 'Prix : %s €',
    ];

    /** Balises gardees dans la description, celles que l'editeur de texte sait afficher. */
    private const ALLOWED_TAGS = '<p><ul><ol><li><strong><em><br>';

    public static function isEnabled(): bool
    {
        return filled(config('services.gemini.key'));
    }

    /**
     * @param  array{kind: string, mode: string, title: ?string, category: ?string, price: ?float, notes: ?string, current: ?string}  $input
     * @return array{summary: string, description: string}
     *
     * @throws RuntimeException avec un message a montrer au membre
     */
    public function suggest(array $input): array
    {
        try {
            $response = Http::withHeaders(['x-goog-api-key' => config('services.gemini.key')])
                ->acceptJson()
                ->timeout(45)
                ->post(self::ENDPOINT, [
                    'model'              => config('services.gemini.model'),
                    'system_instruction' => self::SYSTEM,
                    'input'              => $this->prompt($input),
                    'response_format'    => [
                        'type'      => 'text',
                        'mime_type' => 'application/json',
                        'schema'    => [
                            'type'       => 'object',
                            'properties' => [
                                'summary'     => ['type' => 'string'],
                                'description' => ['type' => 'string'],
                            ],
                            'required'   => ['summary', 'description'],
                        ],
                    ],
                    'generation_config'  => ['max_output_tokens' => 4000],
                    // Une suggestion n'a pas a etre conservee chez Google
                    'store'              => false,
                ]);
        } catch (ConnectionException $e) {
            report($e);
            throw new RuntimeException('L\'assistant n\'a pas pu répondre. Réessayez dans un instant.');
        }

        // 429 : quota du niveau gratuit atteint (par minute ou par jour)
        if ($response->status() === 429) {
            throw new RuntimeException('L\'assistant est très sollicité : réessayez dans un instant.');
        }

        if ($response->failed()) {
            report(new RuntimeException('Gemini a répondu ' . $response->status() . ' : ' . $response->body()));
            throw new RuntimeException('L\'assistant de rédaction est indisponible pour le moment.');
        }

        // Le texte genere est dans l'etape « model_output » de l'interaction.
        // Une interaction interrompue (blocage, limite de longueur) n'en
        // donne pas d'exploitable : on le dit au membre plutot que d'inventer.
        $text = collect($response->json('steps', []))
            ->where('type', 'model_output')
            ->flatMap(fn ($step) => $step['content'] ?? [])
            ->where('type', 'text')
            ->pluck('text')
            ->implode('');

        $data = json_decode($text, true);

        if ($response->json('status') !== 'completed' || ! is_array($data) || ! isset($data['summary'], $data['description'])) {
            throw new RuntimeException('L\'assistant n\'a pas pu rédiger ce texte. Reformulez votre demande, ou rédigez-la vous-même.');
        }

        return [
            'summary'     => trim(strip_tags($data['summary'])),
            'description' => $this->cleanHtml($data['description']),
        ];
    }

    /** Le message propre a l'annonce : ce que le membre a saisi, et la tache. */
    private function prompt(array $input): string
    {
        $kind  = array_key_exists($input['kind'], self::KINDS) ? $input['kind'] : 'location';
        $lines = ['Type d\'annonce : ' . self::KINDS[$kind]];

        if (filled($input['title'])) {
            $lines[] = 'Titre : ' . $input['title'];
        }

        if (filled($input['category'])) {
            $lines[] = 'Catégorie : ' . $input['category'];
        }

        if ($input['price'] !== null && $input['price'] > 0) {
            $lines[] = sprintf(self::PRICE_LABELS[$kind], number_format($input['price'], 2, ',', ' '));
        }

        if (filled($input['notes'])) {
            $lines[] = "Ce que le membre veut mettre en avant :\n" . $input['notes'];
        }

        if ($input['mode'] === 'improve') {
            $lines[] = "Texte actuel du membre :\n" . $input['current'];
            $lines[] = 'Tâche : améliore ce texte. Corrige-le, clarifie-le et structure-le sans changer les faits ni en ajouter, et propose une accroche.';
        } else {
            $lines[] = 'Tâche : rédige l\'accroche et la description de cette annonce.';
        }

        return implode("\n\n", $lines);
    }

    /**
     * Ne garde que les balises simples, sans attribut : la description
     * s'affiche telle quelle sur la fiche de l'annonce.
     */
    private function cleanHtml(string $html): string
    {
        // strip_tags garderait le contenu d'un <script> ou d'un <style> en texte
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1\s*>#is', '', $html);
        $html = strip_tags($html, self::ALLOWED_TAGS);

        return trim(preg_replace('/<(\/?)(p|ul|ol|li|strong|em|br)\b[^>]*>/i', '<$1$2>', $html));
    }
}
