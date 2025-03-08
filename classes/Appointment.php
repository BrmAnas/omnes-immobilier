<?php
class Appointment {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Création d'un rendez-vous
    public function create($data) {
        $this->db->query('INSERT INTO Rendez_Vous (id_client, id_agent, id_propriete, date, heure, motif, statut, commentaire) 
                        VALUES (:id_client, :id_agent, :id_propriete, :date, :heure, :motif, :statut, :commentaire)');
        
        $this->db->bind(':id_client', $data['id_client']);
        $this->db->bind(':id_agent', $data['id_agent']);
        $this->db->bind(':id_propriete', $data['id_propriete']);
        $this->db->bind(':date', $data['date']);
        $this->db->bind(':heure', $data['heure']);
        $this->db->bind(':motif', $data['motif'] ?? null);
        $this->db->bind(':statut', $data['statut'] ?? 'en attente');
        $this->db->bind(':commentaire', $data['commentaire'] ?? null);
        
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    // Récupération d'un rendez-vous par son ID
    public function getAppointmentById($id_rdv) {
        $this->db->query('SELECT r.*, 
                        p.titre as propriete_titre, p.adresse as propriete_adresse, 
                        ca.nom as client_nom, ca.prenom as client_prenom, 
                        aa.nom as agent_nom, aa.prenom as agent_prenom 
                        FROM Rendez_Vous r 
                        INNER JOIN Propriete p ON r.id_propriete = p.id_propriete 
                        INNER JOIN Client c ON r.id_client = c.id_client 
                        INNER JOIN Utilisateur ca ON c.id_utilisateur = ca.id_utilisateur 
                        INNER JOIN Agent_Immobilier a ON r.id_agent = a.id_agent 
                        INNER JOIN Utilisateur aa ON a.id_utilisateur = aa.id_utilisateur 
        WHERE r.id_rdv = :id_rdv');
        $this->db->bind(':id_rdv', $id_rdv);
        
        return $this->db->single();
    }

    // Mise à jour du statut d'un rendez-vous
    public function updateStatus($id_rdv, $statut) {
        $this->db->query('UPDATE Rendez_Vous SET statut = :statut WHERE id_rdv = :id_rdv');
        
        $this->db->bind(':id_rdv', $id_rdv);
        $this->db->bind(':statut', $statut);
        
        return $this->db->execute();
    }

    // Récupération des rendez-vous d'un agent
    public function getAgentAppointments($id_agent) {
        $this->db->query('SELECT r.*, 
                        p.titre as propriete_titre, 
                        u.nom as client_nom, u.prenom as client_prenom 
                        FROM Rendez_Vous r 
                        INNER JOIN Propriete p ON r.id_propriete = p.id_propriete 
                        INNER JOIN Client c ON r.id_client = c.id_client 
                        INNER JOIN Utilisateur u ON c.id_utilisateur = u.id_utilisateur 
                        WHERE r.id_agent = :id_agent 
                        ORDER BY r.date ASC, r.heure ASC');
        $this->db->bind(':id_agent', $id_agent);
        
        return $this->db->resultSet();
    }

    // Récupération des rendez-vous d'un client
    public function getClientAppointments($id_client) {
        $this->db->query('SELECT r.*, 
                        p.titre as propriete_titre, p.adresse as propriete_adresse, 
                        u.nom as agent_nom, u.prenom as agent_prenom 
                        FROM Rendez_Vous r 
                        INNER JOIN Propriete p ON r.id_propriete = p.id_propriete 
                        INNER JOIN Agent_Immobilier a ON r.id_agent = a.id_agent 
                        INNER JOIN Utilisateur u ON a.id_utilisateur = u.id_utilisateur 
                        WHERE r.id_client = :id_client 
                        ORDER BY r.date ASC, r.heure ASC');
        $this->db->bind(':id_client', $id_client);
        
        return $this->db->resultSet();
    }

    // Vérifier si un créneau est disponible
    public function isSlotAvailable($id_agent, $date, $heure) {
        // Vérifier s'il y a déjà un rendez-vous à ce créneau
        $this->db->query('SELECT COUNT(*) as count FROM Rendez_Vous 
                        WHERE id_agent = :id_agent AND date = :date AND heure = :heure 
                        AND statut != "annulé"');
        $this->db->bind(':id_agent', $id_agent);
        $this->db->bind(':date', $date);
        $this->db->bind(':heure', $heure);
        
        $result = $this->db->single();
        
        // Si le nombre est > 0, le créneau est déjà pris
        if ($result->count > 0) {
            return false;
        }
        
        // Vérifier si l'agent est disponible à ce créneau
        $jour_semaine = date('N', strtotime($date)); // 1 (lundi) à 7 (dimanche)
        
        $this->db->query('SELECT COUNT(*) as count FROM Disponibilite 
                        WHERE id_agent = :id_agent 
                        AND DATE_FORMAT(jour, "%w") = :jour_semaine 
                        AND :heure BETWEEN heure_debut AND heure_fin 
                        AND statut = "disponible"');
        $this->db->bind(':id_agent', $id_agent);
        $this->db->bind(':jour_semaine', $jour_semaine - 1); // Conversion au format MySQL (0 = dimanche)
        $this->db->bind(':heure', $heure);
        
        $result = $this->db->single();
        
        // Si le nombre est > 0, l'agent est disponible
        return $result->count > 0;
    }
}?>