<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ad;

class FavoriteController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        $favorites = $user->favorites()->with('category')->get();

        return view('pages.locateur.favoris', compact('favorites'));
    }
    public function toggle(Ad $ad)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $toggled = $user->favorites()->toggle($ad->id);
        $status = !empty($toggled['attached']) ? 'added' : 'removed';

        return response()->json(['status' => $status]);
    }
}
