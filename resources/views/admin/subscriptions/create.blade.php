@extends('admin.layouts.app')

@section('title', 'Offrir un abonnement')

@section('page_title', 'Offrir un abonnement')

@section('content')

<div class="max-w-4xl mx-auto">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Offrir un abonnement
            </h1>
            <p class="text-gray-500 mt-1">
                Attribuez gratuitement un abonnement à un utilisateur
            </p>
        </div>

        <a href="{{ route('admin.subscriptions.index') }}"
           class="btn-red">
            <i class="fas fa-arrow-left mr-2"></i>
            Retour
        </a>
    </div>

    {{-- FORMULAIRE --}}
    <div class="card-white">

        <form action="{{ route('admin.subscriptions.gift') }}" method="POST" id="gift-subscription-form" class="p-3">
            @csrf

            {{-- UTILISATEUR --}}
            <div class="mb-6 relative">

                <label for="user-search"
                       class="block text-sm font-semibold text-gray-700 mb-2">
                    Utilisateur <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    id="user-search"
                    autocomplete="off"
                    placeholder="Rechercher par nom, prénom ou email..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-red-500 focus:ring-red-500"
                >

                {{-- ID utilisateur sélectionné --}}
                <input type="hidden"
                       name="user_id"
                       id="user_id"
                       value="{{ old('user_id') }}">

                {{-- Résultats autocomplete --}}
                <div id="user-results"
                     class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg hidden overflow-hidden">
                </div>

                {{-- Utilisateur sélectionné --}}
                <div id="selected-user"
                     class="hidden mt-3 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                </div>

                @error('user_id')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- ABONNEMENT --}}
            <div class="mb-6">

                <label for="subscription_id"
                       class="block text-sm font-semibold text-gray-700 mb-2">
                    Abonnement <span class="text-red-500">*</span>
                </label>

                <select
                    name="subscription_id"
                    id="subscription_id"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-red-500 focus:ring-red-500"
                    required
                >

                    <option value="">
                        -- Sélectionner un abonnement --
                    </option>

                    @foreach($subscriptions as $subscription)

                        <option
                            value="{{ $subscription->id }}"
                            {{ old('subscription_id') == $subscription->id ? 'selected' : '' }}
                        >
                            {{ $subscription->name }}
                            — {{ number_format($subscription->price, 2, ',', ' ') }} €/mois
                        </option>

                    @endforeach

                </select>

                @error('subscription_id')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- DURÉE --}}
            <div class="mb-6">

                <label for="duration"
                       class="block text-sm font-semibold text-gray-700 mb-2">
                    Durée de l'offre <span class="text-red-500">*</span>
                </label>

                <select
                    name="duration"
                    id="duration"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-red-500 focus:ring-red-500"
                    required
                >

                    <option value="1" {{ old('duration') == 1 ? 'selected' : '' }}>
                        1 mois
                    </option>

                    <option value="3" {{ old('duration') == 3 ? 'selected' : '' }}>
                        3 mois
                    </option>

                    <option value="6" {{ old('duration') == 6 ? 'selected' : '' }}>
                        6 mois
                    </option>

                    <option value="12" {{ old('duration') == 12 ? 'selected' : '' }}>
                        12 mois
                    </option>

                </select>

                @error('duration')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- INFO --}}
            <div class="mb-6 p-4 rounded-lg border border-red-100 bg-red-50">

                <div class="flex items-start gap-3">

                    <i class="fas fa-gift text-red-500 mt-1"></i>

                    <div>
                        <p class="font-semibold text-gray-800">
                            Abonnement offert
                        </p>

                        <p class="text-sm text-gray-600 mt-1">
                            L'abonnement sera activé immédiatement pour l'utilisateur
                            sélectionné. Aucun paiement Stripe ne sera effectué.
                        </p>
                    </div>

                </div>

            </div>


            {{-- BOUTONS --}}
            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-gray-100">

                <a href="{{ route('admin.subscriptions.index') }}"
                   class="px-6 py-3 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">
                    Annuler
                </a>

                <button
                    type="submit"
                    class="btn-red"
                    id="submit-gift"
                >
                    <i class="fas fa-gift mr-2"></i>
                    Offrir l'abonnement
                </button>

            </div>

        </form>

    </div>

</div>


{{-- AUTOCOMPLETE --}}
<script>

document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.getElementById('user-search');
    const userResults = document.getElementById('user-results');
    const userIdInput = document.getElementById('user_id');
    const selectedUser = document.getElementById('selected-user');

    let searchTimeout = null;


    searchInput.addEventListener('input', function () {

        const query = this.value.trim();

        clearTimeout(searchTimeout);

        userIdInput.value = '';
        selectedUser.classList.add('hidden');
        userResults.innerHTML = '';

        if (query.length < 2) {
            userResults.classList.add('hidden');
            return;
        }

        searchTimeout = setTimeout(() => {

            fetch("{{ route('admin.subscriptions.users.search') }}?q=" + encodeURIComponent(query), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(users => {

                userResults.innerHTML = '';

                if (!users.length) {

                    userResults.innerHTML = `
                        <div class="px-4 py-3 text-sm text-gray-500">
                            Aucun utilisateur trouvé.
                        </div>
                    `;

                    userResults.classList.remove('hidden');

                    return;
                }


                users.forEach(user => {

                    const item = document.createElement('button');

                    item.type = 'button';

                    item.className =
                        'w-full text-left px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-b-0 transition';

                    item.innerHTML = `
                        <div class="font-semibold text-gray-800">
                            ${escapeHtml(user.name ?? '')}
                        </div>

                        <div class="text-sm text-gray-500">
                            ${escapeHtml(user.email ?? '')}
                        </div>
                    `;

                    item.addEventListener('click', function () {

                        userIdInput.value = user.id;

                        searchInput.value = user.name ?? user.email;

                        selectedUser.innerHTML = `
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                    <i class="fas fa-user text-red-500"></i>
                                </div>

                                <div>
                                    <p class="font-semibold text-gray-800">
                                        ${escapeHtml(user.name ?? '')}
                                    </p>

                                    <p class="text-sm text-gray-500">
                                        ${escapeHtml(user.email ?? '')}
                                    </p>
                                </div>

                                <i class="fas fa-check text-green-500 ml-auto"></i>
                            </div>
                        `;

                        selectedUser.classList.remove('hidden');
                        userResults.classList.add('hidden');

                    });

                    userResults.appendChild(item);

                });

                userResults.classList.remove('hidden');

            })
            .catch(error => {

                console.error(error);

                userResults.innerHTML = `
                    <div class="px-4 py-3 text-sm text-red-500">
                        Une erreur est survenue.
                    </div>
                `;

                userResults.classList.remove('hidden');

            });

        }, 300);

    });


    document.addEventListener('click', function (event) {

        if (!searchInput.contains(event.target) &&
            !userResults.contains(event.target)) {

            userResults.classList.add('hidden');

        }

    });


    function escapeHtml(value) {

        const div = document.createElement('div');

        div.textContent = value;

        return div.innerHTML;

    }

});

</script>

@endsection