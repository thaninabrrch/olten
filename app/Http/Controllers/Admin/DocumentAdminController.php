<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserDocument;
use Illuminate\Http\Request;

class DocumentAdminController extends Controller
{
    public function index(Request $request)
    {
        // Toutes les pieces du chauffeur, y compris le permis de conduire :
        // la liste vient du modele pour ne pas oublier les futures.
        $query = UserDocument::with('user')
            ->whereIn('name', UserDocument::types())
            ->orderBy('created_at', 'desc');


        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', '%'.$request->search.'%'));
        }

        $documents = $query->paginate(15);
        return view('admin.documents.index', compact('documents'));
    }
    public function approve(UserDocument $document)
    {
        $document->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        // Le libelle suit la piece validee : piece d'identite, permis...
        return redirect()->back()->with(
            'success',
            UserDocument::label($document->name) . ' validé(e) avec succès.'
        );
    }
    public function reject(Request $request, UserDocument $document)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $document->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->back()->with(
            'error',
            UserDocument::label($document->name) . ' rejeté(e).'
        );
    }
}
