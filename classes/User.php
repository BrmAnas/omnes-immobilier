<?php
class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Enregistrement d'un utilisateur
    public function register($data) {
        // Préparation de la requête
        $this->db->query('INSERT INTO Utilisateur (email, mot_de_passe, nom, prenom, telephone, type_utilisateur) 
                        VALUES (:email, :mot_de_passe, :nom, :prenom, :telephone, :type_utilisateur)');

        // Liaison des valeurs
        $this->db->bind(':email', $data['email']);
        // Hashage du mot de passe avec bcrypt
        $this->db->bind(':mot_de_passe', password_hash($data['mot_de_passe'], PASSWORD_DEFAULT));
        $this->db->bind(':nom', $data['nom']);
        $this->db->bind(':prenom', $data['prenom']);
        $this->db->bind(':telephone', $data['telephone'] ?? null);
        $this->db->bind(':type_utilisateur', $data['type_utilisateur']);

        // Exécution
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    // Vérification qu'un email existe déjà
    public function findUserByEmail($email) {
        $this->db->query('SELECT * FROM Utilisateur WHERE email = :email');
        $this->db->bind(':email', $email);

        $this->db->execute();
        
        // Vérification si un utilisateur a été trouvé
        return $this->db->rowCount() > 0;
    }

    // Connexion d'un utilisateur
    public function login($email, $password) {
        $this->db->query('SELECT * FROM Utilisateur WHERE email = :email');
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        if ($row) {
            $hashed_password = $row->mot_de_passe;
            if (password_verify($password, $hashed_password)) {
                return $row;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    // Récupération d'un utilisateur par son ID
    public function getUserById($id) {
        $this->db->query('SELECT * FROM Utilisateur WHERE id_utilisateur = :id');
        $this->db->bind(':id', $id);

        return $this->db->single();
    }
    
    // Mise à jour des informations d'un utilisateur
    public function updateUser($data) {
        $this->db->query('UPDATE Utilisateur SET nom = :nom, prenom = :prenom, 
                        telephone = :telephone WHERE id_utilisateur = :id_utilisateur');
        
        $this->db->bind(':id_utilisateur', $data['id_utilisateur']);
        $this->db->bind(':nom', $data['nom']);
        $this->db->bind(':prenom', $data['prenom']);
        $this->db->bind(':telephone', $data['telephone'] ?? null);
        
        return $this->db->execute();
    }
    
    // Modification du mot de passe
    public function updatePassword($user_id, $new_password) {
        $this->db->query('UPDATE Utilisateur SET mot_de_passe = :mot_de_passe 
                        WHERE id_utilisateur = :id_utilisateur');
        
        $this->db->bind(':id_utilisateur', $user_id);
        $this->db->bind(':mot_de_passe', password_hash($new_password, PASSWORD_DEFAULT));
        
        return $this->db->execute();
    }

    // Suppression d'un utilisateur par son ID
    public function delete($id_utilisateur) {
        try {
            $this->db->query('DELETE FROM Utilisateur WHERE id_utilisateur = :id_utilisateur');
            $this->db->bind(':id_utilisateur', $id_utilisateur);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Exception lors de la suppression d'un utilisateur: " . $e->getMessage());
            return false;
        }
    }
}
?>