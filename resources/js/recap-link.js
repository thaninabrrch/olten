/*
 | Récapitulatif du trajet : met à jour le total, le bouton « Réserver »
 | et le lien de paiement selon les sens cochés.
 | S'appuie sur les attributs data-cvd-* déjà présents dans ton markup.
 | Le total affiché ici est le prix conducteur ; les frais de service
 | sont ajoutés sur la page de paiement.
 */
(function () {
    const recap = document.querySelector('[data-cvd-recap]');
    if (!recap) return;

    const checks  = recap.querySelectorAll('[data-cvd-leg]');
    const totalEl = recap.querySelector('[data-cvd-total]');
    const bookBtn = recap.querySelector('[data-cvd-book]');
    const hint    = recap.querySelector('[data-cvd-hint]');
    const baseUrl = bookBtn.dataset.cvdHref;

    const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n) + '€';

    function refresh() {
        const selected = [...checks].filter(c => c.checked);
        const total    = selected.reduce((sum, c) => sum + parseFloat(c.dataset.cvdPrice || 0), 0);
        const legs     = selected.map(c => c.dataset.cvdLeg);

        totalEl.textContent = fmt(total);

        if (legs.length === 0) {
            bookBtn.removeAttribute('href');
            bookBtn.setAttribute('aria-disabled', 'true');
            bookBtn.style.pointerEvents = 'none';
            bookBtn.style.opacity = '.5';
            if (hint) hint.hidden = false;
            return;
        }

        bookBtn.href = baseUrl + '?legs=' + encodeURIComponent(legs.join(','));
        bookBtn.removeAttribute('aria-disabled');
        bookBtn.style.pointerEvents = '';
        bookBtn.style.opacity = '';
        if (hint) hint.hidden = true;
    }

    checks.forEach(c => c.addEventListener('change', refresh));
    refresh();
})();

/*
 | Blade : dans l'aside, remplace la ligne $bookUrl par
 |     @php $bookUrl = route('trips.checkout', $trip); @endphp
 | Le passage par le login (data-auth-required) reste inchangé. Si ton auth.js
 | renvoie vers l'URL de base, vérifie qu'il conserve bien le ?legs=… après connexion.
 */