<?php

return [
    'commission_rate' => (float) env('PLATFORM_COMMISSION_RATE', 20),

    /*
     * Décalage d'horaire (en minutes) qu'un conducteur peut encore appliquer
     * à un sens déjà réservé. Au-delà, le trajet n'est plus celui que le
     * passager a payé : la date, l'itinéraire et le prix sont, eux, figés.
     * Les passagers concernés sont prévenus de tout décalage.
     */
    'booked_time_shift' => (int) env('CARPOOL_BOOKED_TIME_SHIFT', 30),
];