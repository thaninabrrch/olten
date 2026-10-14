{{--
    Assistant « Rédiger avec l'IA », sous le champ Description d'un formulaire
    d'annonce ou de produit.

    Il reprend ce que le membre a deja saisi (titre, categorie, prix, texte de
    l'editeur) et ce qu'il veut mettre en avant, l'envoie a `ai.description`
    (App\Support\DescriptionWriter) et montre la suggestion. Le membre la
    relit, complete les reperes entre crochets, puis l'utilise ou en demande
    une autre version. Rien n'est enregistre tant que le formulaire n'est pas
    envoye.

    Sans cle d'API Gemini (config services.gemini.key), rien ne s'affiche.

        <x-ai-description target="annonce" />
        <x-ai-description target="produit" />

    Comportement : assets/js/ai-description.js. Styles : assets/css/ai-description.css.
--}}
@props(['target' => 'annonce'])

@if (\App\Support\DescriptionWriter::isEnabled())
<div class="aid" data-aid data-target="{{ $target }}" data-url="{{ route('ai.description') }}">
    <button type="button" class="aid-open" data-aid-open aria-expanded="false" aria-controls="aid-panel">
        <i class="fa-solid fa-wand-magic-sparkles"></i>
        <span>Rédiger avec l'IA</span>
    </button>

    <div class="aid-panel" id="aid-panel" data-aid-panel hidden>
        <div class="aid-head">
            <span class="aid-badge" aria-hidden="true"><i class="fa-solid fa-wand-magic-sparkles"></i></span>
            <span class="aid-head-text">
                <strong>Assistant de rédaction</strong>
                <small>Il s'appuie sur le titre, la catégorie et le prix déjà saisis.</small>
            </span>
            <button type="button" class="aid-close" data-aid-close aria-label="Fermer l'assistant">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <label class="aid-label" for="aid-notes">
            Ce que vous voulez mettre en avant <span>facultatif</span>
        </label>
        <textarea id="aid-notes" class="aid-notes" data-aid-notes rows="2" maxlength="1000"
                  placeholder="Ex. état, ce qui est inclus, vos disponibilités, ce que vous attendez…"></textarea>

        <div class="aid-actions">
            <button type="button" class="aid-btn is-primary" data-aid-run="write">
                <i class="fa-solid fa-pen-nib"></i> Rédiger une description
            </button>
            <button type="button" class="aid-btn" data-aid-run="improve" title="Corrige et structure le texte déjà écrit">
                <i class="fa-solid fa-spell-check"></i> Améliorer mon texte
            </button>
        </div>

        <p class="aid-error" data-aid-error role="alert" hidden></p>

        <div class="aid-loading" data-aid-loading hidden>
            <span class="aid-spinner" aria-hidden="true"></span> Rédaction en cours…
        </div>

        <div class="aid-result" data-aid-result hidden>
            <div class="aid-preview">
                <div data-aid-summary-block>
                    <span class="aid-preview-label">Aperçu</span>
                    <p class="aid-summary" data-aid-summary></p>
                </div>
                <span class="aid-preview-label">Description</span>
                <div class="aid-description" data-aid-description></div>
            </div>

            <label class="aid-check" data-aid-summary-block>
                <input type="checkbox" data-aid-replace-summary>
                Remplacer aussi l'aperçu
            </label>

            <p class="aid-hint">
                <i class="fa-solid fa-circle-info"></i>
                Relisez le texte et complétez les éléments entre crochets avant de publier.
            </p>

            <div class="aid-actions">
                <button type="button" class="aid-btn is-primary" data-aid-use>
                    <i class="fa-solid fa-check"></i> Utiliser ce texte
                </button>
                <button type="button" class="aid-btn" data-aid-again>
                    <i class="fa-solid fa-rotate"></i> Une autre version
                </button>
            </div>
        </div>
    </div>
</div>

@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/ai-description.css') }}?v={{ @filemtime(public_path('assets/css/ai-description.css')) ?: 1 }}">
    @endpush
    @push('scripts')
        <script src="{{ asset('assets/js/ai-description.js') }}?v={{ @filemtime(public_path('assets/js/ai-description.js')) ?: 1 }}"></script>
    @endpush
@endonce
@endif
