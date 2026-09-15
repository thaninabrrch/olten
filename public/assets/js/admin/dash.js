/**
 * Espace admin : comportement de la sidebar.
 *  - < 1024px : la sidebar est un tiroir (ouverture burger, fermeture croix,
 *    fond sombre, touche Echap, clic sur un lien).
 *  - >= 1024px : la sidebar est fixe et peut etre repliee en barre d'icones
 *    (preference memorisee dans le navigateur).
 */
document.addEventListener('DOMContentLoaded', () => {
    const root = document.documentElement;
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const openBtn = document.getElementById('sidebar-toggle');
    const closeBtn = document.getElementById('sidebar-close');
    const collapseBtn = document.getElementById('sidebar-collapse');

    if (!sidebar) return;

    const desktopQuery = window.matchMedia('(min-width: 1024px)');
    const COLLAPSE_KEY = 'admin-sidebar-collapsed';

    // Fait defiler la sidebar pour que le lien actif soit visible (petits ecrans)
    function revealActiveLink() {
        const active = sidebar.querySelector('.sidebar-link.is-active');
        if (!active) return;
        const headH = sidebar.querySelector('.admin-sidebar__head')?.offsetHeight || 0;
        const top = active.offsetTop;
        const bottom = top + active.offsetHeight;
        const viewTop = sidebar.scrollTop + headH;
        const viewBottom = sidebar.scrollTop + sidebar.clientHeight;
        if (top < viewTop || bottom > viewBottom) {
            const visibleH = sidebar.clientHeight - headH;
            sidebar.scrollTop = top - headH - (visibleH - active.offsetHeight) / 2;
        }
    }

    // --- Tiroir mobile / tablette ---

    function openDrawer() {
        root.classList.add('sidebar-open');
        openBtn?.setAttribute('aria-expanded', 'true');
        revealActiveLink();
        // Focus dans le tiroir apres l'animation d'entree
        setTimeout(() => (sidebar.querySelector('.is-active') || closeBtn)?.focus({ preventScroll: true }), 200);
    }

    function closeDrawer({ restoreFocus = true } = {}) {
        if (!root.classList.contains('sidebar-open')) return;
        root.classList.remove('sidebar-open');
        openBtn?.setAttribute('aria-expanded', 'false');
        if (restoreFocus && !desktopQuery.matches) openBtn?.focus({ preventScroll: true });
    }

    openBtn?.addEventListener('click', openDrawer);
    closeBtn?.addEventListener('click', () => closeDrawer());
    backdrop?.addEventListener('click', () => closeDrawer());

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeDrawer();
    });

    // Un lien choisi dans le tiroir le referme (la page suivante se charge derriere)
    sidebar.querySelectorAll('a.sidebar-link').forEach((link) => {
        link.addEventListener('click', () => {
            if (!desktopQuery.matches) closeDrawer({ restoreFocus: false });
        });
    });

    // --- Repli desktop ---

    function applyCollapsed(collapsed) {
        root.classList.toggle('sidebar-collapsed', collapsed);
        collapseBtn?.setAttribute('aria-expanded', String(!collapsed));
        collapseBtn?.setAttribute('aria-label', collapsed ? 'Déplier le menu' : 'Réduire le menu');

        // Infobulle native sur les icones uniquement quand les libelles sont caches
        sidebar.querySelectorAll('.sidebar-link[data-label]').forEach((link) => {
            if (collapsed && desktopQuery.matches) {
                link.setAttribute('title', link.dataset.label);
            } else {
                link.removeAttribute('title');
            }
        });
    }

    collapseBtn?.addEventListener('click', () => {
        const collapsed = !root.classList.contains('sidebar-collapsed');
        applyCollapsed(collapsed);
        try {
            localStorage.setItem(COLLAPSE_KEY, collapsed ? '1' : '0');
        } catch (e) {}
    });

    applyCollapsed(root.classList.contains('sidebar-collapsed'));
    if (desktopQuery.matches) revealActiveLink();

    // Passage mobile <-> desktop : on ne laisse jamais un tiroir ouvert ni le scroll bloque
    desktopQuery.addEventListener('change', () => {
        closeDrawer({ restoreFocus: false });
        applyCollapsed(root.classList.contains('sidebar-collapsed'));
    });

    // Active les transitions seulement apres le premier rendu (pas d'animation au chargement)
    requestAnimationFrame(() => root.classList.add('sidebar-ready'));
});
