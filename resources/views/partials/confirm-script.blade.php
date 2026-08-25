{{--
    Confirmation avant envoi d'un formulaire sensible (annulation, suppression).

    Usage : poser data-sp-confirm sur le <form>, plus si besoin
    data-title, data-text et data-confirm-label pour personnaliser la boite.

    SweetAlert2 est charge par layouts.connected ; si jamais il manque,
    on retombe sur le confirm() natif du navigateur.
--}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form[data-sp-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (form.dataset.confirmed === '1') return;
                e.preventDefault();

                const title   = form.dataset.title || 'Confirmer cette action ?';
                const text    = form.dataset.text || 'Cette action est définitive.';
                const confirm = form.dataset.confirmLabel || 'Confirmer';

                const send = function () {
                    form.dataset.confirmed = '1';
                    form.submit();
                };

                if (typeof Swal === 'undefined') {
                    if (window.confirm(title + '\n\n' + text)) send();
                    return;
                }

                Swal.fire({
                    title: title,
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: confirm,
                    cancelButtonText: 'Retour',
                    confirmButtonColor: '#c0392b',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                }).then(function (result) {
                    if (result.isConfirmed) send();
                });
            });
        });
    });
</script>
