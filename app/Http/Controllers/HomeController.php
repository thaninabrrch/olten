<?php

namespace App\Http\Controllers;

use App\Models\Category;
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
        $query = Ad::with([
            'images',
            'category',
            'user.subscription'
        ])->where('is_approved', true);

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

        // Une annonce correspond si sa catégorie appartient au service sélectionné.
        if ($request->filled('service')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('service_id', $request->input('service'));
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
            ->active()
            ->inStock()
            ->latest()
            ->get()
            ->sortByDesc($visibilityScore)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Derniers éléments
        |--------------------------------------------------------------------------
        | Ici on garde volontairement le classement chronologique.
        | L'abonnement ne modifie pas la sélection des 10 derniers éléments.
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
            ->take(10)
            ->values();

        return view('home', compact(
            'categories',
            'services',
            'ads',
            'products',
            'latestItems'
        ));
    }

    public function show($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $ads = Ad::where('category_id', $category->id)
                 ->where('is_approved', true)
                 ->latest()
                 ->paginate(12);

        $products = Product::where('category_id', $category->id)
                           ->latest()
                           ->paginate(12);
        return view('categories.show', compact('category', 'ads', 'products'));
    }

    public function index(Request $request)
    {
        $categories = Category::latest()->get();
        $query = Ad::query()->where('is_approved', true);
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
        $products = Product::active()->inStock()->latest()->get();

        return view('homeLocation', compact('categories', 'ads', 'products'));
    }
}
