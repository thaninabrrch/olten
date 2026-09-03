<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactMessage::with('user.subscription');

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $messages = $query
            ->orderByRaw("
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM users
                        INNER JOIN subscriptions
                            ON subscriptions.id = users.subscription_id
                        WHERE users.id = contact_messages.user_id
                        AND subscriptions.slug = 'premium'
                    )
                    THEN 0
                    ELSE 1
                END
            ")
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.contact_messages.index', compact('messages'));
    }

    public function show(ContactMessage $contactMessage)
    {
        return view('admin.contact_messages.show', compact('contactMessage'));
    }
        public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return redirect()->route('admin.contact_messages.index')
                         ->with('success', 'Le message a été supprimé avec succès.');
    }
}
