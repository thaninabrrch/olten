<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionLevel
{
    public function handle(Request $request, Closure $next, ...$levels): Response
    {
        $user = $request->user();

        if (!$user || !$user->subscription) {
            return redirect()
                ->route('subscriptions.index')
                ->with('error', 'Vous devez choisir un abonnement.');
        }

        if (!in_array($user->subscription->slug, $levels)) {
            return redirect()
                ->route('subscriptions.index')
                ->with('error', 'Votre abonnement ne permet pas d’accéder à cette fonctionnalité.');
        }

        return $next($request);
    }
}