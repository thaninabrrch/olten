<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Covoiturage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Construit les « sens » (aller / retour) d'un trajet tels que les attendent
 * les vues de détail : villes, adresses, horaires, distance, durée, escales,
 * tarifs et tracé de la carte.
 *
 * Reprend à l'identique ServicePageController::trip(), leg(), routePath() et
 * simplify(). ServicePageController peut à son tour faire `use BuildsTripLegs;`
 * et supprimer ses copies privées : une seule source de vérité.
 */
trait BuildsTripLegs
{
    /** Plafond de points d'un tracé envoyé à la carte. */
    protected int $pathPoints = 400;

    /** @return array<string, array> clés : 'aller' et, s'il existe, 'retour' */
    protected function tripLegs(Covoiturage $trip): array
    {
        $return     = $trip->return_trip_data ?? [];
        $returnTrip = $return['trajet'] ?? [];

        $legs = [
            'aller' => $this->buildLeg(
                key: 'aller',
                label: 'Segment aller',
                from: $trip->depart,
                to: $trip->destination,
                date: $trip->date_depart,
                time: $trip->heure_depart,
                metrics: $trip->selected_route ?? [],
                segments: $trip->segments ?? [],
                total: (float) ($trip->prix_total_affiche ?: $trip->prix_place),
                path: $this->routePath($trip->selected_route, $trip->itineraire),
            ),
        ];

        if ($trip->retour) {
            $legs['retour'] = $this->buildLeg(
                key: 'retour',
                label: 'Segment retour',
                from: $trip->destination,
                to: $trip->depart,
                date: $trip->return_date,
                time: $trip->return_time,
                metrics: $returnTrip,
                segments: $return['pricing'] ?? [],
                total: (float) ($return['total'] ?? 0),
                path: $this->routePath($returnTrip, $trip->return_itinerary),
            );
        }

        return $legs;
    }

    protected function buildLeg(
        string $key,
        string $label,
        ?string $from,
        ?string $to,
        $date,
        ?string $time,
        array $metrics,
        array $segments,
        float $total,
        array $path
    ): array {
        $departure = Str::substr((string) $time, 0, 5);
        $duration  = (int) round((float) ($metrics['duration'] ?? 0));

        $arrival = null;

        if ($departure !== '' && $duration > 0) {
            $arrival = Carbon::createFromFormat('H:i', $departure)->addSeconds($duration);
        }

        return [
            'key'      => $key,
            'label'    => $label,
            'from'     => Covoiturage::villeCourte($from),
            'to'       => Covoiturage::villeCourte($to),
            'address'  => ['from' => $from, 'to' => $to],
            'date'     => $date,
            'time'     => $departure ?: null,
            'arrival'  => $arrival?->format('H:i'),
            'next_day' => $arrival && $departure !== '' && $arrival->format('H:i') < $departure,
            'distance' => (float) ($metrics['distance'] ?? 0),
            'duration' => $duration,
            'segments' => array_values($segments),
            'total'    => $total,
            'path'     => $path,
        ];
    }

    protected function routePath(?array $metrics, ?array $itineraire): array
    {
        $coordinates = $metrics['geometry']['coordinates'] ?? null;

        if (is_array($coordinates) && count($coordinates) > 1) {
            // GeoJSON stocke [longitude, latitude] : Leaflet attend l'inverse.
            $path = array_values(array_map(
                fn ($point) => [(float) ($point[1] ?? 0), (float) ($point[0] ?? 0)],
                $coordinates
            ));

            return $this->simplifyPath($path);
        }

        return collect($itineraire ?? [])
            ->map(fn ($point) => isset($point['latlng'][0], $point['latlng'][1])
                ? [(float) $point['latlng'][0], (float) $point['latlng'][1]]
                : null)
            ->filter()
            ->values()
            ->all();
    }

    protected function simplifyPath(array $path): array
    {
        $count = count($path);

        if ($count <= $this->pathPoints) {
            return $path;
        }

        $step = (int) ceil($count / $this->pathPoints);

        $simplified = [];

        for ($i = 0; $i < $count; $i += $step) {
            $simplified[] = $path[$i];
        }

        $simplified[] = $path[$count - 1];

        return $simplified;
    }
}