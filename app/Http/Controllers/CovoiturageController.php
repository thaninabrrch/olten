<?php

namespace App\Http\Controllers;

use App\Models\Covoiturage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CovoiturageController extends Controller
{
    public function index()
    {
        $trajets = Covoiturage::where('conducteur_id', auth()->id())
            ->orderBy('date_depart', 'desc')
            ->get();

        return view('livreur.covoiturage.index', compact('trajets'));
    }
    public function show($covoiturage_id)
    {
        $trajet = Covoiturage::findOrFail($covoiturage_id);

        $segments = $trajet->segments ?? [];
        $itineraire = $trajet->itineraire ?? [];
        $selectedRoute = $trajet->selected_route ?? [];
        $returnTripData = $trajet->return_trip_data ?? null;

        $prixTotal = collect($segments)
            ->sum(fn ($segment) => $segment['price'] ?? 0);

        return view('livreur.covoiturage.show', [
            'trajet' => $trajet,
            'segments' => $segments,
            'route' => $selectedRoute,
            'prixTotal' => $prixTotal,
            'itineraire' => $itineraire,
            'selectedRoute' => $selectedRoute,
            'returnTripData' => $returnTripData,
        ]);
    }

    public function create()
    {
        return view('livreur.covoiturage.create');
    }

    public function publish(Request $request)
    {
        $input = $request->all();

        $input['itineraire'] = json_decode($request->input('itineraire'), true) ?? [];
        $input['segments'] = json_decode($request->input('segments'), true) ?? [];
        $input['selected_route'] = json_decode($request->input('selected_route'), true) ?? [];
        $input['selected_route_index'] = (int) $request->input('selected_route_index', 0);
        $input['return_trip_data'] = json_decode($request->input('return_trip_data'), true);
        $input['return_datetime'] = json_decode($request->input('return_datetime'), true);

        $data = Validator::make($input, [
            'depart' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'date_depart' => 'required|date',
            'heure_depart' => 'required|string|max:5',
            'nb_places' => 'required|integer|min:1',
            'itineraire' => 'required|array|min:2',
            'segments' => 'required|array|min:1',
            'message_conducteur' => 'nullable|string|max:2000',
            'photo_conducteur' => 'nullable|image|max:2048',
            'passenger_mode' => 'required|string|in:mixed,womenOnly,maxBackSeats',
            'selected_route' => 'nullable|array',
            'selected_route_index' => 'nullable|integer|min:0',
            'return_trip_data' => 'nullable|array',
            'return_datetime' => 'nullable|array',
        ])->validate();

        // 🔹 Calcul prix aller
        $prixTotal = collect($input['segments'])
            ->sum(fn ($segment) => (float)($segment['price'] ?? 0));

        $data['prix_place'] = $prixTotal;
        $data['prix_total_affiche'] = $prixTotal;

        // 🔹 Gestion retour sécurisée
        $returnTrip = $input['return_trip_data'] ?? null;
        $returnDate = $input['return_datetime']['date'] ?? null;
        $returnTime = $input['return_datetime']['time'] ?? null;

        $hasReturn =
            !empty($returnTrip) &&
            !empty($returnDate) &&
            !empty($returnTime);

        $data['retour'] = $hasReturn;
        $data['return_trip_data'] = $hasReturn ? $returnTrip : null;
        $data['return_date'] = $hasReturn ? $returnDate : null;
        $data['return_time'] = $hasReturn ? $returnTime : null;

        if ($request->hasFile('photo_conducteur')) {
            $file = $request->file('photo_conducteur');
            $filename = uniqid('driver_') . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('drivers', $filename, 'public');
            $data['photo_conducteur'] = $path;
        }

        $data['conducteur_id'] = Auth::id();
        $data['statut'] = 'pending';

        $covoiturage = Covoiturage::create($data);

        return response()->json([
            'success' => true,
            'covoiturage_id' => $covoiturage->covoiturage_id
        ]);
    }
    public function destroy($id)
    {
        $covoiturage = Covoiturage::where('covoiturage_id', $id)
            ->where('conducteur_id', Auth::id())
            ->first();

        if (!$covoiturage) {
            return response()->json([
                'success' => false,
                'message' => 'Trajet introuvable'
            ], 404);
        }

        if ($covoiturage->photo_conducteur) {
            \Storage::disk('public')->delete($covoiturage->photo_conducteur);
        }

        $covoiturage->delete();

        return redirect()
         ->route('covoiturage.index')
         ->with('success', 'Trajet supprimé avec succès');
    }
    public function edit($id)
    {
        $trajet = Covoiturage::findOrFail($id);

        return view('livreur.covoiturage.edit', compact('trajet'));
    }

    public function update(Request $request, $id)
    {
        $trajet = Covoiturage::findOrFail($id);

        $data = $request->validate([
            'depart' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'date_depart' => 'required|date',
            'heure_depart' => 'required',
            'nb_places' => 'required|integer|min:1|max:8',
            'prix_place' => 'required|numeric|min:0',
            'passenger_mode' => 'required'
        ]);

        $trajet->update($data);

        return redirect()
            ->route('covoiturage.index', $trajet->covoiturage_id)
            ->with('success', 'Trajet mis à jour');
    }
}
