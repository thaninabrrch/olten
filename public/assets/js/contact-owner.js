// contact-owner.js
//
// Popin « Message » des fiches produit et annonce
// (resources/views/components/contact-owner.blade.php).
//
// Le message part en fetch : le membre reste sur la fiche et voit la
// confirmation sur place. Un texte commence puis abandonne (fermeture,
// clic a cote) est conserve jusqu'a l'envoi.

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.querySelector('[data-cm]');
    if (!modal) return;

    const card       = modal.querySelector('[data-cm-card]');
    const form       = modal.querySelector('[data-cm-form]');
    const done       = modal.querySelector('[data-cm-done]');
    const doneText   = modal.querySelector('[data-cm-done-text]');
    const convLink   = modal.querySelector('[data-cm-conversation]');
    const textarea   = modal.querySelector('[data-cm-text]');
    const counter    = modal.querySelector('[data-cm-count]');
    const alertBox   = modal.querySelector('[data-cm-alert]');
    const alertText  = alertBox.querySelector('span');
    const submitBtn  = modal.querySelector('[data-cm-submit]');
    const submitIcon = submitBtn.querySelector('i');
    const submitText = submitBtn.querySelector('span');
    const chips      = Array.from(modal.querySelectorAll('[data-cm-chip]'));
    const max        = textarea.maxLength > 0 ? textarea.maxLength : 2000;

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    // Sur ecran tactile, focaliser le champ ferait surgir le clavier par-dessus
    // la fiche de l'offre : on laisse le membre toucher le champ lui-meme.
    const finePointer = window.matchMedia('(pointer: fine)').matches;

    let lastFocused = null;
    let sending = false;
    let closeTimer = null;

    // Raccourci affiche selon le systeme
    if (/Mac|iPhone|iPad/.test(navigator.platform || navigator.userAgent)) {
        const mod = modal.querySelector('[data-cm-mod]');
        if (mod) mod.textContent = '⌘';
    }

    // ---- Ouverture / fermeture ----
    function open() {
        clearTimeout(closeTimer);
        lastFocused = document.activeElement;

        modal.hidden = false;
        document.body.classList.add('cm-open');

        // Une image plus tard, pour que la transition parte de l'etat ferme
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                modal.classList.add('is-open');
            });
        });

        refresh();

        if (!done.hidden) {
            done.focus();
        } else if (finePointer) {
            textarea.focus();
            textarea.setSelectionRange(textarea.value.length, textarea.value.length);
        } else {
            card.focus();
        }
    }

    function close() {
        if (modal.hidden) return;

        modal.classList.remove('is-open');
        document.body.classList.remove('cm-open');

        closeTimer = setTimeout(function () {
            modal.hidden = true;

            // Apres un envoi reussi, la prochaine ouverture repart d'un
            // message vierge ; sinon le brouillon reste en place.
            if (!done.hidden) reset();
        }, reduceMotion ? 0 : 240);

        lastFocused?.focus();
    }

    function reset() {
        form.reset();
        done.hidden = true;
        form.hidden = false;
        alertBox.hidden = true;
        refresh();
    }

    document.querySelectorAll('[data-cm-open]').forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            open();
        });
    });

    modal.querySelectorAll('[data-cm-close]').forEach(function (el) {
        el.addEventListener('click', close);
    });

    // Echap ferme ; Tab reste prisonnier de la popin tant qu'elle est ouverte
    document.addEventListener('keydown', function (e) {
        if (modal.hidden) return;

        if (e.key === 'Escape') {
            close();
            return;
        }

        if (e.key !== 'Tab') return;

        const focusables = Array.from(card.querySelectorAll(
            'a[href], button:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        )).filter(function (el) {
            return el.offsetParent !== null;
        });

        if (!focusables.length) return;

        const first = focusables[0];
        const last  = focusables[focusables.length - 1];

        if (e.shiftKey && (document.activeElement === first || document.activeElement === card)) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    });

    // ---- Champ : compteur, hauteur, bouton, questions deja posees ----
    function refresh() {
        const length = textarea.value.length;

        counter.textContent = length.toLocaleString('fr-FR') + ' / ' + max.toLocaleString('fr-FR');
        counter.classList.toggle('is-near', length >= max * .9 && length < max);
        counter.classList.toggle('is-full', length >= max);

        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight + 3, 260) + 'px';

        if (!sending) submitBtn.disabled = textarea.value.trim().length < 2;

        chips.forEach(function (chip) {
            const used = textarea.value.includes(chip.dataset.cmChip);
            chip.classList.toggle('is-used', used);
            chip.setAttribute('aria-pressed', used ? 'true' : 'false');
        });
    }

    textarea.addEventListener('input', function () {
        alertBox.hidden = true;
        refresh();
    });

    // Ctrl / Cmd + Entree envoie ; Entree seule va a la ligne
    textarea.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
            e.preventDefault();
            form.requestSubmit();
        }
    });

    // ---- Questions rapides ----
    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            const question = chip.dataset.cmChip;
            let value = textarea.value.replace(/\s+$/, '');

            if (!value.includes(question)) {
                if (value === '') value = textarea.dataset.cmGreeting || '';
                value = value ? value + '\n' + question : question;
                textarea.value = value.slice(0, max);
            }

            refresh();
            textarea.focus();
            textarea.setSelectionRange(textarea.value.length, textarea.value.length);
            textarea.scrollTop = textarea.scrollHeight;
        });
    });

    // ---- Envoi ----
    function showAlert(message) {
        alertText.textContent = message;
        alertBox.hidden = false;
        alertBox.scrollIntoView({ block: 'nearest', behavior: reduceMotion ? 'auto' : 'smooth' });
    }

    function setSending(state) {
        sending = state;
        textarea.disabled = state;
        submitBtn.disabled = state;
        submitBtn.classList.toggle('is-loading', state);
        submitIcon.className = state ? 'fa-solid fa-spinner' : 'fa-solid fa-paper-plane';
        submitText.textContent = state ? 'Envoi…' : 'Envoyer';
        if (!state) refresh();
    }

    // Retour sur la fiche apres connexion, popin rouverte
    function loginUrl() {
        const url = new URL(window.location.href);
        url.searchParams.set('contacter', '1');
        return url.toString();
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (sending) return;

        alertBox.hidden = true;

        if (textarea.value.trim().length < 2) {
            showAlert('Écrivez votre message avant de l’envoyer.');
            textarea.focus();
            return;
        }

        // FormData lit le champ avant qu'il soit desactive par setSending()
        const body = new FormData(form);
        setSending(true);

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: body
            });

            // Session expiree pendant la redaction : le texte reste dans le
            // champ, le membre se reconnecte puis revient sur la fiche.
            if (res.status === 401 || res.status === 419) {
                close();
                window.openAuthModal?.('login', loginUrl());
                return;
            }

            const data = await res.json().catch(function () { return {}; });

            if (!res.ok) {
                const firstError = data.errors ? Object.values(data.errors)[0]?.[0] : null;
                showAlert(firstError || data.message || 'Le message n’a pas pu être envoyé. Merci de réessayer.');
                return;
            }

            if (data.message) doneText.textContent = data.message;
            if (data.conversation_url && convLink) convLink.href = data.conversation_url;

            form.hidden = true;
            done.hidden = false;
            done.focus();
        } catch (err) {
            console.error(err);
            showAlert('Connexion impossible. Vérifiez votre réseau, votre message est conservé.');
        } finally {
            setSending(false);
        }
    });

    // ---- Retour de la popin de connexion : ?contacter=1 ----
    const params = new URLSearchParams(window.location.search);

    if (params.has('contacter')) {
        params.delete('contacter');
        const query = params.toString();
        history.replaceState(null, '', window.location.pathname + (query ? '?' + query : '') + window.location.hash);
        open();
    }
});
