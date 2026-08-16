<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #1e293b;
            background: #fff;
        }

        /* Cover */
        .cover {
            padding: 50px 40px 30px;
            border-bottom: 4px solid #e91d28;
            margin-bottom: 20px;
        }

        .cover-logo {
            font-size: 22px;
            font-weight: 900;
            color: #e91d28;
            letter-spacing: -1px;
        }

        .cover-title {
            font-size: 18px;
            font-weight: 900;
            color: #1e293b;
            margin-top: 8px;
        }

        .cover-sub {
            font-size: 10px;
            color: #64748b;
            margin-top: 4px;
        }

        .cover-meta {
            margin-top: 16px;
            display: flex;
            gap: 30px;
        }

        .cover-badge {
            background: #f1f5f9;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 8px;
        }

        .cover-badge strong {
            display: block;
            font-size: 10px;
            color: #1e293b;
        }

        /* Stats bar */
        .stats-row {
            display: flex;
            gap: 10px;
            margin: 0 40px 20px;
        }

        .stat-box {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
        }

        .stat-box .val {
            font-size: 18px;
            font-weight: 900;
            color: #e91d28;
        }

        .stat-box .lbl {
            font-size: 7px;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* Section */
        .section {
            margin: 0 40px 18px;
        }

        .section-title {
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #fff;
            background: #1e293b;
            padding: 6px 12px;
            border-radius: 6px 6px 0 0;
        }

        .section-title span {
            color: #e91d28;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: #f1f5f9;
        }

        thead th {
            padding: 5px 8px;
            font-size: 7.5px;
            font-weight: 900;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        tbody tr {
            border-bottom: 1px solid #f1f5f9;
        }

        tbody tr:nth-child(even) {
            background: #fafbfc;
        }

        tbody td {
            padding: 5px 8px;
            font-size: 8.5px;
            vertical-align: middle;
        }

        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 7px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .badge-ok {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-ko {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-del {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-na {
            background: #f1f5f9;
            color: #64748b;
        }

        .badge-get {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-post {
            background: #ede9fe;
            color: #5b21b6;
        }

        .badge-put {
            background: #fce7f3;
            color: #9d174d;
        }

        .result-box {
            width: 40px;
            height: 14px;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            display: inline-block;
        }

        .res-pass {
            background: #d1fae5;
            color: #065f46;
            font-weight: 900;
            font-size: 7px;
            text-align: center;
            line-height: 14px;
            border-radius: 3px;
            display: inline-block;
            width: 40px;
        }

        .res-fail {
            background: #fee2e2;
            color: #991b1b;
            font-weight: 900;
            font-size: 7px;
            text-align: center;
            line-height: 14px;
            border-radius: 3px;
            display: inline-block;
            width: 40px;
        }

        .res-skip {
            background: #f1f5f9;
            color: #64748b;
            font-weight: 900;
            font-size: 7px;
            text-align: center;
            line-height: 14px;
            border-radius: 3px;
            display: inline-block;
            width: 40px;
        }

        .summary-bar {
            margin: 0 40px 18px;
            display: flex;
            gap: 12px;
        }

        .sum-box {
            flex: 1;
            border-radius: 10px;
            padding: 12px;
            text-align: center;
        }

        .sum-pass {
            background: #d1fae5;
        }

        .sum-fail {
            background: #fee2e2;
        }

        .sum-skip {
            background: #f1f5f9;
        }

        .sum-box .num {
            font-size: 24px;
            font-weight: 900;
        }

        .sum-box .lab {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .footer {
            margin: 20px 40px 10px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
            font-size: 7px;
            color: #94a3b8;
        }

        .note {
            margin: 0 40px 10px;
            background: #fffbeb;
            border-left: 3px solid #f59e0b;
            padding: 8px 12px;
            font-size: 8px;
            color: #78350f;
            border-radius: 0 4px 4px 0;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    {{-- ===== EN-TÊTE ===== --}}
    <div class="cover">
        <div class="cover-logo">OLTEN Agency</div>
        <div class="cover-title">Rapport de Test — Interface Administration</div>
        <div class="cover-sub">Test fonctionnel des actions CRUD & suppressions | Périmètre : ~99% des actions
            hebdomadaires</div>
        <div class="cover-meta">
            <div class="cover-badge"><strong>{{ $date }}</strong>Date de génération</div>
            <div class="cover-badge"><strong>Admin</strong>Profil testé</div>
            <div class="cover-badge"><strong>PostgreSQL</strong>Base de données</div>
            <div class="cover-badge"><strong>Laravel</strong>Framework</div>
        </div>
    </div>

    {{-- ===== STATS ===== --}}
    <div class="stats-row">
        <div class="stat-box">
            <div class="val">{{ $stats['users'] }}</div>
            <div class="lbl">Utilisateurs</div>
        </div>
        <div class="stat-box">
            <div class="val">{{ $stats['covoiturages'] }}</div>
            <div class="lbl">Covoiturages</div>
        </div>
        <div class="stat-box">
            <div class="val">{{ $stats['livraisons'] }}</div>
            <div class="lbl">Livraisons</div>
        </div>
        <div class="stat-box">
            <div class="val">{{ $stats['messages'] }}</div>
            <div class="lbl">Messages</div>
        </div>
        <div class="stat-box">
            <div class="val">{{ $stats['services'] }}</div>
            <div class="lbl">Services</div>
        </div>
    </div>

    {{-- ===== RÉSUMÉ RÉSULTATS ===== --}}
    <div class="summary-bar">
        <div class="sum-box sum-pass">
            <div class="num" style="color:#065f46">{{ $totalPass }}</div>
            <div class="lab" style="color:#065f46">PASS</div>
        </div>
        <div class="sum-box sum-fail">
            <div class="num" style="color:#991b1b">{{ $totalFail }}</div>
            <div class="lab" style="color:#991b1b">FAIL</div>
        </div>
        <div class="sum-box sum-skip">
            <div class="num" style="color:#64748b">{{ $totalSkip }}</div>
            <div class="lab" style="color:#64748b">SKIPPED</div>
        </div>
        <div class="sum-box" style="background:#ede9fe;flex:2">
            <div class="num" style="color:#5b21b6">{{ $totalPass + $totalFail + $totalSkip }}</div>
            <div class="lab" style="color:#5b21b6">CAS DE TEST TOTAL</div>
        </div>
    </div>


    @php
        // [methode, libellé, résultat attendu, résultat observé, clé testResults]
        $sections = [
            [
                'titre' => '01 — Tableau de Bord',
                'cas' => [
                    ['GET', 'Accès dashboard admin', 'Affiche KPIs, graphes, activité récente', 'HTTP 200 OK', '01_01'],
                    ['GET', 'KPI — Utilisateurs', 'Comptage réel depuis la BDD', 'Valeur affichée', '01_02'],
                    ['GET', 'KPI — Covoiturages', 'Comptage réel depuis la BDD', 'Valeur affichée', '01_03'],
                    [
                        'GET',
                        'KPI — Livraisons (Colis/Repas/VTC)',
                        'Sous-totaux affichés séparément',
                        'Valeur affichée',
                        '01_04',
                    ],
                    ['GET', 'KPI — Messages de contact', 'Comptage réel', 'Valeur affichée', '01_05'],
                    ['GET', 'KPI — Services', 'Comptage réel', 'Valeur affichée', '01_06'],
                    [
                        'GET',
                        'Graphe répartition des rôles',
                        'Donut ECharts avec données réelles',
                        'Canvas rendu',
                        '01_07',
                    ],
                    [
                        'GET',
                        'Graphe inscriptions 6 mois',
                        'Barres avec 0 si aucune inscription',
                        'Canvas rendu',
                        '01_08',
                    ],
                    ['GET', 'Tableau activité récente', '8 entrées covoiturages + ventes', 'Lignes affichées', '01_09'],
                    ['GET', 'Panneau nouveaux membres', '5 derniers inscrits avec rôle', 'Lignes affichées', '01_10'],
                ],
            ],
            [
                'titre' => '02 — Gestion des Utilisateurs',
                'cas' => [
                    ['GET', 'Liste des utilisateurs', 'Pagination + filtre recherche', 'HTTP 200 OK', '02_01'],
                    ['GET', 'Voir fiche utilisateur', 'Toutes les infos du profil', 'HTTP 200 OK', '02_02'],
                    ['GET', 'Formulaire création utilisateur', 'Champs requis présents', 'HTTP 200 OK', '02_03'],
                    ['POST', 'Créer un utilisateur', 'Validation + hash + redirect', '302 + BDD mise à jour', '02_04'],
                    ['GET', 'Formulaire édition utilisateur', 'Pré-remplissage des champs', 'HTTP 200 OK', '02_05'],
                    ['PATCH', 'Modifier un utilisateur', 'Mise à jour BDD + redirection', '302 Redirect', '02_06'],
                    [
                        'DELETE',
                        'Supprimer un utilisateur',
                        'Suppression + redirect + flash',
                        '302 + Soft Delete (champ deleted_at rempli)',
                        '02_07',
                    ],
                    ['GET', 'Dashboard bloqué sans authentification', 'Redirect vers login', '302 Redirect', '02_08'],
                    [
                        'GET',
                        'Accès refusé — compte non-admin',
                        'Middleware role:admin → 403',
                        'HTTP 403 Forbidden',
                        '02_09',
                    ],
                ],
            ],
            [
                'titre' => '03 — Catégories',
                'cas' => [
                    ['GET', 'Liste des catégories', 'Affichage de toutes les catégories', 'HTTP 200 OK', '03_01'],
                    ['GET', 'Formulaire création catégorie', 'Champs requis présents', 'HTTP 200 OK', '03_02'],
                    ['POST', 'Créer une catégorie', 'Insertion BDD + redirect', '302 + BDD OK', '03_03'],
                    ['GET', 'Formulaire édition catégorie', 'Valeurs pré-remplies', 'HTTP 200 OK', '03_04'],
                    ['PUT', 'Modifier une catégorie', 'Mise à jour BDD', '302 Redirect', '03_05'],
                    [
                        'DELETE',
                        'Supprimer une catégorie sans sous-cat.',
                        'Suppression réussie + flash',
                        '302 + Soft Delete (champ deleted_at rempli)',
                        '03_06',
                    ],
                    [
                        'POST',
                        'Création catégorie sans nom',
                        'Erreur validation champ nom',
                        'Erreur session nom',
                        '03_07',
                    ],
                ],
            ],
            [
                'titre' => '04 — Sous-catégories',
                'cas' => [
                    ['GET', 'Liste des sous-catégories', 'Affichage avec catégorie parente', 'HTTP 200 OK', '04_01'],
                    ['GET', 'Formulaire création', 'Sélecteur catégorie parente', 'HTTP 200 OK', '04_02'],
                    ['POST', 'Créer une sous-catégorie', 'Insertion BDD + redirect', '302 + BDD OK', '04_03'],
                    [
                        'DELETE',
                        'Supprimer une sous-catégorie',
                        'Suppression + flash',
                        '302 + Soft Delete (champ deleted_at rempli)',
                        '04_04',
                    ],
                ],
            ],
            [
                'titre' => '05 — Types de Service',
                'cas' => [
                    ['GET', 'Liste des types de service', 'Affichage complet', 'HTTP 200 OK', '05_01'],
                    ['POST', 'Créer un type de service', 'Insertion BDD', '302 + BDD OK', '05_02'],
                    ['PUT', 'Modifier un type de service', 'Mise à jour', '302 Redirect', '05_03'],
                    [
                        'DELETE',
                        'Supprimer un type de service',
                        'Suppression réussie',
                        '302 + Soft Delete (champ deleted_at rempli)',
                        '05_04',
                    ],
                ],
            ],
            [
                'titre' => '06 — Services',
                'cas' => [
                    ['GET', 'Liste des services', 'Filtre par nom + type', 'HTTP 200 OK', '06_01'],
                    ['GET', 'Formulaire création service', 'Sélecteur type + upload image', 'HTTP 200 OK', '06_02'],
                    ['POST', 'Créer un service', 'Insertion + stockage image', '302 + BDD OK', '06_03'],
                    ['PUT', 'Modifier un service', 'Mise à jour + nouvelle image si fournie', '302 Redirect', '06_04'],
                    [
                        'DELETE',
                        'Supprimer un service',
                        'Suppression BDD + fichier image',
                        '302 + Soft Delete (champ deleted_at rempli)',
                        '06_05',
                    ],
                ],
            ],
            [
                'titre' => '07 — Covoiturages',
                'cas' => [
                    ['GET', 'Liste des covoiturages', 'Filtre conducteur + statut', 'HTTP 200 OK', '07_01'],
                    ['GET', 'Filtrer par statut "pending"', 'Seules les courses en attente', 'HTTP 200 OK', '07_02'],
                    ['GET', 'Filtrer par statut "actif"', 'Seules les courses actives', 'HTTP 200 OK', '07_03'],
                    [
                        'PATCH',
                        'Passer de "pending" à "actif"',
                        'Statut mis à jour + redirect',
                        '302 + statut=actif',
                        '07_04',
                    ],
                    ['PATCH', 'Passer de "actif" à "inactif"', 'Toggle correct', '302 + statut=inactif', '07_05'],
                ],
            ],
            [
                'titre' => '08 — Messages de Contact',
                'cas' => [
                    ['GET', 'Liste des messages', 'Pagination + filtre nom/email/sujet', 'HTTP 200 OK', '08_01'],
                    ['GET', 'Voir détail d\'un message', 'Toutes les infos affichées', 'HTTP 200 OK', '08_02'],
                    [
                        'DELETE',
                        'Supprimer un message',
                        'Suppression + flash "succès"',
                        '302 + Soft Delete (champ deleted_at rempli)',
                        '08_03',
                    ],
                    ['DELETE', 'Supprimer un message inexistant', 'Erreur 404', 'HTTP 404', '08_04'],
                ],
            ],
            [
                'titre' => '09 — Annonces',
                'cas' => [
                    ['GET', 'Liste des annonces', 'Affichage admin de toutes les annonces', 'HTTP 200 OK', '09_01'],
                    ['PATCH', 'Approuver une annonce', '"is_approved" mis à true', 'HTTP 200 OK', '09_02'],
                ],
            ],
            [
                'titre' => '10 — Cartes VTC',
                'cas' => [
                    ['GET', 'Liste des demandes VTC', 'Documents en attente de validation', 'HTTP 200 OK', '10_01'],
                    ['POST', 'Approuver un document VTC', 'Statut "approuvé"', '302 Redirect', '10_02'],
                    ['POST', 'Rejeter un document VTC', 'Statut "refusé" + raison', '302 Redirect', '10_03'],
                ],
            ],
            [
                'titre' => '11 — Authentification Admin',
                'cas' => [
                    ['GET', 'Page de connexion admin', 'Formulaire email + mot de passe', 'HTTP 200 OK', '11_01'],
                    ['POST', 'Connexion avec bons identifiants', 'Redirection vers dashboard', '302 → /admin', '11_02'],
                    ['POST', 'Connexion mauvais mot de passe', 'Erreur, pas de session', 'Erreur session', '11_03'],
                    [
                        'GET',
                        'Accès compte non-admin',
                        'Accès refusé (middleware role:admin)',
                        'HTTP 403 Forbidden',
                        '11_04',
                    ],
                    ['POST', 'Déconnexion admin', 'Session détruite + redirect login', '302 + guest', '11_05'],
                ],
            ],
        ];
    @endphp

    @foreach ($sections as $idx => $section)
        @if ($idx > 0 && $idx % 3 === 0)
            <div class="page-break"></div>
        @endif

        <div class="section">
            <div class="section-title">{{ $section['titre'] }}</div>
            <table>
                <thead>
                    <tr>
                        <th style="width:40px">Méthode</th>
                        <th style="width:155px">Cas de test</th>
                        <th>Résultat attendu</th>
                        <th style="width:90px">Résultat observé</th>
                        <th style="width:48px;text-align:center">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($section['cas'] as $cas)
                        @php
                            $key = $cas[4];
                            $status = $testResults[$key] ?? 'N/A';
                            $methBadge = match ($cas[0]) {
                                'GET' => 'badge-get',
                                'POST' => 'badge-post',
                                'PUT', 'PATCH' => 'badge-put',
                                'DELETE' => 'badge-del',
                                default => 'badge-na',
                            };
                            $statusClass = match ($status) {
                                'PASS' => 'res-pass',
                                'FAIL' => 'res-fail',
                                'SKIP' => 'res-skip',
                                default => 'res-skip',
                            };
                            $statusLabel = match ($status) {
                                'PASS' => ' PASS',
                                'FAIL' => ' FAIL',
                                'SKIP' => ' SKIP',
                                default => 'N/A',
                            };
                        @endphp
                        <tr>
                            <td><span class="badge {{ $methBadge }}">{{ $cas[0] }}</span></td>
                            <td>{{ $cas[1] }}</td>
                            <td style="color:#475569">{{ $cas[2] }}</td>
                            <td style="color:#334155;font-size:7.5px">{{ $cas[3] }}</td>
                            <td style="text-align:center"><span class="{{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="page-break"></div>

    {{-- ===== LÉGENDE ===== --}}
    <div class="section" style="margin-top:20px">
        <div class="section-title">Légende & Instructions</div>
        <table>
            <tbody>
                <tr>
                    <td style="width:120px;font-weight:700">Colonne Résultat</td>
                    <td>Écrire le résultat observé (ex: "200 OK", "redirect /admin", message d'erreur...)</td>
                </tr>
                <tr>
                    <td style="font-weight:700">Colonne Statut</td>
                    <td><span class="badge badge-ok">PASS</span> &nbsp; <span class="badge badge-ko">FAIL</span> &nbsp;
                        <span class="badge badge-na">N/A</span>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:700">DELETE avec données liées</td>
                    <td>Vérifier qu'il n'y a pas d'erreur silencieuse — toujours tester avec un enregistrement qui a des
                        relations</td>
                </tr>
                <tr>
                    <td style="font-weight:700">Suppressions critiques</td>
                    <td>Toujours tester via UI (bouton de confirmation) ET directement via l'URL pour vérifier le
                        middleware CSRF</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <span>OLTEN — Rapport de test administration</span>
        <span>Généré le {{ $date }}</span>
        <span>Confidentiel — usage interne</span>
    </div>

</body>

</html>
