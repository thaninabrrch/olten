<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\ServiceProposal;
use App\Notifications\ServiceProposalAnswered;
use App\Notifications\ServiceProposalReceived;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Prestations de services, sur le principe d'AlloVoisins :
 *
 *   1. le client publie une annonce dans une categorie du service
 *      « Prestations de services » : c'est sa demande (formulaire d'annonce,
 *      voir assets/js/ad-form.js) ;
 *   2. les prestataires y repondent par un prix total (store, update,
 *      withdraw), depuis la fiche de l'annonce ;
 *   3. le client compare les propositions (compare) ;
 *   4. il choisit son prestataire (select) : les autres propositions sont
 *      declinees et leurs auteurs prevenus.
 *
 * Le paiement par la plateforme et les avis viendront se brancher sur la
 * proposition retenue (ServiceProposal::ACCEPTED).
 */
class ServiceProposalController extends Controller
{
    /** Ordres de tri de la page de comparaison. */
    private const SORTS = ['price', 'recent', 'availability'];

    /**
     * « Mes propositions » : les propositions envoyees en tant que
     * prestataire, et les demandes publiees avec les propositions recues.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $tab  = $request->input('onglet') === 'recues' ? 'recues' : 'envoyees';

        $sent = $user->serviceProposals()
            ->with(['ad.category', 'ad.images', 'ad.user'])
            ->latest('updated_at')
            ->get();

        $received = Ad::where('user_id', $user->id)
            ->whereHas('category.service', fn ($q) => $q->where('slug', ServiceProposal::SERVICE_SLUG))
            ->withCount(['proposals as proposals_count' => fn ($q) => $q->visible()])
            ->withCount(['proposals as pending_count' => fn ($q) => $q->pending()])
            ->withMin(['proposals as best_price' => fn ($q) => $q->visible()], 'price')
            ->with(['category', 'images', 'acceptedProposal.provider'])
            ->latest()
            ->get();

        return view('proposals.index', [
            'tab'      => $tab,
            'sent'     => $sent,
            'received' => $received,
        ]);
    }

    /**
     * Page du client : les propositions recues sur sa demande, cote a cote,
     * pour les comparer puis choisir son prestataire.
     */
    public function compare(Request $request, Ad $ad)
    {
        abort_unless((int) $ad->user_id === (int) $request->user()->id, 403);
        abort_unless($ad->isPrestation(), 404);

        $sort = in_array($request->input('tri'), self::SORTS, true) ? $request->input('tri') : 'price';

        // Le prestataire retenu reste en tete, quel que soit le tri.
        $proposals = $ad->proposals()
            ->visible()
            ->with('provider')
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [ServiceProposal::ACCEPTED])
            ->when($sort === 'price', fn ($q) => $q->orderBy('price')->orderBy('created_at'))
            ->when($sort === 'recent', fn ($q) => $q->latest())
            // Sans date, le prestataire n'a pas dit quand il pouvait venir :
            // il passe apres ceux qui se sont engages.
            ->when($sort === 'availability', fn ($q) => $q
                ->orderByRaw('CASE WHEN available_on IS NULL THEN 1 ELSE 0 END')
                ->orderBy('available_on')
                ->orderBy('price'))
            ->get();

        // Nombre de fois ou chaque prestataire a deja ete retenu sur la
        // plateforme : un repere de confiance, en une requete pour la page.
        $hired = ServiceProposal::query()
            ->where('status', ServiceProposal::ACCEPTED)
            ->whereIn('user_id', $proposals->pluck('user_id')->unique())
            ->groupBy('user_id')
            ->selectRaw('user_id, COUNT(*) as total')
            ->pluck('total', 'user_id');

        $prices = $proposals->pluck('price')->map(fn ($price) => (float) $price);

        return view('proposals.compare', [
            'ad'        => $ad->load(['category', 'images']),
            'proposals' => $proposals,
            'accepted'  => $proposals->first(fn (ServiceProposal $p) => $p->isAccepted()),
            'hired'     => $hired,
            'sort'      => $sort,
            'stats'     => [
                'count' => $proposals->count(),
                'min'   => $prices->min(),
                'max'   => $prices->max(),
                'avg'   => $prices->avg(),
            ],
        ]);
    }

    public function store(Request $request, Ad $ad)
    {
        abort_unless($ad->isPrestation(), 404);

        $user = $request->user();

        if ((int) $ad->user_id === (int) $user->id) {
            return $this->backToAd($ad)->with('error', 'Vous ne pouvez pas répondre à votre propre demande.');
        }

        if ($closed = $this->closedReason($ad)) {
            return $this->backToAd($ad)->with('error', $closed);
        }

        $data     = $this->validated($request);
        $existing = $ad->proposals()->where('user_id', $user->id)->first();

        if ($existing && $existing->status !== ServiceProposal::WITHDRAWN) {
            return $this->backToAd($ad)->with('error', 'Vous avez déjà fait une proposition sur cette demande : modifiez-la plutôt que d\'en envoyer une nouvelle.');
        }

        // Une proposition retiree peut etre renvoyee : la ligne est reprise,
        // un prestataire n'a qu'une proposition par demande. Elle repart
        // comme neuve, avec la date du nouvel envoi.
        $proposal = $existing ?? new ServiceProposal(['ad_id' => $ad->id, 'user_id' => $user->id]);
        $proposal->fill($data + ['status' => ServiceProposal::PENDING, 'answered_at' => null]);
        $proposal->created_at = now();

        try {
            $proposal->save();
        } catch (UniqueConstraintViolationException) {
            // Double envoi du formulaire : la premiere proposition est passee.
            return $this->backToAd($ad)->with('error', 'Votre proposition a déjà été envoyée.');
        }

        $ad->user?->notify(new ServiceProposalReceived($proposal->setRelation('ad', $ad)));

        return $this->backToAd($ad)->with('success', 'Proposition envoyée : le client est prévenu et va pouvoir la comparer aux autres.');
    }

    public function update(Request $request, ServiceProposal $proposal)
    {
        $this->ownProposal($request, $proposal);

        $ad = $proposal->ad;

        if (! $proposal->isPending()) {
            return $this->backToAd($ad)->with('error', 'Le client a déjà répondu : cette proposition ne peut plus être modifiée.');
        }

        if ($closed = $this->closedReason($ad)) {
            return $this->backToAd($ad)->with('error', $closed);
        }

        $proposal->update($this->validated($request));

        return $this->backToAd($ad)->with('success', 'Proposition mise à jour.');
    }

    public function withdraw(Request $request, ServiceProposal $proposal)
    {
        $this->ownProposal($request, $proposal);

        if (! $proposal->isPending()) {
            return back()->with('error', 'Le client a déjà répondu : cette proposition ne peut plus être retirée.');
        }

        $proposal->update(['status' => ServiceProposal::WITHDRAWN]);

        return back()->with('success', 'Proposition retirée : le client ne la voit plus. Vous pouvez en envoyer une nouvelle tant que la demande est ouverte.');
    }

    /**
     * Le client retient une proposition : elle passe « acceptee », les
     * autres propositions en attente sont declinees, et chaque prestataire
     * est prevenu de la reponse.
     */
    public function select(Request $request, ServiceProposal $proposal)
    {
        $ad = $proposal->ad;

        abort_unless($ad && (int) $ad->user_id === (int) $request->user()->id, 403);

        $declined = DB::transaction(function () use ($ad, $proposal) {
            // Verrou sur les propositions de la demande : deux clics, ou deux
            // onglets, ne doivent pas retenir deux prestataires.
            $proposals = ServiceProposal::where('ad_id', $ad->id)->lockForUpdate()->get();
            $chosen    = $proposals->firstWhere('id', $proposal->id);

            if (! $chosen?->isPending() || $proposals->contains(fn (ServiceProposal $p) => $p->isAccepted())) {
                return null;
            }

            $now    = now();
            $others = $proposals->filter(fn (ServiceProposal $p) => $p->isPending() && $p->id !== $chosen->id);

            $chosen->update(['status' => ServiceProposal::ACCEPTED, 'answered_at' => $now]);

            ServiceProposal::whereKey($others->modelKeys())
                ->update(['status' => ServiceProposal::DECLINED, 'answered_at' => $now]);

            return $others;
        });

        if ($declined === null) {
            return back()->with('error', 'Ce choix n\'est plus possible : un prestataire a déjà été retenu, ou cette proposition a été retirée.');
        }

        $proposal->refresh()->setRelation('ad', $ad)->load('provider');
        $proposal->provider?->notify(new ServiceProposalAnswered($proposal, ServiceProposalAnswered::ACCEPTED));

        $declined->load('provider')->each(function (ServiceProposal $other) use ($ad) {
            $other->provider?->notify(new ServiceProposalAnswered($other->setRelation('ad', $ad), ServiceProposalAnswered::DECLINED));
        });

        return redirect()->route('ads.proposals', $ad)->with('success', 'Vous avez choisi ' . $proposal->provider?->public_name
            . '. Écrivez-lui pour convenir ensemble des modalités de la prestation.');
    }

    /**
     * Pourquoi la demande n'accepte plus de proposition, ou null si elle en
     * accepte encore.
     */
    private function closedReason(Ad $ad): ?string
    {
        return match (true) {
            ! $ad->is_approved                    => 'Cette demande n\'est pas encore en ligne.',
            $ad->isExpired()                      => 'Cette demande a expiré : elle n\'accepte plus de propositions.',
            $ad->acceptedProposal()->exists()     => 'Le client a déjà choisi son prestataire : cette demande n\'accepte plus de propositions.',
            default                               => null,
        };
    }

    private function ownProposal(Request $request, ServiceProposal $proposal): void
    {
        abort_unless((int) $proposal->user_id === (int) $request->user()->id, 403);
    }

    /** Retour sur la fiche de la demande, a hauteur de l'encart de proposition. */
    private function backToAd(Ad $ad)
    {
        return redirect()->to(route('ads.show', $ad) . '#proposition');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'price'        => 'required|numeric|min:1|max:999999',
            'available_on' => 'nullable|date|after_or_equal:today',
            'message'      => 'required|string|min:20|max:2000',
        ], [
            'price.required'              => 'Indiquez le prix de votre prestation.',
            'price.numeric'               => 'Le prix doit être un nombre.',
            'price.min'                   => 'Le prix doit être d\'au moins :min €.',
            'price.max'                   => 'Le prix ne peut pas dépasser :max €.',
            'available_on.date'           => 'Cette date n\'est pas valide.',
            'available_on.after_or_equal' => 'Choisissez une date à venir.',
            'message.required'            => 'Présentez votre proposition en quelques mots.',
            'message.min'                 => 'Votre message doit faire au moins :min caractères : expliquez ce que comprend votre prix.',
            'message.max'                 => 'Votre message ne peut pas dépasser :max caractères.',
        ]);
    }
}
