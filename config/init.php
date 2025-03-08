<?php
// Empêcher l'accès direct à ce fichier
if (!defined('BASE_PATH')) {
    define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
}

// Activer l'affichage des erreurs en développement
// À commenter en production
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion du fichier de configuration de base de données
require_once BASE_PATH . 'config/database.php';

// Fonction d'autoload pour charger automatiquement les classes
spl_autoload_register(function($class_name) {
    $class_file = BASE_PATH . 'classes/' . $class_name . '.php';
    
    if (file_exists($class_file)) {
        require_once $class_file;
    } else {
        // Log l'erreur mais ne pas arrêter l'exécution
        error_log("Impossible de charger la classe : {$class_name}");
    }
});

// Fonction utilitaire pour rediriger
function redirect($path) {
    header("Location: {$path}");
    exit;
}

// Fonction pour vérifier si l'utilisateur est connecté
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Fonction pour vérifier le type d'utilisateur
function is_user_type($type) {
    return is_logged_in() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === $type;
}

// Fonction pour nettoyer les entrées utilisateur
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fonction pour générer des messages d'alerte
function set_alert($type, $message) {
    $_SESSION['alert_type'] = $type; // 'success', 'danger', 'warning', 'info'
    $_SESSION['alert_message'] = $message;
}

// Fonction pour afficher des messages d'alerte
function display_alert() {
    if (isset($_SESSION['alert_type']) && isset($_SESSION['alert_message'])) {
        $type = $_SESSION['alert_type'];
        $message = $_SESSION['alert_message'];
        
        echo "<div class='alert alert-{$type}'>{$message}</div>";
        
        // Une fois affiché, on supprime le message
        unset($_SESSION['alert_type']);
        unset($_SESSION['alert_message']);
    }
}

// Fonction pour formater les prix
function format_price($price) {
    return number_format($price, 0, ',', ' ') . ' €';
}

// Fonction pour formater les dates
function format_date($date) {
    return date('d/m/Y', strtotime($date));
}

// Fonction pour formater les heures
function format_time($time) {
    return date('H:i', strtotime($time));
}