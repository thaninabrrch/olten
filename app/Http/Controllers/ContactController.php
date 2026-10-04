<?php

namespace App\Http\Controllers;
use App\Mail\ContactMessageMail;
use App\Mail\OwnerMessageMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Ad;
use App\Models\ContactMessage;
use App\Models\Message;
use App\Models\Product;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return view('pages.contact');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'nullable|string',
        ]);
        $validated['user_id'] = auth()->id();
        // Stockage dans la base
        ContactMessage::create($validated);
        // Envoi du mail
        Mail::to($validated['email'])->send(new ContactMessageMail($validated));

        return redirect()
            ->route('contact')
            ->with('success', 'Votre message a été envoyé avec succès.');
    }

    /**
     * Message envoye depuis la popin « Message » d'une fiche produit ou annonce.
     *
     * Le destinataire se deduit de l'offre, jamais d'un identifiant poste : on
     * ne peut ecrire qu'au proprietaire d'une offre, et l'objet de l'e-mail
     * parti au nom d'Olten reste celui de la plateforme, pas un texte libre.
     */
    public function ownerMessage(Request $request)
    {
        $validated = $request->validate([
            'listing_type' => 'required|in:product,ad',
            'listing_id'   => 'required|integer',
            'message'      => 'required|string|min:2|max:2000',
        ], [
            'message.required' => 'Écrivez votre message avant de l’envoyer.',
            'message.min'      => 'Votre message est un peu court.',
            'message.max'      => 'Votre message ne doit pas dépasser 2 000 caractères.',
        ]);

        if ($validated['listing_type'] === 'product') {
            $listing = Product::findOrFail($validated['listing_id']);
            $title   = $listing->name;
            $url     = route('products.show', $listing);
        } else {
            $listing = Ad::findOrFail($validated['listing_id']);
            $title   = $listing->title;
            $url     = route('ads.show', $listing);
        }

        $owner  = $listing->user;
        $sender = $request->user();

        if (! $owner) {
            return $this->refuse($request, 'Cet annonceur ne peut pas encore être contacté.');
        }

        if ($owner->id === $sender->id) {
            return $this->refuse($request, 'Il s’agit de votre propre offre.');
        }

        // L'offre ouvre le message : dans la messagerie, le proprietaire sait
        // d'emblee de quel bien on lui parle (la table n'a pas de lien vers l'offre).
        Message::create([
            'sender_id'   => $sender->id,
            'receiver_id' => $owner->id,
            'content'     => 'À propos de « ' . $title . ' »' . "\n\n" . $validated['message'],
            'is_read'     => false,
        ]);

        // L'e-mail ne fait que prevenir : le message est deja dans la
        // messagerie. Un SMTP en panne ne doit donc pas faire croire a un echec.
        if ($owner->email) {
            try {
                Mail::to($owner->email)->send(
                    new OwnerMessageMail($sender, $owner, $title, $url, $validated['message'])
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $text = 'Votre message a bien été envoyé à ' . ($owner->firstname ?: $owner->name) . '.';

        if ($request->expectsJson()) {
            return response()->json([
                'message'          => $text,
                'conversation_url' => route('messages', ['avec' => $owner->id]),
            ]);
        }

        return back()->with('success', $text);
    }

    private function refuse(Request $request, string $text)
    {
        return $request->expectsJson()
            ? response()->json(['message' => $text], 422)
            : back()->with('error', $text);
    }
}
