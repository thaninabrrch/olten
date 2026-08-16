// JS
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', async function(e) {
        const card = this.closest('.favori-card');
        const adId = card.dataset.id;

        if (!confirm('Voulez-vous vraiment supprimer ce favori ?')) return;

        try {
            const response = await fetch(`/ads/${adId}/favorite`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.status === 'removed') {
                card.remove();
                const favorisList = document.getElementById('favorisList');
                if (favorisList.querySelectorAll('.favori-card').length === 0) {
                    const emptyState = document.createElement('div');
                    emptyState.id = 'emptyState';
                    emptyState.className = 'empty-state';
                    emptyState.innerHTML = `
                        <div class="empty-icon">
                            <i class="fa-solid fa-heart-crack"></i>
                        </div>
                        <h3>Aucun favori enregistré</h3>
                    `;
                    favorisList.parentNode.appendChild(emptyState);
                }

            }
        } catch(err) {
            console.error('Erreur suppression favoris:', err);
        }
    });
});
