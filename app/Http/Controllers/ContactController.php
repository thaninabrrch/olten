<?php

namespace App\Http\Controllers;
use App\Mail\ContactMessageMail;
use Illuminate\Support\Facades\Mail;
use App\Models\ContactMessage;
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

    // Stockage dans la base
    ContactMessage::create($validated);

    // Envoi du mail
    Mail::to($validated['email'])->send(new ContactMessageMail($validated));

    return redirect()
        ->route('contact')
        ->with('success', 'Votre message a été envoyé avec succès.');
}
}
