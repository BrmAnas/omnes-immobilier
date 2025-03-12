<?php
class Agent {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Création d'un agent immobilier
     * @param array $data Les données de l'agent
     * @return int|false L'ID de l'agent créé ou false en cas d'erreur
     */
    public function create($data) {
        try {
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
                error_log("Erreur lors de la création d'un agent: " . print_r($data, true));
                return false;
            }
        } catch (Exception $e) {
            error_log("Exception lors de la création d'un agent: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupération d'un agent par son ID utilisateur
     * @param int $id_utilisateur L'ID de l'utilisateur
     * @return object|false L'agent ou false si non trouvé
     */
    public function getAgentByUserId($id_utilisateur) {
        try {
            $this->db->query('SELECT * FROM Agent_Immobilier WHERE id_utilisateur = :id_utilisateur');
            $this->db->bind(':id_utilisateur', $id_utilisateur);
            
            return $this->db->single();
        } catch (Exception $e) {
            error_log("Exception lors de la récupération d'un agent par ID utilisateur: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupération d'un agent par son ID agent
     * @param int $id_agent L'ID de l'agent
     * @return object|false L'agent avec ses informations ou false si non trouvé
     */
    public function getAgentById($id_agent) {
        try {
            $this->db->query('SELECT a.*, u.nom, u.prenom, u.email, u.telephone 
                            FROM Agent_Immobilier a 
                            INNER JOIN Utilisateur u ON a.id_utilisateur = u.id_utilisateur 
                            WHERE a.id_agent = :id_agent');
            $this->db->bind(':id_agent', $id_agent);
            
            return $this->db->single();
        } catch (Exception $e) {
            error_log("Exception lors de la récupération d'un agent par ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupération de tous les agents
     * @return array Les agents
     */
    public function getAllAgents() {
        try {
            $this->db->query('SELECT a.*, u.nom, u.prenom, u.email, u.telephone 
                            FROM Agent_Immobilier a 
                            INNER JOIN Utilisateur u ON a.id_utilisateur = u.id_utilisateur 
                            ORDER BY u.nom, u.prenom');
            
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Exception lors de la récupération de tous les agents: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupération des agents par spécialité
     * @param string $specialite La spécialité recherchée
     * @return array Les agents correspondants
     */
    public function getAgentsBySpeciality($specialite) {
        try {
            $this->db->query('SELECT a.*, u.nom, u.prenom, u.email, u.telephone 
                            FROM Agent_Immobilier a 
                            INNER JOIN Utilisateur u ON a.id_utilisateur = u.id_utilisateur 
                            WHERE a.specialite = :specialite 
                            ORDER BY u.nom, u.prenom');
            $this->db->bind(':specialite', $specialite);
            
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Exception lors de la récupération des agents par spécialité: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Mise à jour des informations d'un agent
     * @param array $data Les données à mettre à jour
     * @return bool Succès ou échec
     */
    public function update($data) {
        try {
            $this->db->query('UPDATE Agent_Immobilier 
                            SET specialite = :specialite, biographie = :biographie, 
                            cv_path = :cv_path, photo_path = :photo_path 
                            WHERE id_agent = :id_agent');
            
            $this->db->bind(':id_agent', $data['id_agent']);
            $this->db->bind(':specialite', $data['specialite']);
            $this->db->bind(':biographie', $data['biographie'] ?? null);
            $this->db->bind(':cv_path', $data['cv_path'] ?? null);
            $this->db->bind(':photo_path', $data['photo_path'] ?? null);
            
            $result = $this->db->execute();
            if (!$result) {
                error_log("Erreur lors de la mise à jour d'un agent: " . print_r($data, true));
            }
            return $result;
        } catch (Exception $e) {
            error_log("Exception lors de la mise à jour d'un agent: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupération des disponibilités d'un agent
     * @param int $id_agent L'ID de l'agent
     * @return array Les disponibilités
     */
    public function getAvailabilities($id_agent) {
        try {
            $this->db->query('SELECT * FROM Disponibilite 
                            WHERE id_agent = :id_agent 
                            ORDER BY jour, heure_debut');
            $this->db->bind(':id_agent', $id_agent);
            
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Exception lors de la récupération des disponibilités: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupération des disponibilités d'un agent par semaine
     * @param int $id_agent L'ID de l'agent
     * @param string $date_debut Date de début de la semaine (format Y-m-d)
     * @return array Les disponibilités de la semaine
     */
    public function getWeeklyAvailabilities($id_agent, $date_debut = null) {
        try {
            // Si aucune date n'est fournie, utiliser la date du jour
            if (!$date_debut) {
                $date_debut = date('Y-m-d');
            }
            
            // Calculer la date de début de semaine (lundi)
            $day_of_week = date('N', strtotime($date_debut));
            $days_to_subtract = $day_of_week - 1;
            $monday = date('Y-m-d', strtotime("-{$days_to_subtract} days", strtotime($date_debut)));
            
            // Calculer la date de fin de semaine (dimanche)
            $sunday = date('Y-m-d', strtotime("+6 days", strtotime($monday)));
            
            $this->db->query('SELECT * FROM Disponibilite 
                            WHERE id_agent = :id_agent 
                            AND jour BETWEEN :date_debut AND :date_fin 
                            ORDER BY jour, heure_debut');
            $this->db->bind(':id_agent', $id_agent);
            $this->db->bind(':date_debut', $monday);
            $this->db->bind(':date_fin', $sunday);
            
            $results = $this->db->resultSet();
            
            // Organiser les résultats par jour
            $availabilities = [];
            foreach ($results as $result) {
                $day_of_week = date('N', strtotime($result->jour)); // 1 (lundi) à 7 (dimanche)
                
                if (!isset($availabilities[$day_of_week])) {
                    $availabilities[$day_of_week] = [];
                }
                
                $availabilities[$day_of_week][] = $result;
            }
            
            return $availabilities;
        } catch (Exception $e) {
            error_log("Exception lors de la récupération des disponibilités hebdomadaires: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Ajout d'une disponibilité pour un agent
     * @param array $data Les données de disponibilité
     * @return bool Succès ou échec
     */
    public function addAvailability($data) {
        try {
            // Vérifier si une disponibilité existe déjà pour cette date et cette période
            $this->db->query('SELECT COUNT(*) as count FROM Disponibilite 
                            WHERE id_agent = :id_agent AND jour = :jour 
                            AND ((heure_debut <= :heure_debut AND heure_fin > :heure_debut) 
                            OR (heure_debut < :heure_fin AND heure_fin >= :heure_fin)
                            OR (heure_debut >= :heure_debut AND heure_fin <= :heure_fin))');
            
            $this->db->bind(':id_agent', $data['id_agent']);
            $this->db->bind(':jour', $data['jour']);
            $this->db->bind(':heure_debut', $data['heure_debut']);
            $this->db->bind(':heure_fin', $data['heure_fin']);
            
            $result = $this->db->single();
            
            // Si une disponibilité existe déjà, retourner false
            if ($result->count > 0) {
                return false;
            }
            
            // Sinon, ajouter la nouvelle disponibilité
            $this->db->query('INSERT INTO Disponibilite (id_agent, jour, heure_debut, heure_fin, statut) 
                            VALUES (:id_agent, :jour, :heure_debut, :heure_fin, :statut)');
            
            $this->db->bind(':id_agent', $data['id_agent']);
            $this->db->bind(':jour', $data['jour']);
            $this->db->bind(':heure_debut', $data['heure_debut']);
            $this->db->bind(':heure_fin', $data['heure_fin']);
            $this->db->bind(':statut', $data['statut'] ?? 'disponible');
            
            $result = $this->db->execute();
            if (!$result) {
                error_log("Erreur lors de l'ajout d'une disponibilité: " . print_r($data, true));
            }
            return $result;
        } catch (Exception $e) {
            error_log("Exception lors de l'ajout d'une disponibilité: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Suppression d'une disponibilité
     * @param int $id_disponibilite L'ID de la disponibilité
     * @return bool Succès ou échec
     */
    public function deleteAvailability($id_disponibilite) {
        try {
            $this->db->query('DELETE FROM Disponibilite WHERE id_disponibilite = :id_disponibilite');
            $this->db->bind(':id_disponibilite', $id_disponibilite);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Exception lors de la suppression d'une disponibilité: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier si une date et une heure sont dans les disponibilités d'un agent
     * @param int $id_agent L'ID de l'agent
     * @param string $date La date (format Y-m-d)
     * @param string $heure L'heure (format H:i:s)
     * @return bool Disponible ou non
     */
    public function isAvailable($id_agent, $date, $heure) {
        try {
            $this->db->query('SELECT COUNT(*) as count FROM Disponibilite 
                            WHERE id_agent = :id_agent AND jour = :jour 
                            AND :heure BETWEEN heure_debut AND heure_fin 
                            AND statut = "disponible"');
            
            $this->db->bind(':id_agent', $id_agent);
            $this->db->bind(':jour', $date);
            $this->db->bind(':heure', $heure);
            
            $result = $this->db->single();
            
            return $result->count > 0;
        } catch (Exception $e) {
            error_log("Exception lors de la vérification de disponibilité: " . $e->getMessage());
            return false;
        }
    }
}
?>