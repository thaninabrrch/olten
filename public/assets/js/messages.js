// messages.js
//
// Messagerie de l'espace connecte : liste des conversations a gauche,
// fil de discussion a droite. Les appels reseau sont inchanges
// (GET /messages, GET /messages/{id}, POST /messages/{id}) ; seul le
// balisage produit suit desormais le design de l'espace connecte (.sp-*).

document.addEventListener('DOMContentLoaded', function () {
    initMessagesApp();
});

function initMessagesApp() {
    loadMessagesList();
    initConversationSearch();
}

// ===================================
// OUTILS
// ===================================
function esc(value) {
    return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function getInitials(name) {
    const parts = String(name || '?').trim().split(/\s+/);
    if (parts.length === 1) return (parts[0][0] || '?').toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

// Couleur d'avatar stable : meme personne, meme teinte d'une page a l'autre.
function getColorFromInitials(initials) {
    let hash = 0;
    for (let i = 0; i < initials.length; i++) {
        hash = initials.charCodeAt(i) + ((hash << 5) - hash);
    }

    const hue = Math.abs(hash) % 360;
    return `hsl(${hue}, 45%, 42%)`;
}

function avatarMarkup(name, extraClass) {
    const initials = getInitials(name);
    return `<span class="sp-avatar ${extraClass || ''}" style="background-color:${getColorFromInitials(initials)}">${esc(initials)}</span>`;
}

// Apercu decoratif du fil vide : trois bulles vides, sans image
function threadGhost() {
    return `
        <div class="sp-thread-ghost" aria-hidden="true">
            <span class="sp-ghost-line is-in"></span>
            <span class="sp-ghost-line is-out"></span>
            <span class="sp-ghost-line is-in is-short"></span>
        </div>
    `;
}

// Separateur de date entre deux groupes de messages
function dayLabel(value) {
    const date = new Date(value);
    if (isNaN(date)) return '';

    const today = new Date();
    const yesterday = new Date();
    yesterday.setDate(today.getDate() - 1);

    const same = (a, b) => a.toDateString() === b.toDateString();

    if (same(date, today)) return "Aujourd'hui";
    if (same(date, yesterday)) return 'Hier';

    return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
}

function formatTime(value) {
    const date = new Date(value);
    if (isNaN(date)) return '';

    return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
}

// ===================================
// LISTE DES CONVERSATIONS
// ===================================
async function loadMessagesList() {
    const messagesList = document.getElementById('messagesList');
    if (!messagesList) return;

    messagesList.innerHTML = '<p class="sp-conv-loading">Chargement des conversations...</p>';

    try {
        const response = await fetch('/messages');
        if (!response.ok) throw new Error('Erreur réseau');
        const conversations = await response.json();

        messagesList.innerHTML = '';

        if (!conversations.length) {
            messagesList.innerHTML = `
                <div class="sp-conv-empty">
                    <strong>Aucune conversation</strong>
                    Vos échanges apparaîtront ici dès le premier message.
                </div>
            `;
            updateCounter(0);
            return;
        }

        conversations.forEach(conv => {
            const card = document.createElement('button');
            card.type = 'button';
            card.className = 'sp-conv';
            card.dataset.id = conv.user_id;
            card.dataset.name = conv.name || '';

            card.innerHTML = `
                ${avatarMarkup(conv.name)}
                <span class="sp-conv-body">
                    <span class="sp-conv-top">
                        <span class="sp-conv-name">${esc(conv.name)}</span>
                        <span class="sp-conv-time">${esc(conv.time)}</span>
                    </span>
                    <span class="sp-conv-last">${esc(conv.last_message)}</span>
                </span>
            `;

            card.addEventListener('click', () => showConversation(conv.user_id));
            messagesList.appendChild(card);
        });

        updateCounter(conversations.length);

    } catch (error) {
        console.error('Erreur lors du chargement des conversations :', error);
        messagesList.innerHTML = '<p class="sp-conv-empty">Impossible de charger les conversations.</p>';
    }
}

function updateCounter(n) {
    const counter = document.getElementById('convCounter');
    if (counter) counter.textContent = n > 0 ? n : '';
}

// Filtre local sur les conversations deja chargees
function initConversationSearch() {
    const input = document.getElementById('convSearch');
    const list = document.getElementById('messagesList');
    if (!input || !list) return;

    input.addEventListener('input', function () {
        const needle = input.value.trim().toLowerCase();
        let visible = 0;

        list.querySelectorAll('.sp-conv').forEach(function (card) {
            const match = !needle || (card.dataset.name || '').toLowerCase().includes(needle);
            card.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        const empty = list.querySelector('.sp-conv-empty[data-search]');

        if (!visible && !empty) {
            const p = document.createElement('p');
            p.className = 'sp-conv-empty';
            p.dataset.search = '1';
            p.textContent = 'Aucun contact ne correspond à cette recherche.';
            list.appendChild(p);
        } else if (visible && empty) {
            empty.remove();
        }
    });
}

// ===================================
// FIL DE DISCUSSION
// ===================================
async function showConversation(userId) {
    const detail = document.getElementById('conversationDetail');
    if (!detail) return;

    document.querySelectorAll('.sp-conv').forEach(card => card.classList.remove('is-active'));
    const activeCard = document.querySelector(`.sp-conv[data-id="${userId}"]`);
    if (activeCard) activeCard.classList.add('is-active');

    try {
        const response = await fetch(`/messages/${userId}`);
        if (!response.ok) throw new Error('Erreur réseau');
        const messages = await response.json();

        const userName = activeCard ? (activeCard.dataset.name || 'Contact') : 'Contact';

        let lastDay = null;

        const messagesHtml = messages.map(msg => {
            const mine = msg.sender_id === AUTH_ID;
            const author = mine ? AUTH_NAME : userName;

            const day = dayLabel(msg.created_at);
            const separator = day && day !== lastDay
                ? `<div class="sp-day"><span>${esc(day)}</span></div>`
                : '';
            lastDay = day || lastDay;

            const file = msg.attachment_path
                ? `<a class="sp-bubble-file" href="/storage/${esc(msg.attachment_path)}" target="_blank" rel="noopener">${esc(msg.attachment_name || 'Pièce jointe')}</a>`
                : '';

            return `
                ${separator}
                <div class="sp-msg ${mine ? 'is-mine' : ''}">
                    ${avatarMarkup(author)}
                    <div class="sp-bubble">
                        <div class="sp-bubble-name">${mine ? 'Vous' : esc(userName)}</div>
                        <div class="sp-bubble-text">${esc(msg.content || '')}</div>
                        ${file}
                        <div class="sp-bubble-time">${esc(formatTime(msg.created_at))}</div>
                    </div>
                </div>
            `;
        }).join('');

        detail.innerHTML = `
            <div class="sp-thread-head">
                <div class="sp-thread-user">
                    ${avatarMarkup(userName)}
                    <div>
                        <h3>${esc(userName)}</h3>
                        <p>${messages.length} message${messages.length > 1 ? 's' : ''} échangé${messages.length > 1 ? 's' : ''}</p>
                    </div>
                </div>

                <button type="button" class="sp-act is-ghost" id="hideConversationBtn">Fermer</button>
            </div>

            <div class="sp-thread-body" id="conversationMessages">
                ${messagesHtml || '<div class="sp-conv-empty"><strong>Aucun message</strong>Écrivez le premier message ci-dessous.</div>'}
            </div>

            <div class="sp-composer">
                <div class="sp-composer-row">
                    <textarea id="messageInput" placeholder="Écrivez votre message..." aria-label="Votre message"></textarea>
                    <button type="button" class="sp-act is-ghost" id="attachFileBtn">Joindre</button>
                    <button type="button" class="sp-act is-edit" id="sendMessageBtn">Envoyer</button>
                    <input type="file" id="fileInput" style="display:none;">
                </div>
                <div class="sp-composer-file" id="filePreview"></div>
            </div>
        `;

        bindComposer(userId);
        scrollToBottom();

    } catch (error) {
        console.error('Erreur lors du chargement de la conversation :', error);
    }
}

function bindComposer(userId) {
    const attachBtn = document.getElementById('attachFileBtn');
    const fileInput = document.getElementById('fileInput');
    const preview = document.getElementById('filePreview');
    const sendBtn = document.getElementById('sendMessageBtn');
    const messageInput = document.getElementById('messageInput');
    const hideBtn = document.getElementById('hideConversationBtn');

    if (attachBtn && fileInput) {
        attachBtn.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', function () {
            const file = fileInput.files[0];

            if (!file) {
                preview.innerHTML = '';
                return;
            }

            preview.innerHTML = `Fichier joint : ${esc(file.name)} <button type="button" id="removeFile">Retirer</button>`;

            document.getElementById('removeFile').addEventListener('click', function () {
                fileInput.value = '';
                preview.innerHTML = '';
            });
        });
    }

    if (hideBtn) hideBtn.addEventListener('click', hideConversation);

    if (sendBtn) sendBtn.addEventListener('click', () => sendMessage(userId));

    if (messageInput) {
        messageInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage(userId);
            }
        });
    }
}

// ===================================
// FERMER LA CONVERSATION
// ===================================
function hideConversation() {
    const detail = document.getElementById('conversationDetail');
    if (!detail) return;

    detail.innerHTML = `
        <div class="sp-thread-empty">
            ${threadGhost()}
            <h3>Sélectionnez une conversation</h3>
            <p>Choisissez un contact dans la liste pour afficher vos échanges.</p>
        </div>
    `;

    document.querySelectorAll('.sp-conv').forEach(card => card.classList.remove('is-active'));
}

// ===================================
// ENVOYER UN MESSAGE
// ===================================
async function sendMessage(userId) {
    const messageInput = document.getElementById('messageInput');
    const fileInput = document.getElementById('fileInput');
    if (!messageInput) return;

    const messageText = messageInput.value.trim();
    const file = fileInput ? fileInput.files[0] : null;

    if (!messageText && !file) return; // message ou fichier obligatoire

    const formData = new FormData();
    formData.append('message', messageText);
    if (file) formData.append('file', file);

    try {
        const response = await fetch(`/messages/${userId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        });

        if (response.ok) {
            messageInput.value = '';
            if (fileInput) fileInput.value = '';
            // la liste d'abord : elle recree les cartes, donc l'etat actif
            await loadMessagesList();
            showConversation(userId);
        }
    } catch (error) {
        console.error('Erreur lors de l\'envoi du message :', error);
    }
}

// ===================================
// SCROLL VERS LE BAS
// ===================================
function scrollToBottom() {
    setTimeout(() => {
        const messagesContainer = document.getElementById('conversationMessages');
        if (messagesContainer) messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }, 100);
}

window.messagesApp = {
    loadMessagesList,
    showConversation,
    hideConversation,
    sendMessage
};
