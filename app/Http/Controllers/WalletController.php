<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Delivery;
use App\Models\ProductSale;
use App\Models\TripBooking;

class WalletController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $adEarnings = Booking::whereHas('ad', fn ($q) => $q->where('user_id', $user->id))
                            ->where('status', 'paid')
                            ->sum('total_price');

        $productEarnings = ProductSale::where('user_id', $user->id)
                                      ->where('status', 'paid')
                                      ->sum('total_price');

        $deliveryEarnings = Delivery::where('delivery_person_id', $user->id)
                                    ->where('status', 'delivered')
                                    ->sum('total_price');

        // Covoiturage : part du conducteur, hors commission plateforme.
        // Une réservation annulée a le statut "cancelled" : elle sort de la somme.
        $tripEarnings = TripBooking::whereHas('trip', fn ($q) => $q->where('conducteur_id', $user->id))
                                   ->where('status', 'paid')
                                   ->sum('driver_amount');

        // Détail par trajet (évite les requêtes en boucle dans la vue)
        $trips = $user->trips()->with(['bookings' => fn ($q) => $q->where('status', 'paid')])->get();

        return view('pages.wallet', compact(
            'user', 'adEarnings', 'productEarnings', 'deliveryEarnings', 'tripEarnings', 'trips'
        ));
    }
}