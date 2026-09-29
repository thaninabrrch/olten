<?php

namespace App\Http\Controllers;

use App\Models\Covoiturage;
use App\Models\TripAlert;
use Illuminate\Http\Request;

/**
 * Alertes trajet du membre : « prévenez-moi quand un Paris → Lyon est
 * publié ». La notification part à la publication du trajet
 * (Covoiturage::created → TripAlert::notifyFor) et s'affiche dans la cloche
 * du header.
 */
class TripAlertController extends Controller
{
    public function index(Request $request)
    {
        $alerts = $request->user()->tripAlerts()->latest()->get();

        // Trajets déjà en ligne pour chaque alerte : le membre voit d'un coup
        // d'œil si la liaison a déjà des offres, sans attendre la prochaine.
        $alerts->each(function (TripAlert $alert) {
            $alert->setAttribute('matches', Covoiturage::query()
                ->where('statut', '!=', 'inactif')
                ->upcoming()
                ->withSeats()
                ->where('depart_slug', $alert->depart_slug)
                ->where('destination_slug', $alert->destination_slug)
                ->when($alert->date, fn ($q, $date) => $q->whereDate('date_depart', $date))
                ->count());
        });

        // Villes déjà présentes dans des trajets, proposées à la saisie : une
        // alerte ne se déclenche que si sa ville s'écrit comme celle des
        // trajets (« Saint-Étienne » et « St Étienne » n'ont pas le même slug).
        $cities = collect(['depart' => 'depart_slug', 'destination' => 'destination_slug'])
            ->flatMap(fn ($slug, $column) => Covoiturage::query()->toBase()
                ->select($slug)
                ->selectRaw("MIN($column) as ville")
                ->groupBy($slug)
                ->pluck('ville'))
            ->map(fn ($ville) => Covoiturage::villeCourte($ville))
            ->filter()
            ->unique(fn ($ville) => Covoiturage::citySlug($ville))
            ->sortBy(fn ($ville) => Covoiturage::citySlug($ville))
            ->values();

        return view('trips.alerts', [
            'alerts' => $alerts,
            'max'    => TripAlert::MAX_PER_USER,
            'cities' => $cities,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'depart'      => 'required|string|max:120',
            'destination' => 'required|string|max:120',
            'date'        => 'nullable|date|after_or_equal:today',
        ], [
            'depart.required'      => 'Indiquez la ville de départ.',
            'destination.required' => 'Indiquez la ville d\'arrivée.',
            'date.date'            => 'Cette date n\'est pas valide.',
            'date.after_or_equal'  => 'Choisissez une date à venir.',
        ]);

        $depart      = Covoiturage::villeCourte($data['depart']);
        $destination = Covoiturage::villeCourte($data['destination']);
        $slugs       = [Covoiturage::citySlug($depart), Covoiturage::citySlug($destination)];

        if (in_array('', $slugs, true) || $slugs[0] === $slugs[1]) {
            return back()->withInput()->with('error', 'Choisissez deux villes différentes.');
        }

        $user = $request->user();
        $date = $data['date'] ?? null;

        // Une même alerte n'est enregistrée qu'une fois : la recréer ne
        // doublerait pas les notifications, mais encombrerait la liste.
        $exists = $user->tripAlerts()
            ->where('depart_slug', $slugs[0])
            ->where('destination_slug', $slugs[1])
            ->when($date, fn ($q) => $q->whereDate('date', $date), fn ($q) => $q->whereNull('date'))
            ->exists();

        if (! $exists) {
            if ($user->tripAlerts()->count() >= TripAlert::MAX_PER_USER) {
                return back()->withInput()->with('error', 'Vous avez atteint le maximum de ' . TripAlert::MAX_PER_USER
                    . ' alertes : supprimez-en une pour en créer une nouvelle.');
            }

            $user->tripAlerts()->create([
                'depart'           => $depart,
                'destination'      => $destination,
                'depart_slug'      => $slugs[0],
                'destination_slug' => $slugs[1],
                'date'             => $date,
            ]);
        }

        return back()->with('success', 'Alerte enregistrée : vous serez prévenu dans la cloche dès qu\'un trajet '
            . $depart . ' → ' . $destination . ' sera publié.');
    }

    public function destroy(Request $request, TripAlert $alert)
    {
        abort_unless((int) $alert->user_id === (int) $request->user()->id, 403);

        $alert->delete();

        return back()->with('success', 'Alerte supprimée.');
    }
}
