document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const stars = document.querySelectorAll('.rating-star');

    stars.forEach(star => {

        star.addEventListener('click', function() {

            const rating = this.dataset.value;
            document.getElementById('rating-input').value = rating;
            const container = this.closest('.stars-rating');
            const deliveryId = container.dataset.delivery;

            stars.forEach(s => {

                if (parseInt(s.dataset.value) <= rating) {
                    s.classList.remove('far');
                    s.classList.add('fas', 'active');
                } else {
                    s.classList.remove('fas', 'active');
                    s.classList.add('far');
                }

            });

            fetch(`/${deliveryId}/rate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    rating: rating,
                    comment: document.getElementById('delivery-comment').value
                })
            });

        });

    });

});

document.getElementById('save-comment')?.addEventListener('click', function () {

    const orderId = this.dataset.order;

    fetch(`/orders/${orderId}/rate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            rating: document.getElementById('rating-input').value,
            comment: document.getElementById('delivery-comment').value
        })
    })
    .then(response => response.json())
    .then(data => {

        if (data.success) {

            const successBox = document.getElementById('ajax-success-message');

            successBox.textContent = 'Votre avis a été enregistré avec succès.';
            successBox.style.display = 'block';

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });

            setTimeout(() => {
                successBox.style.display = 'none';
            }, 5000);
        }

    });

});