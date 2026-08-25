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
            $totalViews = Ad::where('user_id', $user->id)->sum('views');

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
            'revenusPeriode'
        ));
    }
}