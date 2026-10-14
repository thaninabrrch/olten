document.addEventListener('DOMContentLoaded', function () {

    // Les editeurs crees ici restent accessibles par l'id de leur champ
    // (window.oltenEditors.description, .summary) : l'assistant de redaction
    // (assets/js/ai-description.js) y lit le texte et y depose sa suggestion.
    window.oltenEditors = window.oltenEditors || {};

    const description = document.querySelector('#description');
    const summary = document.querySelector('#summary');

    if (description) {
        ClassicEditor
            .create(description)
            .then(editor => {
                window.oltenEditors.description = editor;
            })
            .catch(error => {
                console.error(error);
            });
    }

    if (summary) {
        ClassicEditor
            .create(summary)
            .then(editor => {
                window.oltenEditors.summary = editor;
            })
            .catch(error => {
                console.error(error);
            });
    }
});
