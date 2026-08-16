@extends('layouts.connected')

@section('title', 'Accès refusé')

@section('content')

<div class="error-container">

    <div class="error-card">

        <div class="error-icon">
            <i class="fas fa-lock"></i>
        </div>

        <h1>403</h1>

        <h2>Accès refusé</h2>

        <p>
            Vous n'avez pas les autorisations nécessaires pour accéder à cette page.
        </p>

        <div class="error-actions">

            <a href="{{ url()->previous() }}" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Retour
            </a>

            <a href="{{ route('home') }}" class="btn-home">
                <i class="fas fa-home"></i>
                Accueil
            </a>

        </div>

    </div>

</div>

<style>

.error-container{
    min-height:70vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:40px 20px;
}

.error-card{
    max-width:600px;
    width:100%;
    background:#fff;
    border-radius:20px;
    padding:50px;
    text-align:center;
    box-shadow:0 10px 35px rgba(0,0,0,.08);
}

.error-icon{
    width:100px;
    height:100px;
    margin:auto;
    border-radius:50%;
    background:#fff3cd;
    color:#f59e0b;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:42px;
}

.error-card h1{
    margin-top:25px;
    font-size:72px;
    color:#dc3545;
}

.error-card h2{
    margin-bottom:15px;
}

.error-card p{
    color:#6c757d;
    font-size:16px;
    margin-bottom:30px;
}

.error-actions{
    display:flex;
    justify-content:center;
    gap:15px;
    flex-wrap:wrap;
}

.btn-back,
.btn-home{
    padding:12px 20px;
    border-radius:10px;
    text-decoration:none;
    color:white;
    font-weight:600;
}

.btn-back{
    background:#6c757d;
}

.btn-home{
    background:#f97316;
}

.btn-back:hover,
.btn-home:hover{
    opacity:.9;
}

</style>

@endsection