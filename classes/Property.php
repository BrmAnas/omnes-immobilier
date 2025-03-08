<?php
class Property {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Création d'une propriété
    public function create($data) {
        $this->db->query('INSERT INTO Propriete (titre, description, prix, surface, nb_pieces, 
                        nb_chambres, nb_salles_bain, etage, balcon, parking, ascenseur, adresse, ville, 
                        code_postal, pays, statut, type_propriete, id_agent) 
                        VALUES (:titre, :description, :prix, :surface, :nb_pieces, :nb_chambres, 
                        :nb_salles_bain, :etage, :balcon, :parking, :ascenseur, :adresse, :ville, 
                        :code_postal, :pays, :statut, :type_propriete, :id_agent)');
        
        $this->db->bind(':titre', $data['titre']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':prix', $data['prix']);
        $this->db->bind(':surface', $data['surface']);
        $this->db->bind(':nb_pieces', $data['nb_pieces']);
        $this->db->bind(':nb_chambres', $data['nb_chambres']);
        $this->db->bind(':nb_salles_bain', $data['nb_salles_bain']);
        $this->db->bind(':etage', $data['etage'] ?? null);
        $this->db->bind(':balcon', $data['balcon'] ?? false);
        $this->db->bind(':parking', $data['parking'] ?? false);
        $this->db->bind(':ascenseur', $data['ascenseur'] ?? false);
        $this->db->bind(':adresse', $data['adresse']);
        $this->db->bind(':ville', $data['ville']);
        $this->db->bind(':code_postal', $data['code_postal']);
        $this->db->bind(':pays', $data['pays']);
        $this->db->bind(':statut', $data['statut'] ?? 'disponible');
        $this->db->bind(':type_propriete', $data['type_propriete']);
        $this->db->bind(':id_agent', $data['id_agent']);
        
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    // Récupération d'une propriété par son ID
    public function getPropertyById($id_propriete) {
        $this->db->query('SELECT p.*, a.id_agent, u.nom as agent_nom, u.prenom as agent_prenom, 
                        u.email as agent_email, u.telephone as agent_telephone 
                        FROM Propriete p 
                        INNER JOIN Agent_Immobilier a ON p.id_agent = a.id_agent 
                        INNER JOIN Utilisateur u ON a.id_utilisateur = u.id_utilisateur 
                        WHERE p.id_propriete = :id_propriete');
        $this->db->bind(':id_propriete', $id_propriete);
        
        return $this->db->single();
    }

    // Récupération de toutes les propriétés
    public function getAllProperties() {
        $this->db->query('SELECT * FROM Propriete WHERE statut = "disponible" ORDER BY date_ajout DESC');
        
        return $this->db->resultSet();
    }

    // Récupération des propriétés par type
    public function getPropertiesByType($type) {
        $this->db->query('SELECT * FROM Propriete 
                        WHERE type_propriete = :type AND statut = "disponible" 
                        ORDER BY date_ajout DESC');
        $this->db->bind(':type', $type);
        
        return $this->db->resultSet();
    }
    
    // Récupération des propriétés gérées par un agent
    public function getPropertiesByAgent($id_agent) {
        $this->db->query('SELECT * FROM Propriete 
                        WHERE id_agent = :id_agent 
                        ORDER BY date_ajout DESC');
        $this->db->bind(':id_agent', $id_agent);
        
        return $this->db->resultSet();
    }

    // Recherche de propriétés
    public function searchProperties($search_term) {
        $this->db->query('SELECT * FROM Propriete 
                        WHERE (titre LIKE :search OR description LIKE :search OR ville LIKE :search) 
                        AND statut = "disponible" 
                        ORDER BY date_ajout DESC');
        $this->db->bind(':search', '%' . $search_term . '%');
        
        return $this->db->resultSet();
    }

    // Filtrer les propriétés
    public function filterProperties($filters) {
        $sql = 'SELECT * FROM Propriete WHERE statut = "disponible"';
        
        // Ajout des critères de filtre
        if (!empty($filters['type_propriete'])) {
            $sql .= ' AND type_propriete = :type_propriete';
        }
        
        if (!empty($filters['ville'])) {
            $sql .= ' AND ville LIKE :ville';
        }
        
        if (!empty($filters['prix_min'])) {
            $sql .= ' AND prix >= :prix_min';
        }
        
        if (!empty($filters['prix_max'])) {
            $sql .= ' AND prix <= :prix_max';
        }
        
        if (!empty($filters['surface_min'])) {
            $sql .= ' AND surface >= :surface_min';
        }
        
        if (!empty($filters['nb_pieces_min'])) {
            $sql .= ' AND nb_pieces >= :nb_pieces_min';
        }
        
        $sql .= ' ORDER BY date_ajout DESC';
        
        $this->db->query($sql);
        
        // Liaison des paramètres
        if (!empty($filters['type_propriete'])) {
            $this->db->bind(':type_propriete', $filters['type_propriete']);
        }
        
        if (!empty($filters['ville'])) {
            $this->db->bind(':ville', '%' . $filters['ville'] . '%');
        }
        
        if (!empty($filters['prix_min'])) {
            $this->db->bind(':prix_min', $filters['prix_min']);
        }
        
        if (!empty($filters['prix_max'])) {
            $this->db->bind(':prix_max', $filters['prix_max']);
        }
        
        if (!empty($filters['surface_min'])) {
            $this->db->bind(':surface_min', $filters['surface_min']);
        }
        
        if (!empty($filters['nb_pieces_min'])) {
            $this->db->bind(':nb_pieces_min', $filters['nb_pieces_min']);
        }
        
        return $this->db->resultSet();
    }

    // Mise à jour d'une propriété
    public function update($data) {
        $this->db->query('UPDATE Propriete 
                        SET titre = :titre, description = :description, prix = :prix, 
                        surface = :surface, nb_pieces = :nb_pieces, nb_chambres = :nb_chambres, 
                        nb_salles_bain = :nb_salles_bain, etage = :etage, balcon = :balcon, 
                        parking = :parking, ascenseur = :ascenseur, adresse = :adresse, 
                        ville = :ville, code_postal = :code_postal, pays = :pays, 
                        statut = :statut, type_propriete = :type_propriete 
                        WHERE id_propriete = :id_propriete');
        
        $this->db->bind(':id_propriete', $data['id_propriete']);
        $this->db->bind(':titre', $data['titre']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':prix', $data['prix']);
        $this->db->bind(':surface', $data['surface']);
        $this->db->bind(':nb_pieces', $data['nb_pieces']);
        $this->db->bind(':nb_chambres', $data['nb_chambres']);
        $this->db->bind(':nb_salles_bain', $data['nb_salles_bain']);
        $this->db->bind(':etage', $data['etage'] ?? null);
        $this->db->bind(':balcon', $data['balcon'] ?? false);
        $this->db->bind(':parking', $data['parking'] ?? false);
        $this->db->bind(':ascenseur', $data['ascenseur'] ?? false);
        $this->db->bind(':adresse', $data['adresse']);
        $this->db->bind(':ville', $data['ville']);
        $this->db->bind(':code_postal', $data['code_postal']);
        $this->db->bind(':pays', $data['pays']);
        $this->db->bind(':statut', $data['statut']);
        $this->db->bind(':type_propriete', $data['type_propriete']);
        
        return $this->db->execute();
    }

    // Récupération des médias d'une propriété
    public function getMedia($id_propriete) {
        $this->db->query('SELECT * FROM Media WHERE id_propriete = :id_propriete');
        $this->db->bind(':id_propriete', $id_propriete);
        
        return $this->db->resultSet();
    }

    // Ajout d'un média pour une propriété
    public function addMedia($data) {
        $this->db->query('INSERT INTO Media (id_propriete, type, url_path, est_principale, titre) 
                        VALUES (:id_propriete, :type, :url_path, :est_principale, :titre)');
        
        $this->db->bind(':id_propriete', $data['id_propriete']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':url_path', $data['url_path']);
        $this->db->bind(':est_principale', $data['est_principale'] ?? false);
        $this->db->bind(':titre', $data['titre'] ?? null);
        
        return $this->db->execute();
    }
}
?>