/*
 | Assistant « Rediger avec l'IA » (components/ai-description.blade.php).
 |
 | Il lit les champs deja remplis du formulaire (titre, categorie, prix, texte
 | de l'editeur), demande une suggestion a `ai.description`, puis la montre.
 | Le membre l'utilise (elle remplace la description, et l'apercu s'il le
 | choisit) ou en demande une autre version. La suggestion arrive du serveur
 | deja reduite a quelques balises simples (DescriptionWriter::cleanHtml).
 |
 | Les editeurs de texte sont ceux de assets/js/ckeditor.js
 | (window.oltenEditors) ; sans eux, on lit et ecrit le <textarea>.
 */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-aid]').forEach(setupAssistant);
});

function setupAssistant(root) {
    const form = root.closest('form');
    if (!form) return;

    const openBtn      = root.querySelector('[data-aid-open]');
    const panel        = root.querySelector('[data-aid-panel]');
    const notes        = root.querySelector('[data-aid-notes]');
    const errorBox     = root.querySelector('[data-aid-error]');
    const loading      = root.querySelector('[data-aid-loading]');
    const result       = root.querySelector('[data-aid-result]');
    const summaryOut   = root.querySelector('[data-aid-summary]');
    const descOut      = root.querySelector('[data-aid-description]');
    const replaceBox   = root.querySelector('[data-aid-replace-summary]');
    const runButtons   = Array.from(root.querySelectorAll('[data-aid-run], [data-aid-again], [data-aid-use]'));
    const improveBtn   = root.querySelector('[data-aid-run="improve"]');

    // Champs du formulaire : une annonce a un titre et un prix par jour, un
    // produit un nom et un prix.
    const title    = form.querySelector('#title, #name');
    const category = form.querySelector('#category_id');
    const price    = form.querySelector('#price_per_day, #price');
    const hasSummary = !!form.querySelector('#summary');

    let lastMode   = 'write';
    let suggestion = null;
    let busy       = false;

    root.querySelectorAll('[data-aid-summary-block]').forEach(function (el) {
        el.hidden = !hasSummary;
    });

    // ---- Lecture / ecriture des champs de texte ----
    function editor(id) {
        return (window.oltenEditors || {})[id] || null;
    }

    function getText(id) {
        const ed = editor(id);
        if (ed) return ed.getData();
        const field = form.querySelector('#' + id);
        return field ? field.value : '';
    }

    function setText(id, html) {
        const ed = editor(id);
        if (ed) {
            ed.setData(html);
            return;
        }
        const field = form.querySelector('#' + id);
        if (field) field.value = html;
    }

    function isEmpty(html) {
        return html.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim() === '';
    }

    // ---- Ouverture / fermeture ----
    function open() {
        panel.hidden = false;
        openBtn.setAttribute('aria-expanded', 'true');
        openBtn.hidden = true;
        refresh();
        notes.focus();
    }

    function close() {
        panel.hidden = true;
        openBtn.hidden = false;
        openBtn.setAttribute('aria-expanded', 'false');
        openBtn.focus();
    }

    // « Ameliorer » n'a de sens que s'il y a deja un texte
    function refresh() {
        if (!busy) improveBtn.disabled = isEmpty(getText('description'));
    }

    openBtn.addEventListener('click', open);
    root.querySelector('[data-aid-close]').addEventListener('click', close);

    // ---- Demande de suggestion ----
    function setBusy(state) {
        busy = state;
        loading.hidden = !state;
        runButtons.forEach(function (btn) { btn.disabled = state; });
        if (!state) refresh();
    }

    function showError(message) {
        errorBox.textContent = message;
        errorBox.hidden = false;
    }

    async function run(mode) {
        if (busy) return;

        lastMode = mode;
        errorBox.hidden = true;
        result.hidden = true;
        setBusy(true);

        const body = new FormData();
        body.append('target', root.dataset.target);
        body.append('mode', mode);
        body.append('title', title ? title.value : '');
        body.append('notes', notes.value);
        body.append('current', getText('description'));
        if (category && category.value) body.append('category_id', category.value);
        if (price && price.value !== '') body.append('price', price.value);

        try {
            const res = await fetch(root.dataset.url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: body
            });

            const data = await res.json().catch(function () { return {}; });

            if (!res.ok) {
                const firstError = data.errors ? Object.values(data.errors)[0]?.[0] : null;
                showError(res.status === 429
                    ? 'Vous avez demandé beaucoup de suggestions : patientez une minute avant de recommencer.'
                    : (firstError || data.message || 'L’assistant n’a pas pu répondre. Réessayez dans un instant.'));
                return;
            }

            suggestion = data;
            summaryOut.textContent = data.summary;
            descOut.innerHTML = data.description;
            // L'apercu deja ecrit n'est remplace que si le membre le demande
            replaceBox.checked = hasSummary && isEmpty(getText('summary'));
            result.hidden = false;
        } catch (err) {
            console.error(err);
            showError('Connexion impossible. Vérifiez votre réseau, puis réessayez.');
        } finally {
            setBusy(false);
        }
    }

    root.querySelectorAll('[data-aid-run]').forEach(function (btn) {
        btn.addEventListener('click', function () { run(btn.dataset.aidRun); });
    });

    root.querySelector('[data-aid-again]').addEventListener('click', function () { run(lastMode); });

    // ---- Utiliser la suggestion ----
    root.querySelector('[data-aid-use]').addEventListener('click', function () {
        if (!suggestion) return;

        setText('description', suggestion.description);

        if (hasSummary && replaceBox.checked) {
            const p = document.createElement('p');
            p.textContent = suggestion.summary;
            setText('summary', p.outerHTML);
        }

        result.hidden = true;
        suggestion = null;
        close();

        const ed = editor('description');
        const target = ed ? ed.ui.view.element : form.querySelector('#description');
        target?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
}
