@extends('layouts.connected')
@section('title', 'Tableau de bord | ' . config('app.name'))

@section('content')
    <section class="tab-content active animate-fade">
        @php
            $identityDoc = auth()->user()->documents->where('name', 'identity_card')->first();
            $vtcCard = auth()->user()->documents->where('name', 'vtc_card')->first();
        @endphp
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <!-- Breadcrumb -->
                <nav aria-label="Breadcrumb" class="flex-1">
                    <ol class="flex items-center space-x-2 text-sm font-medium">
                        <li><a href="#" class="text-slate-400 hover:text-slate-600 transition-colors">Espace Driver</a>
                        </li>
                        <li><i data-lucide="chevron-right" class="w-4 h-4 text-slate-300"></i></li>
                        <li class="text-slate-900 font-bold">Certification VTC</li>
                    </ol>
                </nav>

                <!-- Bouton à droite -->
                <button id="openUploadModal"
                    class="py-3 px-6 bg-[#ff3c00] hover:bg-black text-white rounded-[2rem] font-black uppercase tracking-widest text-xs transition-all duration-300 shadow-lg shadow-orange-200 flex items-center justify-center gap-2 whitespace-nowrap">
                    <i data-lucide="upload" class="w-4 h-4"></i>
                    Mettre à jour mes documents
                </button>
            </div>


            <div class="max-w-7xl mx-auto">
                <div class="mb-10">
                    <p class="text-slate-500 mt-2">Vérifiez l'état de vos documents légaux et mettez-les à jour pour rester
                        actif.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    @php
                        $docs = [
                            [
                                'name' => 'identity_card',
                                'label' => "Pièce d'identité",
                                'desc' => 'CNI ou Passeport valide (Recto/Verso)',
                                'icon' => 'contact-2',
                            ],
                            [
                                'name' => 'vtc_card',
                                'label' => 'Carte VTC',
                                'desc' => 'Carte professionnelle en cours de validité',
                                'icon' => 'award',
                            ],
                        ];
                    @endphp

                    @foreach ($docs as $doc)
                        @php $file = auth()->user()->documents->where('name', $doc['name'])->first(); @endphp

                        <div
                            class="group bg-white rounded-[2.5rem] p-8 border {{ $file && $file->status === 'rejected' ? 'border-red-200 bg-red-50/30' : 'border-slate-200' }} shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between min-h-[320px]">
                            <div>
                                <div class="flex justify-between items-start mb-6">
                                    <div
                                        class="w-14 h-14 rounded-2xl bg-white shadow-sm flex items-center justify-center {{ $file && $file->status === 'rejected' ? 'text-red-500' : 'text-slate-400' }}">
                                        <i data-lucide="{{ $doc['icon'] }}" class="w-7 h-7"></i>
                                    </div>

                                    @if ($file)
                                        <div
                                            class="px-4 py-1.5 rounded-full flex items-center gap-2 
                        {{ $file->status === 'approved'
                            ? 'bg-green-50 text-green-600'
                            : ($file->status === 'rejected'
                                ? 'bg-red-100 text-red-600'
                                : 'bg-amber-50 text-amber-600') }}">
                                            <i data-lucide="{{ $file->status === 'approved' ? 'check-circle-2' : ($file->status === 'rejected' ? 'alert-circle' : 'timer') }}"
                                                class="w-4 h-4"></i>
                                            <span class="text-[10px] font-black uppercase tracking-widest">
                                                {{ $file->status === 'approved' ? 'Validé' : ($file->status === 'rejected' ? 'Refusé' : 'En examen') }}
                                            </span>
                                        </div>
                                    @else
                                        <div
                                            class="px-4 py-1.5 rounded-full bg-slate-100 text-slate-500 flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-slate-400 animate-pulse"></span>
                                            <span class="text-[10px] font-black uppercase tracking-widest">Manquant</span>
                                        </div>
                                    @endif
                                </div>

                                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight">{{ $doc['label'] }}
                                </h3>

                                @if ($file && $file->status === 'rejected' && $file->rejection_reason)
                                    <div class="mt-4 p-4 bg-red-100/50 rounded-2xl border border-red-200">
                                        <p
                                            class="text-[10px] font-black uppercase text-red-600 mb-1 flex items-center gap-1">
                                            <i data-lucide="info" class="w-3 h-3"></i> Motif du refus :
                                        </p>
                                        <p class="text-xs text-red-700 leading-relaxed font-medium italic">
                                            "{{ $file->rejection_reason }}"</p>
                                    </div>
                                @else
                                    <p class="text-slate-400 text-sm mt-2 leading-relaxed">{{ $doc['desc'] }}</p>
                                @endif
                            </div>

                            <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between">
                                @if ($file)
                                    <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank"
                                        class="flex items-center gap-2 text-slate-500 hover:text-slate-900 font-bold text-sm transition-colors">
                                        <i data-lucide="eye" class="w-4 h-4"></i> Consulter
                                    </a>
                                    <button onclick="document.getElementById('openUploadModal').click()"
                                        class="p-2 {{ $file->status === 'rejected' ? 'bg-red-500 text-white' : 'hover:bg-slate-100 text-slate-400' }} rounded-xl transition-all shadow-sm"
                                        title="Mettre à jour">
                                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                    </button>
                                @else
                                    <button onclick="document.getElementById('openUploadModal').click()"
                                        class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-[#ff3c00] transition-all">
                                        Envoyer le fichier
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div
                        class="bg-blue-600 rounded-[2.5rem] p-8 text-white relative overflow-hidden flex flex-col justify-between shadow-lg shadow-blue-200">
                        <i data-lucide="help-circle"
                            class="absolute -right-6 -top-6 w-40 h-40 text-white/10 -rotate-12"></i>

                        <div class="relative z-10">
                            <div
                                class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center mb-6">
                                <i data-lucide="message-circle" class="w-6 h-6"></i>
                            </div>
                            <h4 class="text-2xl font-black tracking-tight mb-2">Besoin d'aide ?</h4>
                            <p class="text-blue-100 text-sm leading-relaxed opacity-80">Nos équipes vérifient vos documents
                                sous 24h à 48h ouvrées.</p>
                        </div>

                        <a href="{{ route('contact') }}"
                            class="relative z-10 flex items-center justify-between group mt-8">
                            <span class="font-black text-xs uppercase tracking-[0.2em]">Contacter le support</span>
                            <div
                                class="w-10 h-10 bg-white text-blue-600 rounded-full flex items-center justify-center group-hover:translate-x-2 transition-transform">
                                <i data-lucide="arrow-right" class="w-5 h-5"></i>
                            </div>
                        </a>

                    </div>
                </div>
            </div>
        </div>

        <div id="uploadModal" class="fixed inset-0 z-[100] hidden">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md transition-opacity"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div
                    class="bg-white w-full max-w-lg rounded-[3rem] p-10 shadow-2xl transform transition-all animate-in fade-in zoom-in duration-300">
                    <div class="flex justify-between items-center mb-10">
                        <h3 class="text-3xl font-black text-slate-900 tracking-tighter">Importer vos documents</h3>
                        <button id="closeUploadModal"
                            class="p-3 bg-slate-100 rounded-2xl hover:bg-red-50 hover:text-red-500 transition-colors">
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </div>

                    <form action="{{ route('documents.upload') }}" method="POST" enctype="multipart/form-data"
                        class="space-y-8">
                        @csrf
                        <div class="space-y-3">
                            <label
                                class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-2">Séléctionnez
                                le type</label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="cursor-pointer group">
                                    <input type="radio" name="document_type" value="identity_card" class="peer hidden"
                                        required>
                                    <div
                                        class="p-4 border-2 border-slate-100 rounded-2xl group-hover:border-slate-200 peer-checked:border-[#ff3c00] peer-checked:bg-orange-50 transition-all text-center">
                                        <i data-lucide="contact-2"
                                            class="w-6 h-6 mx-auto mb-2 text-slate-400 group-hover:text-slate-600 peer-checked:text-[#ff3c00]"></i>
                                        <span class="text-xs font-bold text-slate-600">Identité</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="document_type" value="vtc_card" class="peer hidden">
                                    <div
                                        class="p-4 border-2 border-slate-100 rounded-2xl group-hover:border-slate-200 peer-checked:border-[#ff3c00] peer-checked:bg-orange-50 transition-all text-center">
                                        <i data-lucide="award"
                                            class="w-6 h-6 mx-auto mb-2 text-slate-400 group-hover:text-slate-600 peer-checked:text-[#ff3c00]"></i>
                                        <span class="text-xs font-bold text-slate-600">Carte VTC</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div id="drop-area" class="relative group">
                            <label
                                class="flex flex-col items-center justify-center w-full h-56 border-3 border-dashed border-slate-200 rounded-[2.5rem] bg-slate-50 hover:bg-white hover:border-[#ff3c00] transition-all cursor-pointer">
                                <div
                                    class="flex flex-col items-center justify-center text-slate-400 group-hover:text-[#ff3c00]">
                                    <div
                                        class="w-16 h-16 bg-white rounded-3xl shadow-sm flex items-center justify-center mb-4 transition-transform group-hover:scale-110">
                                        <i data-lucide="cloud-upload" class="w-8 h-8"></i>
                                    </div>
                                    <p class="text-sm font-black uppercase tracking-tight">Glissez votre fichier ici</p>
                                    <p class="text-[10px] mt-1 opacity-60">PDF, JPG, PNG jusqu'à 5 Mo</p>
                                </div>
                                <input type="file" name="file" id="file-input" class="hidden" required />
                            </label>
                            <div id="file-preview"
                                class="hidden absolute inset-0 bg-white rounded-[2.5rem] flex items-center justify-center border-2 border-[#ff3c00]">
                                <p class="text-sm font-bold text-[#ff3c00] flex items-center gap-2">
                                    <i data-lucide="file-check" class="w-5 h-5"></i> <span id="file-name"></span>
                                </p>
                            </div>
                        </div>

                        <button
                            class="w-full py-4 rounded-[2rem] bg-slate-900 text-white font-black uppercase tracking-widest text-xs hover:bg-black hover:shadow-2xl transition-all shadow-xl">
                            Confirmer l'envoi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <style>
        .vtc-card-gradient {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }

        .animate-fade {
            animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        // Cette commande transforme les <i data-lucide="..."> en véritables icônes SVG
        lucide.createIcons();
    </script>
    <script>
        const modal = document.getElementById('uploadModal');
        const openBtn = document.getElementById('openUploadModal');
        const closeBtn = document.getElementById('closeUploadModal');
        const fileInput = document.getElementById('file-input');
        const preview = document.getElementById('file-preview');
        const fileNameDisplay = document.getElementById('file-name');

        const toggleModal = (show) => {
            modal.classList.toggle('hidden', !show);
            document.body.style.overflow = show ? 'hidden' : 'auto';
        };

        openBtn.addEventListener('click', () => toggleModal(true));
        closeBtn.addEventListener('click', () => toggleModal(false));

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                preview.classList.remove('hidden');
                fileNameDisplay.textContent = e.target.files[0].name;
            }
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) toggleModal(false);
        });
    </script>
@endsection
