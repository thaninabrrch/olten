<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Support\Facades\View;

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
    }
}
