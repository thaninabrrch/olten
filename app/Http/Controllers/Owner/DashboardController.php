<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\LivraisonRepas;
use App\Models\LivraisonColis;
use App\Models\LivraisonVtc;
use App\Models\Covoiturage;
use App\Models\User;
use App\Models\Transaction;
use App\Models\ProductSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Delivery;
use App\Models\Ad;
use App\Models\Booking;
use App\Models\PointsFidelite;
use App\Models\AdVisit;
use App\Models\ProductVisit;
use App\Models\Product;
use App\Models\UserDocument;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $startWeek = Carbon::now()->startOfWeek();
        $endWeek = Carbon::now()->endOfWeek();

        $revenusTotal = 0;
        $totalCourses = 0;
        $heuresTotales = 0;
        $noteClient = 4.5;
        $derniereMission = null;
        $graphData = [];
        $activeAds = 0;
        $totalViews = 0;
        $favoritesCount = 0;
        $recentActivities = [];
        $ventesTotal = 0;
        $totalCommandes = 0;

        /*
        |--------------------------------------------------------------------------
        | LIVREUR
        |--------------------------------------------------------------------------
        */
        if ($user->hasRole('livreur')) {

            $revenusTotal = Transaction::where('user_id', $user->id)
                ->where('statut', 'validee')
                ->sum('montant');

            $deliveries = Delivery::where('delivery_person_id', $user->id)->get();

            $totalCourses = $deliveries->count();
            $heuresTotales = $totalCourses * 0.5;

            $noteClient = PointsFidelite::where('user_id', $user->id)
                ->avg('points_gagnes') ?? 4.5;

            $derniereMission = Delivery::where('delivery_person_id', $user->id)
                ->latest()
                ->first();

            $missionsSemaine = Delivery::where('delivery_person_id', $user->id)
                ->whereBetween('created_at', [$startWeek, $endWeek])
                ->get();

            for ($i = 0; $i < 7; $i++) {
                $day = $startWeek->copy()->addDays($i)->format('Y-m-d');

                $graphData[$day] = $missionsSemaine
                    ->filter(fn($m) => Carbon::parse($m->created_at)->format('Y-m-d') === $day)
                    ->sum('total_price');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | LOCATEUR
        |--------------------------------------------------------------------------
        */
        if ($user->hasRole('locateur')) {

            $activeAds = Ad::where('user_id', $user->id)->count();
            $totalViews += Ad::where('user_id', $user->id)->sum('views');

            $favoritesCount = $user->favorites()->count() + $user->productFavorites()->count();
            $recentActivities = [
                ['description' => 'Nouvelle réservation reçue', 'time' => 'Il y a 2h'],
                ['description' => 'Votre annonce a été vue', 'time' => 'Hier'],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | VENDEUR
        |--------------------------------------------------------------------------
        */
        if ($user->hasRole('vendeur')) {

            $totalViews += Product::where('user_id', $user->id)->sum('views');

            $ventesTotal = ProductSale::whereHas('product', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->sum('total_price');

            $totalCommandes = ProductSale::whereHas('product', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->count();
        }

        /*
        |--------------------------------------------------------------------------
        | SERIES DES GRAPHIQUES
        |--------------------------------------------------------------------------
        | Trois sources de revenus, quel que soit le role : locations payees sur
        | ses annonces, ventes payees sur ses produits, livraisons effectuees.
        | Les montants sont regroupes en PHP (et non en SQL) pour rester
        | independant du moteur de base de donnees.
        */
        $debutSerie = Carbon::now()->startOfMonth()->subMonths(5);

        $locationsPayees = Booking::whereHas('ad', fn ($q) => $q->where('user_id', $user->id))
            ->where('status', 'paid')
            ->where('created_at', '>=', $debutSerie)
            ->get(['total_price', 'created_at']);

        $ventesPayees = ProductSale::where('user_id', $user->id)
            ->where('status', 'paid')
            ->where('created_at', '>=', $debutSerie)
            ->get(['total_price', 'created_at']);

        $livraisonsFaites = Delivery::where('delivery_person_id', $user->id)
            ->where('status', 'delivered')
            ->where('created_at', '>=', $debutSerie)
            ->get(['total_price', 'created_at']);

        // Douze mois glissants ne tiendraient pas lisiblement : on en garde six.
        $moisFr = [1 => 'janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin',
                   'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];

        $moisCles = [];
        $labelsMois = [];

        for ($i = 5; $i >= 0; $i--) {
            $mois = Carbon::now()->startOfMonth()->subMonths($i);
            $moisCles[] = $mois->format('Y-m');
            $labelsMois[] = $moisFr[(int) $mois->format('n')] . ' ' . $mois->format('y');
        }

        $parMois = function ($lignes) use ($moisCles) {
            $totaux = $lignes->groupBy(fn ($l) => Carbon::parse($l->created_at)->format('Y-m'))
                             ->map(fn ($g) => round((float) $g->sum('total_price'), 2));

            return array_map(fn ($cle) => $totaux[$cle] ?? 0, $moisCles);
        };

        $revenusMensuels = [
            'labels'     => $labelsMois,
            'locations'  => $parMois($locationsPayees),
            'ventes'     => $parMois($ventesPayees),
            'livraisons' => $parMois($livraisonsFaites),
        ];

        // Repartition sur la meme periode : elle explique la courbe ci-dessus.
        $repartition = [
            'Locations'  => round((float) $locationsPayees->sum('total_price'), 2),
            'Ventes'     => round((float) $ventesPayees->sum('total_price'), 2),
            'Livraisons' => round((float) $livraisonsFaites->sum('total_price'), 2),
        ];

        // Sept derniers jours, toutes sources confondues
        $septJours = [];

        for ($i = 6; $i >= 0; $i--) {
            $septJours[Carbon::now()->subDays($i)->format('Y-m-d')] = 0.0;
        }

        foreach ([$locationsPayees, $ventesPayees, $livraisonsFaites] as $lignes) {
            foreach ($lignes as $ligne) {
                $jour = Carbon::parse($ligne->created_at)->format('Y-m-d');

                if (array_key_exists($jour, $septJours)) {
                    $septJours[$jour] += (float) $ligne->total_price;
                }
            }
        }

        $septJours = array_map(fn ($v) => round($v, 2), $septJours);

        // Le graphe hebdomadaire n'etait alimente que pour les livreurs :
        // il couvre desormais les trois sources.
        if (! $user->hasRole('livreur')) {
            $graphData = $septJours;
        }

        /*
        |--------------------------------------------------------------------------
        | STATISTIQUES DETAILLEES
        |--------------------------------------------------------------------------
        | Toutes les series ci-dessous sont lues en base, sans valeur de
        | demonstration. Un graphique vide veut donc dire « aucune donnee de ce
        | type pour l'instant » : c'est une information juste, la vue affiche un
        | message plutot qu'une courbe inventee.
        */
        $adIds     = Ad::where('user_id', $user->id)->pluck('id');
        $produitIds = Product::where('user_id', $user->id)->pluck('id');

        $visitesAnnonces = $adIds->isEmpty()
            ? collect()
            : AdVisit::whereIn('ad_id', $adIds)->get(['user_id', 'created_at']);

        $visitesProduits = $produitIds->isEmpty()
            ? collect()
            : ProductVisit::whereIn('product_id', $produitIds)->get(['user_id', 'created_at']);

        // Annonces et produits alimentent la meme courbe : c'est le trafic de
        // l'utilisateur, quel que soit le type d'offre consultee.
        $visites = $visitesAnnonces->concat($visitesProduits);

        // -- Trafic : une barre par jour sur trente jours glissants --
        $parJour = [];

        for ($i = 29; $i >= 0; $i--) {
            $parJour[Carbon::today()->subDays($i)->format('Y-m-d')] = 0;
        }

        foreach ($visites as $visite) {
            $jour = Carbon::parse($visite->created_at)->format('Y-m-d');

            if (array_key_exists($jour, $parJour)) {
                $parJour[$jour]++;
            }
        }

        $trafic = [
            'labels'  => array_map(fn ($j) => Carbon::parse($j)->format('d/m'), array_keys($parJour)),
            'valeurs' => array_values($parJour),
            'total'   => array_sum($parJour),
        ];

        // -- Affluence par jour de la semaine, sur tout l'historique --
        $affluence = array_fill(0, 7, 0);

        foreach ($visites as $visite) {
            $affluence[Carbon::parse($visite->created_at)->dayOfWeekIso - 1]++;
        }

        // Les zeros sont retires : une part nulle n'a pas de place dans un anneau.
        $catalogue = array_filter([
            'Annonces' => Ad::where('user_id', $user->id)->count(),
            'Produits' => $produitIds->count(),
            'Trajets'  => Covoiturage::where('conducteur_id', $user->id)->count(),
        ]);

        // -- Recette potentielle des trajets publies : prix de la place x places --
        $premiereVille = function ($adresse) {
            $ville = trim(explode(',', (string) $adresse)[0] ?? '');

            return $ville !== '' ? $ville : '—';
        };

        $trajets = Covoiturage::where('conducteur_id', $user->id)
            ->orderByDesc('date_depart')
            ->limit(6)
            ->get(['depart', 'destination', 'prix_place', 'nb_places']);

        $recetteTrajets = [
            'labels'  => $trajets->map(fn ($t) => $premiereVille($t->depart) . ' → ' . $premiereVille($t->destination))->all(),
            'valeurs' => $trajets->map(fn ($t) => round((float) $t->prix_place * max((int) $t->nb_places, 1), 2))->all(),
        ];

        // -- Dossier de verification : un document, un statut --
        $statutsDocs = UserDocument::where('user_id', $user->id)->pluck('status')->countBy();

        $verification = array_filter([
            'Validé'     => (int) ($statutsDocs['approved'] ?? 0),
            'En attente' => (int) ($statutsDocs['pending'] ?? 0),
            'Refusé'     => (int) ($statutsDocs['rejected'] ?? 0),
        ]);

        // -- Entonnoir : ce que devient une visite --
        $reservations = $adIds->isEmpty()
            ? collect()
            : Booking::whereIn('ad_id', $adIds)->pluck('status');

        $misesEnFavori = DB::table('favorites')
            ->when($adIds->isNotEmpty(), fn ($q) => $q->orWhereIn('ad_id', $adIds))
            ->when($produitIds->isNotEmpty(), fn ($q) => $q->orWhereIn('product_id', $produitIds))
            ->count();

        $entonnoir = [
            'Vues'         => (int) Ad::where('user_id', $user->id)->sum('views')
                              + (int) Product::where('user_id', $user->id)->sum('views'),
            'Favoris'      => ($adIds->isEmpty() && $produitIds->isEmpty()) ? 0 : $misesEnFavori,
            'Réservations' => $reservations->count(),
            'Payées'       => $reservations->filter(fn ($s) => $s === 'paid')->count(),
        ];

        $revenusPeriode = array_sum($repartition);

        return view('pages.locateur.dashboard', compact(
            'user',
            'revenusTotal',
            'totalCourses',
            'heuresTotales',
            'noteClient',
            'derniereMission',
            'graphData',
            'activeAds',
            'totalViews',
            'favoritesCount',
            'recentActivities',
            'ventesTotal',
            'totalCommandes',
            'revenusMensuels',
            'repartition',
            'septJours',
            'revenusPeriode',
            'trafic',
            'affluence',
            'catalogue',
            'recetteTrajets',
            'verification',
            'entonnoir'
        ));
    }
}
