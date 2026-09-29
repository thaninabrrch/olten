{{-- Confidentialite : les donnees sous bouclier, les cookies, les droits. --}}
<svg viewBox="0 0 400 320" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
    <defs>
        <filter id="lgc-shadow" x="-40%" y="-40%" width="180%" height="180%">
            <feDropShadow dx="0" dy="16" stdDeviation="14" flood-color="#000" flood-opacity=".38"/>
        </filter>
        <linearGradient id="lgc-shield" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#ff6a2b"/>
            <stop offset="1" stop-color="#d63500"/>
        </linearGradient>
        {{-- La croquee du cookie : un masque plutot qu'un disque de la
             couleur du fond, qui est un degrade. --}}
        <mask id="lgc-bite" maskUnits="userSpaceOnUse" x="0" y="0" width="400" height="320">
            <rect x="0" y="0" width="400" height="320" fill="#fff"/>
            <circle cx="93" cy="219" r="10" fill="#000"/>
        </mask>
    </defs>

    {{-- Decor --}}
    <circle cx="200" cy="160" r="132" fill="#fff" fill-opacity=".04"/>
    <path d="M362 50 L364 56 L370 58 L364 60 L362 66 L360 60 L354 58 L360 56 Z" fill="#ff8b62"/>
    <path d="M374 214 L375.5 218.5 L380 220 L375.5 221.5 L374 226 L372.5 221.5 L368 220 L372.5 218.5 Z" fill="#fff" fill-opacity=".55"/>
    <circle cx="44" cy="136" r="4" fill="#fff" fill-opacity=".22"/>

    {{-- Document protege --}}
    <g transform="rotate(8 262 150)">
        <rect x="200" y="58" width="132" height="172" rx="12" fill="#fff" filter="url(#lgc-shadow)"/>
        <rect x="248" y="80" width="68" height="8" rx="4" fill="#1b1d25"/>
        <rect x="248" y="100" width="68" height="5" rx="2.5" fill="#e1e4e8"/>
        <rect x="248" y="114" width="58" height="5" rx="2.5" fill="#e1e4e8"/>
        <rect x="248" y="128" width="66" height="5" rx="2.5" fill="#e1e4e8"/>
        <rect x="248" y="142" width="48" height="5" rx="2.5" fill="#e1e4e8"/>
        <rect x="248" y="162" width="68" height="5" rx="2.5" fill="#e1e4e8"/>
        <rect x="248" y="176" width="54" height="5" rx="2.5" fill="#e1e4e8"/>
        <rect x="248" y="190" width="62" height="5" rx="2.5" fill="#e1e4e8"/>
        <rect x="248" y="204" width="36" height="5" rx="2.5" fill="#ff3c00" fill-opacity=".55"/>
    </g>

    {{-- Orbite --}}
    <ellipse cx="178" cy="170" rx="150" ry="58" fill="none" stroke="#fff" stroke-opacity=".16" stroke-dasharray="2 6"
             transform="rotate(-12 178 170)"/>

    {{-- Bouclier et cadenas --}}
    <path d="M170 64 L240 90 Q244 92 244 97 L244 150 C244 206 212 240 170 262 C128 240 96 206 96 150 L96 97 Q96 92 100 90 Z"
          fill="url(#lgc-shield)" filter="url(#lgc-shadow)"/>
    <path d="M170 64 L240 90 Q244 92 244 97 L244 150 C244 206 212 240 170 262 Z" fill="#000" fill-opacity=".08"/>
    <path d="M170 80 L228 101 L228 150 C228 197 202 226 170 245 C138 226 112 197 112 150 L112 101 Z"
          fill="none" stroke="#fff" stroke-opacity=".28" stroke-width="2"/>
    <path d="M152 150 V136 A18 18 0 0 1 188 136 V150" fill="none" stroke="#fff" stroke-width="9" stroke-linecap="round"/>
    <rect x="140" y="146" width="60" height="50" rx="10" fill="#fff"/>
    <circle cx="170" cy="166" r="6.5" fill="#e13800"/>
    <rect x="167" y="168" width="6" height="14" rx="3" fill="#e13800"/>

    <circle cx="36" cy="180" r="5" fill="#ff3c00"/>
    <circle cx="115" cy="235" r="4" fill="#fff" fill-opacity=".6"/>

    {{-- Donnee validee --}}
    <g class="legal-float legal-float--late">
        <circle cx="250" cy="72" r="16" fill="#fff" filter="url(#lgc-shadow)"/>
        <path d="M242.5 72 L248 77.5 L258 66.5" fill="none" stroke="#ff3c00" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
    </g>

    {{-- Vos droits --}}
    <g class="legal-float legal-float--slow">
        <rect x="20" y="58" width="96" height="36" rx="18" fill="#262a34" stroke="#fff" stroke-opacity=".12" filter="url(#lgc-shadow)"/>
        <circle cx="40" cy="76" r="6" fill="none" stroke="#ff8b62" stroke-width="3"/>
        <path d="M46 76 H58 M54 76 V81" fill="none" stroke="#ff8b62" stroke-width="3" stroke-linecap="round"/>
        <rect x="66" y="69" width="38" height="6" rx="3" fill="#fff" fill-opacity=".85"/>
        <rect x="66" y="80" width="24" height="5" rx="2.5" fill="#fff" fill-opacity=".35"/>
    </g>

    {{-- Cookie --}}
    <g class="legal-float">
        <g mask="url(#lgc-bite)">
            <circle cx="72" cy="238" r="25" fill="#d9a066" filter="url(#lgc-shadow)"/>
            <circle cx="72" cy="238" r="25" fill="none" stroke="#c28449" stroke-width="2"/>
        </g>
        <circle cx="63" cy="229" r="3.2" fill="#7a4a26"/>
        <circle cx="78" cy="242" r="3.4" fill="#7a4a26"/>
        <circle cx="64" cy="249" r="2.6" fill="#7a4a26"/>
        <circle cx="80" cy="228" r="2.3" fill="#7a4a26"/>
        <circle cx="56" cy="239" r="2" fill="#7a4a26"/>
        <circle cx="88" cy="248" r="2.2" fill="#7a4a26"/>
    </g>
</svg>
