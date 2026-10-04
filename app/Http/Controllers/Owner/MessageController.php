<?php

namespace App\Http\Controllers\Owner;

use App\Models\Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;

class MessageController extends Controller
{
    /**
     * Liste des conversations : un fil par interlocuteur, le plus recent en
     * tete, avec son nombre de messages non lus.
     */
    public function index()
    {
        $authId = auth()->id();

        $threads = Message::where('sender_id', $authId)
                            ->orWhere('receiver_id', $authId)
                            ->latest('id')
                            ->get()
                            ->groupBy(function ($message) use ($authId) {
                                return $message->sender_id == $authId
                                        ? $message->receiver_id
                                        : $message->sender_id;
                            });

        // Une seule requete pour tous les interlocuteurs (auparavant un
        // User::find() par conversation).
        $users = User::whereIn('id', $threads->keys())->get()->keyBy('id');

        $conversations = $threads->map(function ($msgs, $userId) use ($users, $authId) {
            $user = $users->get($userId);

            if (! $user) {
                return null;
            }

            $last = $msgs->first();

            return [
                'user_id'      => $user->id,
                'name'         => $user->name,
                'avatar'       => $this->avatarUrl($user),
                'last_message' => $this->preview($last),
                'last_mine'    => $last->sender_id == $authId,
                'last_read'    => (bool) $last->is_read,
                'at'           => $last->created_at?->toIso8601String(),
                'unread'       => $msgs->where('receiver_id', $authId)->where('is_read', false)->count(),
            ];
        })->filter()->values();

        return response()->json($conversations);
    }

    /**
     * Fil avec un interlocuteur. `?after={id}` ne renvoie que les messages
     * plus recents : la page interroge ainsi le fil ouvert sans le recharger.
     */
    public function show(Request $request, User $user)
    {
        $authId = auth()->id();
        $after  = (int) $request->query('after', 0);

        $messages = Message::where(function ($q) use ($authId, $user) {
                                $q->where(function ($q) use ($authId, $user) {
                                    $q->where('sender_id', $authId)
                                    ->where('receiver_id', $user->id);
                                })->orWhere(function ($q) use ($authId, $user) {
                                    $q->where('sender_id', $user->id)
                                    ->where('receiver_id', $authId);
                                });
                            })
                            ->when($after > 0, fn ($q) => $q->where('id', '>', $after))
                            ->orderBy('id', 'asc')
                            ->get();

        // Ouvrir le fil vaut lecture : les messages recus passent a « lu ».
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $authId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'user' => [
                'id'       => $user->id,
                'name'     => $user->name,
                'avatar'   => $this->avatarUrl($user),
                'verified' => (bool) $user->is_approved,
                'since'    => $user->created_at?->translatedFormat('F Y'),
            ],
            'messages'   => $messages->map(fn ($m) => $this->present($m, $authId))->values(),
            // Dernier de mes messages lu par l'interlocuteur : la coche « Lu »
            // se met a jour sans recharger tout le fil.
            'read_up_to' => (int) Message::where('sender_id', $authId)
                                ->where('receiver_id', $user->id)
                                ->where('is_read', true)
                                ->max('id'),
        ]);
    }

    public function store(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Vous ne pouvez pas vous écrire à vous-même.'], 422);
        }

        $request->validate([
            'message' => 'nullable|string|max:2000',
            // Liste fermee : le disque public sert ces fichiers tels quels, un
            // script depose ici serait accessible (voire execute) depuis /storage.
            'file'    => 'nullable|file|max:10240|mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,txt',
        ], [
            'message.max' => 'Votre message ne doit pas dépasser 2 000 caractères.',
            'file.max'    => 'La pièce jointe ne doit pas dépasser 10 Mo.',
            'file.mimes'  => 'Formats acceptés : images, PDF, Word, Excel ou texte.',
            'file.file'   => 'La pièce jointe n’a pas pu être lue.',
        ]);

        $attachmentPath = null;
        $attachmentName = null;
        $attachmentType = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $attachmentPath = $file->store('messages', 'public');
            $attachmentName = $file->getClientOriginalName();
            $attachmentType = $file->getMimeType();
        }

        if (! trim((string) $request->message) && ! $attachmentPath) {
            return response()->json([
                'message' => 'Écrivez un message ou joignez un fichier.'
            ], 422);
        }

        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $user->id,
            'content' => $request->message ?? '',
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_type' => $attachmentType,
        ]);

        return response()->json($this->present($message, auth()->id()), 201);
    }

    /** Forme commune d'un message pour le script de la messagerie. */
    private function present(Message $message, int $authId): array
    {
        $attachment = null;

        if ($message->attachment_path) {
            $attachment = [
                'url'      => asset('storage/' . $message->attachment_path),
                'name'     => $message->attachment_name ?: 'Pièce jointe',
                'is_image' => str_starts_with((string) $message->attachment_type, 'image/'),
            ];
        }

        return [
            'id'         => $message->id,
            'mine'       => $message->sender_id == $authId,
            'content'    => (string) $message->content,
            'at'         => $message->created_at?->toIso8601String(),
            'is_read'    => (bool) $message->is_read,
            'attachment' => $attachment,
        ];
    }

    private function preview(Message $message): string
    {
        if (filled($message->content)) {
            // Les retours a la ligne n'ont pas de sens sur une ligne d'apercu
            return preg_replace('/\s+/u', ' ', trim($message->content));
        }

        return $message->attachment_path
            ? 'Pièce jointe : ' . ($message->attachment_name ?: 'fichier')
            : '';
    }

    private function avatarUrl(User $user): ?string
    {
        return $user->profile_photo ? asset('storage/' . $user->profile_photo) : null;
    }
}
