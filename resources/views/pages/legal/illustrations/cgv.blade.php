{{-- CGV : le recu, la carte bancaire et le paiement securise. --}}
<svg viewBox="0 0 400 320" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
    <defs>
        <filter id="lgv-shadow" x="-40%" y="-40%" width="180%" height="180%">
            <feDropShadow dx="0" dy="16" stdDeviation="14" flood-color="#000" flood-opacity=".38"/>
        </filter>
        <linearGradient id="lgv-card" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#ff6a2b"/>
            <stop offset="1" stop-color="#c93200"/>
        </linearGradient>
    </defs>

    {{-- Decor --}}
    <circle cx="210" cy="160" r="132" fill="#fff" fill-opacity=".04"/>
    <circle cx="210" cy="160" r="154" fill="none" stroke="#fff" stroke-opacity=".12" stroke-dasharray="3 7"/>
    <path d="M66 58 L68 64 L74 66 L68 68 L66 74 L64 68 L58 66 L64 64 Z" fill="#ff8b62"/>
    <path d="M372 196 L373.5 200.5 L378 202 L373.5 203.5 L372 208 L370.5 203.5 L366 202 L370.5 200.5 Z" fill="#fff" fill-opacity=".55"/>
    <circle cx="112" cy="120" r="3" fill="#ff3c00"/>
    <circle cx="40" cy="150" r="4" fill="#fff" fill-opacity=".22"/>

    {{-- Recu --}}
    <g transform="rotate(-3 216 150)">
        <path d="M146 48 Q146 34 160 34 L272 34 Q286 34 286 48 L286 262 L276 254 L266 262 L256 254 L246 262 L236 254 L226 262 L216 254 L206 262 L196 254 L186 262 L176 254 L166 262 L156 254 L146 262 Z"
              fill="#fff" filter="url(#lgv-shadow)"/>

        <circle cx="216" cy="62" r="13" fill="#fff1ec"/>
        <circle cx="216" cy="62" r="5.5" fill="none" stroke="#ff3c00" stroke-width="3"/>
        <rect x="186" y="84" width="60" height="8" rx="4" fill="#1b1d25"/>
        <rect x="196" y="98" width="40" height="6" rx="3" fill="#d8dbe0"/>

        <line x1="162" y1="118" x2="270" y2="118" stroke="#d8dbe0" stroke-width="1.5" stroke-dasharray="4 4"/>
        <rect x="162" y="130" width="64" height="7" rx="3.5" fill="#c4c9d1"/>
        <rect x="246" y="130" width="24" height="7" rx="3.5" fill="#98a2b3"/>
        <rect x="162" y="150" width="52" height="7" rx="3.5" fill="#c4c9d1"/>
        <rect x="246" y="150" width="24" height="7" rx="3.5" fill="#98a2b3"/>
        <rect x="162" y="170" width="70" height="7" rx="3.5" fill="#c4c9d1"/>
        <rect x="246" y="170" width="24" height="7" rx="3.5" fill="#98a2b3"/>
        <line x1="162" y1="192" x2="270" y2="192" stroke="#d8dbe0" stroke-width="1.5" stroke-dasharray="4 4"/>

        <rect x="162" y="206" width="40" height="9" rx="4.5" fill="#1b1d25"/>
        <rect x="230" y="204" width="40" height="13" rx="6.5" fill="#ff3c00"/>
        <line x1="170" y1="236" x2="262" y2="236" stroke="#1b1d25" stroke-opacity=".75" stroke-width="12"
              stroke-dasharray="2 2 1 3 3 1 1 2 2 3 1 1"/>
    </g>

    {{-- Carte bancaire --}}
    <g class="legal-float legal-float--slow">
        <g transform="rotate(-12 136 222)">
            <rect x="60" y="176" width="152" height="94" rx="13" fill="url(#lgv-card)" filter="url(#lgv-shadow)"/>
            <rect x="76" y="196" width="26" height="19" rx="4" fill="#ffd8c6"/>
            <path d="M76 205.5 H102 M89 196 V215" stroke="#f0b096" stroke-width="1.3"/>
            <path d="M112 200 a8 8 0 0 1 0 12 M117 197 a12 12 0 0 1 0 18" fill="none" stroke="#fff" stroke-opacity=".6" stroke-width="2" stroke-linecap="round"/>
            <rect x="76" y="230" width="24" height="6" rx="3" fill="#fff" fill-opacity=".85"/>
            <rect x="106" y="230" width="24" height="6" rx="3" fill="#fff" fill-opacity=".85"/>
            <rect x="136" y="230" width="24" height="6" rx="3" fill="#fff" fill-opacity=".85"/>
            <rect x="166" y="230" width="24" height="6" rx="3" fill="#fff" fill-opacity=".85"/>
            <rect x="76" y="248" width="52" height="5" rx="2.5" fill="#fff" fill-opacity=".5"/>
            <circle cx="182" cy="251" r="8" fill="#fff" fill-opacity=".35"/>
            <circle cx="193" cy="251" r="8" fill="#fff" fill-opacity=".55"/>
        </g>
    </g>

    {{-- Pieces --}}
    <g class="legal-float">
        <circle cx="352" cy="124" r="18" fill="#f0a92e" filter="url(#lgv-shadow)"/>
        <circle cx="352" cy="124" r="13" fill="none" stroke="#d58a12" stroke-width="1.5"/>
        <circle cx="322" cy="84" r="30" fill="#ffc24b" filter="url(#lgv-shadow)"/>
        <circle cx="322" cy="84" r="23" fill="none" stroke="#e89a1c" stroke-width="2"/>
        <text x="322" y="94" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="28" font-weight="700" fill="#b8700a">€</text>
    </g>

    {{-- Paiement securise --}}
    <g class="legal-float legal-float--late">
        <rect x="262" y="236" width="106" height="38" rx="19" fill="#262a34" stroke="#fff" stroke-opacity=".12" filter="url(#lgv-shadow)"/>
        <path d="M280 253 V249 a5 5 0 0 1 10 0 V253" fill="none" stroke="#ff8b62" stroke-width="2.4"/>
        <rect x="276" y="252" width="18" height="13" rx="3" fill="#ff8b62"/>
        <rect x="302" y="247" width="52" height="6" rx="3" fill="#fff" fill-opacity=".85"/>
        <rect x="302" y="258" width="34" height="5" rx="2.5" fill="#fff" fill-opacity=".35"/>
    </g>
</svg>
