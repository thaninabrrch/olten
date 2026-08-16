<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Delivery;
use App\Models\ProductSale;

class WalletController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $adEarnings = Booking::whereHas('ad', function ($q) use ($user) {
                                $q->where('user_id', $user->id);
                            })
                            ->where('status', 'paid')
                            ->sum('total_price');

        $productEarnings = ProductSale::where('user_id', $user->id)
                                      ->where('status', 'paid')
                                      ->sum('total_price');

        $deliveryEarnings = Delivery::where('delivery_person_id', $user->id)
                                    ->where('status', 'delivered')
                                    ->sum('total_price');

        return view('pages.wallet', compact('user', 'adEarnings', 'productEarnings', 'deliveryEarnings'));
    }
}