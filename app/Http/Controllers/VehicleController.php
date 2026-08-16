<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function edit()
    {
        $vehicle = auth()->user()->vehicle;
        return view('vehicle.edit', compact('vehicle'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'marque' => 'required',
            'modele' => 'required',
            'immatriculation' => 'required',
            'annee' => 'nullable|integer',
            'couleur' => 'nullable',
            'places' => 'required|integer|min:1',
            'type' => 'required|in:thermique,hybride,electrique',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('vehicles', 'public');
        }

        auth()->user()->vehicle()->updateOrCreate(
            ['user_id' => auth()->id()],
            $data
        );

        return back()->with('success', 'Véhicule mis à jour');
    }
}
