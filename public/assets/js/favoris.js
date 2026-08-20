document.querySelectorAll('.btn-delete').forEach(btn => {

    btn.addEventListener('click', async function(e) {

        e.preventDefault();
        e.stopPropagation();

        const card = this.closest('.favori-card');

        if (!card) return;

        const id = card.dataset.id;
        const type = card.dataset.type;

        if (!confirm('Voulez-vous vraiment supprimer ce favori ?')) {
            return;
        }

        const url = type === 'product'
            ? `/products/${id}/favorite`
            : `/ads/${id}/favorite`;

        try {

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.status === 'removed') {

                card.remove();

                const favorisList = document.getElementById('favorisList');

                if (
                    favorisList &&
                    favorisList.querySelectorAll('.favori-card').length === 0
                ) {
                    favorisList.innerHTML = `
                        <div class="empty-state" id="emptyState">
                            <div class="empty-icon">
                                <i class="fa-solid fa-heart-crack"></i>
                            </div>
                            <h3>Aucun favori enregistré</h3>
                        </div>
                    `;
                }
            }

        } catch (err) {
            console.error('Erreur suppression favoris:', err);
        }
    });
});