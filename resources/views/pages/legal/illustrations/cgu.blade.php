{{-- CGU : les regles de la communaute, cochees une a une. --}}
<svg viewBox="0 0 400 320" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
    <defs>
        <filter id="lgu-shadow" x="-40%" y="-40%" width="180%" height="180%">
            <feDropShadow dx="0" dy="16" stdDeviation="14" flood-color="#000" flood-opacity=".38"/>
        </filter>
        <linearGradient id="lgu-board" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#ff6a2b"/>
            <stop offset="1" stop-color="#d63500"/>
        </linearGradient>
        <path id="lgu-star" d="M0 -7 L1.76 -2.43 L6.66 -2.16 L2.85 .93 L4.11 5.66 L0 3 L-4.11 5.66 L-2.85 .93 L-6.66 -2.16 L-1.76 -2.43 Z"/>
    </defs>

    {{-- Decor --}}
    <circle cx="210" cy="160" r="132" fill="#fff" fill-opacity=".04"/>
    <circle cx="210" cy="160" r="154" fill="none" stroke="#fff" stroke-opacity=".12" stroke-dasharray="3 7"/>
    <path d="M358 58 L360 64 L366 66 L360 68 L358 74 L356 68 L350 66 L356 64 Z" fill="#ff8b62"/>
    <path d="M40 176 L41.5 180.5 L46 182 L41.5 183.5 L40 188 L38.5 183.5 L34 182 L38.5 180.5 Z" fill="#fff" fill-opacity=".55"/>
    <circle cx="116" cy="44" r="3" fill="#ff3c00"/>
    <circle cx="364" cy="262" r="4" fill="#fff" fill-opacity=".22"/>

    {{-- Presse-papiers --}}
    <g transform="rotate(3 216 165)">
        <rect x="132" y="46" width="168" height="238" rx="16" fill="url(#lgu-board)" filter="url(#lgu-shadow)"/>
        <rect x="144" y="62" width="144" height="210" rx="10" fill="#fff"/>

        <rect x="184" y="40" width="64" height="26" rx="9" fill="#1b1d25"/>
        <circle cx="216" cy="40" r="11" fill="#1b1d25"/>
        <circle cx="216" cy="40" r="4" fill="#ff3c00"/>

        <rect x="160" y="84" width="78" height="10" rx="5" fill="#1b1d25"/>
        <rect x="160" y="101" width="54" height="7" rx="3.5" fill="#d8dbe0"/>

        <rect x="160" y="128" width="20" height="20" rx="6" fill="#ff3c00"/>
        <path d="M165 138 L169 142 L175 134" fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
        <rect x="190" y="130" width="80" height="6" rx="3" fill="#c4c9d1"/>
        <rect x="190" y="141" width="56" height="5" rx="2.5" fill="#e7e9ec"/>

        <rect x="160" y="164" width="20" height="20" rx="6" fill="#ff3c00"/>
        <path d="M165 174 L169 178 L175 170" fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
        <rect x="190" y="166" width="72" height="6" rx="3" fill="#c4c9d1"/>
        <rect x="190" y="177" width="48" height="5" rx="2.5" fill="#e7e9ec"/>

        <rect x="160" y="200" width="20" height="20" rx="6" fill="#ff3c00"/>
        <path d="M165 210 L169 214 L175 206" fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
        <rect x="190" y="202" width="84" height="6" rx="3" fill="#c4c9d1"/>
        <rect x="190" y="213" width="60" height="5" rx="2.5" fill="#e7e9ec"/>

        <rect x="161" y="237" width="18" height="18" rx="5.5" fill="#fff" stroke="#d0d5dd" stroke-width="2"/>
        <rect x="190" y="238" width="66" height="6" rx="3" fill="#c4c9d1"/>
        <rect x="190" y="249" width="42" height="5" rx="2.5" fill="#e7e9ec"/>
    </g>

    {{-- Stylo --}}
    <g class="legal-float legal-float--slow">
        <g transform="rotate(28 330 170)">
            <rect x="322" y="90" width="16" height="118" rx="8" fill="#f4f5f7" filter="url(#lgu-shadow)"/>
            <rect x="322" y="90" width="16" height="28" rx="8" fill="#ff3c00"/>
            <rect x="335" y="96" width="3" height="36" rx="1.5" fill="#e13800"/>
            <path d="M322 204 L330 226 L338 204 Z" fill="#d8dbe0"/>
            <path d="M327.3 219 L330 226 L332.7 219 Z" fill="#1b1d25"/>
        </g>
    </g>

    {{-- Membre --}}
    <g class="legal-float">
        <circle cx="86" cy="104" r="30" fill="#262a34" stroke="#fff" stroke-opacity=".12" filter="url(#lgu-shadow)"/>
        <circle cx="86" cy="97" r="8" fill="#ff8b62"/>
        <path d="M72 119 C73 110 79 107.5 86 107.5 C93 107.5 99 110 100 119 Z" fill="#ff8b62"/>
    </g>

    {{-- Avis --}}
    <g class="legal-float legal-float--late">
        <rect x="46" y="214" width="100" height="36" rx="18" fill="#fff" filter="url(#lgu-shadow)"/>
        <use href="#lgu-star" x="66" y="232" fill="#ffb020"/>
        <use href="#lgu-star" x="84" y="232" fill="#ffb020"/>
        <use href="#lgu-star" x="102" y="232" fill="#ffb020"/>
        <rect x="116" y="229" width="18" height="6" rx="3" fill="#d8dbe0"/>
    </g>
</svg>
