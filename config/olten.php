<?php

/*
|--------------------------------------------------------------------------
| Identite de marque Olten
|--------------------------------------------------------------------------
| Les e-mails ne peuvent pas charger public/assets/css/style.css : un client
| de messagerie ne lit pas de feuille externe, tout doit etre ecrit en ligne
| dans le message. Les valeurs du site sont donc recopiees ici, une fois,
| pour que les douze gabarits n'aient pas chacun leur nuance d'orange.
|
| Toute valeur ci-dessous vient de la feuille du site : la source est notee
| en commentaire. Si le site change de couleur, c'est ici qu'on suit.
*/

return [

    'email' => [

        /*
         * Pile de polices.
         *
         * Montserrat est la police declaree par le site (--font-family,
         * style.css:9). Les replis couvrent les clients qui ne chargent pas
         * de police distante ; les deux dernieres familles evitent que les
         * emoji des libelles ne tombent en carres vides sous Windows.
         */
        'font' => "'Montserrat',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji'",

        'colors' => [
            // Orange de marque — style.css:2 (--color-primary)
            'primary'      => '#ff3c00',
            // style.css:3 (--color-primary-dark)
            'primaryDark'  => '#e13800',
            // Orange lisible sur fond sombre — style.css:6274 (.cs-hero-tag)
            'primarySoft'  => '#ff8b62',

            // Encre — style.css:4 et search.css:135
            'ink'          => '#1f2328',
            // Gris de note et de meta — style.css:6148 (--cs-ink-soft)
            'inkSoft'      => '#6b7280',
            // Gris des sous-titres — search.css:154 (.sg-sub)
            'inkFaint'     => '#8b929a',

            // Filets — style.css:6146 (--cs-line) et 44 (bas du header)
            'line'         => '#e7e9ec',
            'lineHeader'   => '#ebedf0',
            // Filet chaud du pied de page du site — style.css:5317
            'lineFooter'   => '#e4dcce',

            // Fonds — style.css:6149 (--cs-tint) et 5318 (.site-footer)
            'tint'         => '#fff5f1',
            'tintBorder'   => '#ffe0d2',
            'page'         => '#f5f6f8',
            'white'        => '#ffffff',
            'footer'       => '#f8fafc',
            // Encre chaude du pied de page du site — style.css:5319
            'footerInk'    => '#15110d',
            // Mention legale du pied de page — style.css:5420
            'footerFaint'  => '#6b6259',

            // Bandeau sombre — style.css:6210 (.cs-hero sans photo)
            'heroFrom'     => '#12141a',
            'heroMid'      => '#1b1d25',
            'heroTo'       => '#241a17',
            // Aplats calcules du surtitre orange pose sur ce fond, pour les
            // clients qui n'appliquent ni rgba ni degrade (Outlook Windows).
            'heroTagBg'    => '#381a16',
            'heroTagLine'  => '#753f2f',
        ],

        /*
         * Coordonnees affichees en pied de message.
         * Source : resources/views/components/footer.blade.php
         */
        'contact' => 'olten-location@outlook.fr',
        'address' => "L'Horme, 42152, Loire, France",
        'tagline' => 'Louez, vendez, covoiturez et faites livrer entre nous.',
    ],

];
