<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Notifications du membre, affichées dans la cloche du header
 * (<x-notification-bell />) et sur la page « Notifications ».
 *
 * Chaque notification enregistrée en base porte le même jeu de champs
 * (icon, title, text, url) : la cloche n'a pas à connaître leur type.
 */
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return view('notifications.index', [
            'notifications' => $request->user()->notifications()->paginate(20),
            'unread'        => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Ouvre une notification : elle est marquée lue, puis le membre part
     * vers la page qu'elle désigne. Seules les adresses du site sont
     * suivies, jamais une URL extérieure.
     */
    public function open(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        return is_string($url) && str_starts_with($url, url('/'))
            ? redirect($url)
            : redirect()->route('notifications.index');
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }
}
