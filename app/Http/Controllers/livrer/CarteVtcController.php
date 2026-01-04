<?php

namespace App\Http\Controllers\livrer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\UserDocument;
class CarteVtcController extends Controller
{
    public function index()
    {
        return view('livreur.carte.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'document_type' => 'required|in:identity_card,vtc_card',
            'file'          => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', 
        ]);

        $path = $request->file('file')->store('documents', 'public');
        $document = auth()->user()->documents->where('name', $request->document_type)->first();

        $identifier = $document && $document->identifier ? $document->identifier : null;
        if ($request->document_type === 'vtc_card' && !$identifier) {
            $identifier = 'OLT-' . strtoupper(Str::random(8)); 
        }

        UserDocument::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'name'    => $request->document_type,
            ],
            [
                'file_path'  => $path,
                'status'     => 'pending',
                'identifier' => $identifier,
            ]
        );

        return back()->with('success', 'Document envoyé avec succès. En attente de validation.');
    }


}
