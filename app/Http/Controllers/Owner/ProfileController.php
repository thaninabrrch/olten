<?php

namespace App\Http\Controllers\Owner;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function profile(Request $request)
    {
        return view('pages.locateur.profile', [
            'user' => $request->user(),
        ]);
    }
    /**
     * Display the user's profile form.
     */
    // public function edit(Request $request): View
    // {
    //     return view('profile.edit', [
    //         'user' => $request->user(),
    //     ]);
    // }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'display_format' => 'nullable|in:first_last,last_first',
            'profile_photo' => 'nullable|image|max:2048',
            'about_me' => 'nullable|string|max:1000',
            'disable_email_notifications' => 'nullable|boolean',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:Homme,Femme',
            'x_com' => 'nullable|url',
            'facebook' => 'nullable|url',
            'linkedin' => 'nullable|url',
            'instagram' => 'nullable|url',
            'youtube' => 'nullable|url',
            'tiktok' => 'nullable|url',
            'whatsapp' => 'nullable|string|max:20',
            'identity_verification' => 'nullable|image|max:2048',
        ]);

        $data = $request->only([
            'firstname',
            'lastname',
            'display_format',
            'about_me',
            'phone',
            'gender',
            'x_com',
            'facebook',
            'linkedin',
            'instagram',
            'youtube',
            'tiktok',
            'whatsapp',
        ]);

        $data['disable_email_notifications'] = $request->has('disable_email_notifications') ? 1 : 0;

        if (isset($data['display_format'])) {
            $data['name'] = $data['display_format'] === 'first_last'
                ? $data['firstname'] . ' ' . $data['lastname']
                : $data['lastname'] . ' ' . $data['firstname'];
        } else {
            $data['name'] = $data['firstname'] . ' ' . $data['lastname'];
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $data['profile_photo'] = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        if ($request->hasFile('identity_verification')) {
            if ($user->identity_verification) {
                Storage::disk('public')->delete($user->identity_verification);
            }
            $data['identity_verification'] = $request->file('identity_verification')->store('identity_verifications', 'public');
        }

        $user->update($data);

        return back()->with('status', 'Profil mis à jour avec succès !');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
    public function toggleVtc(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'is_vtc_driver' => 'required|boolean',
        ]);

        $user->is_vtc_driver = $request->is_vtc_driver;
        $user->save();

        return response()->json([
            'is_vtc_driver' => $user->is_vtc_driver
        ]);
    }

    public function toggleLivreur(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'is_livreur' => 'required|boolean',
        ]);

        $role = Role::where('name', 'livreur')->first();

        if (!$role) {
            return response()->json(['message' => 'Rôle livreur non trouvé'], 500);
        }

        if ($request->is_livreur) {

            if (!$user->roles()->where('role_id', $role->id)->exists()) {
                $user->roles()->attach($role->id);
            }

        } else {

            DB::table('role_user')
                ->where('role_id', $role->id)
                ->delete();
        }

        $activeRoles = $user->roles()->pluck('display_name')->toArray();

        return response()->json([
            'roles' => $activeRoles
        ]);
    }
}
