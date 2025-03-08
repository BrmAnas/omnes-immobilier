<?php
class Client {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Création d'un client
    public function create($data) {
        $this->db->query('INSERT INTO Client (id_utilisateur, adresse, ville, code_postal, pays) 
                        VALUES (:id_utilisateur, :adresse, :ville, :code_postal, :pays)');
        
        $this->db->bind(':id_utilisateur', $data['id_utilisateur']);
        $this->db->bind(':adresse', $data['adresse'] ?? null);
        $this->db->bind(':ville', $data['ville'] ?? null);
        $this->db->bind(':code_postal', $data['code_postal'] ?? null);
        $this->db->bind(':pays', $data['pays'] ?? 'France');
        
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    // Récupération d'un client par son ID utilisateur
    public function getClientByUserId($id_utilisateur) {
        $this->db->query('SELECT * FROM Client WHERE id_utilisateur = :id_utilisateur');
        $this->db->bind(':id_utilisateur', $id_utilisateur);
        
        return $this->db->single();
    }

    // Récupération d'un client par son ID client
    public function getClientById($id_client) {
        $this->db->query('SELECT c.*, u.nom, u.prenom, u.email, u.telephone 
                        FROM Client c 
                        INNER JOIN Utilisateur u ON c.id_utilisateur = u.id_utilisateur 
                        WHERE c.id_client = :id_client');
        $this->db->bind(':id_client', $id_client);
        
        return $this->db->single();
    }

    // Mise à jour des informations d'un client
    public function update($data) {
        $this->db->query('UPDATE Client SET adresse = :adresse, ville = :ville, 
                        code_postal = :code_postal, pays = :pays 
                        WHERE id_client = :id_client');
        
        $this->db->bind(':id_client', $data['id_client']);
        $this->db->bind(':adresse', $data['adresse'] ?? null);
        $this->db->bind(':ville', $data['ville'] ?? null);
        $this->db->bind(':code_postal', $data['code_postal'] ?? null);
        $this->db->bind(':pays', $data['pays'] ?? 'France');
        
        return $this->db->execute();
    }
} ?>