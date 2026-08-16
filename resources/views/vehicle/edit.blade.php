@extends('layouts.connected')
@section('title', 'Mon véhicule | ' . config('app.name'))

@section('content')
    <section class="tab-content active animate-fade">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
                <div>
                    <nav aria-label="Breadcrumb" class="mb-4">
                        <ol class="flex items-center space-x-2 text-xs font-bold uppercase tracking-widest">
                            <li><a href="{{ route('dashboard') }}"
                                    class="text-slate-400 hover:text-[#ff3c00] transition-colors">Espace Chauffeur</a></li>
                            <li><i data-lucide="chevron-right" class="w-3 h-3 text-slate-300"></i></li>
                            <li class="text-slate-900">Configuration Véhicule</li>
                        </ol>
                    </nav>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tighter">Mon Véhicule</h1>
                    <p class="text-slate-500 mt-2 max-w-xl">Renseignez les détails techniques de votre véhicule pour la
                        validation de votre profil VTC.</p>
                </div>

            </div>

            <!-- Professional Form Card -->
            <form action="{{ route('vehicle.update') }}" method="POST" enctype="multipart/form-data"
                class="relative group">
                @csrf

                <div class="bg-white rounded-[3rem] shadow-2xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                    <div class="p-8 sm:p-12">

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">

                            <!-- Left Column: Primary Info -->
                            <div class="lg:col-span-7 space-y-10">
                                <div class="flex items-center gap-4 mb-2">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-[#ff3c00]/10 flex items-center justify-center text-[#ff3c00]">
                                        <i data-lucide="info" class="w-5 h-5"></i>
                                    </div>
                                    <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">Informations
                                        Générales</h2>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label
                                            class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Marque
                                            du véhicule</label>
                                        <input type="text" name="marque" placeholder="ex: Mercedes-Benz"
                                            value="{{ old('marque', $vehicle->marque ?? '') }}"
                                            class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-[1.5rem] focus:ring-4 focus:ring-orange-500/10 focus:border-[#ff3c00] focus:bg-white transition-all outline-none font-bold text-slate-800">
                                    </div>

                                    <div class="space-y-2">
                                        <label
                                            class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Modèle
                                            précis</label>
                                        <input type="text" name="modele" placeholder="ex: Classe E"
                                            value="{{ old('modele', $vehicle->modele ?? '') }}"
                                            class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-[1.5rem] focus:ring-4 focus:ring-orange-500/10 focus:border-[#ff3c00] focus:bg-white transition-all outline-none font-bold text-slate-800">
                                    </div>

                                    <div class="space-y-2">
                                        <label
                                            class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Immatriculation</label>
                                        <input type="text" name="immatriculation" placeholder="AA-123-BB"
                                            value="{{ old('immatriculation', $vehicle->immatriculation ?? '') }}"
                                            class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-[1.5rem] focus:ring-4 focus:ring-orange-500/10 focus:border-[#ff3c00] focus:bg-white transition-all outline-none font-black text-slate-800 tracking-widest uppercase">
                                    </div>

                                    <div class="space-y-2">
                                        <label
                                            class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Année
                                            de mise en circulation</label>
                                        <input type="number" name="annee" placeholder="2024"
                                            value="{{ old('annee', $vehicle->annee ?? '') }}"
                                            class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-[1.5rem] focus:ring-4 focus:ring-orange-500/10 focus:border-[#ff3c00] focus:bg-white transition-all outline-none font-bold text-slate-800">
                                    </div>

                                    <div class="space-y-2">
                                        <label
                                            class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Couleur
                                            dominante</label>
                                        <input type="text" name="couleur" placeholder="ex: Noir Obsidienne"
                                            value="{{ old('couleur', $vehicle->couleur ?? '') }}"
                                            class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-[1.5rem] focus:ring-4 focus:ring-orange-500/10 focus:border-[#ff3c00] focus:bg-white transition-all outline-none font-bold text-slate-800">
                                    </div>

                                    <div class="space-y-2">
                                        <label
                                            class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Nombre
                                            de places</label>
                                        <input type="number" name="places" min="1" max="9" placeholder="4"
                                            value="{{ old('places', $vehicle->places ?? 4) }}"
                                            class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-[1.5rem] focus:ring-4 focus:ring-orange-500/10 focus:border-[#ff3c00] focus:bg-white transition-all outline-none font-bold text-slate-800">
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <label
                                        class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Motorisation</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        @foreach ([['id' => 'thermique', 'label' => 'Thermique', 'icon' => 'fuel'], ['id' => 'hybride', 'label' => 'Hybride', 'icon' => 'zap-off'], ['id' => 'electrique', 'label' => 'Électrique', 'icon' => 'zap']] as $type)
                                            <label class="relative cursor-pointer group/type">
                                                <input type="radio" name="type" value="{{ $type['id'] }}"
                                                    class="peer hidden"
                                                    {{ old('type', $vehicle->type ?? '') === $type['id'] ? 'checked' : '' }}>
                                                <div
                                                    class="p-4 border-2 border-slate-100 rounded-2xl transition-all duration-300 peer-checked:border-[#ff3c00] peer-checked:bg-orange-50/50 flex flex-col items-center gap-2 group-hover/type:border-slate-300">
                                                    <i data-lucide="{{ $type['icon'] }}"
                                                        class="w-5 h-5 text-slate-400 peer-checked:text-[#ff3c00] transition-colors"></i>
                                                    <span
                                                        class="text-[10px] font-black uppercase tracking-widest text-slate-600 peer-checked:text-slate-900">{{ $type['label'] }}</span>
                                                </div>
                                                <div
                                                    class="absolute -top-2 -right-2 w-5 h-5 bg-[#ff3c00] text-white rounded-full flex items-center justify-center scale-0 peer-checked:scale-100 transition-transform shadow-lg">
                                                    <i data-lucide="check" class="w-3 h-3"></i>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Visuals & Actions -->
                            <div class="lg:col-span-5 flex flex-col">
                                <div class="bg-slate-50 rounded-[2.5rem] p-8 border border-slate-100 flex flex-col h-full">
                                    <div class="flex items-center gap-4 mb-6">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white">
                                            <i data-lucide="camera" class="w-5 h-5"></i>
                                        </div>
                                        <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">Visuel du
                                            véhicule</h2>
                                    </div>

                                    <div id="drop-zone" class="relative flex-1 min-h-[250px] group/upload">
                                        <label
                                            class="flex flex-col items-center justify-center w-full h-full border-2 border-dashed border-slate-200 rounded-[2rem] bg-white hover:border-[#ff3c00] transition-all cursor-pointer overflow-hidden group">

                                            @if (!empty($vehicle?->photo))
                                                <img src="{{ asset('storage/' . $vehicle->photo) }}" id="image-preview"
                                                    class="absolute inset-0 w-full h-full object-cover opacity-100 transition-opacity">
                                                <div
                                                    class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                                                    <p class="text-white text-[10px] font-black uppercase tracking-widest">
                                                        Changer la photo</p>
                                                </div>
                                            @else
                                                <div id="upload-placeholder" class="flex flex-col items-center">
                                                    <div
                                                        class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mb-4 transition-transform group-hover:scale-110">
                                                        <i data-lucide="image-plus" class="w-8 h-8 text-slate-300"></i>
                                                    </div>
                                                    <p class="text-sm font-black text-slate-900 uppercase tracking-tight">
                                                        Photo véhicule</p>
                                                    <p
                                                        class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-2">
                                                        Format 3:2 • Max 5Mo</p>
                                                </div>
                                                <img id="image-preview"
                                                    class="absolute inset-0 w-full h-full object-cover opacity-0">
                                            @endif

                                            <input type="file" name="photo" id="photo-input" class="hidden"
                                                accept="image/*" />
                                        </label>
                                    </div>

                                    <div class="mt-8 flex justify-end">
                                        <button type="submit"
                                            class="px-8 py-4 bg-slate-900 text-white rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] hover:bg-[#ff3c00] transition-all shadow-xl shadow-slate-200 group flex items-center gap-3">
                                            Enregistrer
                                            <i data-lucide="save"
                                                class="w-4 h-4 group-hover:scale-110 transition-transform"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Footer Help -->
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-between px-8 text-slate-400">
                <div class="flex items-center gap-2 mb-4 sm:mb-0">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest">Données sécurisées (RGPD)</span>
                </div>
       
            </div>
        </div>
    </section>

    <style>
        .animate-fade {
            animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        input:focus::placeholder {
            color: transparent;
        }

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();

        const photoInput = document.getElementById('photo-input');
        const imagePreview = document.getElementById('image-preview');
        const uploadPlaceholder = document.getElementById('upload-placeholder');

        photoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.classList.replace('opacity-0', 'opacity-100');
                    if (uploadPlaceholder) uploadPlaceholder.classList.add('hidden');
                }
                reader.readAsDataURL(file);
            }
        });

        const dropZone = document.getElementById('drop-zone');
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
    </script>
@endsection
