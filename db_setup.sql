-- Création de la base de données (si elle n'existe pas déjà)
CREATE DATABASE IF NOT EXISTS omnes_immobilier CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE omnes_immobilier;

-- Table Utilisateur
CREATE TABLE IF NOT EXISTS Utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    telephone VARCHAR(20),
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    type_utilisateur ENUM('client', 'agent', 'admin') NOT NULL,
    statut ENUM('actif', 'inactif') DEFAULT 'actif'
);

-- Table Client
CREATE TABLE IF NOT EXISTS Client (
    id_client INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    adresse VARCHAR(255),
    ville VARCHAR(100),
    code_postal VARCHAR(20),
    pays VARCHAR(50),
    informations_bancaires TEXT,
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE
);

-- Table Agent_Immobilier
CREATE TABLE IF NOT EXISTS Agent_Immobilier (
    id_agent INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    specialite VARCHAR(100),
    biographie TEXT,
    cv_path VARCHAR(255),
    photo_path VARCHAR(255),
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE
);

-- Table Administrateur
CREATE TABLE IF NOT EXISTS Administrateur (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    niveau_acces INT DEFAULT 1,
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE
);

-- Table Propriete
CREATE TABLE IF NOT EXISTS Propriete (
    id_propriete INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    prix DECIMAL(10, 2) NOT NULL,
    surface DECIMAL(10, 2),
    nb_pieces INT,
    nb_chambres INT,
    nb_salles_bain INT,
    etage INT,
    balcon BOOLEAN DEFAULT FALSE,
    parking BOOLEAN DEFAULT FALSE,
    ascenseur BOOLEAN DEFAULT FALSE,
    adresse VARCHAR(255) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    code_postal VARCHAR(20) NOT NULL,
    pays VARCHAR(50) NOT NULL,
    statut ENUM('disponible', 'vendu', 'loué') DEFAULT 'disponible',
    type_propriete ENUM('résidentiel', 'commercial', 'terrain', 'location') NOT NULL,
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_agent INT NOT NULL,
    FOREIGN KEY (id_agent) REFERENCES Agent_Immobilier(id_agent)
);

-- Table Media
CREATE TABLE IF NOT EXISTS Media (
    id_media INT AUTO_INCREMENT PRIMARY KEY,
    id_propriete INT NOT NULL,
    type ENUM('photo', 'video') NOT NULL,
    url_path VARCHAR(255) NOT NULL,
    est_principale BOOLEAN DEFAULT FALSE,
    titre VARCHAR(255),
    FOREIGN KEY (id_propriete) REFERENCES Propriete(id_propriete) ON DELETE CASCADE
);

-- Table Disponibilite
CREATE TABLE IF NOT EXISTS Disponibilite (
    id_disponibilite INT AUTO_INCREMENT PRIMARY KEY,
    id_agent INT NOT NULL,
    jour DATE NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    statut ENUM('disponible', 'indisponible', 'réservé') DEFAULT 'disponible',
    FOREIGN KEY (id_agent) REFERENCES Agent_Immobilier(id_agent) ON DELETE CASCADE
);

-- Table Rendez_Vous
CREATE TABLE IF NOT EXISTS Rendez_Vous (
    id_rdv INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT NOT NULL,
    id_agent INT NOT NULL,
    id_propriete INT NOT NULL,
    date DATE NOT NULL,
    heure TIME NOT NULL,
    motif TEXT,
    statut ENUM('confirmé', 'annulé', 'en attente') DEFAULT 'en attente',
    commentaire TEXT,
    FOREIGN KEY (id_client) REFERENCES Client(id_client) ON DELETE CASCADE,
    FOREIGN KEY (id_agent) REFERENCES Agent_Immobilier(id_agent) ON DELETE CASCADE,
    FOREIGN KEY (id_propriete) REFERENCES Propriete(id_propriete) ON DELETE CASCADE
);

-- Table Message
CREATE TABLE IF NOT EXISTS Message (
    id_message INT AUTO_INCREMENT PRIMARY KEY,
    id_expediteur INT NOT NULL,
    id_destinataire INT NOT NULL,
    contenu TEXT NOT NULL,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    lu BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_expediteur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_destinataire) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE
);

-- Table Evenement
CREATE TABLE IF NOT EXISTS Evenement (
    id_evenement INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    image_path VARCHAR(255),
    actif BOOLEAN DEFAULT TRUE
);