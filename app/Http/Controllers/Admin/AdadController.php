<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;

class AdadController extends Controller
{
    public function index(Request $request)
    {
        $query = Ad::with(['user', 'category'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            if ($request->status === 'approved') {
                $query->where('is_approved', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_approved', false);
            }
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        $ads = $query->paginate(10)->withQueryString();

        $categories = \App\Models\Category::orderBy('nom')->get();
        $users = \App\Models\User::orderBy('name')->get();

        return view('admin.ads.index', compact('ads', 'users', 'categories'));
    }
    public function approve(Ad $ad)
    {
        $ad->update([
            'is_approved' => true,
            'rejected_at' => null,   // annule un éventuel refus précédent
        ]);

        return redirect()->back()->with('success', 'Annonce approuvée avec succès.');
    }

    public function reject(Ad $ad)
    {
        $ad->update([
            'is_approved'  => false,
            'rejected_at'  => now(),
        ]);

        return redirect()->back()->with('success', 'Annonce refusée.');
    }
}
