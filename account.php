<?php
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
require_once BASE_PATH . 'config/init.php';

// Vérifier si l'utilisateur est connecté
if (!is_logged_in()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    set_alert('warning', 'Veuillez vous connecter pour accéder à votre compte.');
    redirect('/omnes-immobilier/login.php');
}

// Inclure les classes nécessaires selon le type d'utilisateur
require_once BASE_PATH . 'classes/User.php';

// Initialiser la classe User
$user = new User();

// Récupérer les infos de l'utilisateur
$user_info = $user->getUserById($_SESSION['user_id']);

// Déterminer le type d'utilisateur et inclure les classes appropriées
if ($user_info->type_utilisateur == 'client') {
    require_once BASE_PATH . 'classes/Client.php';
    require_once BASE_PATH . 'classes/Appointment.php';
    
    $client = new Client();
    $appointment = new Appointment();
    
    // Récupérer les infos du client
    $client_info = $client->getClientByUserId($_SESSION['user_id']);
    
    if ($client_info) {
        // Récupérer les rendez-vous du client
        $appointments = $appointment->getClientAppointments($client_info->id_client);
        
        // Filtrer pour obtenir uniquement les rendez-vous à venir
        $upcoming_appointments = array_filter($appointments, function($rdv) {
            return $rdv->statut != 'annulé' && (strtotime($rdv->date) > time() || (strtotime($rdv->date) == time() && strtotime($rdv->heure) > time()));
        });
    }
} elseif ($user_info->type_utilisateur == 'agent') {
    require_once BASE_PATH . 'classes/Agent.php';
    require_once BASE_PATH . 'classes/Property.php';
    require_once BASE_PATH . 'classes/Appointment.php';
    
    $agent = new Agent();
    $property = new Property();
    $appointment = new Appointment();
    
    // Récupérer les infos de l'agent
    $agent_info = $agent->getAgentByUserId($_SESSION['user_id']);
    
    if ($agent_info) {
        // Récupérer les propriétés gérées par l'agent
        $properties = $property->getPropertiesByAgent($agent_info->id_agent);
        
        // Récupérer les rendez-vous de l'agent
        $appointments = $appointment->getAgentAppointments($agent_info->id_agent);
        
        // Filtrer pour obtenir uniquement les rendez-vous à venir
        $upcoming_appointments = array_filter($appointments, function($rdv) {
            return $rdv->statut != 'annulé' && (strtotime($rdv->date) > time() || (strtotime($rdv->date) == time() && strtotime($rdv->heure) > time()));
        });
    }
} elseif ($user_info->type_utilisateur == 'admin') {
    // Rediriger vers l'interface d'administration
    redirect('/omnes-immobilier/admin/index.php');
}

$page_title = "Mon Compte";
include BASE_PATH . 'includes/header.php';
?>

<div class="container mt-4">
    <h1 class="section-title mb-4" data-aos="fade-up">Mon Compte</h1>
    
    <div class="row">
        <!-- Menu latéral -->
        <div class="col-md-3">
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    Menu
                </div>
                <div class="list-group list-group-flush">
                    <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="collapse" aria-expanded="true" aria-controls="profile">
                        <i class="fas fa-user"></i> Mon Profil
                    </a>
                    <?php if ($user_info->type_utilisateur == 'client') : ?>
                        <a href="/omnes-immobilier/my-appointments.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-calendar-alt"></i> Mes Rendez-vous
                        </a>
                    
                    <?php elseif ($user_info->type_utilisateur == 'agent') : ?>
                        <a href="/omnes-immobilier/agent-appointments.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-calendar-alt"></i> Mes Rendez-vous
                        </a>
                        <a href="/omnes-immobilier/agent-properties.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-home"></i> Mes Propriétés
                        </a>
                    <?php endif; ?>
                    <a href="/omnes-immobilier/logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Contenu principal -->
        <div class="col-md-9">
            <!-- Profil -->
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    Mon Profil
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <?php if (isset($agent_info) && isset($agent_info->photo_path) && $agent_info->photo_path) : ?>
                                <img src="<?php echo $agent_info->photo_path; ?>" class="img-thumbnail rounded-circle mb-3" alt="Photo de profil" style="width: 150px; height: 150px; object-fit: cover;" onerror="this.src='/omnes-immobilier/assets/img/agents/default.jpg'">
                            <?php else : ?>
                                <img src="/omnes-immobilier/assets/img/agents/default.jpg" class="img-thumbnail rounded-circle mb-3" alt="Photo de profil" style="width: 150px; height: 150px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/150?text=Profile'">
                            <?php endif; ?>
                            <h4><?php echo $user_info->prenom . ' ' . $user_info->nom; ?></h4>
                            <p class="text-muted">
                                <?php if ($user_info->type_utilisateur == 'client') : ?>
                                    Client
                                <?php elseif ($user_info->type_utilisateur == 'agent') : ?>
                                    Agent Immobilier
                                <?php elseif ($user_info->type_utilisateur == 'admin') : ?>
                                    Administrateur
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-8">
                            <h5>Informations personnelles</h5>
                            <hr>
                            <div class="mb-3 row">
                                <div class="col-sm-4">
                                    <strong>Nom :</strong>
                                </div>
                                <div class="col-sm-8">
                                    <?php echo $user_info->nom; ?>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <div class="col-sm-4">
                                    <strong>Prénom :</strong>
                                </div>
                                <div class="col-sm-8">
                                    <?php echo $user_info->prenom; ?>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <div class="col-sm-4">
                                    <strong>Email :</strong>
                                </div>
                                <div class="col-sm-8">
                                    <?php echo $user_info->email; ?>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <div class="col-sm-4">
                                    <strong>Téléphone :</strong>
                                </div>
                                <div class="col-sm-8">
                                    <?php echo $user_info->telephone ?? 'Non renseigné'; ?>
                                </div>
                            </div>
                            
                            <?php if (isset($client_info)) : ?>
                                <div class="mb-3 row">
                                    <div class="col-sm-4">
                                        <strong>Adresse :</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo $client_info->adresse ?? 'Non renseignée'; ?>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <div class="col-sm-4">
                                        <strong>Ville :</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo $client_info->ville ?? 'Non renseignée'; ?>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <div class="col-sm-4">
                                        <strong>Code postal :</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo $client_info->code_postal ?? 'Non renseigné'; ?>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <div class="col-sm-4">
                                        <strong>Pays :</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo $client_info->pays ?? 'Non renseigné'; ?>
                                    </div>
                                </div>
                            <?php elseif (isset($agent_info)) : ?>
                                <div class="mb-3 row">
                                    <div class="col-sm-4">
                                        <strong>Spécialité :</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php echo $agent_info->specialite ?? 'Non renseignée'; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="text-end mt-3">
                                <a href="/omnes-immobilier/edit-profile.php" class="btn btn-outline-primary">Modifier mes informations</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Rendez-vous à venir (résumé) -->
            <?php if (isset($upcoming_appointments) && !empty($upcoming_appointments)) : ?>
                <div class="card mb-4" data-aos="fade-up">
                    <div class="card-header bg-primary text-white">
                        Rendez-vous à venir
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Heure</th>
                                        <?php if ($user_info->type_utilisateur == 'client') : ?>
                                            <th>Agent</th>
                                        <?php else : ?>
                                            <th>Client</th>
                                        <?php endif; ?>
                                        <th>Propriété</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Afficher seulement les 3 premiers rendez-vous
                                    $displayed_appointments = array_slice($upcoming_appointments, 0, 3);
                                    ?>
                                    <?php foreach ($displayed_appointments as $rdv) : ?>
                                        <tr>
                                            <td><?php echo format_date($rdv->date); ?></td>
                                            <td><?php echo format_time($rdv->heure); ?></td>
                                            <?php if ($user_info->type_utilisateur == 'client') : ?>
                                                <td><?php echo $rdv->agent_prenom . ' ' . $rdv->agent_nom; ?></td>
                                            <?php else : ?>
                                                <td><?php echo $rdv->client_nom . ' ' . $rdv->client_prenom; ?></td>
                                            <?php endif; ?>
                                            <td><?php echo $rdv->propriete_titre; ?></td>
                                            <td>
                                                <?php if ($rdv->statut == 'confirmé') : ?>
                                                    <span class="badge bg-success">Confirmé</span>
                                                <?php elseif ($rdv->statut == 'en attente') : ?>
                                                    <span class="badge bg-warning">En attente</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (count($upcoming_appointments) > 3) : ?>
                            <div class="text-center mt-3">
                                <p>Vous avez <?php echo count($upcoming_appointments) - 3; ?> autres rendez-vous à venir.</p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-center mt-3">
                            <?php if ($user_info->type_utilisateur == 'client') : ?>
                                <a href="/omnes-immobilier/my-appointments.php" class="btn btn-primary">Voir tous mes rendez-vous</a>
                            <?php else : ?>
                                <a href="/omnes-immobilier/agent-appointments.php" class="btn btn-primary">Voir tous mes rendez-vous</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="card mb-4" data-aos="fade-up">
                    <div class="card-header bg-primary text-white">
                        Rendez-vous à venir
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            Vous n'avez aucun rendez-vous à venir.
                        </div>
                        
                        <?php if ($user_info->type_utilisateur == 'client') : ?>
                            <div class="text-center mt-3">
                                <a href="/omnes-immobilier/appointment.php" class="btn btn-primary">Prendre un rendez-vous</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>