document.addEventListener('DOMContentLoaded', function () {
    const deleteModal = document.getElementById('deleteAdModal');
    if (!deleteModal) return;
    console.log(deleteModal)

    deleteModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        console.log('ttt')

        const title = button.getAttribute('data-title');
        const url = button.getAttribute('data-url');

        deleteModal.querySelector('#ad-title').textContent = title;
        deleteModal.querySelector('#deleteAdForm').action = url;
    });
});
