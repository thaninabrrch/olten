<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Service;
use Illuminate\Http\Request;

class ServicePageController extends Controller
{
    /**
     * Designs specifiques : un slug de service => une vue dediee.
     * Tout service absent de cette liste utilise la page standard.
     */
    private const DESIGNS = [
        'covoiturage'         => 'services.covoiturage-service',
        'covoiturage-service' => 'services.covoiturage-service',
        'location'            => 'services.location-voiture',
        'location-voiture'    => 'services.location-voiture',
    ];

    /**
     * Point d'entree unique de /{slug}.
     *
     * La separation entre les differents services se fait par leur slug :
     *   /covoiturage -> design covoiturage
     *   /location    -> design location
     *   /vente, ...  -> page service standard
     *
     * Si aucun service ne porte ce slug, on retombe sur la page categorie.
     */
    public function show(Request $request, string $slug)
    {
        $service = Service::where('slug', $slug)->first();

        if (! $service) {
            return app(HomeController::class)->show($slug);
        }

        if (isset(self::DESIGNS[$service->slug])) {
            return view(self::DESIGNS[$service->slug], compact('service'));
        }

        return $this->standard($request, $service);
    }

    /**
     * Page standard : titre du service, ses categories, la carte,
     * les filtres et la grille d'annonces.
     */
    private function standard(Request $request, Service $service)
    {
        $categories = $service->categories()->orderBy('id')->get();

        // Categorie selectionnee via ?category=slug
        $selectedCategory = $request->filled('category')
            ? $categories->firstWhere('slug', $request->input('category'))
            : null;

        $categoryIds = $selectedCategory
            ? [$selectedCategory->id]
            : $categories->pluck('id')->all();

        $query = Ad::with(['images', 'category'])
                   ->where('is_approved', true)
                   ->whereIn('category_id', $categoryIds);

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('location')) {
            $query->where('address', 'like', '%' . $request->input('location') . '%');
        }

        if ($request->filled('min_price')) {
            $query->where('price_per_day', '>=', (float) $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_day', '<=', (float) $request->input('max_price'));
        }

        match ($request->input('sort')) {
            'price_asc'  => $query->orderBy('price_per_day'),
            'price_desc' => $query->orderByDesc('price_per_day'),
            default      => $query->latest(),
        };

        $ads = $query->paginate(12)->withQueryString();

        // Annonces geolocalisees, pour la carte
        $locatedCount = Ad::where('is_approved', true)
                          ->whereIn('category_id', $categoryIds)
                          ->whereNotNull('latitude')
                          ->whereNotNull('longitude')
                          ->count();

        return view('services.service-standard', compact(
            'service',
            'categories',
            'selectedCategory',
            'ads',
            'locatedCount'
        ));
    }
}
