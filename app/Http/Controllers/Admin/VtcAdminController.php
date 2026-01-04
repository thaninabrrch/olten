<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserDocument;
use Illuminate\Http\Request;

class VtcAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = UserDocument::with('user')
            ->whereIn('name', ['vtc_card', 'identity_card'])
            ->orderBy('created_at', 'desc');


        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', '%'.$request->search.'%'));
        }

        $documents = $query->paginate(15);
        return view('admin.vtc_cards.index', compact('documents'));
    }
    public function approve(UserDocument $document)
    {
        $document->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        return redirect()->back()->with('success', 'Carte VTC validée avec succès.');
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

        return redirect()->back()->with('error', 'Carte VTC rejetée.');
    }
}
