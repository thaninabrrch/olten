{{-- Erreurs de validation d'une proposition (ServiceProposalController::validated) --}}
@if ($errors->any())
    <div class="pp-flash is-error" role="alert">
        <i class="fa-solid fa-circle-exclamation"></i>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
