<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SubscriptionsController extends Controller
{
    /**
     * Afficher la liste des abonnements
     */
    public function index()
    {
        $subscriptions = Subscription::withCount('users')
            ->orderBy('price', 'asc')
            ->get();

        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    /**
     * Rechercher des utilisateurs pour l'autocomplete
     *
     * Recherche par nom, prénom ou email.
     */
    public function searchUsers(Request $request)
    {
        $search = trim($request->input('q', ''));

        if ($search === '') {
            return response()->json([]);
        }

        $users = User::query()
                    ->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->limit(10)
                    ->get([
                        'id',
                        'name',
                        'email',
                    ]);
        return response()->json($users);
    }

    /**
     * Afficher le formulaire pour offrir un abonnement
     */
    public function create()
    {
        $subscriptions = Subscription::orderBy('price', 'asc')->get();

        return view('admin.subscriptions.create', compact('subscriptions'));
    }

    /**
     * Offrir un abonnement à un utilisateur
     *
     * L'abonnement est activé immédiatement sans passer
     * par Stripe ou un paiement en ligne.
     */
    public function gift(Request $request)
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'subscription_id' => [
                'required',
                'integer',
                'exists:subscriptions,id',
            ],

            'duration' => [
                'required',
                'integer',
                'in:1,3,6,12',
            ],
        ], [
            'user_id.required' => 'Veuillez sélectionner un utilisateur.',
            'user_id.exists' => 'L’utilisateur sélectionné n’existe pas.',

            'subscription_id.required' => 'Veuillez sélectionner un abonnement.',
            'subscription_id.exists' => 'L’abonnement sélectionné n’existe pas.',

            'duration.required' => 'Veuillez sélectionner une durée.',
            'duration.in' => 'La durée sélectionnée est invalide.',
        ]);

        $user = User::findOrFail($validated['user_id']);

        $subscription = Subscription::findOrFail(
            $validated['subscription_id']
        );

        // Conversion explicite en entier
        $duration = (int) $validated['duration'];

        $now = Carbon::now();

        /*
        * Si l'utilisateur possède déjà un abonnement actif,
        * on prolonge sa date d'expiration.
        */
        if (
            $user->subscription_id &&
            $user->subscription_expired_at &&
            Carbon::parse($user->subscription_expired_at)->isFuture()
        ) {
            $expiration = Carbon::parse(
                $user->subscription_expired_at
            )->addMonths($duration);
        } else {
            /*
            * Sinon, l'abonnement commence immédiatement.
            */
            $expiration = $now->copy()->addMonths($duration);
        }

        /*
        * Activation immédiate de l'abonnement offert.
        */
        $user->subscription_id = $subscription->id;
        $user->subscription_expired_at = $expiration;

        $user->save();

        return redirect()
            ->route('admin.subscriptions.index')
            ->with(
                'success',
                'L’abonnement ' . $subscription->name .
                ' a été offert à ' . $user->name .
                ' pour ' . $duration . ' mois.'
            );
    }
}