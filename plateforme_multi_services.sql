CREATE TABLE objets (
    objet_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    proprietaire_id BIGINT UNSIGNED NOT NULL,
    titre VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    categorie VARCHAR(100),
    prix_jour DECIMAL(10,2),
    disponible BOOLEAN DEFAULT TRUE,
    localisation VARCHAR(255) NOT NULL,
    condition_sanitaire TEXT,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_objets_proprietaire
        FOREIGN KEY (proprietaire_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE INDEX idx_objets_categorie ON objets(categorie);
CREATE INDEX idx_objets_proprietaire ON objets(proprietaire_id);

CREATE TABLE covoiturages (
    covoiturage_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conducteur_id BIGINT UNSIGNED NOT NULL,
    depart VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    date_depart TIMESTAMP NOT NULL,
    nb_places INT,
    prix_place DECIMAL(10,2),
    commission_plateforme DECIMAL(10,2) DEFAULT 0,
    statut ENUM('actif','complet','annule') DEFAULT 'actif',
    reglementation_applicable TEXT,

    FOREIGN KEY (conducteur_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_covoiturages_conducteur ON covoiturages(conducteur_id);

CREATE TABLE livraisons_vtc (
    vtc_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    chauffeur_id BIGINT UNSIGNED NULL,
    adresse_depart VARCHAR(255) NOT NULL,
    adresse_arrivee VARCHAR(255) NOT NULL,
    distance_km DECIMAL(5,2),
    prix_base DECIMAL(10,2),
    commission_plateforme DECIMAL(10,2) DEFAULT 0,
    prix_total_affiche DECIMAL(10,2),
    statut ENUM('en_attente','en_cours','termine','annule') DEFAULT 'en_attente',
    reglementation_transport TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (chauffeur_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_vtc_client ON livraisons_vtc(client_id);
CREATE INDEX idx_vtc_chauffeur ON livraisons_vtc(chauffeur_id);

CREATE TABLE commissions (
    commission_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type_service VARCHAR(30) UNIQUE NOT NULL,
    taux DECIMAL(5,2),
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE transactions (
    transaction_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type_operation VARCHAR(20) NOT NULL,
    service_type VARCHAR(30) NOT NULL,
    service_id BIGINT UNSIGNED NOT NULL,
    montant DECIMAL(10,2),
    moyen_paiement VARCHAR(20),
    statut ENUM('en_attente','validee','echouee') DEFAULT 'en_attente',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_transactions_service
ON transactions(service_type, service_id);

CREATE INDEX idx_transactions_user ON transactions(user_id);

CREATE TABLE revenus_plateforme (
    revenu_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_id BIGINT UNSIGNED NOT NULL,
    montant_commission DECIMAL(10,2) NOT NULL,
    date_reception TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (transaction_id)
        REFERENCES transactions(transaction_id)
        ON DELETE CASCADE
);

CREATE TABLE points_fidelite (
    fidelite_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    action_source VARCHAR(30),
    points_gagnes INT DEFAULT 0,
    points_utilises INT DEFAULT 0,
    date_operation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE INDEX idx_points_user ON points_fidelite(user_id);