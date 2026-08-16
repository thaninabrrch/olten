<?php

namespace App\Http\Controllers\livrer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\DemandeLivreur;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LivraisonColis;
use App\Models\ProductSale;
use App\Models\DeliveryRequest;
use App\Models\Delivery;
use App\Models\DeliveryReview;

class DeliveryAdController extends Controller
{
    public function missions()
    {
        $livreurId = auth()->id();

        $bookings = Booking::with([
                                'ad.user',
                                'ad.category'
                            ])
                            ->where('status', 'paid')
                            ->where('booking_status', 'confirmed')
                            ->where('delivery_requested', true)
                            ->whereDoesntHave('delivery')
                            ->whereDoesntHave('deliveryRequests', function ($q) use ($livreurId) {
                                $q->where('delivery_person_id', $livreurId);
                            })
                            ->latest()
                            ->get();

        $productSales = ProductSale::with([
                                        'product',
                                        'buyer',
                                        'seller'
                                    ])
                                    ->where('status', 'paid')
                                    ->where('order_status', 'confirmed')
                                    ->where('delivery_requested', true)
                                    ->whereDoesntHave('delivery')
                                    ->whereDoesntHave('deliveryRequests', function ($q) use ($livreurId) {
                                        $q->where('delivery_person_id', $livreurId);
                                    })
                                    ->latest()
                                    ->get();

        $missions = collect()->merge($bookings)->merge($productSales) ->sortByDesc('created_at')->values();

        return view('livreur.missions.available', compact('missions'));
    }

    public function demandes()
    {
        $missions = DeliveryRequest::with([
                                                'booking.ad',
                                                'productSale.product'
                                            ])
                                            ->where('delivery_person_id', auth()->id())
                                            ->where('status', 'pending')
                                            ->latest()
                                            ->get();

        return view('livreur.missions.requests', compact('missions'));
    }

    public function livraisons()
    {
        $missions = Delivery::with([
                                    'booking.ad',
                                    'productSale.product'
                                ])
                                ->where('delivery_person_id', auth()->id())
                                ->whereNotIn('status', [
                                    'delivered',
                                    'cancelled'
                                ])
                                ->latest()
                                ->get();
        return view('livreur.missions.inProgress', compact('missions'));
    }
    
    public function sendRequest(Request $request, $ad, $type)
    {
        DeliveryRequest::create([
                                'delivery_person_id' => auth()->id(),
                                'booking_id' => $type == 'ad' ? $ad : null,
                                'product_sale_id' => $type == 'product' ? $ad : null,
                                'status' => 'pending',
                            ]);
        return back()->with('success', 'Demande envoyée avec succès');
    }
    
    public function pickupDelivery(Delivery $delivery)
    {
        if ($delivery->delivery_person_id !== auth()->id()) {
            return back()->with('error', 'Action non autorisée.');
        }

        $delivery->update([
            'status' => 'picked_up',
            'picked_up_at' => now(),
        ]);

        return back()->with('success', 'Colis récupéré.');
    }

    public function startDelivery(Delivery $delivery)
    {
        if ($delivery->delivery_person_id !== auth()->id()) {
            return back()->with('error', 'Action non autorisée.');
        }

        $delivery->update([
            'status' => 'in_transit',
        ]);

        return back()->with('success', 'Livraison en cours.');
    }

    public function finaliserMission(Delivery $delivery)
    {
        if ($delivery->delivery_person_id !== auth()->id()) {
            return back()->with('error', 'Action non autorisée.');
        }

        $delivery->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        if ($delivery->productSale) {
            $delivery->productSale->update([
                'order_status' => 'delivered',
            ]);
        }

        if ($delivery->booking) {
            $delivery->booking->update([
                'booking_status' => 'completed',
            ]);
        }

        return back()->with('success', 'Livraison terminée avec succès.');
    }

    public function historiqueTermine()
    {
        $livreurId = Auth::id();

        $livraisonsTerminees = Delivery::with([
                                                'booking.ad.user',
                                                'productSale.product.user',
                                                'reviews'
                                            ])
                                            ->where('delivery_person_id', $livreurId)
                                            ->where('status', 'delivered')
                                            ->latest()
                                            ->get();

        $totalLivres = $livraisonsTerminees->count();

        $revenusCumules = $livraisonsTerminees->sum('total_price');

        return view('livreur.ads.termine', compact('livraisonsTerminees', 'totalLivres', 'revenusCumules')
        );
    }

    public function rateDelivery(Request $request, Delivery $delivery)
    {
        DeliveryReview::updateOrCreate(
            [
                'delivery_id' => $delivery->id,
                'user_id' => auth()->id(),
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]
        );

        return response()->json([
            'success' => true
        ]);
    }
}
