<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        // Valider le formulaire
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'mot_de_passe' => ['required'],
        ]);

        // Récupérer l'utilisateur par email
        $user = \App\Models\User::where('email', $credentials['email'])->first();

        // Vérifier si utilisateur existe et mot de passe correct
if (!$user || !Hash::check($credentials['mot_de_passe'], $user->password)) {
    return back()->withErrors(['email' => 'Email ou mot de passe incorrect'])
                 ->withInput($request->only('email'));
}




        // Connecter l'utilisateur manuellement
        Auth::login($user);

        // Rediriger vers le dashboard admin
        return redirect()->route('admin.dashboard');
    }
    public function logout(Request $request)
    {
        Auth::logout(); // Déconnecte l'utilisateur
        $request->session()->invalidate(); // Invalide la session
        $request->session()->regenerateToken(); // Regénère le token CSRF

        return redirect()->route('admin.login'); // Redirige vers la page de login
    }

}
