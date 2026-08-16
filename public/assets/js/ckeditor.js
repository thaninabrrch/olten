document.addEventListener('DOMContentLoaded', function () {

    const description = document.querySelector('#description');
    const summary = document.querySelector('#summary');

    if (description) {
        ClassicEditor
            .create(description)
            .catch(error => {
                console.error(error);
            });
    }

    if (summary) {
        ClassicEditor
            .create(summary)
            .catch(error => {
                console.error(error);
            });
    }
});
