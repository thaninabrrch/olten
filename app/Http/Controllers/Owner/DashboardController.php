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

            $favoritesCount = $user->favorites()->count();

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
            'totalCommandes'
        ));
    }
}