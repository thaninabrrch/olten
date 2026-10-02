<?php

namespace App\Http\Controllers;
use App\Mail\ContactMessageMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Models\User;

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

    public function ownerMessage(Request $request)
    {
        $validated = $request->validate([
            'owner_id' => 'required|exists:users,id',
            'subject'  => 'required|string|max:255',
            'message'  => 'required|string',
        ]);

        $owner = User::findOrFail($validated['owner_id']);

        if (!$owner->email) {
            return back()->with('error', 'Ce propriétaire n’a pas d’adresse email.');
        }

        $user = auth()->user();

        $data = [
            'name'    => $user->name,
            'email'   => $user->email,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
        ];

        Message::create([
            'sender_id'   => $user->id,
            'receiver_id' => $owner->id,
            'content'     => $validated['message'],
            'is_read'     => false,
        ]);

        Mail::to($owner->email)->send(
            new ContactMessageMail($data)
        );

        return back()->with(
            'success',
            'Votre message a été envoyé avec succès.'
        );
    }
}
