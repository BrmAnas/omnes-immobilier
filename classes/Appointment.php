<?php
class Appointment {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Création d'un rendez-vous
     * @param array $data Les données du rendez-vous
     * @return int|false L'ID du rendez-vous créé ou false en cas d'erreur
     */
    public function create($data) {
        try {
            // Vérifier d'abord que le créneau est disponible
            if (!$this->isSlotAvailable($data['id_agent'], $data['date'], $data['heure'])) {
                error_log("Tentative de création d'un rendez-vous sur un créneau non disponible");
                return false;
            }

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
                error_log("Erreur lors de la création d'un rendez-vous: " . print_r($data, true));
                return false;
            }
        } catch (Exception $e) {
            error_log("Exception lors de la création d'un rendez-vous: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupération d'un rendez-vous par son ID
     * @param int $id_rdv L'ID du rendez-vous
     * @return object|false Le rendez-vous ou false si non trouvé
     */
    public function getAppointmentById($id_rdv) {
        try {
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
        } catch (Exception $e) {
            error_log("Exception lors de la récupération d'un rendez-vous par ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mise à jour du statut d'un rendez-vous
     * @param int $id_rdv L'ID du rendez-vous
     * @param string $statut Le nouveau statut
     * @return bool Succès ou échec
     */
    public function updateStatus($id_rdv, $statut) {
        try {
            $this->db->query('UPDATE Rendez_Vous SET statut = :statut WHERE id_rdv = :id_rdv');
            
            $this->db->bind(':id_rdv', $id_rdv);
            $this->db->bind(':statut', $statut);
            
            $result = $this->db->execute();
            if (!$result) {
                error_log("Erreur lors de la mise à jour du statut d'un rendez-vous. ID: {$id_rdv}, Statut: {$statut}");
            }
            return $result;
        } catch (Exception $e) {
            error_log("Exception lors de la mise à jour du statut d'un rendez-vous: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupération des rendez-vous d'un agent
     * @param int $id_agent L'ID de l'agent
     * @return array Les rendez-vous
     */
    public function getAgentAppointments($id_agent) {
        try {
            $this->db->query('SELECT r.*, 
                            p.titre as propriete_titre, p.adresse as propriete_adresse, 
                            c.id_client, ca.nom as client_nom, ca.prenom as client_prenom, ca.email as client_email, ca.telephone as client_telephone
                            FROM Rendez_Vous r 
                            INNER JOIN Propriete p ON r.id_propriete = p.id_propriete 
                            INNER JOIN Client c ON r.id_client = c.id_client 
                            INNER JOIN Utilisateur ca ON c.id_utilisateur = ca.id_utilisateur 
                            WHERE r.id_agent = :id_agent 
                            ORDER BY r.date ASC, r.heure ASC');
            $this->db->bind(':id_agent', $id_agent);
            
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Exception lors de la récupération des rendez-vous d'un agent: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupération des rendez-vous en attente d'un agent
     * @param int $id_agent L'ID de l'agent
     * @return array Les rendez-vous en attente
     */
    public function getPendingAppointments($id_agent) {
        try {
            $this->db->query('SELECT r.*, 
                            p.titre as propriete_titre, 
                            c.id_client, ca.nom as client_nom, ca.prenom as client_prenom
                            FROM Rendez_Vous r 
                            INNER JOIN Propriete p ON r.id_propriete = p.id_propriete 
                            INNER JOIN Client c ON r.id_client = c.id_client 
                            INNER JOIN Utilisateur ca ON c.id_utilisateur = ca.id_utilisateur 
                            WHERE r.id_agent = :id_agent AND r.statut = "en attente"
                            ORDER BY r.date ASC, r.heure ASC');
            $this->db->bind(':id_agent', $id_agent);
            
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Exception lors de la récupération des rendez-vous en attente: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Accepter un rendez-vous
     * @param int $id_rdv L'ID du rendez-vous
     * @return bool Succès ou échec
     */
    public function acceptAppointment($id_rdv) {
        return $this->updateStatus($id_rdv, 'confirmé');
    }

    /**
     * Rejeter un rendez-vous
     * @param int $id_rdv L'ID du rendez-vous
     * @return bool Succès ou échec
     */
    public function rejectAppointment($id_rdv) {
        return $this->updateStatus($id_rdv, 'annulé');
    }

    /**
     * Récupération des rendez-vous d'un client
     * @param int $id_client L'ID du client
     * @return array Les rendez-vous
     */
    public function getClientAppointments($id_client) {
        try {
            $this->db->query('SELECT r.*, 
                            p.titre as propriete_titre, p.adresse as propriete_adresse, 
                            a.id_agent, u.nom as agent_nom, u.prenom as agent_prenom, u.email as agent_email, u.telephone as agent_telephone
                            FROM Rendez_Vous r 
                            INNER JOIN Propriete p ON r.id_propriete = p.id_propriete 
                            INNER JOIN Agent_Immobilier a ON r.id_agent = a.id_agent 
                            INNER JOIN Utilisateur u ON a.id_utilisateur = u.id_utilisateur 
                            WHERE r.id_client = :id_client 
                            ORDER BY r.date ASC, r.heure ASC');
            $this->db->bind(':id_client', $id_client);
            
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Exception lors de la récupération des rendez-vous d'un client: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Vérifier si un créneau est disponible
     * @param int $id_agent L'ID de l'agent
     * @param string $date La date (format Y-m-d)
     * @param string $heure L'heure (format H:i:s)
     * @return bool Disponible ou non
     */
    public function isSlotAvailable($id_agent, $date, $heure) {
        try {
            // 1. Vérifier s'il y a déjà un rendez-vous à ce créneau
            $this->db->query('SELECT COUNT(*) as count FROM Rendez_Vous 
                            WHERE id_agent = :id_agent AND date = :date 
                            AND TIME_FORMAT(heure, "%H:%i") = TIME_FORMAT(:heure, "%H:%i") 
                            AND statut != "annulé"');
            $this->db->bind(':id_agent', $id_agent);
            $this->db->bind(':date', $date);
            $this->db->bind(':heure', $heure);
            
            $result = $this->db->single();
            
            // Si le nombre est > 0, le créneau est déjà pris
            if ($result->count > 0) {
                return false;
            }
            
            // 2. Vérifier si l'agent est disponible à ce créneau
            $jour_semaine = date('N', strtotime($date)); // 1 (lundi) à 7 (dimanche)
            
            $this->db->query('SELECT COUNT(*) as count FROM Disponibilite 
                            WHERE id_agent = :id_agent 
                            AND jour = :date
                            AND TIME_FORMAT(:heure, "%H:%i:%s") BETWEEN TIME_FORMAT(heure_debut, "%H:%i:%s") AND TIME_FORMAT(heure_fin, "%H:%i:%s") 
                            AND statut = "disponible"');
            $this->db->bind(':id_agent', $id_agent);
            $this->db->bind(':date', $date);
            $this->db->bind(':heure', $heure);
            
            $result = $this->db->single();
            
            // Si le nombre est > 0, l'agent est disponible
            return $result->count > 0;
        } catch (Exception $e) {
            error_log("Exception lors de la vérification de disponibilité d'un créneau: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupération de tous les rendez-vous
     * @return array Tous les rendez-vous
     */
    public function getAllAppointments() {
        try {
            $this->db->query('SELECT r.*, 
                            p.titre as propriete_titre, 
                            c.id_client, ca.nom as client_nom, ca.prenom as client_prenom, 
                            a.id_agent, aa.nom as agent_nom, aa.prenom as agent_prenom
                            FROM Rendez_Vous r 
                            INNER JOIN Propriete p ON r.id_propriete = p.id_propriete 
                            INNER JOIN Client c ON r.id_client = c.id_client 
                            INNER JOIN Utilisateur ca ON c.id_utilisateur = ca.id_utilisateur 
                            INNER JOIN Agent_Immobilier a ON r.id_agent = a.id_agent 
                            INNER JOIN Utilisateur aa ON a.id_utilisateur = aa.id_utilisateur 
                            ORDER BY r.date DESC, r.heure DESC');
            
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Exception lors de la récupération de tous les rendez-vous: " . $e->getMessage());
            return [];
        }
    }
}
?>