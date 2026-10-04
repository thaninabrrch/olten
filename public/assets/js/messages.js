// messages.js
//
// Messagerie de l'espace connecte (pages/locateur/messages.blade.php) :
// conversations a gauche, fil a droite ; sous 900px, un seul panneau a la
// fois, le fil passant en plein ecran.
//
// Les adresses viennent des data-* de la vue (noms de route). Le fil ouvert
// est interroge toutes les 8 s et la liste toutes les 25 s, seulement quand
// l'onglet est visible ; `?after=` ne ramene que les nouveaux messages.
// L'envoi est optimiste : le message s'affiche aussitot, puis se confirme.

document.addEventListener('DOMContentLoaded', function () {
    const root = document.querySelector('[data-ib]');
    if (root) initInbox(root);
});

function initInbox(root) {
    const q = sel => root.querySelector(sel);

    const el = {
        total:       q('[data-ib-total]'),
        search:      q('[data-ib-search]'),
        filters:     Array.from(root.querySelectorAll('[data-ib-filter]')),
        unreadCount: q('[data-ib-unread-count]'),
        list:        q('[data-ib-list]'),
        welcome:     q('[data-ib-welcome]'),
        head:        q('[data-ib-head]'),
        headAvatar:  q('[data-ib-head-avatar]'),
        headName:    q('[data-ib-head-name]'),
        headMeta:    q('[data-ib-head-meta]'),
        back:        q('[data-ib-back]'),
        scroll:      q('[data-ib-scroll]'),
        messages:    q('[data-ib-messages]'),
        jump:        q('[data-ib-jump]'),
        composer:    q('[data-ib-composer]'),
        input:       q('[data-ib-input]'),
        send:        q('[data-ib-send]'),
        attach:      q('[data-ib-attach]'),
        fileInput:   q('[data-ib-file-input]'),
        file:        q('[data-ib-file]'),
        fileName:    q('[data-ib-file-name]'),
        fileSize:    q('[data-ib-file-size]'),
        fileRemove:  q('[data-ib-file-remove]'),
        error:       q('[data-ib-error]'),
        live:        q('[data-ib-live]'),
    };

    const listUrl     = root.dataset.listUrl;
    const threadUrl   = id => root.dataset.threadUrl.replace('__ID__', encodeURIComponent(id));
    const me          = parseInt(root.dataset.me, 10);
    const csrf        = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const finePointer = window.matchMedia('(pointer: fine)').matches;
    const narrow      = window.matchMedia('(max-width: 899.98px)');
    const baseTitle   = document.title;

    const MAX_FILE  = 10 * 1024 * 1024;
    const FILE_EXTS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];
    const GROUP_GAP = 5 * 60 * 1000;

    const state = {
        conversations: [],
        loaded: false,
        filter: 'all',
        query: '',
        activeId: null,
        user: null,
        messages: [],
        lastId: 0,
        readUpTo: 0,
        token: 0,
        tmp: 0,
        drafts: {},
    };

    // =====================================================================
    // OUTILS
    // =====================================================================
    function esc(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Recherche insensible a la casse et aux accents
    function normalize(value) {
        return String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    }

    function initials(name) {
        const parts = String(name || '?').trim().split(/\s+/);
        if (parts.length === 1) return (parts[0][0] || '?').toUpperCase();
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }

    // Teinte stable : meme personne, meme couleur d'une visite a l'autre
    function hue(name) {
        let hash = 0;
        const s = String(name || '');
        for (let i = 0; i < s.length; i++) hash = s.charCodeAt(i) + ((hash << 5) - hash);
        return Math.abs(hash) % 360;
    }

    function avatarHtml(name, url, size) {
        const cls = 'ib-avatar' + (size ? ' ' + size : '');

        if (url) {
            return `<span class="${cls}" data-name="${esc(name)}"><img src="${esc(url)}" alt="" loading="lazy"></span>`;
        }

        return `<span class="${cls}" style="--h:${hue(name)}" aria-hidden="true">${esc(initials(name))}</span>`;
    }

    function capitalize(s) {
        return s ? s.charAt(0).toUpperCase() + s.slice(1) : s;
    }

    function sameDay(a, b) {
        return a.toDateString() === b.toDateString();
    }

    function dayKey(iso) {
        const d = new Date(iso);
        return isNaN(d) ? '' : d.toDateString();
    }

    function dayLabel(iso) {
        const d = new Date(iso);
        if (isNaN(d)) return '';

        const today = new Date();
        const yesterday = new Date();
        yesterday.setDate(today.getDate() - 1);

        if (sameDay(d, today)) return "Aujourd'hui";
        if (sameDay(d, yesterday)) return 'Hier';

        const opts = { weekday: 'long', day: 'numeric', month: 'long' };
        if (d.getFullYear() !== today.getFullYear()) opts.year = 'numeric';

        return capitalize(d.toLocaleDateString('fr-FR', opts));
    }

    function timeLabel(iso) {
        const d = new Date(iso);
        return isNaN(d) ? '' : d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    }

    function fullDate(iso) {
        const d = new Date(iso);
        return isNaN(d) ? '' : capitalize(d.toLocaleString('fr-FR', { dateStyle: 'full', timeStyle: 'short' }));
    }

    // Heure du dernier message dans la liste : « 14:32 », « Hier », « lun. »…
    function listTime(iso) {
        const d = new Date(iso);
        if (isNaN(d)) return '';

        const now = new Date();
        const yesterday = new Date();
        yesterday.setDate(now.getDate() - 1);

        if (sameDay(d, now)) return timeLabel(iso);
        if (sameDay(d, yesterday)) return 'Hier';
        if (now - d < 6 * 864e5) return d.toLocaleDateString('fr-FR', { weekday: 'short' });
        if (d.getFullYear() === now.getFullYear()) return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });

        return d.toLocaleDateString('fr-FR');
    }

    function ext(name) {
        const m = /\.([a-z0-9]+)$/i.exec(name || '');
        return m ? m[1].toLowerCase() : '';
    }

    function fileIcon(name) {
        const e = ext(name);
        if (e === 'pdf') return 'fa-file-pdf';
        if (e === 'doc' || e === 'docx') return 'fa-file-word';
        if (e === 'xls' || e === 'xlsx') return 'fa-file-excel';
        if (e === 'txt') return 'fa-file-lines';
        if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(e)) return 'fa-file-image';
        return 'fa-file';
    }

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' o';
        if (bytes < 1024 * 1024) return Math.round(bytes / 1024) + ' Ko';
        return (bytes / 1024 / 1024).toFixed(1).replace('.', ',') + ' Mo';
    }

    // Liens cliquables. Le texte est deja echappe : les guillemets y sont
    // des entites, un lien ne peut donc pas sortir de son attribut.
    function linkify(html) {
        return html.replace(/\bhttps?:\/\/[^\s<]+/g, function (url) {
            const trail = url.match(/[.,;:!?)]+$/);
            const clean = trail ? url.slice(0, -trail[0].length) : url;
            return `<a href="${clean}" target="_blank" rel="noopener noreferrer nofollow">${clean}</a>` + (trail ? trail[0] : '');
        });
    }

    // Un message envoye depuis une fiche commence par « À propos de « … » » :
    // on l'affiche comme une etiquette au-dessus du texte.
    const CONTEXT_RE = /^À propos de « (.+?) »[ \t]*\n/;

    function splitContext(content) {
        const m = String(content || '').match(CONTEXT_RE);
        if (!m) return { context: null, text: String(content || '') };
        return { context: m[1], text: content.slice(m[0].length).replace(/^\s*\n/, '') };
    }

    function announce(text) {
        el.live.textContent = '';
        setTimeout(function () { el.live.textContent = text; }, 60);
    }

    function maxId(messages) {
        return messages.reduce((max, m) => (typeof m.id === 'number' && m.id > max ? m.id : max), 0);
    }

    async function getJson(url) {
        const res = await fetch(url, { headers: { Accept: 'application/json' } });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
    }

    // =====================================================================
    // LISTE DES CONVERSATIONS
    // =====================================================================
    async function loadList() {
        try {
            const data = await getJson(listUrl);

            // Le fil ouvert est lu a l'ecran : son compteur reste a zero
            state.conversations = data.map(c => (c.user_id === state.activeId ? Object.assign({}, c, { unread: 0 }) : c));
            state.loaded = true;

            renderList();
            updateTotals();

            // Fil ouvert depuis l'URL avant que la liste n'arrive : son nom
            // est maintenant connu.
            if (state.activeId && !state.user) {
                const conv = state.conversations.find(c => c.user_id === state.activeId);
                if (conv) fillHead({ name: conv.name, avatar: conv.avatar });
            }
        } catch (err) {
            console.error('Conversations :', err);

            if (!state.loaded) {
                el.list.innerHTML = `
                    <div class="ib-list-empty">
                        <span class="ib-list-empty-icon is-red"><i class="fa-solid fa-triangle-exclamation"></i></span>
                        <strong>Chargement impossible</strong>
                        <p>Vos conversations n'ont pas pu être chargées.</p>
                        <button type="button" class="ib-btn is-ghost" data-ib-retry>Réessayer</button>
                    </div>`;
            }
        }
    }

    function convHtml(c) {
        const active = c.user_id === state.activeId;
        const you = c.last_mine ? '<span class="ib-conv-you">Vous : </span>' : '';

        let status = '';

        if (c.unread > 0) {
            status = `<span class="ib-conv-count" aria-label="${c.unread} non lu${c.unread > 1 ? 's' : ''}">${c.unread > 99 ? '99+' : c.unread}</span>`;
        } else if (c.last_mine) {
            status = c.last_read
                ? '<i class="fa-solid fa-check-double ib-conv-tick is-read" title="Lu" aria-label="Lu"></i>'
                : '<i class="fa-solid fa-check ib-conv-tick" title="Envoyé" aria-label="Envoyé"></i>';
        }

        return `
            <button type="button" class="ib-conv${active ? ' is-active' : ''}${c.unread ? ' is-unread' : ''}"
                    data-id="${c.user_id}"${active ? ' aria-current="true"' : ''}>
                ${avatarHtml(c.name, c.avatar)}
                <span class="ib-conv-main">
                    <span class="ib-conv-top">
                        <span class="ib-conv-name">${esc(c.name)}</span>
                        <time class="ib-conv-time" datetime="${esc(c.at || '')}">${esc(listTime(c.at))}</time>
                    </span>
                    <span class="ib-conv-bottom">
                        <span class="ib-conv-last">${you}${esc(c.last_message || '')}</span>
                        ${status}
                    </span>
                </span>
            </button>`;
    }

    function renderList() {
        if (!state.loaded) return;

        const needle = normalize(state.query.trim());
        let items = state.conversations;

        if (state.filter === 'unread') items = items.filter(c => c.unread > 0);
        if (needle) items = items.filter(c => normalize(c.name + ' ' + c.last_message).includes(needle));

        if (!items.length) {
            const tpl = !state.conversations.length ? 'ib-empty-all' : (needle ? 'ib-empty-search' : 'ib-empty-unread');
            el.list.replaceChildren(document.getElementById(tpl).content.cloneNode(true));
            return;
        }

        el.list.innerHTML = items.map(convHtml).join('');
    }

    function updateTotals() {
        const total = state.conversations.reduce((sum, c) => sum + (c.unread || 0), 0);

        el.total.hidden = total === 0;
        el.total.textContent = total > 99 ? '99+' : total;
        el.unreadCount.textContent = total ? total : '';
        document.title = total ? '(' + total + ') ' + baseTitle : baseTitle;
    }

    // Le dernier message envoye remonte la conversation en tete de liste
    function bumpConversation(message) {
        const conv = state.conversations.find(c => c.user_id === state.activeId);

        if (!conv) {
            loadList();
            return;
        }

        const { text } = splitContext(message.content);
        conv.last_message = text.trim()
            ? text.trim().replace(/\s+/g, ' ')
            : (message.attachment ? 'Pièce jointe : ' + message.attachment.name : '');
        conv.last_mine = true;
        conv.last_read = false;
        conv.at = message.at;

        state.conversations = [conv].concat(state.conversations.filter(c => c !== conv));
        renderList();
    }

    el.list.addEventListener('click', function (e) {
        if (e.target.closest('[data-ib-retry]')) {
            loadList();
            return;
        }

        const item = e.target.closest('.ib-conv');
        if (item) openThread(parseInt(item.dataset.id, 10));
    });

    // Fleches haut / bas pour parcourir la liste au clavier
    el.list.addEventListener('keydown', function (e) {
        if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp') return;

        const items = Array.from(el.list.querySelectorAll('.ib-conv'));
        const index = items.indexOf(document.activeElement);
        if (index === -1) return;

        e.preventDefault();
        items[Math.max(0, Math.min(items.length - 1, index + (e.key === 'ArrowDown' ? 1 : -1)))].focus();
    });

    el.search.addEventListener('input', function () {
        state.query = el.search.value;
        renderList();
    });

    el.filters.forEach(function (btn) {
        btn.addEventListener('click', function () {
            state.filter = btn.dataset.ibFilter;

            el.filters.forEach(function (b) {
                const on = b === btn;
                b.classList.toggle('is-active', on);
                b.setAttribute('aria-selected', on ? 'true' : 'false');
            });

            renderList();
        });
    });

    // =====================================================================
    // FIL DE DISCUSSION
    // =====================================================================
    function fillHead(user) {
        el.headAvatar.innerHTML = avatarHtml(user.name, user.avatar, 'is-md');
        el.headName.textContent = user.name || '';

        const meta = [];
        if (user.verified) meta.push('<span class="ib-verified"><i class="fa-solid fa-circle-check"></i> Profil vérifié</span>');
        if (user.since) meta.push('<span>Membre depuis ' + esc(user.since) + '</span>');

        el.headMeta.innerHTML = meta.join('<span class="ib-dot" aria-hidden="true"></span>');
        el.headMeta.hidden = meta.length === 0;
    }

    function showThread(on) {
        el.welcome.hidden = on;
        el.head.hidden = !on;
        el.scroll.hidden = !on;
        el.composer.hidden = !on;
        el.jump.hidden = true;
        root.classList.toggle('is-thread-open', on);
    }

    function syncUrl(id, fromHistory) {
        if (fromHistory) return;

        const url = new URL(window.location.href);
        if (id) url.searchParams.set('avec', id); else url.searchParams.delete('avec');

        // Sur telephone, le fil est un ecran a part : le bouton retour du
        // navigateur doit ramener a la liste.
        if (id && narrow.matches && !(history.state && history.state.ib)) {
            history.pushState({ ib: id }, '', url);
        } else {
            history.replaceState(id ? { ib: id } : null, '', url);
        }
    }

    async function openThread(id, fromHistory) {
        if (!id || id === me) return;

        if (state.activeId) state.drafts[state.activeId] = el.input.value;

        state.activeId = id;
        state.user = null;
        state.messages = [];
        state.lastId = 0;
        state.readUpTo = 0;
        const token = ++state.token;

        const conv = state.conversations.find(c => c.user_id === id);
        if (conv) conv.unread = 0;
        renderList();
        updateTotals();

        showThread(true);
        fillHead(conv ? { name: conv.name, avatar: conv.avatar } : { name: 'Conversation' });
        el.messages.innerHTML = `
            <div class="ib-loading" aria-label="Chargement de la conversation">
                <span class="is-in"></span><span class="is-out"></span><span class="is-in is-short"></span>
            </div>`;

        el.input.value = state.drafts[id] || '';
        clearFile();
        hideError();
        autosize();
        updateSend();
        syncUrl(id, fromHistory);

        try {
            const data = await getJson(threadUrl(id));
            if (token !== state.token) return;

            state.user = data.user;
            state.messages = data.messages;
            state.lastId = maxId(data.messages);
            state.readUpTo = data.read_up_to || 0;

            fillHead(data.user);
            renderMessages();
            scrollToBottom(false);

            if (finePointer) el.input.focus();
        } catch (err) {
            if (token !== state.token) return;
            console.error('Conversation :', err);

            el.messages.innerHTML = `
                <div class="ib-thread-state">
                    <span class="ib-list-empty-icon is-red"><i class="fa-solid fa-triangle-exclamation"></i></span>
                    <strong>Conversation indisponible</strong>
                    <p>Elle n'a pas pu être chargée.</p>
                    <button type="button" class="ib-btn is-ghost" data-ib-reload>Réessayer</button>
                </div>`;
        }
    }

    function closeThread(fromHistory) {
        if (state.activeId) state.drafts[state.activeId] = el.input.value;

        state.activeId = null;
        state.user = null;
        state.token++;

        showThread(false);
        renderList();
        syncUrl(null, fromHistory);
    }

    el.back.addEventListener('click', function () {
        if (history.state && history.state.ib && narrow.matches) {
            history.back();
        } else {
            closeThread();
        }
    });

    window.addEventListener('popstate', function () {
        const id = parseInt(new URLSearchParams(window.location.search).get('avec'), 10);

        if (id) {
            if (id !== state.activeId) openThread(id, true);
        } else if (state.activeId) {
            closeThread(true);
        }
    });

    // ---- Messages ----
    function isRead(m) {
        return m.is_read || (typeof m.id === 'number' && m.id <= state.readUpTo);
    }

    function attachmentHtml(a) {
        if (a.is_image && a.url) {
            return `<a class="ib-img" href="${esc(a.url)}" target="_blank" rel="noopener">
                        <img src="${esc(a.url)}" alt="${esc(a.name)}" loading="lazy">
                    </a>`;
        }

        const inner = `
            <span class="ib-attach-icon"><i class="fa-solid ${fileIcon(a.name)}"></i></span>
            <span class="ib-attach-text">
                <strong>${esc(a.name)}</strong>
                <small>${esc((ext(a.name) || 'fichier').toUpperCase())}</small>
            </span>`;

        return a.url
            ? `<a class="ib-attach" href="${esc(a.url)}" target="_blank" rel="noopener">${inner}<i class="fa-solid fa-arrow-down ib-attach-dl" aria-hidden="true"></i></a>`
            : `<span class="ib-attach">${inner}</span>`;
    }

    function messageHtml(m, first, last, showStatus) {
        const cls = ['ib-msg', m.mine ? 'is-mine' : 'is-theirs'];
        if (first) cls.push('is-first');
        if (last) cls.push('is-last');
        if (m.pending) cls.push('is-pending');
        if (m.failed) cls.push('is-failed');

        const { context, text } = splitContext(m.content);
        let body = '';

        if (context) {
            body += `<span class="ib-context"><i class="fa-solid fa-tag" aria-hidden="true"></i>
                        <span><small>À propos de</small>${esc(context)}</span></span>`;
        }
        if (m.attachment) body += attachmentHtml(m.attachment);
        if (text.trim()) body += `<span class="ib-text">${linkify(esc(text))}</span>`;

        let meta = '';

        if (m.failed) {
            meta = `<span class="ib-meta is-error">
                        <i class="fa-solid fa-circle-exclamation"></i> Non envoyé ·
                        <button type="button" data-ib-resend="${esc(m.id)}">Réessayer</button>
                    </span>`;
        } else if (last) {
            let status = '';

            if (showStatus) {
                status = m.pending
                    ? ' · Envoi…'
                    : (isRead(m)
                        ? ' · <span class="is-read"><i class="fa-solid fa-check-double"></i> Lu</span>'
                        : ' · <i class="fa-solid fa-check"></i> Envoyé');
            }

            meta = `<span class="ib-meta">${esc(timeLabel(m.at))}${status}</span>`;
        }

        return `
            <div class="${cls.join(' ')}" data-id="${esc(m.id)}">
                <div class="ib-bubble" title="${esc(fullDate(m.at))}">${body}</div>
                ${meta}
            </div>`;
    }

    function renderMessages() {
        const msgs = state.messages;

        if (!msgs.length) {
            const user = state.user || { name: el.headName.textContent };

            el.messages.innerHTML = `
                <div class="ib-thread-state">
                    ${avatarHtml(user.name, user.avatar, 'is-lg')}
                    <strong>Démarrez la conversation</strong>
                    <p>Présentez-vous et posez votre question à ${esc(user.name)}.</p>
                </div>`;
            return;
        }

        // Statut « Envoyé / Lu » sous mon dernier message parti seulement
        let lastMine = null;
        for (let i = msgs.length - 1; i >= 0; i--) {
            if (msgs[i].mine && !msgs[i].failed) { lastMine = msgs[i]; break; }
        }

        let html = '';
        let lastDay = null;

        msgs.forEach(function (m, i) {
            const day = dayKey(m.at);

            if (day !== lastDay) {
                html += `<div class="ib-day"><span>${esc(dayLabel(m.at))}</span></div>`;
                lastDay = day;
            }

            // Messages consecutifs du meme auteur, a moins de 5 min : une
            // seule heure, des bulles accolees. Un envoi en echec reste a
            // part, pour que le precedent garde son heure et son statut.
            const prev = msgs[i - 1];
            const next = msgs[i + 1];
            const linked = (a, b) => a && b && a.mine === b.mine && !a.failed && !b.failed
                && dayKey(a.at) === dayKey(b.at)
                && Math.abs(new Date(b.at) - new Date(a.at)) < GROUP_GAP;

            html += messageHtml(m, !linked(prev, m), !linked(m, next), m === lastMine);
        });

        el.messages.innerHTML = html;
    }

    // ---- Defilement ----
    function nearBottom() {
        return el.scroll.scrollHeight - el.scroll.scrollTop - el.scroll.clientHeight < 90;
    }

    function scrollToBottom(smooth) {
        el.scroll.scrollTo({ top: el.scroll.scrollHeight, behavior: smooth ? 'smooth' : 'auto' });
        el.jump.hidden = true;
    }

    // `stick` : le membre est en bas du fil. Une image chargee apres coup
    // allonge alors le fil sans l'en decoller.
    let stick = true;

    el.scroll.addEventListener('scroll', function () {
        stick = nearBottom();
        if (stick) el.jump.hidden = true;
    }, { passive: true });

    el.jump.addEventListener('click', function () {
        scrollToBottom(true);
    });

    el.messages.addEventListener('load', function (e) {
        if (e.target.tagName === 'IMG' && stick) scrollToBottom(false);
    }, true);

    // Photo de profil introuvable : retour aux initiales
    root.addEventListener('error', function (e) {
        const img = e.target;
        if (img.tagName !== 'IMG' || !img.parentElement?.classList.contains('ib-avatar')) return;

        const span = img.parentElement;
        span.style.setProperty('--h', hue(span.dataset.name));
        span.textContent = initials(span.dataset.name);
    }, true);

    el.messages.addEventListener('click', function (e) {
        if (e.target.closest('[data-ib-reload]')) {
            openThread(state.activeId, true);
            return;
        }

        const resend = e.target.closest('[data-ib-resend]');
        if (!resend) return;

        const temp = state.messages.find(m => String(m.id) === resend.dataset.ibResend);
        if (!temp) return;

        temp.failed = false;
        temp.pending = true;
        renderMessages();
        deliver(temp);
    });

    // ---- Actualisation du fil ouvert ----
    let polling = false;

    async function pollThread() {
        if (!state.activeId || !state.user || polling || document.hidden) return;

        polling = true;
        const id = state.activeId;
        const token = state.token;

        try {
            const data = await getJson(threadUrl(id) + '?after=' + state.lastId);
            if (token !== state.token) return;

            const known = new Set(state.messages.map(m => m.id));
            const fresh = data.messages.filter(m => !known.has(m.id));
            const readChanged = (data.read_up_to || 0) !== state.readUpTo;

            state.readUpTo = data.read_up_to || 0;
            if (!fresh.length && !readChanged) return;

            const wasNear = nearBottom();
            const temps = state.messages.filter(m => m.pending || m.failed);

            state.messages = state.messages.filter(m => !m.pending && !m.failed).concat(fresh, temps);
            state.lastId = Math.max(state.lastId, maxId(fresh));
            renderMessages();

            const incoming = fresh.filter(m => !m.mine);

            if (incoming.length) {
                announce('Nouveau message de ' + (state.user?.name || 'votre contact'));
                if (wasNear) scrollToBottom(true); else el.jump.hidden = false;
                loadList();
            }
        } catch (err) {
            // Reseau coupe : on retentera au prochain passage
        } finally {
            polling = false;
        }
    }

    setInterval(pollThread, 8000);
    setInterval(function () { if (!document.hidden) loadList(); }, 25000);

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) return;
        pollThread();
        loadList();
    });

    // =====================================================================
    // SAISIE ET ENVOI
    // =====================================================================
    function autosize() {
        el.input.style.height = 'auto';
        el.input.style.height = Math.min(el.input.scrollHeight, 160) + 'px';
    }

    function updateSend() {
        el.send.disabled = !(el.input.value.trim() || el.fileInput.files.length);
    }

    function showError(message) {
        el.error.textContent = message;
        el.error.hidden = false;
    }

    function hideError() {
        el.error.hidden = true;
    }

    function clearFile() {
        el.fileInput.value = '';
        el.file.hidden = true;
        updateSend();
    }

    function setFile(file) {
        if (!file) {
            clearFile();
            return;
        }

        if (!FILE_EXTS.includes(ext(file.name))) {
            clearFile();
            showError('Formats acceptés : images, PDF, Word, Excel ou texte.');
            return;
        }

        if (file.size > MAX_FILE) {
            clearFile();
            showError('La pièce jointe ne doit pas dépasser 10 Mo.');
            return;
        }

        hideError();
        el.fileName.textContent = file.name;
        el.fileSize.textContent = formatSize(file.size);
        el.file.querySelector('.ib-file-icon i').className = 'fa-solid ' + fileIcon(file.name);
        el.file.hidden = false;
        updateSend();
    }

    el.input.addEventListener('input', function () {
        autosize();
        updateSend();
        hideError();
        if (state.activeId) state.drafts[state.activeId] = el.input.value;
    });

    // Entree envoie, Maj + Entree va a la ligne. Sur ecran tactile, Entree
    // reste un retour a la ligne : on envoie avec le bouton.
    el.input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey && !e.isComposing && finePointer) {
            e.preventDefault();
            send();
        }
    });

    el.attach.addEventListener('click', function () {
        el.fileInput.click();
    });

    el.fileInput.addEventListener('change', function () {
        setFile(el.fileInput.files[0]);
    });

    el.fileRemove.addEventListener('click', function () {
        clearFile();
        el.input.focus();
    });

    el.composer.addEventListener('submit', function (e) {
        e.preventDefault();
        send();
    });

    function send() {
        const text = el.input.value.trim();
        const file = el.fileInput.files[0] || null;

        if ((!text && !file) || !state.activeId || !state.user) return;

        const temp = {
            id: 'tmp-' + (++state.tmp),
            mine: true,
            content: text,
            at: new Date().toISOString(),
            pending: true,
            attachment: file ? { name: file.name, url: null, is_image: false } : null,
            to: state.activeId,
            file: file,
        };

        state.messages.push(temp);
        state.drafts[state.activeId] = '';
        el.input.value = '';
        clearFile();
        hideError();
        autosize();
        updateSend();

        renderMessages();
        scrollToBottom(true);
        deliver(temp);
    }

    // Remet un envoi refuse dans le champ, pour le corriger
    function restore(temp) {
        el.input.value = temp.content;

        if (temp.file && window.DataTransfer) {
            try {
                const dt = new DataTransfer();
                dt.items.add(temp.file);
                el.fileInput.files = dt.files;
                setFile(temp.file);
            } catch (err) { /* navigateur ancien : le fichier est a rejoindre */ }
        }

        autosize();
        updateSend();
    }

    async function deliver(temp) {
        const body = new FormData();
        body.append('message', temp.content);
        if (temp.file) body.append('file', temp.file);

        try {
            const res = await fetch(threadUrl(temp.to), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                body: body,
            });

            const data = await res.json().catch(() => ({}));

            // Le membre a change de fil entre-temps : la liste suffit
            if (temp.to !== state.activeId) {
                loadList();
                return;
            }

            if (res.status === 419 || res.status === 401) {
                temp.pending = false;
                temp.failed = true;
                showError('Votre session a expiré : rechargez la page pour continuer.');
                renderMessages();
                return;
            }

            // Refus de validation : inutile de reessayer tel quel, le texte
            // revient dans le champ avec l'explication.
            if (res.status === 422) {
                state.messages.splice(state.messages.indexOf(temp), 1);
                renderMessages();
                restore(temp);
                const first = data.errors ? Object.values(data.errors)[0]?.[0] : null;
                showError(first || data.message || "Le message n'a pas pu être envoyé.");
                return;
            }

            if (!res.ok) throw new Error('HTTP ' + res.status);

            const index = state.messages.indexOf(temp);

            // L'actualisation a pu ramener ce message avant la reponse
            if (state.messages.some(m => m.id === data.id)) {
                state.messages.splice(index, 1);
            } else {
                state.messages.splice(index, 1, data);
            }

            state.lastId = Math.max(state.lastId, data.id);
            renderMessages();
            bumpConversation(data);
        } catch (err) {
            console.error('Envoi :', err);
            temp.pending = false;
            temp.failed = true;
            if (temp.to === state.activeId) renderMessages();
        }
    }

    // =====================================================================
    // DEMARRAGE
    // =====================================================================
    // Lien profond ?avec={id} : la popin « Message » d'une fiche et l'e-mail
    // envoye au proprietaire ouvrent directement le bon fil.
    const startId = parseInt(new URLSearchParams(window.location.search).get('avec'), 10);

    loadList();

    // Sans etat d'historique : sur telephone, la fleche du fil ramene a la
    // liste au lieu de quitter la page.
    if (startId && startId !== me) openThread(startId, true);
}
