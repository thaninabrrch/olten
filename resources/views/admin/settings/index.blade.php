{{-- resources/views/admin/settings/index.blade.php --}}

@extends('admin.layouts.app')



@section('title', 'Paramètres')
@section('page_title', 'Paramètres')

@section('content')

    <div class="space-y-6">

        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Paramètres
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                Gérez la configuration de la plateforme.
            </p>
        </div>

        {{-- Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">

            {{-- Types Services --}}
            <a href="{{ route('admin.type_services.index') }}"
                class="group bg-white border border-gray-200 rounded-2xl p-5
                   hover:border-red-500 hover:shadow-md transition duration-200">

                <div class="flex items-start justify-between">

                    <div class="flex items-center gap-4">

                        <div
                            class="w-12 h-12 rounded-xl bg-red-100
                                flex items-center justify-center
                                group-hover:bg-red-600 transition duration-200">

                            <i
                                class="bi bi-grid-fill text-red-600 text-lg
                                  group-hover:text-white"></i>
                        </div>

                        <div>
                            <h3 class="font-semibold text-gray-800">
                                Types de service
                            </h3>

                            <p class="text-sm text-gray-500 mt-1">
                                Configurer les types de service.
                            </p>
                        </div>

                    </div>

                    <i
                        class="bi bi-chevron-right text-gray-400
                          group-hover:text-red-600 transition"></i>

                </div>
            </a>

            {{-- Exemple future card --}}
            <div
                class="bg-gray-50 border border-dashed border-gray-300
                    rounded-2xl p-5 flex items-center justify-center">

                <p class="text-sm text-gray-400">
                    Nouvelle configuration bientôt...
                </p>

            </div>

        </div>

    </div>

@endsection
