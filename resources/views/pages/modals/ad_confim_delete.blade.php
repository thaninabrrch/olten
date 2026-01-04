<div class="modal fade" id="deleteAdModal" tabindex="-1" aria-labelledby="deleteAdModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="deleteAdModalLabel">
                    Confirmation de suppression
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>

            <div class="modal-body">
                <p>
                    Êtes-vous sûr de vouloir supprimer l’annonce
                    <strong id="ad-title"></strong> ?
                </p>
                <p class="text-danger mb-0">
                    Cette action est irréversible.
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Annuler
                </button>

                <form id="deleteAdForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        Supprimer
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
