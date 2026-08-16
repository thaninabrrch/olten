<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsApproved
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if ($user && !$user->hasVerifiedEmail()) {
            return redirect()->route('account.verify');
        }

        if ($user && !$user->is_approved) {
            return redirect()->route('account.pending');
        }
        return $next($request);
    }
}
