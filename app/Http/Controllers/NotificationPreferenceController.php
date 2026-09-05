<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function update(Request $request)
    {
        $user = auth()->user();

        if ($user->subscription?->slug !== 'premium') {
            return back()->with('error', 'Cette fonctionnalité est réservée aux abonnés Premium.');
        }

        $validated = $request->validate([
            'notifications_enabled' => ['nullable', 'boolean'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        $user->notifications_enabled = $request->boolean('notifications_enabled');
        $user->save();

        $user->notificationCategories()->sync(
            $request->input('category_ids', [])
        );

        return back()->with(
            'success',
            'Vos préférences de notifications ont été enregistrées.'
        );
    }
}