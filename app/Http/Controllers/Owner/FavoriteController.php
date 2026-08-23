<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\Product;

class FavoriteController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        $adFavorites = $user->favorites()
                            ->with('category')
                            ->get()
                            ->map(function ($ad) {
                                $ad->favorite_type = 'ad';
                                return $ad;
                            });

        $productFavorites = $user->productFavorites()
                                ->with('category')
                                ->get()
                                ->map(function ($product) {
                                    $product->favorite_type = 'product';
                                    return $product;
                                });

        $favorites = $adFavorites
                                ->concat($productFavorites)
                                ->sortByDesc('created_at')
                                ->values();

        return view('pages.locateur.favoris', compact('favorites'));
    }

    public function toggle(Ad $ad)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        $toggled = $user->favorites()->toggle($ad->id);

        $status = !empty($toggled['attached'])
            ? 'added'
            : 'removed';

        return response()->json([
            'status' => $status
        ]);
    }

    public function toggleProduct(Product $product)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        $toggled = $user->productFavorites()->toggle($product->id);

        $status = !empty($toggled['attached'])
            ? 'added'
            : 'removed';

        return response()->json([
            'status' => $status
        ]);
    }
}
