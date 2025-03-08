<?php
require_once '../config/database.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';

// Données de l'administrateur
$admin_data = [
    'email' => 'admin@omnesimmobilier.fr',
    'mot_de_passe' => 'admin123', // À changer pour un mot de passe plus sécurisé
    'nom' => 'Admin',
    'prenom' => 'System',
    'telephone' => '0123456789',
    'type_utilisateur' => 'admin'
];

// Création de l'instance User
$user = new User();

// Vérification que l'email n'existe pas déjà
if ($user->findUserByEmail($admin_data['email'])) {
    echo "Un utilisateur avec cet email existe déjà.";
} else {
    // Enregistrement de l'utilisateur
    $user_id = $user->register($admin_data);

    if ($user_id) {
        // Création de l'administrateur
        $db = new Database();
        $db->query('INSERT INTO Administrateur (id_utilisateur, niveau_acces) VALUES (:id_utilisateur, :niveau_acces)');
        $db->bind(':id_utilisateur', $user_id);
        $db->bind(':niveau_acces', 1); // Niveau d'accès maximum

        if ($db->execute()) {
            echo "Administrateur créé avec succès !";
        } else {
            echo "Erreur lors de la création de l'administrateur.";
        }
    } else {
        echo "Erreur lors de l'enregistrement de l'utilisateur.";
    }
}
?>