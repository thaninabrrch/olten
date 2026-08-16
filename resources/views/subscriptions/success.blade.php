@extends('layouts.main')

@section('content')

<div class="container py-5">

    <div class="text-center">

        <div class="mb-4">
            <i class="fas fa-check-circle"
               style="font-size:80px;color:#ff5a1f;"></i>
        </div>

        <h1 class="fw-bold mb-3">
            Paiement effectué avec succès !
        </h1>

        <p class="text-muted fs-5 mb-4">
            Merci pour votre abonnement à Olten.
        </p>

        <a href="{{ route('dashboard') }}"
           class="btn btn-olten px-5 py-3">
            Accéder à mon compte
        </a>

    </div>

</div>

<style>

.btn-olten {
    background:#ff5a1f;
    border:1px solid #ff5a1f;
    color:#fff;
    font-size:18px;
    font-weight:600;
    border-radius:12px;
    transition:.3s;
}

.btn-olten:hover {
    background:#e84f17;
    border-color:#e84f17;
    color:#fff;
    transform:translateY(-2px);
}

</style>

@endsection