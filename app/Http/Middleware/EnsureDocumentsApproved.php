<?php

namespace App\Http\Middleware;

use App\Models\UserDocument;
use Closure;
use Illuminate\Http\Request;

/**
 * Verrouille une activite tant que les pieces qu'elle exige n'ont pas ete
 * validees par l'administrateur (back-office /admin/documents).
 *
 * Deux activites aujourd'hui, declarees ci-dessous :
 *   documents.approved:publish  -> publier un trajet   (permis)
 *   documents.approved:deliver  -> espace livraison    (permis)
 *
 * Tant qu'une piece manque, est en cours d'examen ou a ete refusee, la page
 * demandee est remplacee par une page d'attente et les endpoints d'ecriture
 * sont refuses : le blocage ne doit pas etre seulement visuel.
 *
 * Remplace les anciens filtres propres au covoiturage.
 */
class EnsureDocumentsApproved
{
    private const CONTEXTS = [
        'publish' => [
            'documents' => UserDocument::REQUIRED_TO_PUBLISH,
            'action'    => 'publier un trajet',
            'dossier'   => 'dossier conducteur',
            'retour'    => ['covoiturage.index', 'Mes trajets', 'fa-list'],
        ],
        'deliver' => [
            'documents' => UserDocument::REQUIRED_TO_DELIVER,
            'action'    => 'accepter des livraisons',
            'dossier'   => 'dossier livreur',
            'retour'    => ['dashboard', 'Tableau de bord', 'fa-gauge-high'],
        ],
    ];

    public function handle(Request $request, Closure $next, string $context = 'publish')
    {
        $config = self::CONTEXTS[$context] ?? self::CONTEXTS['publish'];

        $documents = UserDocument::where('user_id', auth()->id())
            ->whereIn('name', $config['documents'])
            ->get()
            ->keyBy('name');

        $bloquants = collect($config['documents'])
            ->reject(fn ($name) => optional($documents->get($name))->status === 'approved');

        if ($bloquants->isEmpty()) {
            return $next($request);
        }

        // Certaines actions se font en AJAX : on repond en JSON pour que le
        // front puisse afficher l'erreur sans casser.
        if ($request->expectsJson()) {
            return response()->json([
                'success'  => false,
                'message'  => $this->message($bloquants, $documents, $config['action']),
                'redirect' => route('livreur.documents'),
            ], 403);
        }

        return response()->view('livreur.documents-required', [
            'documents' => $documents,
            'requis'    => $config['documents'],
            'action'    => $config['action'],
            'dossier'   => $config['dossier'],
            'retour'    => $config['retour'],
        ]);
    }

    /**
     * Message parlant : on nomme les pieces concernees plutot que de renvoyer
     * un refus generique, et on distingue « a transmettre » de « en cours ».
     *
     * Les libelles sont donnes en liste apres deux-points : coller un article
     * devant (« transmettre Carte professionnelle ») supposerait de connaitre
     * le genre de chaque piece, pour un gain nul.
     */
    private function message($bloquants, $documents, string $action): string
    {
        $aTransmettre = $bloquants
            ->filter(fn ($name) => ! $documents->has($name) || $documents->get($name)->status === 'rejected')
            ->map(fn ($name) => UserDocument::label($name));

        if ($aTransmettre->isNotEmpty()) {
            return 'Pièce' . ($aTransmettre->count() > 1 ? 's' : '')
                 . ' à transmettre avant ' . $this->elider($action) . ' : '
                 . $aTransmettre->implode(', ') . '.';
        }

        $enCours = $bloquants->map(fn ($name) => UserDocument::label($name));

        return 'Pièce' . ($enCours->count() > 1 ? 's' : '')
             . ' en cours de validation par notre équipe : '
             . $enCours->implode(', ') . '.';
    }

    /**
     * « de publier un trajet » mais « d'accepter des livraisons » : la
     * preposition s'elide devant une voyelle.
     */
    private function elider(string $action): string
    {
        return preg_match('/^[aeiouyâàéèêëîïôöûü]/iu', $action)
            ? "d'" . $action
            : 'de ' . $action;
    }
}
