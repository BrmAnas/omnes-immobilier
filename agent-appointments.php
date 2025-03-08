<?php
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
require_once BASE_PATH . 'config/init.php';

// Vérifier si l'utilisateur est connecté
if (!is_logged_in()) {
    set_alert('warning', 'Veuillez vous connecter pour accéder à vos rendez-vous.');
    redirect('/omnes-immobilier/login.php');
}

// Vérifier si l'utilisateur est un agent
if (!is_user_type('agent')) {
    set_alert('danger', 'Vous n\'êtes pas autorisé à accéder à cette page.');
    redirect('/omnes-immobilier/index.php');
}

// Inclure les classes nécessaires
require_once BASE_PATH . 'classes/Agent.php';
require_once BASE_PATH . 'classes/Appointment.php';

// Initialiser les classes
$agent = new Agent();
$appointment = new Appointment();

// Récupérer les infos de l'agent
$agent_info = $agent->getAgentByUserId($_SESSION['user_id']);
if (!$agent_info) {
    set_alert('danger', 'Une erreur est survenue lors de la récupération de vos informations.');
    redirect('/omnes-immobilier/index.php');
}

// Récupérer les rendez-vous de l'agent
$appointments = $appointment->getAgentAppointments($agent_info->id_agent);

// Gérer les actions sur les rendez-vous
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id_rdv = $_GET['id'];
    $action = $_GET['action'];
    
    // Vérifier que le rendez-vous appartient bien à l'agent
    $rdv = $appointment->getAppointmentById($id_rdv);
    if ($rdv && $rdv->id_agent == $agent_info->id_agent) {
        // Actions possibles
        if ($action == 'confirm' && $rdv->statut == 'en attente') {
            if ($appointment->updateStatus($id_rdv, 'confirmé')) {
                set_alert('success', 'Le rendez-vous a été confirmé avec succès.');
            } else {
                set_alert('danger', 'Une erreur est survenue lors de la confirmation du rendez-vous.');
            }
        } elseif ($action == 'cancel' && $rdv->statut != 'annulé') {
            if ($appointment->updateStatus($id_rdv, 'annulé')) {
                set_alert('success', 'Le rendez-vous a été annulé avec succès.');
            } else {
                set_alert('danger', 'Une erreur est survenue lors de l\'annulation du rendez-vous.');
            }
        }
    } else {
        set_alert('danger', 'Vous n\'êtes pas autorisé à modifier ce rendez-vous.');
    }
    
    // Rediriger pour éviter la réexécution du code en cas de rafraîchissement
    redirect('/omnes-immobilier/agent-appointments.php');
}

$page_title = "Mes rendez-vous";
include BASE_PATH . 'includes/header.php';
?>

<div class="container mt-4">
    <h1 class="section-title mb-4" data-aos="fade-up">Mes rendez-vous</h1>
    
    <div class="row">
        <div class="col-md-3">
            <!-- Menu latéral -->
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    Menu
                </div>
                <div class="list-group list-group-flush">
                    <a href="/omnes-immobilier/account.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-user"></i> Mon Profil
                    </a>
                    <a href="/omnes-immobilier/agent-appointments.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-calendar-alt"></i> Mes Rendez-vous
                    </a>
                    <a href="/omnes-immobilier/agent-properties.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-home"></i> Mes Propriétés
                    </a>
                    <a href="/omnes-immobilier/edit-profile.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-cog"></i> Paramètres
                    </a>
                    <a href="/omnes-immobilier/logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <!-- Rendez-vous d'aujourd'hui -->
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Rendez-vous d'aujourd'hui</h5>
                </div>
                <div class="card-body">
                    <?php 
                    $today_appointments = array_filter($appointments, function($rdv) {
                        return $rdv->date == date('Y-m-d') && $rdv->statut != 'annulé';
                    });
                    ?>
                    
                    <?php if (!empty($today_appointments)) : ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Heure</th>
                                        <th>Client</th>
                                        <th>Propriété</th>
                                        <th>Motif</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($today_appointments as $rdv) : ?>
                                        <tr>
                                            <td><?php echo format_time($rdv->heure); ?></td>
                                            <td>
                                                <?php echo $rdv->client_nom . ' ' . $rdv->client_prenom; ?>
                                            </td>
                                            <td><?php echo $rdv->propriete_titre; ?></td>
                                            <td><?php echo $rdv->motif; ?></td>
                                            <td>
                                                <?php if ($rdv->statut == 'confirmé') : ?>
                                                    <span class="badge bg-success">Confirmé</span>
                                                <?php elseif ($rdv->statut == 'en attente') : ?>
                                                    <span class="badge bg-warning">En attente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($rdv->statut == 'en attente') : ?>
                                                    <a href="/omnes-immobilier/agent-appointments.php?action=confirm&id=<?php echo $rdv->id_rdv; ?>" class="btn btn-sm btn-success me-1" title="Confirmer">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="/omnes-immobilier/agent-appointments.php?action=cancel&id=<?php echo $rdv->id_rdv; ?>" class="btn btn-sm btn-danger" title="Annuler" onclick="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?')">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-info">
                            Vous n'avez aucun rendez-vous aujourd'hui.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Rendez-vous à venir -->
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Rendez-vous à venir</h5>
                </div>
                <div class="card-body">
                    <?php 
                    $upcoming_appointments = array_filter($appointments, function($rdv) {
                        return $rdv->date > date('Y-m-d') && $rdv->statut != 'annulé';
                    });
                    ?>
                    
                    <?php if (!empty($upcoming_appointments)) : ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Heure</th>
                                        <th>Client</th>
                                        <th>Propriété</th>
                                        <th>Motif</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcoming_appointments as $rdv) : ?>
                                        <tr>
                                            <td><?php echo format_date($rdv->date); ?></td>
                                            <td><?php echo format_time($rdv->heure); ?></td>
                                            <td>
                                                <?php echo $rdv->client_nom . ' ' . $rdv->client_prenom; ?>
                                            </td>
                                            <td><?php echo $rdv->propriete_titre; ?></td>
                                            <td><?php echo $rdv->motif; ?></td>
                                            <td>
                                                <?php if ($rdv->statut == 'confirmé') : ?>
                                                    <span class="badge bg-success">Confirmé</span>
                                                <?php elseif ($rdv->statut == 'en attente') : ?>
                                                    <span class="badge bg-warning">En attente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($rdv->statut == 'en attente') : ?>
                                                    <a href="/omnes-immobilier/agent-appointments.php?action=confirm&id=<?php echo $rdv->id_rdv; ?>" class="btn btn-sm btn-success me-1" title="Confirmer">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="/omnes-immobilier/agent-appointments.php?action=cancel&id=<?php echo $rdv->id_rdv; ?>" class="btn btn-sm btn-danger" title="Annuler" onclick="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?')">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        phpCopy                    <?php else : ?>
                        <div class="alert alert-info">
                            Vous n'avez aucun rendez-vous à venir.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Historique des rendez-vous -->
            <div class="card" data-aos="fade-up">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Historique des rendez-vous</h5>
                </div>
                <div class="card-body">
                    <?php 
                    $past_appointments = array_filter($appointments, function($rdv) {
                        return ($rdv->date < date('Y-m-d') || ($rdv->date == date('Y-m-d') && $rdv->heure < date('H:i:s'))) || $rdv->statut == 'annulé';
                    });
                    ?>
                    
                    <?php if (!empty($past_appointments)) : ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Heure</th>
                                        <th>Client</th>
                                        <th>Propriété</th>
                                        <th>Motif</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($past_appointments as $rdv) : ?>
                                        <tr>
                                            <td><?php echo format_date($rdv->date); ?></td>
                                            <td><?php echo format_time($rdv->heure); ?></td>
                                            <td>
                                                <?php echo $rdv->client_nom . ' ' . $rdv->client_prenom; ?>
                                            </td>
                                            <td><?php echo $rdv->propriete_titre; ?></td>
                                            <td><?php echo $rdv->motif; ?></td>
                                            <td>
                                                <?php if ($rdv->statut == 'confirmé') : ?>
                                                    <span class="badge bg-success">Confirmé</span>
                                                <?php elseif ($rdv->statut == 'en attente') : ?>
                                                    <span class="badge bg-warning">En attente</span>
                                                <?php elseif ($rdv->statut == 'annulé') : ?>
                                                    <span class="badge bg-danger">Annulé</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-info">
                            Aucun historique de rendez-vous.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>