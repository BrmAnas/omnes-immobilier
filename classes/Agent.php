<?php
class Agent {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Création d'un agent immobilier
    public function create($data) {
        $this->db->query('INSERT INTO Agent_Immobilier (id_utilisateur, specialite, biographie, cv_path, photo_path) 
                        VALUES (:id_utilisateur, :specialite, :biographie, :cv_path, :photo_path)');
        
        $this->db->bind(':id_utilisateur', $data['id_utilisateur']);
        $this->db->bind(':specialite', $data['specialite']);
        $this->db->bind(':biographie', $data['biographie'] ?? null);
        $this->db->bind(':cv_path', $data['cv_path'] ?? null);
        $this->db->bind(':photo_path', $data['photo_path'] ?? null);
        
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    // Récupération d'un agent par son ID utilisateur
    public function getAgentByUserId($id_utilisateur) {
        $this->db->query('SELECT * FROM Agent_Immobilier WHERE id_utilisateur = :id_utilisateur');
        $this->db->bind(':id_utilisateur', $id_utilisateur);
        
        return $this->db->single();
    }

    // Récupération d'un agent par son ID agent
    public function getAgentById($id_agent) {
        $this->db->query('SELECT a.*, u.nom, u.prenom, u.email, u.telephone 
                        FROM Agent_Immobilier a 
                        INNER JOIN Utilisateur u ON a.id_utilisateur = u.id_utilisateur 
                        WHERE a.id_agent = :id_agent');
        $this->db->bind(':id_agent', $id_agent);
        
        return $this->db->single();
    }

    // Récupération de tous les agents
    public function getAllAgents() {
        $this->db->query('SELECT a.*, u.nom, u.prenom, u.email, u.telephone 
                        FROM Agent_Immobilier a 
                        INNER JOIN Utilisateur u ON a.id_utilisateur = u.id_utilisateur 
                        ORDER BY u.nom, u.prenom');
        
        return $this->db->resultSet();
    }

    // Récupération des agents par spécialité
    public function getAgentsBySpeciality($specialite) {
        $this->db->query('SELECT a.*, u.nom, u.prenom, u.email, u.telephone 
                        FROM Agent_Immobilier a 
                        INNER JOIN Utilisateur u ON a.id_utilisateur = u.id_utilisateur 
                        WHERE a.specialite = :specialite 
                        ORDER BY u.nom, u.prenom');
        $this->db->bind(':specialite', $specialite);
        
        return $this->db->resultSet();
    }

    // Mise à jour des informations d'un agent
    public function update($data) {
        $this->db->query('UPDATE Agent_Immobilier 
                        SET specialite = :specialite, biographie = :biographie, 
                        cv_path = :cv_path, photo_path = :photo_path 
                        WHERE id_agent = :id_agent');
        
        $this->db->bind(':id_agent', $data['id_agent']);
        $this->db->bind(':specialite', $data['specialite']);
        $this->db->bind(':biographie', $data['biographie'] ?? null);
        $this->db->bind(':cv_path', $data['cv_path'] ?? null);
        $this->db->bind(':photo_path', $data['photo_path'] ?? null);
        
        return $this->db->execute();
    }

    // Récupération des disponibilités d'un agent
    public function getAvailabilities($id_agent) {
        $this->db->query('SELECT * FROM Disponibilite 
                        WHERE id_agent = :id_agent 
                        ORDER BY jour, heure_debut');
        $this->db->bind(':id_agent', $id_agent);
        
        return $this->db->resultSet();
    }

    // Ajout d'une disponibilité pour un agent
    public function addAvailability($data) {
        $this->db->query('INSERT INTO Disponibilite (id_agent, jour, heure_debut, heure_fin, statut) 
                        VALUES (:id_agent, :jour, :heure_debut, :heure_fin, :statut)');
        
        $this->db->bind(':id_agent', $data['id_agent']);
        $this->db->bind(':jour', $data['jour']);
        $this->db->bind(':heure_debut', $data['heure_debut']);
        $this->db->bind(':heure_fin', $data['heure_fin']);
        $this->db->bind(':statut', $data['statut'] ?? 'disponible');
        
        return $this->db->execute();
    }
}
?>