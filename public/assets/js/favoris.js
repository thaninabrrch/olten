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
                    // Meme etat vide que le composant <x-empty-state /> :
                    // les URLs viennent des data-attributs du conteneur.
                    const emptyImage = favorisList.dataset.emptyImage || '';
                    const browseUrl = favorisList.dataset.browseUrl || '/';

                    favorisList.innerHTML = `
                        <div class="olten-empty" id="emptyState">
                            <img class="olten-empty-illustration" src="${emptyImage}" alt="" aria-hidden="true">
                            <h3 class="olten-empty-title">Aucun favori enregistré</h3>
                            <p class="olten-empty-text">
                                Les annonces et produits que vous ajoutez en favori apparaîtront ici.
                            </p>
                            <a href="${browseUrl}" class="olten-empty-btn">Parcourir les annonces</a>
                        </div>
                    `;
                }
            }

        } catch (err) {
            console.error('Erreur suppression favoris:', err);
        }
    });
});