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

-- --------------------------------------------------------

--
-- Structure de la table `Administrateur`
--

CREATE TABLE `Administrateur` (
  `id_admin` int NOT NULL,
  `id_utilisateur` int NOT NULL,
  `niveau_acces` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Administrateur`
--

INSERT INTO `Administrateur` (`id_admin`, `id_utilisateur`, `niveau_acces`) VALUES
(1, 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `Agent_Immobilier`
--

CREATE TABLE `Agent_Immobilier` (
  `id_agent` int NOT NULL,
  `id_utilisateur` int NOT NULL,
  `specialite` varchar(100) DEFAULT NULL,
  `biographie` text,
  `cv_path` varchar(255) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL
);

--
-- Déchargement des données de la table `Agent_Immobilier`
--

INSERT INTO `Agent_Immobilier` (`id_agent`, `id_utilisateur`, `specialite`, `biographie`, `cv_path`, `photo_path`) VALUES
(1, 3, 'Maison de luxe', 'Avec plus de 15 ans d\'expérience dans l\'immobilier haut de gamme parisien, Pierre Boucher s\'est imposé comme une référence incontournable dans le secteur. Sa connaissance approfondie des quartiers prisés de la capitale et son réseau d\'influence lui permettent d\'offrir un service sur mesure à une clientèle exigeante. Chaque propriété qu\'il représente bénéficie d\'une mise en valeur personnalisée et d\'une stratégie marketing ciblée.', NULL, '/omnes-immobilier/assets/uploads/agents/agent_1741406263.png'),
(2, 5, 'Maison de luxe', 'Marc Leblanc a forgé sa réputation dans le secteur de l\'immobilier commercial grâce à une approche analytique rigoureuse et une compréhension fine des dynamiques d\'investissement. Après une carrière dans la finance, il a rejoint Omnes Immobilier pour mettre son expertise au service des investisseurs institutionnels et privés. Sa capacité à identifier des opportunités à fort potentiel et à structurer des transactions complexes en fait un conseiller privilégié pour les clients cherchant à optimiser leur patrimoine immobilier.', NULL, '/omnes-immobilier/assets/uploads/agents/agent_1741406385.png'),
(3, 6, 'Maison de compagne', 'Dynamique et passionnée, Émilie Martin est reconnue pour son approche personnalisée et son sens du service irréprochable. Spécialisée dans la location d\'appartements haut de gamme et la gestion locative, elle accompagne ses clients avec attention tout au long de leur parcours. Sa connaissance approfondie du marché locatif parisien et son réseau étendu lui permettent de trouver rapidement le bien idéal pour chaque client, qu\'il s\'agisse d\'expatriés, de cadres en mobilité ou de propriétaires souhaitant valoriser leur investissement.', NULL, '/omnes-immobilier/assets/uploads/agents/agent_1741406438.png'),
(4, 7, 'Maison de luxe', '', NULL, '/omnes-immobilier/assets/uploads/agents/1720476654483.jpeg'),
(5, 8, 'Maison de luxe', '', NULL, '/omnes-immobilier/assets/uploads/agents/agent_1741781907.jpeg');

-- --------------------------------------------------------

--
-- Structure de la table `ChequeCadeau`
--

CREATE TABLE `ChequeCadeau` (
  `id_cheque` int NOT NULL,
  `code` varchar(50) NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_expiration` datetime DEFAULT NULL,
  `id_createur` int DEFAULT NULL,
  `statut` enum('actif','utilisé','expiré') DEFAULT 'actif'
);

-- --------------------------------------------------------

--
-- Structure de la table `Client`
--

CREATE TABLE `Client` (
  `id_client` int NOT NULL,
  `id_utilisateur` int NOT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `code_postal` varchar(20) DEFAULT NULL,
  `pays` varchar(50) DEFAULT NULL,
  `informations_bancaires` text
);

--
-- Déchargement des données de la table `Client`
--

INSERT INTO `Client` (`id_client`, `id_utilisateur`, `adresse`, `ville`, `code_postal`, `pays`, `informations_bancaires`) VALUES
(1, 2, '', '', '', 'France', NULL),
(2, 3, 'theo', 'theo', '90000', 'France', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `DetailPaiement`
--

CREATE TABLE `DetailPaiement` (
  `id_detail` int NOT NULL,
  `id_transaction` int NOT NULL,
  `nom_titulaire` varchar(100) DEFAULT NULL,
  `type_carte` varchar(20) DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------

--
-- Structure de la table `Disponibilite`
--

CREATE TABLE `Disponibilite` (
  `id_disponibilite` int NOT NULL,
  `id_agent` int NOT NULL,
  `jour` date NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `statut` enum('disponible','indisponible','réservé') DEFAULT 'disponible'
);

--
-- Déchargement des données de la table `Disponibilite`
--

INSERT INTO `Disponibilite` (`id_disponibilite`, `id_agent`, `jour`, `heure_debut`, `heure_fin`, `statut`) VALUES
(1, 1, '2025-03-10', '09:00:00', '18:00:00', 'disponible'),
(2, 2, '2025-03-10', '09:00:00', '18:00:00', 'disponible'),
(3, 3, '2025-03-10', '09:00:00', '18:00:00', 'disponible'),
(4, 1, '2025-03-11', '09:00:00', '18:00:00', 'disponible'),
(5, 2, '2025-03-11', '09:00:00', '18:00:00', 'disponible'),
(6, 3, '2025-03-11', '09:00:00', '18:00:00', 'disponible'),
(7, 1, '2025-03-12', '09:00:00', '18:00:00', 'disponible'),
(8, 2, '2025-03-12', '09:00:00', '18:00:00', 'disponible'),
(9, 3, '2025-03-12', '09:00:00', '18:00:00', 'disponible'),
(10, 1, '2025-03-13', '09:00:00', '18:00:00', 'disponible'),
(11, 2, '2025-03-13', '09:00:00', '18:00:00', 'disponible'),
(12, 3, '2025-03-13', '09:00:00', '18:00:00', 'disponible'),
(13, 1, '2025-03-14', '09:00:00', '18:00:00', 'disponible'),
(14, 2, '2025-03-14', '09:00:00', '18:00:00', 'disponible'),
(15, 3, '2025-03-14', '09:00:00', '18:00:00', 'disponible'),
(16, 1, '2025-03-15', '09:00:00', '12:00:00', 'disponible'),
(17, 2, '2025-03-15', '09:00:00', '12:00:00', 'disponible'),
(18, 3, '2025-03-15', '09:00:00', '12:00:00', 'disponible'),
(19, 4, '2025-03-13', '08:00:00', '12:00:00', 'disponible'),
(20, 4, '2025-03-13', '14:00:00', '17:00:00', 'disponible');

-- --------------------------------------------------------

--
-- Structure de la table `Evenement`
--

CREATE TABLE `Evenement` (
  `id_evenement` int NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1'
);

--
-- Déchargement des données de la table `Evenement`
--

INSERT INTO `Evenement` (`id_evenement`, `titre`, `description`, `date_debut`, `date_fin`, `image_path`, `actif`) VALUES
(1, 'Portes Ouvertes - Résidence \"Les Jardins d\'Auteuil\"', 'Omnes Immobilier a le plaisir de vous convier à un événement exceptionnel : les portes ouvertes de la nouvelle résidence \"Les Jardins d\'Auteuil\", située dans le prestigieux 16ème arrondissement de Paris.\r\nNichée au cœur d\'un quartier résidentiel prisé, cette résidence de standing incarne l\'élégance parisienne moderne. Conçue par le cabinet d\'architecture renommé Wilmotte & Associés, elle propose 28 appartements haut de gamme allant du studio au 5 pièces.\r\nDurant ces deux journées exclusives, vous pourrez:\r\n\r\nVisiter les appartements témoins entièrement meublés et décorés\r\nDécouvrir les espaces communs incluant un jardin paysager de 500m²\r\nRencontrer nos agents spécialisés pour des conseils personnalisés\r\nBénéficier d\'une étude financière gratuite avec nos partenaires bancaires\r\nProfiter de conditions de lancement exceptionnelles\r\n\r\nUn cocktail vous sera offert dans le jardin de la résidence, où notre équipe sera à votre disposition pour répondre à toutes vos questions.\r\nAdresse: 18 rue d\'Auteuil, 75016 Paris\r\nParking à proximité: Parking Auteuil, 6 rue d\'Auteuil\r\nMétro: Ligne 10, station Michel-Ange Auteuil\r\nInscription recommandée mais non obligatoire: [lien d\'inscription]\r\nContact: evenements@omnesimmobilier.fr ou 01.XX.XX.XX.XX', '2025-03-14 05:11:00', '2025-03-15 05:11:00', '/omnes-immobilier/assets/uploads/events/event_1741407138.jpg', 1);

-- --------------------------------------------------------

--
-- Structure de la table `Media`
--

CREATE TABLE `Media` (
  `id_media` int NOT NULL,
  `id_propriete` int NOT NULL,
  `type` enum('photo','video') NOT NULL,
  `url_path` varchar(255) NOT NULL,
  `est_principale` tinyint(1) DEFAULT '0',
  `titre` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Media`
--

INSERT INTO `Media` (`id_media`, `id_propriete`, `type`, `url_path`, `est_principale`, `titre`) VALUES
(1, 1, 'photo', '/omnes-immobilier/assets/uploads/properties/property_1_1741406692_0.png', 1, 'Duplex d\'Exception Avenue Montaigne - Image 1'),
(2, 1, 'photo', '/omnes-immobilier/assets/uploads/properties/property_1_1741406692_1.png', 0, 'Duplex d\'Exception Avenue Montaigne - Image 2'),
(3, 1, 'photo', '/omnes-immobilier/assets/uploads/properties/property_1_1741406692_2.jpg', 0, 'Duplex d\'Exception Avenue Montaigne - Image 3'),
(4, 2, 'photo', '/omnes-immobilier/assets/uploads/properties/property_2_1741406831_0.jpg', 1, 'Appartement Haussmannien Place des Vosges - Image 1'),
(5, 2, 'photo', '/omnes-immobilier/assets/uploads/properties/property_2_1741406831_1.jpg', 0, 'Appartement Haussmannien Place des Vosges - Image 2'),
(6, 2, 'photo', '/omnes-immobilier/assets/uploads/properties/property_2_1741406831_2.jpg', 0, 'Appartement Haussmannien Place des Vosges - Image 3'),
(7, 2, 'photo', '/omnes-immobilier/assets/uploads/properties/property_2_1741406831_3.jpg', 0, 'Appartement Haussmannien Place des Vosges - Image 4'),
(8, 3, 'photo', '/omnes-immobilier/assets/uploads/properties/property_3_1741406967_0.jpg', 1, 'Chalet Haut de Gamme avec Vue Massif du Mont-Blanc - Image 1'),
(9, 3, 'photo', '/omnes-immobilier/assets/uploads/properties/property_3_1741406967_1.jpg', 0, 'Chalet Haut de Gamme avec Vue Massif du Mont-Blanc - Image 2'),
(10, 3, 'photo', '/omnes-immobilier/assets/uploads/properties/property_3_1741406967_2.jpg', 0, 'Chalet Haut de Gamme avec Vue Massif du Mont-Blanc - Image 3'),
(11, 3, 'photo', '/omnes-immobilier/assets/uploads/properties/property_3_1741406967_3.jpg', 0, 'Chalet Haut de Gamme avec Vue Massif du Mont-Blanc - Image 4'),
(12, 3, 'photo', '/omnes-immobilier/assets/uploads/properties/property_3_1741406967_4.jpg', 0, 'Chalet Haut de Gamme avec Vue Massif du Mont-Blanc - Image 5'),
(13, 5, 'photo', '/omnes-immobilier/assets/uploads/properties/property_5_1741782367_0.jpg', 1, 'Exclusivité -Appartement rénové - Paris 6 - Saint-Germain-des-Prés - Standing - climatisé - 3 chambres - Image 1'),
(14, 5, 'photo', '/omnes-immobilier/assets/uploads/properties/property_5_1741782367_1.jpg', 0, 'Exclusivité -Appartement rénové - Paris 6 - Saint-Germain-des-Prés - Standing - climatisé - 3 chambres - Image 2'),
(15, 5, 'photo', '/omnes-immobilier/assets/uploads/properties/property_5_1741782367_2.jpg', 0, 'Exclusivité -Appartement rénové - Paris 6 - Saint-Germain-des-Prés - Standing - climatisé - 3 chambres - Image 3'),
(16, 5, 'photo', '/omnes-immobilier/assets/uploads/properties/property_5_1741782367_3.jpg', 0, 'Exclusivité -Appartement rénové - Paris 6 - Saint-Germain-des-Prés - Standing - climatisé - 3 chambres - Image 4'),
(17, 5, 'photo', '/omnes-immobilier/assets/uploads/properties/property_5_1741782367_4.jpg', 0, 'Exclusivité -Appartement rénové - Paris 6 - Saint-Germain-des-Prés - Standing - climatisé - 3 chambres - Image 5');

-- --------------------------------------------------------

--
-- Structure de la table `Message`
--

CREATE TABLE `Message` (
  `id_message` int NOT NULL,
  `id_expediteur` int NOT NULL,
  `id_destinataire` int NOT NULL,
  `contenu` text NOT NULL,
  `date_envoi` datetime DEFAULT CURRENT_TIMESTAMP,
  `lu` tinyint(1) DEFAULT '0'
);

-- --------------------------------------------------------

--
-- Structure de la table `Propriete`
--

CREATE TABLE `Propriete` (
  `id_propriete` int NOT NULL,
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
  `id_agent` int NOT NULL
);

--
-- Déchargement des données de la table `Propriete`
--

INSERT INTO `Propriete` (`id_propriete`, `titre`, `description`, `prix`, `surface`, `nb_pieces`, `nb_chambres`, `nb_salles_bain`, `etage`, `balcon`, `parking`, `ascenseur`, `adresse`, `ville`, `code_postal`, `pays`, `statut`, `type_propriete`, `date_ajout`, `id_agent`) VALUES
(1, 'Duplex d\'Exception Avenue Montaigne', 'Situé sur la prestigieuse Avenue Montaigne, ce duplex d\'exception offre une vue imprenable sur la Tour Eiffel. Entièrement rénové par un architecte de renom, il allie le charme de l\'ancien aux prestations les plus modernes. Au premier niveau, un vaste séjour de 65m² baigné de lumière grâce à ses grandes fenêtres, une cuisine équipée haut de gamme signée Bulthaup, et une suite parentale avec dressing et salle de bain en marbre de Carrare. À l\'étage, deux chambres supplémentaires, une seconde salle de bain et un espace bureau. L\'appartement dispose également d\'une terrasse de 25m² orientée sud-ouest, offrant un cadre idéal pour des dîners avec vue sur Paris. Une cave et deux places de parking complètent ce bien rare sur le marché.', 3850000.00, 185.00, 4, 2, 2, 5, 1, 1, 1, '123 Avenue des Lilas', 'Paris', '75015', 'France', 'disponible', 'résidentiel', '2025-03-08 05:04:52', 1),
(2, 'Appartement Haussmannien Place des Vosges', 'Au cœur du Marais historique, cet appartement haussmannien de 165m² situé Place des Vosges incarne l\'élégance parisienne par excellence. Niché dans un hôtel particulier du XVIIème siècle classé monument historique, il a conservé ses éléments d\'origine: parquets en point de Hongrie, moulures, cheminées en marbre et plafonds de 3,5m. La réception de 70m² offre une vue imprenable sur la place et ses arcades. La partie nuit comprend trois chambres dont une suite parentale avec sa salle de bain en marbre et son dressing. Une cuisine équipée avec coin repas, une salle à manger formelle et un bureau complètent ce bien d\'exception. La rénovation méticuleuse a permis d\'intégrer des équipements modernes tout en préservant l\'authenticité des lieux. Une cave voutée en pierre de 15m² complète ce bien rare, témoin de l\'histoire parisienne.', 2950000.00, 165.00, 5, 3, 2, 3, 0, 0, 1, 'Place des Vosges', 'Paris', '75004', 'France', 'disponible', 'résidentiel', '2025-03-08 05:07:11', 2),
(3, 'Chalet Haut de Gamme avec Vue Massif du Mont-Blanc', 'Barnes Mont-Blanc vous propose ce chalet haut de gamme neuf et personnalisable, situé dans un quartier très réputé et apprécié de Saint-Gervais-les-Bains. Ce chalet offre une vue imprenable sur le massif du Mont-Blanc et est implanté sur environ 860 m² de terrain plat et piscinable. D\'une surface totale d\'environ 210 m² pour 5 chambres, il propose de très beaux volumes et des espaces lumineux. La grande pièce de vie de plus de 65 m² est idéale pour recevoir famille et amis. Le chalet dispose également de trois chambres en suite, dont une magnifique master bedroom avec une grande salle de bains et un dressing. Deux autres chambres se partagent une salle de bains supplémentaire. Pour compléter son confort, ce dernier est équipé d\'un garage, d\'un ski room et d\'une buanderie. Réalisé par un constructeur et promoteur local de belle renommée, ce chalet garantit des prestations haut de gamme et une qualité de finition exceptionnelle. Situé dans un environnement bucolique et paisible, il bénéficie de la proximité du centre du village accessible à pied. Vivre ici est une opportunité rare de profiter d\'un cadre idyllique avec une vue exceptionnelle sur la massif du Mont-Blanc. Ne manquez pas cette occasion unique de posséder un chalet d\'exception dans l\'un des plus beaux villages des Alpes. N\'hésitez pas à contacter Barnes Saint-Gervais au +33 4 50 18 14 50 pour tout complément d\'information. Photographies non contractuelles. Les informations sur les risques auxquels ce bien est exposé sont disponibles sur le site Géorisques : www.georisques.gouv.fr', 2200000.00, 186.00, 6, 4, 1, 1, 1, 1, 0, 'xxx', 'SAINT-GERVAIS-LES-BAINS', '74000', 'France', 'disponible', 'résidentiel', '2025-03-08 05:09:27', 3),
(4, 'PARIS 15 - SUFFREN - 2/3 CHAMBRES - VUE DEGAGEE', 'A quelques pas du Champ de Mars, au 7ème étage d\'une résidence de standing sécurisée avec gardienne, Barnes vous propose un bel appartement lumineux. Il se compose d\'une entrée donnant sur un vaste double séjour de plus de 40m2 avec cuisine ouverte aménagée et équipée, d\'un couloir aménagé en dressing desservant deux chambres de très bonne taille, d\'une salle de bains et de toilettes séparées à l\'entrée. Résidence aérée et parfaitement entretenue, avec jardin. Une cave complète le bien. L\'emplacement de parking est proposé en supplément du prix. 1,075,000 € Honoraires d\'agence non inclus - Honoraires agence: 4.65%TTC Honoraires à la charge de l\'acquéreur - Nombre de lots dans la copropriété: 120 - Montant moyen de la quote-part de charges courantes 6,200 €/an - Montant estimé des dépenses annuelles d\'énergie pour un usage standard, établi à partir des prix de l\'énergie de l\'année 2021 : 1100€ ~ 1540€ - Les informations sur les risques auxquels ce bien est exposé sont disponibles sur le site Géorisques : www.georisques.gouv.fr - Alexia TOCCHINI - Agent commercial - EI - RSAC 909 171 779\r\nLes informations sur les risques auxquels ce bien est exposé sont disponibles sur le site Géorisques : www.georisques.gouv.fr', 200000.00, 154.00, 2, 1, 2, 1, 1, 0, 0, 'Boulevard de grenelle', 'Paris', '75015', 'France', 'disponible', 'commercial', '2025-03-12 11:02:46', 4),
(5, 'Exclusivité -Appartement rénové - Paris 6 - Saint-Germain-des-Prés - Standing - climatisé - 3 chambres', 'Au coeur de Saint-Germain-des-Prés, au sein d\'un très bel immeuble ravalé construit en 1914, aux parties communes rénovées, avec ascenseur, à l\'étage noble, un appartement climatisé de 150,48m² carrez (154,71m² au sol) bénéficiant d\'un balcon filant de 9,47m² et offrant une belle hauteur sous plafond. Cet appartement, seul à l\'étage, entièrement rénové avec soin par un architecte se compose d\'une galerie d\'entrée desservant une pièce de réception avec cheminée et cuisine ouverte donnant sur un balcon filant, une vaste suite avec cheminée, des dressings sur mesure, une salle de douche avec toilettes donnant sur le balcon filant, une seconde suite avec dressings sur mesure et salle de douche avec toilettes sur cour, une troisième chambre avec sa pièce d\'eau/buanderie sur cour et des toilettes indépendantes. Une cave complète ce bien. Possibilité de mettre des vélos dans la cour. Ce bien rare pourra vous séduire par sa localisation au coeur de Saint-Germain-des-Prés et des Galeries d\'Art, la qualité de ses travaux soignés réalisés avec l\'intervention d\'un architecte, son sol en marbre dans la galerie d\'entrée, son parquet Versailles, la climatisation, le double vitrage sur l\'ensemble des huisseries (huisseries en bois à crémone à l\'ancienne), la qualité des matériaux utilisés, l\'alarme ainsi que l\'exposition Sud offrant une belle luminosité. Honoraires à la charge du vendeur - Nombre de lots dans la copropriété: 22 - Montant moyen de la quote-part de charges courantes 5,100 €/an - Montant estimé des dépenses annuelles d\'énergie pour un usage standard : 3110€ ~ 4250€ - Les informations sur les risques auxquels ce bien est exposé sont disponibles sur le site Géorisques : www.georisques.gouv.fr\r\nLes informations sur les risques auxquels ce bien est exposé sont disponibles sur le site Géorisques ', 3990000.00, 151.00, 4, 3, 2, 3, 0, 0, 0, '13 rue bonaparte', 'Paris', '75006', 'France', 'disponible', 'résidentiel', '2025-03-12 13:26:07', 5);

-- --------------------------------------------------------

--
-- Structure de la table `Reduction`
--

CREATE TABLE `Reduction` (
  `id_reduction` int NOT NULL,
  `code` varchar(50) NOT NULL,
  `type_reduction` enum('pourcentage','montant') NOT NULL,
  `valeur` decimal(10,2) NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_expiration` datetime DEFAULT NULL,
  `nombre_utilisations_max` int DEFAULT NULL,
  `nombre_utilisations_actuelles` int DEFAULT '0',
  `id_createur` int DEFAULT NULL,
  `statut` enum('actif','inactif') DEFAULT 'actif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Rendez_Vous`
--

CREATE TABLE `Rendez_Vous` (
  `id_rdv` int NOT NULL,
  `id_client` int NOT NULL,
  `id_agent` int NOT NULL,
  `id_propriete` int NOT NULL,
  `date` date NOT NULL,
  `heure` time NOT NULL,
  `motif` text,
  `statut` enum('confirmé','annulé','en attente') DEFAULT 'en attente',
  `commentaire` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Rendez_Vous`
--

INSERT INTO `Rendez_Vous` (`id_rdv`, `id_client`, `id_agent`, `id_propriete`, `date`, `heure`, `motif`, `statut`, `commentaire`) VALUES
(1, 2, 1, 1, '2025-03-08', '10:00:00', 'Information sur le bien', 'en attente', ''),
(2, 2, 3, 3, '2025-03-08', '10:30:00', 'Négociation', 'confirmé', 'petit date'),
(3, 2, 1, 1, '2025-03-13', '10:30:00', 'Négociation', 'annulé', ''),
(4, 2, 3, 3, '2025-03-13', '10:30:00', 'Information sur le bien', 'confirmé', ''),
(5, 2, 4, 4, '2025-03-13', '10:30:00', 'Négociation', 'confirmé', 'stp'),
(6, 2, 2, 2, '2025-03-15', '11:00:00', 'Négociation', 'en attente', '');

-- --------------------------------------------------------

--
-- Structure de la table `ServicePayant`
--

CREATE TABLE `ServicePayant` (
  `id_service` int NOT NULL,
  `type_service` varchar(50) NOT NULL,
  `nom_service` varchar(100) NOT NULL,
  `description` text,
  `prix` decimal(10,2) NOT NULL,
  `statut` enum('actif','inactif') DEFAULT 'actif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `ServicePayant`
--

INSERT INTO `ServicePayant` (`id_service`, `type_service`, `nom_service`, `description`, `prix`, `statut`) VALUES
(1, 'dossier_acheteur_premium', 'Dossier Acheteur Premium', 'Dossier complet pour optimiser vos chances auprès des vendeurs', 99.00, 'actif'),
(2, 'photos_professionnelles', 'Photos Professionnelles', 'Séance photo professionnelle de votre bien immobilier', 149.00, 'actif'),
(3, 'visite_virtuelle', 'Visite Virtuelle 3D', 'Visite virtuelle immersive de la propriété', 39.90, 'actif'),
(4, 'plan_2d', 'Plan 2D', 'Plan détaillé de la propriété avec mesures précises', 29.90, 'actif'),
(5, 'rapport_expertise', 'Rapport d\'expertise', 'Analyse complète de la valeur et de l\'état de la propriété', 149.90, 'actif');

-- --------------------------------------------------------

--
-- Structure de la table `Transaction`
--

CREATE TABLE `Transaction` (
  `id_transaction` int NOT NULL,
  `id_client` int NOT NULL,
  `id_propriete` int DEFAULT NULL,
  `montant` decimal(10,2) NOT NULL,
  `type_service` varchar(50) NOT NULL,
  `type_paiement` varchar(50) NOT NULL,
  `reference_paiement` varchar(100) DEFAULT NULL,
  `statut` enum('confirmé','annulé','en attente','remboursé') DEFAULT 'confirmé',
  `date_transaction` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Utilisateur`
--

CREATE TABLE `Utilisateur` (
  `id_utilisateur` int NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `date_inscription` datetime DEFAULT CURRENT_TIMESTAMP,
  `type_utilisateur` enum('client','agent','admin') NOT NULL,
  `statut` enum('actif','inactif') DEFAULT 'actif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Index pour les tables déchargées
--

--
-- Index pour la table `Administrateur`
--
ALTER TABLE `Administrateur`
  ADD PRIMARY KEY (`id_admin`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `Agent_Immobilier`
--
ALTER TABLE `Agent_Immobilier`
  ADD PRIMARY KEY (`id_agent`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `ChequeCadeau`
--
ALTER TABLE `ChequeCadeau`
  ADD PRIMARY KEY (`id_cheque`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `id_createur` (`id_createur`);

--
-- Index pour la table `Client`
--
ALTER TABLE `Client`
  ADD PRIMARY KEY (`id_client`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `DetailPaiement`
--
ALTER TABLE `DetailPaiement`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaction` (`id_transaction`);

--
-- Index pour la table `Disponibilite`
--
ALTER TABLE `Disponibilite`
  ADD PRIMARY KEY (`id_disponibilite`),
  ADD KEY `id_agent` (`id_agent`);

--
-- Index pour la table `Evenement`
--
ALTER TABLE `Evenement`
  ADD PRIMARY KEY (`id_evenement`);

--
-- Index pour la table `Media`
--
ALTER TABLE `Media`
  ADD PRIMARY KEY (`id_media`),
  ADD KEY `id_propriete` (`id_propriete`);

--
-- Index pour la table `Message`
--
ALTER TABLE `Message`
  ADD PRIMARY KEY (`id_message`),
  ADD KEY `id_expediteur` (`id_expediteur`),
  ADD KEY `id_destinataire` (`id_destinataire`);

--
-- Index pour la table `Propriete`
--
ALTER TABLE `Propriete`
  ADD PRIMARY KEY (`id_propriete`),
  ADD KEY `id_agent` (`id_agent`);

--
-- Index pour la table `Reduction`
--
ALTER TABLE `Reduction`
  ADD PRIMARY KEY (`id_reduction`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `id_createur` (`id_createur`);

--
-- Index pour la table `Rendez_Vous`
--
ALTER TABLE `Rendez_Vous`
  ADD PRIMARY KEY (`id_rdv`),
  ADD KEY `id_client` (`id_client`),
  ADD KEY `id_agent` (`id_agent`),
  ADD KEY `id_propriete` (`id_propriete`);

--
-- Index pour la table `ServicePayant`
--
ALTER TABLE `ServicePayant`
  ADD PRIMARY KEY (`id_service`),
  ADD UNIQUE KEY `type_service` (`type_service`);

--
-- Index pour la table `Transaction`
--
ALTER TABLE `Transaction`
  ADD PRIMARY KEY (`id_transaction`),
  ADD KEY `id_client` (`id_client`),
  ADD KEY `id_propriete` (`id_propriete`);

--
-- Index pour la table `Utilisateur`
--
ALTER TABLE `Utilisateur`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `Administrateur`
--
ALTER TABLE `Administrateur`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `Agent_Immobilier`
--
ALTER TABLE `Agent_Immobilier`
  MODIFY `id_agent` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `ChequeCadeau`
--
ALTER TABLE `ChequeCadeau`
  MODIFY `id_cheque` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Client`
--
ALTER TABLE `Client`
  MODIFY `id_client` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `DetailPaiement`
--
ALTER TABLE `DetailPaiement`
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Disponibilite`
--
ALTER TABLE `Disponibilite`
  MODIFY `id_disponibilite` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `Evenement`
--
ALTER TABLE `Evenement`
  MODIFY `id_evenement` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `Media`
--
ALTER TABLE `Media`
  MODIFY `id_media` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `Message`
--
ALTER TABLE `Message`
  MODIFY `id_message` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Propriete`
--
ALTER TABLE `Propriete`
  MODIFY `id_propriete` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `Reduction`
--
ALTER TABLE `Reduction`
  MODIFY `id_reduction` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Rendez_Vous`
--
ALTER TABLE `Rendez_Vous`
  MODIFY `id_rdv` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `ServicePayant`
--
ALTER TABLE `ServicePayant`
  MODIFY `id_service` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `Transaction`
--
ALTER TABLE `Transaction`
  MODIFY `id_transaction` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Utilisateur`
--
ALTER TABLE `Utilisateur`
  MODIFY `id_utilisateur` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `Administrateur`
--
ALTER TABLE `Administrateur`
  ADD CONSTRAINT `administrateur_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `Utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Agent_Immobilier`
--
ALTER TABLE `Agent_Immobilier`
  ADD CONSTRAINT `agent_immobilier_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `Utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `ChequeCadeau`
--
ALTER TABLE `ChequeCadeau`
  ADD CONSTRAINT `chequecadeau_ibfk_1` FOREIGN KEY (`id_createur`) REFERENCES `Administrateur` (`id_admin`);

--
-- Contraintes pour la table `Client`
--
ALTER TABLE `Client`
  ADD CONSTRAINT `client_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `Utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `DetailPaiement`
--
ALTER TABLE `DetailPaiement`
  ADD CONSTRAINT `detailpaiement_ibfk_1` FOREIGN KEY (`id_transaction`) REFERENCES `Transaction` (`id_transaction`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Disponibilite`
--
ALTER TABLE `Disponibilite`
  ADD CONSTRAINT `disponibilite_ibfk_1` FOREIGN KEY (`id_agent`) REFERENCES `Agent_Immobilier` (`id_agent`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Media`
--
ALTER TABLE `Media`
  ADD CONSTRAINT `media_ibfk_1` FOREIGN KEY (`id_propriete`) REFERENCES `Propriete` (`id_propriete`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Message`
--
ALTER TABLE `Message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`id_expediteur`) REFERENCES `Utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_ibfk_2` FOREIGN KEY (`id_destinataire`) REFERENCES `Utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Propriete`
--
ALTER TABLE `Propriete`
  ADD CONSTRAINT `propriete_ibfk_1` FOREIGN KEY (`id_agent`) REFERENCES `Agent_Immobilier` (`id_agent`);

--
-- Contraintes pour la table `Reduction`
--
ALTER TABLE `Reduction`
  ADD CONSTRAINT `reduction_ibfk_1` FOREIGN KEY (`id_createur`) REFERENCES `Administrateur` (`id_admin`);

--
-- Contraintes pour la table `Rendez_Vous`
--
ALTER TABLE `Rendez_Vous`
  ADD CONSTRAINT `rendez_vous_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `Client` (`id_client`) ON DELETE CASCADE,
  ADD CONSTRAINT `rendez_vous_ibfk_2` FOREIGN KEY (`id_agent`) REFERENCES `Agent_Immobilier` (`id_agent`) ON DELETE CASCADE,
  ADD CONSTRAINT `rendez_vous_ibfk_3` FOREIGN KEY (`id_propriete`) REFERENCES `Propriete` (`id_propriete`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Transaction`
--
ALTER TABLE `Transaction`
  ADD CONSTRAINT `transaction_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `Client` (`id_client`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_ibfk_2` FOREIGN KEY (`id_propriete`) REFERENCES `Propriete` (`id_propriete`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
