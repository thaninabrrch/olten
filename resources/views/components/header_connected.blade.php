
        <!-- HEADER -->
        <header class="connected-header">
            <div class="header-left">
                <button class="btn-toggle-sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <div class="header-right">
                <x-user-dropdown />
                @if(auth()->user()->hasRole('locateur'))
                <a href="{{ auth()->user()->is_approved ? route('ads.create') : '#' }}" class="btn-add-annonce {{ !auth()->user()->is_approved ? 'opacity-50 pointer-events-none cursor-not-allowed' : '' }}">
                    <i class="fa-solid fa-plus"></i>
                    <span>Ajouter une annonce</span>
                </a>
                @elseif(auth()->user()->hasRole('vendeur'))
                <a href="{{ auth()->user()->is_approved ? route('seller.produits.create') : '#' }}" class="btn-add-annonce {{ !auth()->user()->is_approved ? 'opacity-50 pointer-events-none cursor-not-allowed' : '' }}">
                    <i class="fa-solid fa-plus"></i>
                    <span>Ajouter un produit</span>
                </a>
                @endif
            </div>
        </header>