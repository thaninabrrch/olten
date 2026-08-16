<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if ($user && is_null($user->subscription_id) && (is_null($user->subscription_expired_at) || now()->greaterThanOrEqualTo($user->subscription_expired_at)))
        {
            if (!$request->routeIs('subscriptions.*')) {
                return redirect()->route('subscriptions.index');
            }
        }

        return $next($request);
    }
}