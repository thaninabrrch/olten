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
use App\Notifications\NewDeliveryRequestNotification;

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
        if (!in_array($type, ['ad', 'product'])) {
            return back()->with('error', 'Type de demande invalide.');
        }

        if ($type === 'ad') {

            $booking = Booking::with('ad.user.subscription')->findOrFail($ad);

            $deliveryRequest = DeliveryRequest::create([
                                    'delivery_person_id' => auth()->id(),
                                    'booking_id' => $booking->id,
                                    'product_sale_id' => null,
                                    'status' => 'pending',
                                ]);

            $owner = $booking->ad->user;

        } else {

            $sale = ProductSale::with('seller.subscription')->findOrFail($ad);

            $deliveryRequest = DeliveryRequest::create([
                                    'delivery_person_id' => auth()->id(),
                                    'booking_id' => null,
                                    'product_sale_id' => $sale->id,
                                    'status' => 'pending',
                                ]);

            $owner = $sale->seller;
        }

        if ($owner && $owner->hasPremiumSubscription()) {

            $deliveryRequest->load([
                'booking.ad',
                'productSale.product',
            ]);

            $owner->notify(new NewDeliveryRequestNotification($deliveryRequest));
        }

        return back()->with(
                            'success',
                            'Demande envoyée avec succès'
                        );
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
        // Seul le client servi (locataire ou acheteur) note la livraison, et
        // une fois celle-ci terminee : sans ce controle, n'importe quel membre
        // pouvait noter n'importe quel livreur.
        $client = $delivery->booking?->user_id ?? $delivery->productSale?->buyer_id;

        if ($client !== auth()->id() || $delivery->status !== 'delivered') {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas noter cette livraison.',
            ], 403);
        }

        $validated = $request->validate([
            'rating'  => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ], [
            'rating.required' => 'Choisissez une note de 1 à 5 étoiles.',
            'rating.*'        => 'La note doit être comprise entre 1 et 5.',
            'comment.max'     => 'Le commentaire ne peut pas dépasser :max caractères.',
        ]);

        DeliveryReview::updateOrCreate(
            [
                'delivery_id' => $delivery->id,
                'user_id' => auth()->id(),
            ],
            [
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]
        );

        return response()->json([
            'success' => true
        ]);
    }
}
