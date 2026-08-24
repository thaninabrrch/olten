/**
 * Ouvre la popin de connexion / inscription.
 *
 * Exposee sur `window` pour que tout le site puisse l'appeler :
 * le bouton « Deposer une annonce », les favoris (script.js), etc.
 *
 * @param {string} tab      'login' (defaut) ou 'register'
 * @param {string} redirect page a atteindre une fois connecte
 */
window.openAuthModal = function (tab, redirect) {
    const modal = document.getElementById('authModal');

    if (!modal) {
        return false;
    }

    tab = tab === 'register' ? 'register' : 'login';

    modal.style.display = 'block';

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tab);
    });

    document.querySelectorAll('.tab-content').forEach(content => {
        content.style.display = content.id === tab ? 'block' : 'none';
    });

    // La destination voyage avec le formulaire : apres connexion,
    // l'utilisateur arrive sur la page qu'il avait demandee.
    const redirectField = document.getElementById('login-redirect');

    if (redirectField) {
        redirectField.value = redirect || '';
    }

    modal.querySelector('#' + tab + ' input:not([type=hidden])')?.focus();

    return true;
};

/**
 * Liens et boutons reserves aux membres : tant que le visiteur n'est pas
 * connecte, le clic ouvre la popin au lieu de quitter la page.
 *
 * Il suffit d'ajouter `data-auth-required` sur le lien ; son `href` reste
 * le repli si le JavaScript ne s'execute pas.
 *
 * L'ecoute est deleguee : elle couvre aussi le contenu injecte apres coup.
 */
document.addEventListener('click', function (event) {
    const trigger = event.target.closest('[data-auth-required]');

    if (!trigger || window.IS_AUTHENTICATED) {
        return;
    }

    const destination = trigger.dataset.authRedirect || trigger.getAttribute('href');

    if (window.openAuthModal(trigger.dataset.authTab, destination)) {
        event.preventDefault();
    }
});

document.addEventListener('DOMContentLoaded', function() {

    const registerForm = document.getElementById('registerForm');
    const registerErrorsDiv = document.getElementById('registerErrors');
    const loginForm = document.getElementById('login-form');
    const loginErrorsDiv = document.getElementById('login-errors');
    const authModal = document.getElementById('authModal');
    const togglePasswordIcons = document.querySelectorAll('.toggle-password');

    function showLoginModalIfNeeded() {
        if (!authModal || !window.SHOW_LOGIN_MODAL) return;

        window.openAuthModal('login');

        if (window.PASSWORD_RESET_STATUS) {
            loginErrorsDiv.innerHTML = `<p class="text-success">${window.PASSWORD_RESET_STATUS}</p>`;
        }
    }

    function setupPasswordToggle() {
        togglePasswordIcons.forEach(icon => {
            icon.addEventListener('click', function() {
                const input = this.previousElementSibling;
                if (!input) return;

                if (input.type === 'password') {
                    input.type = 'text';
                    this.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    this.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });
    }

    // inscription
    function handleRegisterForm() {
        if (!registerForm) return;

        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Empêcher les doubles clics
            if (registerForm.dataset.submitting === 'true') {
                return;
            }

            registerForm.dataset.submitting = 'true';

            registerErrorsDiv.innerHTML = '';

            const submitBtn = document.getElementById('registerSubmitBtn');
            const btnText = submitBtn?.querySelector('.register-btn-text');
            const loader = submitBtn?.querySelector('.register-loader');

            // Désactiver le bouton immédiatement
            if (submitBtn) {
                submitBtn.disabled = true;

                if (btnText) {
                    btnText.style.display = 'none';
                }

                if (loader) {
                    loader.style.display = 'inline-flex';
                }
            }

            const formData = new FormData(registerForm);

            fetch(REGISTER_URL, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async response => {

                const data = await response.json();

                if (response.status === 422 || data.status === 'error') {

                    const errors = data.errors || {};

                    for (let key in errors) {
                        errors[key].forEach(msg => {
                            const p = document.createElement('p');
                            p.className = 'text-danger';
                            p.textContent = msg;
                            registerErrorsDiv.appendChild(p);
                        });
                    }

                    // La requête est terminée et il y a une erreur :
                    // on réactive le bouton
                    registerForm.dataset.submitting = 'false';

                    if (submitBtn) {
                        submitBtn.disabled = false;

                        if (btnText) {
                            btnText.style.display = 'inline';
                        }

                        if (loader) {
                            loader.style.display = 'none';
                        }
                    }

                } else if (data.status === 'success') {

                    // On NE réactive PAS le bouton.
                    // L'utilisateur va être redirigé.
                    window.location.href = data.redirect;
                }
            })
            .catch(err => {

                console.error(err);

                const p = document.createElement('p');
                p.className = 'text-danger';
                p.textContent = 'Une erreur est survenue. Veuillez réessayer.';
                registerErrorsDiv.appendChild(p);

                // Réactiver seulement en cas d'erreur réseau
                registerForm.dataset.submitting = 'false';

                if (submitBtn) {
                    submitBtn.disabled = false;

                    if (btnText) {
                        btnText.style.display = 'inline';
                    }

                    if (loader) {
                        loader.style.display = 'none';
                    }
                }
            });
        });
    }

    // connexion
    function handleLoginForm() {
        if (!loginForm) return;

        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            loginErrorsDiv.innerHTML = '';

            const formData = new FormData(loginForm);

            try {
                const response = await fetch(LOGIN_URL, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.status === 'success') {
                    window.location.href = data.redirect;
                    return;
                }

                // Afficher les erreurs dans la modal
                let errorBox = '<div class="errors">';
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        data.errors[field].forEach(msg => {
                            errorBox += `<p class="text-danger">${msg}</p>`;
                        });
                    });
                } else if (data.message) {
                    errorBox += `<p class="text-danger">${data.message}</p>`;
                }
                errorBox += '</div>';
                loginErrorsDiv.innerHTML = errorBox;

            } catch (err) {
                console.error(err);
                loginErrorsDiv.innerHTML = '<p class="text-danger">Une erreur est survenue. Veuillez réessayer.</p>';
            }
        });
    }

    showLoginModalIfNeeded();
    setupPasswordToggle();
    handleRegisterForm();
    handleLoginForm();

});
