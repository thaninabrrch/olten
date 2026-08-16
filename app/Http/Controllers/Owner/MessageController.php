<?php

namespace App\Http\Controllers\Owner;

use App\Models\Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class MessageController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $messages = Message::where('sender_id', $userId)
                            ->orWhere('receiver_id', $userId)
                            ->latest()
                            ->get()
                            ->groupBy(function ($message) use ($userId) {
                                return $message->sender_id == $userId
                                        ? $message->receiver_id
                                        : $message->sender_id;
                            });

        $conversations = $messages->map(function ($msgs, $userId) {
            $user = User::find($userId);
            $lastMessage = $msgs->first();

            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar ?? null,
                'last_message' => $lastMessage->content,
                'time' => $lastMessage->created_at->diffForHumans()
            ];
        })->values();

        return response()->json($conversations);
    }

    public function show(User $user)
    {
        $authId = auth()->id();
        $messages = Message::where(function ($q) use ($authId, $user) {
                                $q->where('sender_id', $authId)
                                ->where('receiver_id', $user->id);
                            })->orWhere(function ($q) use ($authId, $user) {
                                $q->where('sender_id', $user->id)
                                ->where('receiver_id', $authId);
                            })
                            ->orderBy('id', 'asc')
                            ->get();
        return response()->json($messages);
    }

    public function store(Request $request, User $user)
    {
        $request->validate([
            'message' => 'nullable|string',
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

        if (!$request->message && !$attachmentPath) {
            return response()->json([
                'error' => 'Message ou fichier requis'
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

        return response()->json($message, 201);
    }
}