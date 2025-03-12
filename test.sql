-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : mer. 12 mars 2025 à 17:05
-- Version du serveur : 8.0.40
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `omnes_immobilier`
--
CREATE DATABASE IF NOT EXISTS `omnes_immobilier` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `omnes_immobilier`;

-- --------------------------------------------------------

--
-- Structure de la table `Utilisateur`
--

CREATE TABLE `Utilisateur` (
  `id_utilisateur` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `date_inscription` datetime DEFAULT CURRENT_TIMESTAMP,
  `type_utilisateur` enum('client','agent','admin') NOT NULL,
  `statut` enum('actif','inactif') DEFAULT 'actif',
  PRIMARY KEY (`id_utilisateur`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Structure de la table `Administrateur`
--

CREATE TABLE `Administrateur` (
  `id_admin` int NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int NOT NULL,
  `niveau_acces` int DEFAULT '1',
  PRIMARY KEY (`id_admin`),
  KEY `id_utilisateur` (`id_utilisateur`),
  CONSTRAINT `administrateur_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `Utilisateur` (`id_utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Structure de la table `Client`
--

CREATE TABLE `Client` (
  `id_client` int NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int NOT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `code_postal` varchar(20) DEFAULT NULL,
  `pays` varchar(50) DEFAULT NULL,
  `informations_bancaires` text,
  PRIMARY KEY (`id_client`),
  KEY `id_utilisateur` (`id_utilisateur`),
  CONSTRAINT `client_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `Utilisateur` (`id_utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Structure de la table `Agent_Immobilier`
--

CREATE TABLE `Agent_Immobilier` (
  `id_agent` int NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int NOT NULL,
  `specialite` varchar(100) DEFAULT NULL,
  `biographie` text,
  `cv_path` varchar(255) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_agent`),
  KEY `id_utilisateur` (`id_utilisateur`),
  CONSTRAINT `agent_immobilier_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `Utilisateur` (`id_utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Structure de la table `Propriete`
--

CREATE TABLE `Propriete` (
  `id_propriete` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `description` text,
  `prix` decimal(10,2) NOT NULL,
  `surface` decimal(10,2) DEFAULT NULL,
  `nb_pieces` int DEFAULT NULL,
  `nb_chambres` int DEFAULT NULL,
  `nb_salles_bain` int DEFAULT NULL,
  `etage` int DEFAULT NULL,
  `balcon` tinyint(1) DEFAULT '0',
  `parking` tinyint(1) DEFAULT '0',
  `ascenseur` tinyint(1) DEFAULT '0',
  `adresse` varchar(255) NOT NULL,
  `ville` varchar(100) NOT NULL,
  `code_postal` varchar(20) NOT NULL,
  `pays` varchar(50) NOT NULL,
  `statut` enum('disponible','vendu','loué') DEFAULT 'disponible',
  `type_propriete` enum('résidentiel','commercial','terrain','location') NOT NULL,
  `date_ajout` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_agent` int NOT NULL,
  PRIMARY KEY (`id_propriete`),
  KEY `id_agent` (`id_agent`),
  CONSTRAINT `propriete_ibfk_1` FOREIGN KEY (`id_agent`) REFERENCES `Agent_Immobilier` (`id_agent`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Structure de la table `Media`
--

CREATE TABLE `Media` (
  `id_media` int NOT NULL AUTO_INCREMENT,
  `id_propriete` int NOT NULL,
  `type` enum('photo','video') NOT NULL,
  `url_path` varchar(255) NOT NULL,
  `est_principale` tinyint(1) DEFAULT '0',
  `titre` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_media`),
  KEY `id_propriete` (`id_propriete`),
  CONSTRAINT `media_ibfk_1` FOREIGN KEY (`id_propriete`) REFERENCES `Propriete` (`id_propriete`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Structure de la table `Disponibilite`
--

CREATE TABLE `Disponibilite` (
  `id_disponibilite` int NOT NULL AUTO_INCREMENT,
  `id_agent` int NOT NULL,
  `jour` date NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `statut` enum('disponible','indisponible','réservé') DEFAULT 'disponible',
  PRIMARY KEY (`id_disponibilite`),
  KEY `id_agent` (`id_agent`),
  CONSTRAINT `disponibilite_ibfk_1` FOREIGN KEY (`id_agent`) REFERENCES `Agent_Immobilier` (`id_agent`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Structure de la table `Rendez_Vous`
--

CREATE TABLE `Rendez_Vous` (
  `id_rdv` int NOT NULL AUTO_INCREMENT,
  `id_client` int NOT NULL,
  `id_agent` int NOT NULL,
  `id_propriete` int NOT NULL,
  `date` date NOT NULL,
  `heure` time NOT NULL,
  `motif` text,
  `statut` enum('confirmé','annulé','en attente') DEFAULT 'en attente',
  `commentaire` text,
  PRIMARY KEY (`id_rdv`),
  KEY `id_client` (`id_client`),
  KEY `id_agent` (`id_agent`),
  KEY `id_propriete` (`id_propriete`),
  CONSTRAINT `rendez_vous_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `Client` (`id_client`) ON DELETE CASCADE,
  CONSTRAINT `rendez_vous_ibfk_2` FOREIGN KEY (`id_agent`) REFERENCES `Agent_Immobilier` (`id_agent`) ON DELETE CASCADE,
  CONSTRAINT `rendez_vous_ibfk_3` FOREIGN KEY (`id_propriete`) REFERENCES `Propriete` (`id_propriete`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Structure de la table `Message`
--

CREATE TABLE `Message` (
  `id_message` int NOT NULL AUTO_INCREMENT,
  `id_expediteur` int NOT NULL,
  `id_destinataire` int NOT NULL,
  `contenu` text NOT NULL,
  `date_envoi` datetime DEFAULT CURRENT_TIMESTAMP,
  `lu` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_message`),
  KEY `id_expediteur` (`id_expediteur`),
  KEY `id_destinataire` (`id_destinataire`),
  CONSTRAINT `message_ibfk_1` FOREIGN KEY (`id_expediteur`) REFERENCES `Utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  CONSTRAINT `message_ibfk_2` FOREIGN KEY (`id_destinataire`) REFERENCES `Utilisateur` (`id_utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Structure de la table `Evenement`
--

CREATE TABLE `Evenement` (
  `id_evenement` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `description` text,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_evenement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Structure de la table `ServicePayant`
--

CREATE TABLE `ServicePayant` (
  `id_service` int NOT NULL AUTO_INCREMENT,
  `type_service` varchar(50) NOT NULL,
  `nom_service` varchar(100) NOT NULL,
  `description` text,
  `prix` decimal(10,2) NOT NULL,
  `statut` enum('actif','inactif') DEFAULT 'actif',
  PRIMARY KEY (`id_service`),
  UNIQUE KEY `type_service` (`type_service`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Structure de la table `Transaction`
--

CREATE TABLE `Transaction` (
  `id_transaction` int NOT NULL AUTO_INCREMENT,
  `id_client` int NOT NULL,
  `id_propriete` int DEFAULT NULL,
  `montant` decimal(10,2) NOT NULL,
  `type_service` varchar(50) NOT NULL,
  `type_paiement` varchar(50) NOT NULL,
  `reference_paiement` varchar(100) DEFAULT NULL,
  `statut` enum('confirmé','annulé','en attente','remboursé') DEFAULT 'confirmé',
  `date_transaction` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_transaction`),
  KEY `id_client` (`id_client`),
  KEY `id_propriete` (`id_propriete`),
  CONSTRAINT `transaction_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `Client` (`id_client`) ON DELETE CASCADE,
  CONSTRAINT `transaction_ibfk_2` FOREIGN KEY (`id_propriete`) REFERENCES `Propriete` (`id_propriete`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Structure de la table `DetailPaiement`
--

CREATE TABLE `DetailPaiement` (
  `id_detail` int NOT NULL AUTO_INCREMENT,
  `id_transaction` int NOT NULL,
  `nom_titulaire` varchar(100) DEFAULT NULL,
  `type_carte` varchar(20) DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_detail`),
  KEY `id_transaction` (`id_transaction`),
  CONSTRAINT `detailpaiement_ibfk_1` FOREIGN KEY (`id_transaction`) REFERENCES `Transaction` (`id_transaction`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Structure de la table `Reduction`
--

CREATE TABLE `Reduction` (
  `id_reduction` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `type_reduction` enum('pourcentage','montant') NOT NULL,
  `valeur` decimal(10,2) NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_expiration` datetime DEFAULT NULL,
  `nombre_utilisations_max` int DEFAULT NULL,
  `nombre_utilisations_actuelles` int DEFAULT '0',
  `id_createur` int DEFAULT NULL,
  `statut` enum('actif','inactif') DEFAULT 'actif',
  PRIMARY KEY (`id_reduction`),
  UNIQUE KEY `code` (`code`),
  KEY `id_createur` (`id_createur`),
  CONSTRAINT `reduction_ibfk_1` FOREIGN KEY (`id_createur`) REFERENCES `Administrateur` (`id_admin`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Structure de la table `ChequeCadeau`
--

CREATE TABLE `ChequeCadeau` (
  `id_cheque` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_expiration` datetime DEFAULT NULL,
  `id_createur` int DEFAULT NULL,
  `statut` enum('actif','utilisé','expiré') DEFAULT 'actif',
  PRIMARY KEY (`id_cheque`),
  UNIQUE KEY `code` (`code`),
  KEY `id_createur` (`id_createur`),
  CONSTRAINT `chequecadeau_ibfk_1` FOREIGN KEY (`id_createur`) REFERENCES `Administrateur` (`id_admin`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `Utilisateur`
--

INSERT INTO `Utilisateur` (`id_utilisateur`, `email`, `mot_de_passe`, `nom`, `prenom`, `telephone`, `date_inscription`, `type_utilisateur`, `statut`) VALUES
(1, 'admin@omnesimmobilier.fr', '$2y$10$nN..LFYG.uzWjC36N6gmM.niuikvrKCt8G2aVxXkDtfB0nBJvtF5C', 'Admin', 'System', '0123456789', '2025-03-08 03:31:24', 'admin', 'actif'),
(2, 'anas.bouarramou@gmail.com', '$2y$10$nqbWKhXFu7XigojfsuePZ.10NqZrRZi6PmSIj4ejN1QDrZq47Nxn2', 'Bouarramou', 'Anas', '0749736800', '2025-03-08 03:52:04', 'client', 'actif'),
(3, 'anas.bouarramou@edu.ece.fr', '$2y$10$ZtgXqm4o4LeUtdXzQPotBuFpuqNgvalQ.5bRHdP4wCaVCEsHi2gEW', 'Bouarramou', 'Anas', '', '2025-03-08 04:33:43', 'client', 'actif'),
(4, 'pierre.boucher@agent.fr', '$2y$10$ZtgXqm4o4LeUtdXzQPotBuFpuqNgvalQ.5bRHdP4wCaVCEsHi2gEW', 'Boucher', 'Pierre', '', '2025-03-08 04:57:43', 'agent', 'actif'),
(5, 'marc.leblanc@agent.fr', '$2y$10$ZtgXqm4o4LeUtdXzQPotBuFpuqNgvalQ.5bRHdP4wCaVCEsHi2gEW', 'Leblanc', 'Marc', '', '2025-03-08 04:59:45', 'agent', 'actif'),
(6, 'martin.emilie@agent.fr', '$2y$10$ZtgXqm4o4LeUtdXzQPotBuFpuqNgvalQ.5bRHdP4wCaVCEsHi2gEW', 'Martin', 'Emilie', '', '2025-03-08 05:00:38', 'agent', 'actif'),
(7, 'theo@agent.fr', '$2y$10$ZtgXqm4o4LeUtdXzQPotBuFpuqNgvalQ.5bRHdP4wCaVCEsHi2gEW', 'Dominguez', 'Théo', '', '2025-03-12 10:55:00', 'agent', 'actif'),
(8, 'ManolaHina@agent.fr', '$2y$10$2TgtwjVlmuPnkdXwsXdxKOof2HqUVSAhZeAuO3SRYFKQIzuMjBonm', 'Hina', 'Manolo', '', '2025-03-12 13:18:27', 'agent', 'actif');

--
-- Déchargement des données de la table `Administrateur`
--

INSERT INTO `Administrateur` (`id_admin`, `id_utilisateur`, `niveau_acces`) VALUES
(1, 1, 1);

--
-- Déchargement des données de la table `Client`
--

INSERT INTO `Client` (`id_client`, `id_utilisateur`, `adresse`, `ville`, `code_postal`, `pays`, `informations_bancaires`) VALUES
(1, 2, '', '', '', 'France', NULL),
(2, 3, 'theo', 'theo', '90000', 'France', NULL);

--
-- Déchargement des données de la table `Agent_Immobilier`
--

INSERT INTO `Agent_Immobilier` (`id_agent`, `id_utilisateur`, `specialite`, `biographie`, `cv_path`, `photo_path`) VALUES
(1, 4, 'Maison de luxe', 'Avec plus de 15 ans d\'expérience dans l\'immobilier haut de gamme parisien, Pierre Boucher s\'est imposé comme une référence incontournable dans le secteur. Sa connaissance approfondie des quartiers prisés de la capitale et son réseau d\'influence lui permettent d\'offrir un service sur mesure à une clientèle exigeante. Chaque propriété qu\'il représente bénéficie d\'une mise en valeur personnalisée et d\'une stratégie marketing ciblée.', NULL, '/omnes-immobilier/assets/uploads/agents/agent_1741406263.png'),
(2, 5, 'Maison de luxe', 'Marc Leblanc a forgé sa réputation dans le secteur de l\'immobilier commercial grâce à une approche analytique rigoureuse et une compréhension fine des dynamiques d\'investissement. Après une carrière dans la finance, il a rejoint Omnes Immobilier pour mettre son expertise au service des investisseurs institutionnels et privés. Sa capacité à identifier des opportunités à fort potentiel et à structurer des transactions complexes en fait un conseiller privilégié pour les clients cherchant à optimiser leur patrimoine immobilier.', NULL, '/omnes-immobilier/assets/uploads/agents/agent_1741406385.png'),
(3, 6, 'Maison de compagne', 'Dynamique et passionnée, Émilie Martin est reconnue pour son approche personnalisée et son sens du service irréprochable. Spécialisée dans la location d\'appartements haut de gamme et la gestion locative, elle accompagne ses clients avec attention tout au long de leur parcours. Sa connaissance approfondie du marché locatif parisien et son réseau étendu lui permettent de trouver rapidement le bien idéal pour chaque client, qu\'il s\'agisse d\'expatriés, de cadres en mobilité ou de propriétaires souhaitant valoriser leur investissement.', NULL, '/omnes-immobilier/assets/uploads/agents/agent_1741406438.png'),
(4, 7, 'Maison de luxe', '', NULL, '/omnes-immobilier/assets/uploads/agents/1720476654483.jpeg'),
(5, 8, 'Maison de luxe', '', NULL, '/omnes-immobilier/assets/uploads/agents/agent_1741781907.jpeg');

INSERT INTO `Propriete` (`id_propriete`, `titre`, `description`, `prix`, `surface`, `nb_pieces`, `nb_chambres`, `nb_salles_bain`, `etage`, `balcon`, `parking`, `ascenseur`, `adresse`, `ville`, `code_postal`, `pays`, `statut`, `type_propriete`, `date_ajout`, `id_agent`) VALUES
(1, 'Duplex d\'Exception Avenue Montaigne', 'Situé sur la prestigieuse Avenue Montaigne, ce duplex d\'exception offre une vue imprenable sur la Tour Eiffel. Entièrement rénové par un architecte de renom, il allie le charme de l\'ancien aux prestations les plus modernes. Au premier niveau, un vaste séjour de 65m² baigné de lumière grâce à ses grandes fenêtres, une cuisine équipée haut de gamme signée Bulthaup, et une suite parentale avec dressing et salle de bain en marbre de Carrare. À l\'étage, deux chambres supplémentaires, une seconde salle de bain et un espace bureau. L\'appartement dispose également d\'une terrasse de 25m² orientée sud-ouest, offrant un cadre idéal pour des dîners avec vue sur Paris. Une cave et deux places de parking complètent ce bien rare sur le marché.', 3850000.00, 185.00, 4, 2, 2, 5, 1, 1, 1, '123 Avenue des Lilas', 'Paris', '75015', 'France', 'disponible', 'résidentiel', '2025-03-08 05:04:52', 1),
(2, 'Appartement Haussmannien Place des Vosges', 'Au cœur du Marais historique, cet appartement haussmannien de 165m² situé Place des Vosges incarne l\'élégance parisienne par excellence. Niché dans un hôtel particulier du XVIIème siècle classé monument historique, il a conservé ses éléments d\'origine: parquets en point de Hongrie, moulures, cheminées en marbre et plafonds de 3,5m. La réception de 70m² offre une vue imprenable sur la place et ses arcades. La partie nuit comprend trois chambres dont une suite parentale avec sa salle de bain en marbre et son dressing. Une cuisine équipée avec coin repas, une salle à manger formelle et un bureau complètent ce bien d\'exception. La rénovation méticuleuse a permis d\'intégrer des équipements modernes tout en préservant l\'authenticité des lieux. Une cave voutée en pierre de 15m² complète ce bien rare, témoin de l\'histoire parisienne.', 2950000.00, 165.00, 5, 3, 2, 3, 0, 0, 1, 'Place des Vosges', 'Paris', '75004', 'France', 'disponible', 'résidentiel', '2025-03-08 05:07:11', 2),
(3, 'Chalet Haut de Gamme avec Vue Massif du Mont-Blanc', 'Barnes Mont-Blanc vous propose ce chalet haut de gamme neuf et personnalisable, situé dans un quartier très réputé et apprécié de Saint-Gervais-les-Bains. Ce chalet offre une vue imprenable sur le massif du Mont-Blanc et est implanté sur environ 860 m² de terrain plat et piscinable. D\'une surface totale d\'environ 210 m² pour 5 chambres, il propose de très beaux volumes et des espaces lumineux. La grande pièce de vie de plus de 65 m² est idéale pour recevoir famille et amis. Le chalet dispose également de trois chambres en suite, dont une magnifique master bedroom avec une grande salle de bains et un dressing. Deux autres chambres se partagent une salle de bains supplémentaire. Pour compléter son confort, ce dernier est équipé d\'un garage, d\'un ski room et d\'une buanderie. Réalisé par un constructeur et promoteur local de belle renommée, ce chalet garantit des prestations haut de gamme et une qualité de finition exceptionnelle. Situé dans un environnement bucolique et paisible, il bénéficie de la proximité du centre du village accessible à pied. Vivre ici est une opportunité rare de profiter d\'un cadre idyllique avec une vue exceptionnelle sur la massif du Mont-Blanc. Ne manquez pas cette occasion unique de posséder un chalet d\'exception dans l\'un des plus beaux villages des Alpes. N\'hésitez pas à contacter Barnes Saint-Gervais au +33 4 50 18 14 50 pour tout complément d\'information. Photographies non contractuelles. Les informations sur les risques auxquels ce bien est exposé sont disponibles sur le site Géorisques : www.georisques.gouv.fr', 2200000.00, 186.00, 6, 4, 1, 1, 1, 1, 0, 'xxx', 'SAINT-GERVAIS-LES-BAINS', '74000', 'France', 'disponible', 'résidentiel', '2025-03-08 05:09:27', 3),
(4, 'PARIS 15 - SUFFREN - 2/3 CHAMBRES - VUE DEGAGEE', 'A quelques pas du Champ de Mars, au 7ème étage d\'une résidence de standing sécurisée avec gardienne, Barnes vous propose un bel appartement lumineux. Il se compose d\'une entrée donnant sur un vaste double séjour de plus de 40m2 avec cuisine ouverte aménagée et équipée, d\'un couloir aménagé en dressing desservant deux chambres de très bonne taille, d\'une salle de bains et de toilettes séparées à l\'entrée. Résidence aérée et parfaitement entretenue, avec jardin. Une cave complète le bien. L\'emplacement de parking est proposé en supplément du prix. 1,075,000 € Honoraires d\'agence non inclus - Honoraires agence: 4.65%TTC Honoraires à la charge de l\'acquéreur - Nombre de lots dans la copropriété: 120 - Montant moyen de la quote-part de charges courantes 6,200 €/an - Montant estimé des dépenses annuelles d\'énergie pour un usage standard, établi à partir des prix de l\'énergie de l\'année 2021 : 1100€ ~ 1540€ - Les informations sur les risques auxquels ce bien est exposé sont disponibles sur le site Géorisques : www.georisques.gouv.fr - Alexia TOCCHINI - Agent commercial - EI - RSAC 909 171 779\r\nLes informations sur les risques auxquels ce bien est exposé sont disponibles sur le site Géorisques : www.georisques.gouv.fr', 200000.00, 154.00, 2, 1, 2, 1, 1, 0, 0, 'Boulevard de grenelle', 'Paris', '75015', 'France', 'disponible', 'commercial', '2025-03-12 11:02:46', 4),
(5, 'Exclusivité -Appartement rénové - Paris 6 - Saint-Germain-des-Prés - Standing - climatisé - 3 chambres', 'Au coeur de Saint-Germain-des-Prés, au sein d\'un très bel immeuble ravalé construit en 1914, aux parties communes rénovées, avec ascenseur, à l\'étage noble, un appartement climatisé de 150,48m² carrez (154,71m² au sol) bénéficiant d\'un balcon filant de 9,47m² et offrant une belle hauteur sous plafond. Cet appartement, seul à l\'étage, entièrement rénové avec soin par un architecte se compose d\'une galerie d\'entrée desservant une pièce de réception avec cheminée et cuisine ouverte donnant sur un balcon filant, une vaste suite avec cheminée, des dressings sur mesure, une salle de douche avec toilettes donnant sur le balcon filant, une seconde suite avec dressings sur mesure et salle de douche avec toilettes sur cour, une troisième chambre avec sa pièce d\'eau/buanderie sur cour et des toilettes indépendantes. Une cave complète ce bien. Possibilité de mettre des vélos dans la cour. Ce bien rare pourra vous séduire par sa localisation au coeur de Saint-Germain-des-Prés et des Galeries d\'Art, la qualité de ses travaux soignés réalisés avec l\'intervention d\'un architecte, son sol en marbre dans la galerie d\'entrée, son parquet Versailles, la climatisation, le double vitrage sur l\'ensemble des huisseries (huisseries en bois à crémone à l\'ancienne), la qualité des matériaux utilisés, l\'alarme ainsi que l\'exposition Sud offrant une belle luminosité. Les informations sur les risques auxquels ce bien est exposé sont disponibles sur le site Géorisques : www.georisques.gouv.fr', 3990000.00, 151.00, 4, 3, 2, 3, 0, 0, 0, '13 rue bonaparte', 'Paris', '75006', 'France', 'disponible', 'résidentiel', '2025-03-12 13:26:07', 5);
