<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Ad;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Message;
use App\Models\Service;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Les dates affichees sur le site sont redigees en francais
        // (« lun. 24 aout 2026 »). La locale de l'application reste `en` :
        // seule la mise en forme des dates est traduite, les messages de
        // validation ne sont pas concernes.
        Carbon::setLocale('fr');

        // Pagination unique pour toute la plateforme. La vue livree par
        // Laravel est ecrite pour Tailwind, qui n'est charge que dans
        // l'admin : ailleurs ses classes utilitaires ne s'appliquent pas et
        // ses deux variantes (mobile et bureau) s'affichent l'une sous
        // l'autre. Le style vit dans public/assets/css/pagination.css.
        Paginator::defaultView('vendor.pagination.olten');

        // Ce composer tourne sur chaque vue rendue (layout, composants,
        // partials...) : on memorise les collections pour ne faire qu'une
        // requete par modele et par requete HTTP.
        View::composer('*', function ($view) {
            static $footerCategories = null;
            static $footerServices = null;

            $footerCategories ??= Category::take(8)->get();
            $footerServices ??= Service::orderBy('id', 'asc')->get();

            $view->with('footerCategories', $footerCategories)
                 ->with('footerServices', $footerServices);
        });

        // Compteurs affiches dans le menu utilisateur du header (annonces,
        // reservations recues en attente, messages non lus). Memorises pour
        // ne faire qu'une requete par modele et par requete HTTP.
        View::composer('components.user-dropdown', function ($view) {
            static $counts = null;

            if ($counts === null) {
                $id = auth()->id();

                $counts = [
                    'menuAdsCount' => Ad::where('user_id', $id)->count(),
                    'menuReceivedCount' => Booking::where('booking_status', 'pending')
                        ->whereHas('ad', fn ($q) => $q->where('user_id', $id))
                        ->count(),
                    'menuMessagesCount' => Message::where('receiver_id', $id)
                        ->where('is_read', false)
                        ->count(),
                ];
            }

            $view->with($counts);
        });
    }
}
