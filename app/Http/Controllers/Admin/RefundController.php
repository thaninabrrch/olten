<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Back-office : les remboursements de la plateforme, tous services confondus.
 *
 * Il n'existe pas de table dédiée : chaque service garde la trace de ses
 * remboursements sur ses propres lignes, lues ici telles quelles.
 *
 *   - covoiturage : réservation annulée par le passager (trip_bookings, cancelled)
 *   - location    : réservation refusée par le propriétaire (bookings, refunded)
 *   - vente       : commande annulée par le vendeur (product_sales, refunded)
 *
 * Les trois sont réunies par un UNION ALL, puis filtrées, triées et paginées
 * en SQL : rien n'est chargé en mémoire au-delà de la page affichée.
 */
class RefundController extends Controller
{
    public const SERVICES = [
        'covoiturage' => 'Covoiturage',
        'location'    => 'Location',
        'vente'       => 'Vente',
    ];

    private const PER_PAGE = 20;

    public function index(Request $request)
    {
        $filters = [
            'service' => array_key_exists((string) $request->query('service'), self::SERVICES) ? $request->query('service') : null,
            'search'  => trim((string) $request->query('search')),
            'du'      => $this->asDate($request->query('du')),
            'au'      => $this->asDate($request->query('au')),
        ];

        $refunds = DB::query()
            ->fromSub($this->refunds(), 'remboursements')
            ->when($filters['service'], fn (Builder $q, $service) => $q->where('service', $service))
            ->when($filters['search'], fn (Builder $q, $search) => $q->where(fn (Builder $w) => $w
                ->whereLike('client_name', '%' . $search . '%')
                ->orWhereLike('client_email', '%' . $search . '%')
                ->orWhereLike('reference', '%' . $search . '%')))
            ->when($filters['du'], fn (Builder $q, $date) => $q->whereDate('refunded_at', '>=', $date))
            ->when($filters['au'], fn (Builder $q, $date) => $q->whereDate('refunded_at', '<=', $date));

        // Totaux par service, sur les filtres en cours
        $totals = (clone $refunds)
            ->select('service')
            ->selectRaw('COUNT(*) as count, SUM(amount) as total')
            ->groupBy('service')
            ->get()
            ->keyBy('service');

        return view('admin.refunds.index', [
            'refunds'  => (clone $refunds)->orderByDesc('refunded_at')->paginate(self::PER_PAGE)->withQueryString(),
            'totals'   => $totals,
            'count'    => (int) $totals->sum('count'),
            'total'    => (float) $totals->sum('total'),
            'filters'  => $filters,
            'services' => self::SERVICES,
            // Lien vers le paiement dans le tableau de bord Stripe (mode test ou réel)
            'stripeUrl' => str_starts_with((string) config('services.stripe.secret'), 'sk_test_')
                ? 'https://dashboard.stripe.com/test/payments/'
                : 'https://dashboard.stripe.com/payments/',
        ]);
    }

    /**
     * Les remboursements des trois services, au même jeu de colonnes.
     * Jointures à gauche : un membre ou une annonce supprimés ne font pas
     * disparaître le remboursement de la liste.
     */
    private function refunds(): Builder
    {
        $trips = DB::table('trip_bookings as tb')
            ->leftJoin('users as u', 'u.id', '=', 'tb.user_id')
            ->leftJoin('covoiturages as c', 'c.covoiturage_id', '=', 'tb.trip_id')
            ->where('tb.status', 'cancelled')
            ->select([
                DB::raw("'covoiturage' as service"),
                'tb.id as source_id',
                'tb.trip_id as object_id',
                'tb.cancelled_at as refunded_at',
                'tb.total_price as amount',
                'u.name as client_name',
                'u.email as client_email',
                'c.depart as subject',
                'c.destination as subject_to',
                'tb.stripe_intent as reference',
                DB::raw("'Annulée par le passager' as reason"),
            ]);

        $rentals = DB::table('bookings as b')
            ->leftJoin('users as u', 'u.id', '=', 'b.user_id')
            ->leftJoin('ads as a', 'a.id', '=', 'b.ad_id')
            ->where('b.status', 'refunded')
            ->whereNotNull('b.payment_intent_id')
            ->select([
                DB::raw("'location' as service"),
                'b.id as source_id',
                'b.ad_id as object_id',
                'b.updated_at as refunded_at',
                'b.total_price as amount',
                'u.name as client_name',
                'u.email as client_email',
                'a.title as subject',
                DB::raw('NULL as subject_to'),
                'b.payment_intent_id as reference',
                DB::raw("'Refusée par le propriétaire' as reason"),
            ]);

        $sales = DB::table('product_sales as s')
            ->leftJoin('users as u', 'u.id', '=', 's.buyer_id')
            ->leftJoin('products as p', 'p.id', '=', 's.product_id')
            ->where('s.status', 'refunded')
            ->select([
                DB::raw("'vente' as service"),
                's.id as source_id',
                's.product_id as object_id',
                's.updated_at as refunded_at',
                's.total_price as amount',
                'u.name as client_name',
                'u.email as client_email',
                'p.name as subject',
                DB::raw('NULL as subject_to'),
                's.payment_intent_id as reference',
                DB::raw("'Annulée par le vendeur' as reason"),
            ]);

        return $trips->unionAll($rentals)->unionAll($sales);
    }

    /** Date de filtre saisie, ignorée si illisible : la page ne tombe jamais en erreur. */
    private function asDate($value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
