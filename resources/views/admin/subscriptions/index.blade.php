@extends('admin.layouts.app')

@section('title', 'Gestion des Abonnements')

@section('page_title', 'Gestion des Abonnements')

@section('content')

{{-- HEADER --}}
<div class="flex flex-col md:flex-row justify-between items-center mb-8">

    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            Abonnements
        </h1>

        <p class="text-gray-500 mt-1">
            Gérez les abonnements proposés aux utilisateurs.
        </p>
    </div>

    {{-- Offrir un abonnement --}}
    <a href="{{ route('admin.subscriptions.create') }}" class="btn-red">
        Offrir un abonnement
    </a>

</div>


{{-- STATISTIQUES --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

    {{-- Nombre d'abonnements --}}
    <div class="card-white p-6">
        <div class="flex items-center justify-between">

            <div>
                <p class="text-sm text-gray-500">
                    Abonnements
                </p>

                <p class="text-3xl font-bold text-gray-800 mt-1">
                    {{ $subscriptions->count() }}
                </p>
            </div>

            <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-6 h-6 text-[rgb(233,29,40)]"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 6v12m6-6H6" />

                </svg>

            </div>

        </div>
    </div>


    {{-- Nombre d'utilisateurs --}}
    <div class="card-white p-6">
        <div class="flex items-center justify-between">

            <div>
                <p class="text-sm text-gray-500">
                    Utilisateurs abonnés
                </p>

                <p class="text-3xl font-bold text-gray-800 mt-1">
                    {{ $subscriptions->sum('users_count') }}
                </p>
            </div>

            <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-6 h-6 text-[rgb(233,29,40)]"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-4a4 4 0 100-8 4 4 0 000 8zm6 0a3 3 0 100-6 3 3 0 000 6z" />

                </svg>

            </div>

        </div>
    </div>


    {{-- Prix moyen --}}
    <div class="card-white p-6">
        <div class="flex items-center justify-between">

            <div>
                <p class="text-sm text-gray-500">
                    Prix moyen
                </p>

                <p class="text-3xl font-bold text-gray-800 mt-1">
                    {{ number_format($subscriptions->avg('price'), 2, ',', ' ') }} €
                </p>
            </div>

            <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center">

                <span class="text-xl font-bold text-[rgb(233,29,40)]">
                    €
                </span>

            </div>

        </div>
    </div>

</div>


{{-- MESSAGES --}}

@if(session('success'))

    <div class="mb-6 px-5 py-4 rounded-lg bg-green-50 border border-green-200 text-green-700">

        <div class="flex items-center gap-2">

            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-5 h-5"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">

                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 13l4 4L19 7" />

            </svg>

            {{ session('success') }}

        </div>

    </div>

@endif


@if(session('error'))

    <div class="mb-6 px-5 py-4 rounded-lg bg-red-50 border border-red-200 text-red-700">

        <div class="flex items-center gap-2">

            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-5 h-5"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">

                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M6 18L18 6M6 6l12 12" />

            </svg>

            {{ session('error') }}

        </div>

    </div>

@endif


{{-- TABLEAU --}}

<div class="card-white p-4">

    <div class="table-wrapper">

        <table class="min-w-full table-rounded divide-y divide-gray-200">

            <thead>
                <tr>

                    <th class="px-6 py-3 text-left">
                        #
                    </th>

                    <th class="px-6 py-3 text-left">
                        Abonnement
                    </th>

                    <th class="px-6 py-3 text-left">
                        Prix
                    </th>

                    <th class="px-6 py-3 text-left">
                        Description
                    </th>

                    <th class="px-6 py-3 text-left">
                        Utilisateurs
                    </th>

                    <th class="px-6 py-3 text-right">
                        Action
                    </th>

                </tr>
            </thead>


            <tbody class="divide-y divide-gray-200">

                @forelse ($subscriptions as $subscription)

                    <tr>

                        {{-- ID --}}
                        <td class="px-6 py-4">
                            {{ $subscription->id }}
                        </td>


                        {{-- NOM --}}
                        <td class="px-6 py-4">

                            <div class="font-semibold text-gray-800">
                                {{ $subscription->name }}
                            </div>

                            @if($subscription->slug)

                                <div class="text-xs text-gray-400 mt-1">
                                    {{ $subscription->slug }}
                                </div>

                            @endif

                        </td>


                        {{-- PRIX --}}
                        <td class="px-6 py-4">

                            <span class="font-semibold text-[rgb(233,29,40)]">
                                {{ number_format($subscription->price, 2, ',', ' ') }} €
                            </span>

                            <span class="text-sm text-gray-500">
                                / mois
                            </span>

                        </td>


                        {{-- DESCRIPTION --}}
                        <td class="px-6 py-4 text-gray-600 max-w-md">

                            @if($subscription->description)

                                {{ Str::limit($subscription->description, 100) }}

                            @else

                                <span class="text-gray-400">
                                    -
                                </span>

                            @endif

                        </td>


                        {{-- UTILISATEURS --}}
                        <td class="px-6 py-4">

                            <span class="inline-flex items-center px-3 py-1 rounded-full
                                         bg-red-50 text-[rgb(233,29,40)] text-sm font-medium">

                                {{ $subscription->users_count }}

                                utilisateur{{ $subscription->users_count > 1 ? 's' : '' }}

                            </span>

                        </td>


                        {{-- ACTION --}}
                        <td class="px-6 py-4 text-right">

                            <a href="{{ route('admin.subscriptions.create') }}"
                               class="inline-flex items-center px-4 py-2 rounded-lg
                                      text-sm font-medium text-white
                                      bg-[rgb(233,29,40)]
                                      hover:opacity-90 transition">

                                Offrir

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="6" class="px-6 py-12 text-center">

                            <div class="flex flex-col items-center">

                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-12 h-12 text-gray-300 mb-3"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4" />

                                </svg>

                                <p class="text-gray-500 font-medium">
                                    Aucun abonnement trouvé.
                                </p>

                            </div>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection