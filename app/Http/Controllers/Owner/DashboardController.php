<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\LivraisonRepas;
use App\Models\LivraisonColis;
use App\Models\LivraisonVtc;
use App\Models\Covoiturage;
use App\Models\User;
use App\Models\PointsFidelite;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{ 
    public function index(Request $request)
    {
        $user = Auth::user();
        $startWeek = Carbon::now()->startOfWeek();
        $endWeek = Carbon::now()->endOfWeek();

        $revenusTotal = 0; $totalCourses = 0; $heuresTotales = 0;
        $noteClient = 4.5; $derniereMission = null; $graphData = [];
        $activeAds = 0; $totalViews = 0; $favoritesCount = 0; $recentActivities = [];

        if($user->hasRole('livreur')) {
            $revenusTotal = Transaction::where('user_id', $user->id)
                ->where('statut', 'validee')
                ->sum('montant');

            $nbLivraisonsRepas = LivraisonRepas::where('livreur_id', $user->id)->count();
            $nbLivraisonsColis = LivraisonColis::where('livreur_id', $user->id)->count();
            $nbVTC = LivraisonVtc::where('chauffeur_id', $user->id)->count();
            $nbCovoiturages = Covoiturage::where('conducteur_id', $user->id)->count();
            $totalCourses = $nbLivraisonsRepas + $nbLivraisonsColis + $nbVTC + $nbCovoiturages;


            $heuresTotales = ($nbLivraisonsRepas + $nbLivraisonsColis + $nbVTC) * 0.5; 

            $avis = PointsFidelite::where('user_id', $user->id)
                ->whereIn('action_source', ['livraison_repas','livraison_colis','vtc','covoiturage'])
                ->avg('points_gagnes');
            $noteClient = $avis ? round($avis, 2) : 4.5;

            $derniereMission = LivraisonRepas::where('livreur_id', $user->id)
                ->orderBy('date_creation', 'desc')
                ->first();

         
            $missionsSemaine = collect()
                ->merge(LivraisonRepas::where('livreur_id', $user->id)->whereBetween('date_creation', [$startWeek, $endWeek])->get())
                ->merge(LivraisonColis::where('livreur_id', $user->id)->whereBetween('date_creation', [$startWeek, $endWeek])->get())
                ->merge(LivraisonVtc::where('chauffeur_id', $user->id)->whereBetween('date_creation', [$startWeek, $endWeek])->get());

            for ($i = 0; $i < 7; $i++) {
                $day = $startWeek->copy()->addDays($i)->format('Y-m-d');
                $graphData[$day] = $missionsSemaine
                    ->filter(fn($m) => Carbon::parse($m->date_creation ?? $m->date_depart)->format('Y-m-d') === $day)
                    ->sum(fn($m) => $m->prix_total_affiche ?? $m->prix_base ?? 0);
            }
        }

        if($user->hasRole('locateur')) {
            $activeAds = 3; 
            $totalViews = 1240;
            $favoritesCount = 45;
            $recentActivities = [
                ['description' => 'Nouvelle réservation reçue', 'time' => 'Il y a 2h'],
                ['description' => 'Votre annonce "VTT Pro" a été vue 50 fois', 'time' => 'Hier'],
            ];
        }
        return view('pages.locateur.dashboard', compact(
            'user', 'revenusTotal', 'totalCourses', 'heuresTotales', 
            'noteClient', 'derniereMission', 'graphData',
            'activeAds', 'totalViews', 'favoritesCount', 'recentActivities'
        ));
    }
}