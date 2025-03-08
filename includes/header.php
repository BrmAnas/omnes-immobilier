<?php
// Vérifiez si init.php a déjà été inclus
if (!defined('BASE_PATH')) {
    define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
    require_once BASE_PATH . 'config/init.php';
}

// Récupérer le titre de la page
$page_title = $page_title ?? 'Bienvenue';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Omnes Immobilier - <?php echo $page_title; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo '/omnes-immobilier/assets/css/style.css'; ?>">
</head>
<body>
    <div class="wrapper">
        <header>
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container">
                    <a class="navbar-brand" href="/omnes-immobilier/index.php">
                        Omnes Immobilier
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="/omnes-immobilier/index.php">Accueil</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/omnes-immobilier/properties.php">Tout Parcourir</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/omnes-immobilier/search.php">Recherche</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/omnes-immobilier/appointment.php">Rendez-vous</a>
                            </li>
                            <?php if (isset($_SESSION['user_id'])) : ?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <?php echo $_SESSION['user_name'] ?? 'Mon Compte'; ?>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                        <li><a class="dropdown-item" href="/omnes-immobilier/account.php">Profil</a></li>
                                        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'client') : ?>
                                            <li><a class="dropdown-item" href="/omnes-immobilier/my-appointments.php">Mes rendez-vous</a></li>
                                        <?php elseif (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'agent') : ?>
                                            <li><a class="dropdown-item" href="/omnes-immobilier/agent-appointments.php">Mes rendez-vous</a></li>
                                        <?php elseif (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') : ?>
                                            <li><a class="dropdown-item" href="/omnes-immobilier/admin/index.php">Administration</a></li>
                                        <?php endif; ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="/omnes-immobilier/logout.php">Déconnexion</a></li>
                                    </ul>
                                </li>
                            <?php else : ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="/omnes-immobilier/login.php">Connexion</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
        <main class="container mt-4">
            <?php
            // Afficher les messages d'alerte
            display_alert();
            ?>