<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Covoiturage;
use App\Models\User;

class CovoiturageAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Covoiturage::with('conducteur');

        if ($request->user_id) {
            $query->where('conducteur_id', $request->user_id);
        }

        if ($request->status) {
            $query->where('statut', $request->status);
        }

        $rides = $query->orderBy('date_depart', 'desc')->paginate(15);
        $users = User::orderBy('id')->get();

        return view('admin.rides.index', compact('rides', 'users'));
    }

    public function toggleStatus(Covoiturage $ride)
    {
        if ($ride->statut === 'pending') {
            $ride->statut = 'actif';
        } else {
            $ride->statut = $ride->statut === 'actif' ? 'inactif' : 'actif';
        }

        $ride->save();

        return redirect()->back()->with('success', 'Statut mis à jour avec succès.');
    }

}
