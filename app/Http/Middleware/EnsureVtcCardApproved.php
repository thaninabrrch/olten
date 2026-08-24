<?php

namespace App\Http\Middleware;

use App\Models\UserDocument;
use Closure;
use Illuminate\Http\Request;

/**
 * Publier un trajet suppose une carte professionnelle VTC validee par
 * l'administrateur (back-office /admin/vtc-cards).
 *
 * Tant que le document n'est pas au statut « approved », l'acces au
 * formulaire est remplace par une page d'attente, et l'endpoint de
 * publication est refuse : le blocage ne doit pas etre seulement visuel.
 */
class EnsureVtcCardApproved
{
    public const DOCUMENT_NAME = 'vtc_card';

    public function handle(Request $request, Closure $next)
    {
        $document = UserDocument::where('user_id', auth()->id())
            ->where('name', self::DOCUMENT_NAME)
            ->first();

        if ($document && $document->status === 'approved') {
            return $next($request);
        }

        // La publication d'un trajet se fait en AJAX : on repond en JSON
        // pour que le front puisse afficher l'erreur sans casser.
        if ($request->expectsJson()) {
            return response()->json([
                'success'  => false,
                'message'  => $document
                    ? 'Votre carte professionnelle VTC est en cours de validation par notre équipe.'
                    : 'Vous devez transmettre votre carte professionnelle VTC avant de publier un trajet.',
                'redirect' => route('covoiturage.create'),
            ], 403);
        }

        return response()->view('livreur.covoiturage.vtc-required', [
            'document' => $document,
        ]);
    }
}
