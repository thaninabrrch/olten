// messages.js

document.addEventListener('DOMContentLoaded', function() {
    initMessagesApp();
});

function initMessagesApp() {
    loadMessagesList();
}

function getInitials(name) {
    const parts = name.trim().split(' ');
    if (parts.length === 1) return parts[0][0].toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

function getColorFromInitials(initials) {
    let hash = 0;
    for (let i = 0; i < initials.length; i++) {
        hash = initials.charCodeAt(i) + ((hash << 5) - hash);
    }

    let color = '#';
    for (let i = 0; i < 3; i++) {
        const value = (hash >> (i * 8)) & 0xFF;
        color += ('00' + value.toString(16)).substr(-2);
    }

    return color;
}

// ===================================
// CHARGER LA LISTE DES CONVERSATIONS VIA AJAX
// ===================================
async function loadMessagesList() {
    const messagesList = document.getElementById('messagesList');
    if (!messagesList) return;

    messagesList.innerHTML = '<p>Chargement des messages...</p>';

    try {
        const response = await fetch('/messages');
        if (!response.ok) throw new Error('Erreur réseau');
        const conversations = await response.json();

        messagesList.innerHTML = '';

        conversations.forEach(conv => {
            const messageCard = document.createElement('div');
            messageCard.className = 'message-card';
            messageCard.dataset.id = conv.user_id;
            const initials = getInitials(conv.name);
            const bgColor = getColorFromInitials(initials);
            messageCard.innerHTML = `
                <div class="initials-avatar" style="background-color: ${bgColor}; color: #fff; display:flex; align-items:center; justify-content:center; border-radius:50%; width:40px; height:40px; font-weight:bold;">${initials}</div>
                <div class="message-content">
                    <div class="message-header">
                        <h3 class="message-sender">${conv.name}</h3>
                        <span class="message-time">${conv.time}</span>
                    </div>
                    <span class="message-badge">Réf-user ${conv.user_id}</span>
                    <p class="message-text">
                        <i class="fa-solid fa-reply"></i> ${conv.last_message}
                    </p>
                </div>
            `;

            messageCard.addEventListener('click', () => showConversation(conv.user_id));
            messagesList.appendChild(messageCard);
        });

    } catch (error) {
        console.error('Erreur lors du chargement des conversations :', error);
        messagesList.innerHTML = '<p>Impossible de charger les messages.</p>';
    }
}

// ===================================
// AFFICHER UNE CONVERSATION
// ===================================
async function showConversation(userId) {
    const detail = document.getElementById('conversationDetail');
    if (!detail) return;

    document.querySelectorAll('.message-card').forEach(card => card.classList.remove('active'));
    const activeCard = document.querySelector(`.message-card[data-id="${userId}"]`);
    if (activeCard) activeCard.classList.add('active');

    try {
        const response = await fetch(`/messages/${userId}`);
        if (!response.ok) throw new Error('Erreur réseau');
        const messages = await response.json();

        const userName = activeCard.querySelector('.message-sender').textContent;

        const userInitials = getInitials(userName);
        const userBgColor = getColorFromInitials(userInitials);
        const ref = activeCard.querySelector('.message-badge').textContent
        const messagesHtml = messages
            .map(msg => {
                const userInitials = msg.sender_id === AUTH_ID ? getInitials(AUTH_NAME) : getInitials(userName);
                const initials =  userInitials;

                const bgColor = getColorFromInitials(userInitials);

                return `
                    <div class="chat-message ${msg.sender_id === AUTH_ID ? 'sent' : ''}">
                        <div class="chat-avatar initials-avatar" style="
                            background-color: ${bgColor};
                            color: #fff;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            border-radius: 50%;
                            width: 40px;
                            height: 40px;
                            font-weight: bold;
                        ">
                            ${initials}
                        </div>
                        <div class="chat-bubble">
                            <div class="chat-name">${msg.sender_id === AUTH_ID ? 'Vous' : userName}</div>
                            <div class="chat-text">
                                ${msg.content || ''}
                                ${msg.attachment_path ? `<br><a href="/storage/${msg.attachment_path}" target="_blank">📎 ${msg.attachment_name}</a>` : ''}
                            </div>
                            <div class="chat-time">${new Date(msg.created_at).toLocaleString()}</div>
                        </div>
                    </div>
                `;
            }).join('');

        detail.innerHTML = `
            <div class="conversation-header">
                <div class="conversation-user">
                    <div class="conversation-user-avatar initials-avatar" style="
                        background-color: ${userBgColor};
                        color: #fff;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        border-radius: 50%;
                        width: 40px;
                        height: 40px;
                        font-weight: bold;
                    ">
                        ${userInitials}
                    </div>
                    <div class="conversation-user-info">
                        <h3>${userName}</h3>
                        <p>${ref}</p>
                    </div>
                </div>
                <div class="conversation-actions">
                    <button class="btn-action" id="hideConversationBtn">
                        <i class="fa-solid fa-times"></i> Masquer
                    </button>
                </div>
            </div>
            <div class="conversation-messages" id="conversationMessages">
                ${messagesHtml}
            </div>
            <div class="conversation-input">
                <div class="input-group">
                    <button class="btn-attach" id="attachFileBtn">
                        <i class="fa-solid fa-paperclip"></i>
                    </button>
                    <textarea id="messageInput" placeholder="Votre message..."></textarea>
                    <button class="btn-send" id="sendMessageBtn">
                        Envoyer un message
                    </button>
                    <input type="file" id="fileInput" style="display:none;">
                </div>
            </div>
        `;

        const attachBtn = document.getElementById('attachFileBtn');
        const fileInput = document.getElementById('fileInput');

        if (attachBtn && fileInput) {
            attachBtn.addEventListener('click', () => {
                fileInput.click();
            });

            fileInput.addEventListener('change', () => {
                const file = fileInput.files[0];
                const filePreviewContainer = document.getElementById('filePreview');

                if (!filePreviewContainer) {
                    const container = document.createElement('div');
                    container.id = 'filePreview';
                    container.style.marginTop = '5px';
                    container.style.fontSize = '0.9em';
                    container.style.color = '#555';
                    const inputGroup = document.querySelector('.input-group');
                    inputGroup.appendChild(container);
                }

                const container = document.getElementById('filePreview');

                if (file) {
                    container.innerHTML = `📎 ${file.name} <span style="cursor:pointer;color:red;" id="removeFile">[x]</span>`;

                    document.getElementById('removeFile').addEventListener('click', () => {
                        fileInput.value = '';
                        container.innerHTML = '';
                    });
                } else {
                    container.innerHTML = '';
                }
            });
        }

        document.getElementById('hideConversationBtn').addEventListener('click', hideConversation);

        const sendBtn = document.getElementById('sendMessageBtn');
        const messageInput = document.getElementById('messageInput');

        sendBtn.addEventListener('click', () => sendMessage(userId));
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage(userId);
            }
        });

        scrollToBottom();
    } catch (error) {
        console.error('Erreur lors du chargement de la conversation :', error);
    }
}

// ===================================
// MASQUER LA CONVERSATION
// ===================================
function hideConversation() {
    const detail = document.getElementById('conversationDetail');
    if (!detail) return;

    detail.classList.remove('active');
    detail.innerHTML = `
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fa-solid fa-comments"></i>
            </div>
            <h3>Sélectionnez une conversation</h3>
            <p>Choisissez un message dans la liste pour voir les détails</p>
        </div>
    `;

    document.querySelectorAll('.message-card').forEach(card => card.classList.remove('active'));
}

// ===================================
// ENVOYER UN MESSAGE
// ===================================
async function sendMessage(userId) {
    const messageInput = document.getElementById('messageInput');
    const fileInput = document.getElementById('fileInput');
    if (!messageInput) return;

    const messageText = messageInput.value.trim();
    const file = fileInput.files[0];

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
            fileInput.value = ''; // reset file
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