@extends('layouts.main')

@section('title', 'Réinitialiser le mot de passe - Olten.fr')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <!-- Alertes personnalisées -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-1">Réinitialiser le mot de passe</h3>

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Votre email" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-submit btn-lg">Envoyer le lien</button>
                        </div>
                    </form>

                    <p class="text-muted text-center mt-3" style="font-size: 0.9rem;">
                        Vous recevrez un email avec un lien pour réinitialiser votre mot de passe.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
