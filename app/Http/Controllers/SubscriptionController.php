<?php

namespace App\Http\Controllers;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::where('slug', '!=', 'free')
                                     ->orderBy('price')
                                     ->get();
        return view('subscriptions.index', compact('subscriptions'));
    }

    public function select($slug)
    {
        $user = Auth::user();

        $subscription = Subscription::where('slug', $slug)->firstOrFail();

        if ($subscription->slug === 'free') {

            $user->update([
                'subscription_id' => $subscription->id,
            ]);

            return redirect()->route('dashboard')->with('success', 'Votre compte gratuit a été activé.');
        }

        return redirect()->route('subscriptions.payment', $subscription->slug);
    }

    public function payment(Subscription $subscription)
    {
        $user = Auth::user();

        if ($subscription->slug === 'free') {
            abort(404);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'mode' => 'subscription',

            'customer_email' => $user->email,

            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'eur',

                        'product_data' => [
                            'name' => 'Abonnement Olten - ' . $subscription->name,
                        ],

                        'unit_amount' => (int) round($subscription->price * 100),

                        'recurring' => [
                            'interval' => 'month',
                        ],
                    ],

                    'quantity' => 1,
                ],
            ],

            'success_url' => route('subscriptions.success')
                . '?session_id={CHECKOUT_SESSION_ID}',

            'cancel_url' => route('subscriptions.cancel'),

            'metadata' => [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
            ],
        ]);

        return redirect()->away($session->url);
    }

    public function success(Request $request)
    {
        $user = Auth::user();

        if (!$request->session_id) {
            return redirect()->route('subscriptions.index')->with('error', 'Session de paiement invalide.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {

            $session = Session::retrieve($request->session_id);

            if ($session->payment_status !== 'paid') {
                return redirect()->route('subscriptions.index')->with('error', 'Le paiement n’a pas été confirmé.');
            }

            $subscriptionId = $session->metadata->subscription_id ?? null;

            if (!$subscriptionId) {
                return redirect()->route('subscriptions.index')->with('error', 'Abonnement introuvable.');
            }

            $subscription = Subscription::find($subscriptionId);

            if (!$subscription) {
                return redirect()->route('subscriptions.index')->with('error', 'Abonnement introuvable.');
            }
            $user->update([
                'subscription_id' => $subscription->id,
                'subscription_expired_at' => now()->addMonth(),
            ]);
            return view('subscriptions.success', ['subscription' => $subscription,]);

        } catch (\Exception $e) {

            return redirect()->route('subscriptions.index')->with('error', 'Une erreur est survenue lors de la validation du paiement.');
        }
    }
}