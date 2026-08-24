<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return redirect('/')
            ->with('showLoginModal', true);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        $messages = [
            'email.required'    => 'L’adresse e-mail est obligatoire.',
            'email.email'       => 'L’adresse e-mail doit être valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ];

        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }
        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'general' => ['Identifiants incorrects. Veuillez vérifier vos informations.']
                ],
            ], 422);
        }

        $request->session()->regenerate();
        $request->session()->forget('showLoginModal');

        return response()->json([
            'status' => 'success',
            'redirect' => $this->intendedUrl($request),
        ]);
    }

    /**
     * Où renvoyer l'utilisateur après connexion.
     *
     * 1. `redirect` : la page que la popin voulait atteindre. Quand un visiteur
     *    clique sur « Déposer une annonce », la popin s'ouvre sur place et
     *    transporte cette destination ; il y arrive donc directement.
     * 2. `url.intended` : l'URL interceptée par le middleware `auth` quand le
     *    visiteur est arrivé par une adresse protégée saisie à la main.
     * 3. À défaut, le tableau de bord.
     *
     * Seules les URLs du site sont acceptées : une valeur externe ouvrirait
     * une redirection non maîtrisée vers un autre domaine.
     */
    private function intendedUrl(Request $request): string
    {
        $candidates = [
            $request->input('redirect'),
            $request->session()->pull('url.intended'),
        ];

        foreach ($candidates as $url) {
            if (! is_string($url) || $url === '') {
                continue;
            }

            $host = parse_url($url, PHP_URL_HOST);

            if ($host === null ? str_starts_with($url, '/') : $host === $request->getHost()) {
                return $url;
            }
        }

        return route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
