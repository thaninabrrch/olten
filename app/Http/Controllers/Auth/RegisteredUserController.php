<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Laratrust\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $messages = [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L’adresse e-mail est obligatoire.',
            'email.email' => 'L’adresse e-mail doit être valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.regex' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.',
            'terms.accepted' => 'Vous devez accepter les conditions pour continuer.',
            'role.required' => 'Le role est obligatoire.',
        ];

        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'   => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).+$/'
            ],
            'terms'      => ['accepted'],
            'role' => ['required'],
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ]);
        }

        $user = User::create([
            'name'     => $request->first_name . ' ' . $request->last_name,
            'firstname'     => $request->first_name,
            'lastname'     => $request->last_name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);
        
        $roles = explode('|', $request->role);

        $roleIds = [];

        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->first();

            if (!$role) {
                throw new \Exception("Rôle invalide: $roleName");
            }

            $roleIds[] = $role->id;
        }

        $user->syncRoles($roleIds);

        event(new Registered($user));

        Auth::login($user);

        // return response()->json([
        //     'status' => 'success',
        //     'redirect' => route('dashboard'),
        // ]);
        return response()->json([
            'status' => 'success',
            'redirect' => route('subscriptions.index'),
        ]);
    }
}
