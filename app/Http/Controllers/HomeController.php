<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Covoiturage;
use App\Models\Service;
use App\Models\Ad;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function home(Request $request)
    {
        $categories = Category::orderBy('id', 'asc')->get();

        // Les "piliers de services" affichés sur l'accueil viennent du modèle Service
        $services = Service::orderBy('id', 'asc')->get();

        /*
        |--------------------------------------------------------------------------
        | Score de visibilité selon l'abonnement
        |--------------------------------------------------------------------------
        | Premium  = 2 → meilleure visibilité
        | Standard = 1 → visibilité améliorée
        | Aucun    = 0 → visibilité normale
        |
        | Important : aucun élément n'est filtré selon l'abonnement.
        |--------------------------------------------------------------------------
        */
        $visibilityScore = function ($item) {
            return match ($item->user?->subscription?->slug) {
                'premium' => 2,
                'standard' => 1,
                default => 0,
            };
        };

        /*
        |--------------------------------------------------------------------------
        | Annonces
        |--------------------------------------------------------------------------
        */
        // `category.service` : le libelle de prix depend du service (une
        // vente n'affiche pas « / jour »), autant le charger en une requete.
        $query = Ad::with([
            'images',
            'category.service',
            'user.subscription'
        ])->published();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhereHas('category', function ($q2) use ($request) {
                        $q2->where('nom', 'like', '%' . $request->search . '%');
                    });
            });
        }

        if ($request->filled('location')) {
            $query->where(
                'address',
                'like',
                '%' . $request->location . '%'
            );
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Une annonce correspond si sa catégorie appartient au service
        // sélectionné. Le service est désigné par son slug depuis que la
        // barre du header écrit des URLs lisibles ; les anciens liens qui
        // portent encore un identifiant continuent de fonctionner.
        if ($request->filled('service')) {
            $service = $request->input('service');

            $serviceId = Service::where('slug', $service)->value('id') ?? $service;

            $query->whereHas('category', function ($q) use ($serviceId) {
                $q->where('service_id', $serviceId);
            });
        }

        $ads = $query
            ->latest()
            ->get()
            ->sortByDesc($visibilityScore)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Produits
        |--------------------------------------------------------------------------
        */
        $products = Product::with([
            'images',
            'category',
            'user.subscription'
        ])
            ->available()
            ->latest()
            ->get()
            ->sortByDesc($visibilityScore)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Derniers éléments
        |--------------------------------------------------------------------------
        | Ici on garde volontairement le classement chronologique.
        | L'abonnement ne modifie pas la sélection des derniers éléments.
        | Quatre suffisent à remplir une ligne de la grille : le reste est à
        | un clic, derrière le bouton « Voir tout » (page de recherche).
        |--------------------------------------------------------------------------
        */
        $latestItems = $ads
            ->map(function ($ad) {
                return (object) [
                    'type' => 'ad',
                    'item' => $ad,
                    'created_at' => $ad->created_at,
                ];
            })
            ->concat(
                $products->map(function ($product) {
                    return (object) [
                        'type' => 'product',
                        'item' => $product,
                        'created_at' => $product->created_at,
                    ];
                })
            )
            ->sortByDesc('created_at')
            ->take(4)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Trajets du jour
        |--------------------------------------------------------------------------
        | Les trajets qui partent aujourd'hui et ont encore une place, dans
        | l'ordre des départs. Même règle que la page covoiturage : un trajet
        | désactivé n'est pas proposé, et il reste en ligne jusqu'au soir.
        |--------------------------------------------------------------------------
        */
        $todayTrips = Covoiturage::query()
            ->where('statut', '!=', 'inactif')
            ->whereDate('date_depart', today()->toDateString())
            ->with(['conducteur.vehicle', 'paidBookings'])
            ->orderBy('heure_depart')
            ->get()
            ->filter(fn (Covoiturage $trip) => $trip->seats_left > 0)
            ->values();

        $todayTripsTotal = $todayTrips->count();
        $todayTrips      = $todayTrips->take(4);

        return view('home', compact(
            'categories',
            'services',
            'ads',
            'products',
            'latestItems',
            'todayTrips',
            'todayTripsTotal'
        ));
    }

    public function show($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $ads = Ad::where('category_id', $category->id)
                 ->published()
                 ->latest()
                 ->paginate(12);

        $products = Product::where('category_id', $category->id)
                           ->available()
                           ->latest()
                           ->paginate(12);
        return view('categories.show', compact('category', 'ads', 'products'));
    }

    public function index(Request $request)
    {
        $categories = Category::latest()->get();
        $query = Ad::query()->published();
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhereHas('category', function ($q2) use ($request) {
                      $q2->where('nom', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('location')) {
            $query->where('address', 'like', '%' . $request->location . '%');
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $ads = $query->latest()->get();
        $products = Product::available()->latest()->get();

        return view('homeLocation', compact('categories', 'ads', 'products'));
    }
}
