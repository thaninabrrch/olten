/*
 | Cloche des notifications (<x-notification-bell />) : le bouton ouvre le
 | panneau, un clic en dehors ou Echap le referme. Presente dans le header
 | public et dans le header connecte.
 */
(function () {
    const bells = document.querySelectorAll('[data-notif]');
    if (!bells.length) return;

    function setOpen(bell, open) {
        bell.classList.toggle('is-open', open);
        bell.querySelector('[data-notif-panel]').hidden = !open;
        bell.querySelector('[data-notif-toggle]').setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    bells.forEach(function (bell) {
        bell.querySelector('[data-notif-toggle]').addEventListener('click', function () {
            const open = !bell.classList.contains('is-open');

            // Un seul panneau a la fois : le menu utilisateur se referme
            document.querySelectorAll('.user-menu.open').forEach(function (menu) {
                menu.classList.remove('open');
            });

            setOpen(bell, open);
        });
    });

    document.addEventListener('click', function (e) {
        bells.forEach(function (bell) {
            if (!bell.contains(e.target)) setOpen(bell, false);
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') bells.forEach(function (bell) { setOpen(bell, false); });
    });
})();
