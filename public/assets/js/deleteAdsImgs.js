document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons = document.querySelectorAll('.delete-image');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            const wrapper = this.closest('.image-wrapper');
            const imageId = wrapper.dataset.id;

            fetch(`/ads/images/${imageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    wrapper.remove();
                } else {
                    alert('Erreur lors de la suppression.');
                }
            })
            .catch(() => alert('Erreur serveur.'));
        });
    });
});
