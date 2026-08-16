<?php

namespace App\Http\Controllers\livrer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\Booking;
use App\Models\DemandeLivreur;
use App\Models\LivraisonColis;
use App\Models\PointsFidelite;
use Illuminate\Support\Facades\Auth;
use App\Models\DeliveryRequest;
use App\Models\Delivery;

class AdsLivreurController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $requests = DeliveryRequest::with([
                                        'deliveryPerson',
                                        'booking.ad',
                                        'productSale.product'
                                    ])
                                    ->where(function ($query) use ($userId) {
                                        $query->whereHas('booking.ad', function ($q) use ($userId) {
                                            $q->where('user_id', $userId);
                                        })
                                        ->orWhereHas('productSale.product', function ($q) use ($userId) {
                                            $q->where('user_id', $userId);
                                        });
                                    })
                                    ->latest()
                                    ->get();

        return view('livreur.ads.confirme', compact('requests'));
    }

    public function acceptDemande(DeliveryRequest $demande)
    {
        $demande->update([
                            'status' => 'accepted'
                        ]);

        if ($demande->booking_id) {
            $booking = $demande->booking;
            Delivery::create([
                                'booking_id'         => $booking->id,
                                'delivery_person_id' => $demande->delivery_person_id,
                                'pickup_address'     => $booking->address,
                                'delivery_address'   => $booking->delivery_address,
                                'distance_km'        => 0,
                                'base_price'         => 0,
                                'platform_fee'       => $booking->delivery_cost ?? 0,
                                'total_price'        => $booking->delivery_cost ?? 0,
                                'status'             => 'pending',
                            ]);
        }

        if ($demande->product_sale_id) {

            $sale = $demande->productSale;

            Delivery::create([
                                'product_sale_id'    => $sale->id,
                                'delivery_person_id' => $demande->delivery_person_id,
                                'pickup_address'     => $sale->address,
                                'delivery_address'   => $sale->delivery_address,
                                'distance_km'        => 0,
                                'base_price'         => 0,
                                'platform_fee'       => $sale->delivery_cost ?? 0,
                                'total_price'        => $sale->delivery_cost ?? 0,
                                'status'             => 'pending',
                            ]);

        }
        return back()->with('success', 'Demande acceptée.');
    }

    public function refuseDemande(DeliveryRequest $request)
    {
        $request->status = 'refused';
        $request->save();

        return back()->with('success', 'Demande refusée.');
    }

    public function annulerMission(DeliveryRequest $request)
    {
        $livreurId = auth()->id();

        if ($request->delivery_person_id !== $livreurId) {
            return back()->with('error', 'Vous n’êtes pas autorisé à annuler cette mission.');
        }

        $request->status = 'cancelled';
        $request->save();

        return back()->with('success', 'Mission annulée avec succès.');
    }
}
