<?php
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
require_once BASE_PATH . 'config/init.php';

// Vérifier si l'utilisateur est connecté
if (!is_logged_in()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    set_alert('warning', 'Veuillez vous connecter pour accéder à vos rendez-vous.');
    redirect('/omnes-immobilier/login.php');
}

// Inclure les classes nécessaires
require_once BASE_PATH . 'classes/Client.php';
require_once BASE_PATH . 'classes/Appointment.php';

// Initialiser les classes
$client = new Client();
$appointment = new Appointment();

// Vérifier si l'utilisateur est un client
$user_client = $client->getClientByUserId($_SESSION['user_id']);
if (!$user_client) {
    set_alert('danger', 'Vous devez être un client pour accéder à cette page.');
    redirect('/omnes-immobilier/index.php');
}

// Récupérer les rendez-vous du client
$appointments = $appointment->getClientAppointments($user_client->id_client);

// Gérer l'annulation d'un rendez-vous
if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['id'])) {
    $id_rdv = $_GET['id'];
    
    // Vérifier que le rendez-vous appartient bien au client
    $rdv = $appointment->getAppointmentById($id_rdv);
    if ($rdv && $rdv->id_client == $user_client->id_client) {
        // Annuler le rendez-vous
        if ($appointment->updateStatus($id_rdv, 'annulé')) {
            set_alert('success', 'Le rendez-vous a été annulé avec succès.');
        } else {
            set_alert('danger', 'Une erreur est survenue lors de l\'annulation du rendez-vous.');
        }
    } else {
        set_alert('danger', 'Vous n\'êtes pas autorisé à annuler ce rendez-vous.');
    }
    
    // Rediriger pour éviter la réexécution du code en cas de rafraîchissement
    redirect('/omnes-immobilier/my-appointments.php');
}

$page_title = "Mes rendez-vous";
include BASE_PATH . 'includes/header.php';
?>

<div class="container mt-4">
    <h1 class="section-title mb-4" data-aos="fade-up">Mes rendez-vous</h1>
    
    <!-- Rendez-vous à venir -->
    <div class="card mb-4" data-aos="fade-up">
        <div class="card-header bg-primary text-white">
            Rendez-vous à venir
        </div>
        <div class="card-body">
            <?php 
            $upcoming_appointments = array_filter($appointments, function($rdv) {
                return $rdv->statut != 'annulé' && (strtotime($rdv->date) > time() || (strtotime($rdv->date) == time() && strtotime($rdv->heure) > time()));
            });
            ?>
            
            <?php if (!empty($upcoming_appointments)) : ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Heure</th>
                                <th>Agent</th>
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
                                    <td><?php echo $rdv->agent_prenom . ' ' . $rdv->agent_nom; ?></td>
                                    <td><?php echo $rdv->propriete_titre; ?><br>
                                        <small class="text-muted"><?php echo $rdv->propriete_adresse; ?></small>
                                    </td>
                                    <td><?php echo $rdv->motif; ?></td>
                                    <td>
                                        <?php if ($rdv->statut == 'confirmé') : ?>
                                            <span class="badge bg-success">Confirmé</span>
                                        <?php elseif ($rdv->statut == 'en attente') : ?>
                                            <span class="badge bg-warning">En attente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="/omnes-immobilier/my-appointments.php?action=cancel&id=<?php echo $rdv->id_rdv; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?')">Annuler</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <div class="alert alert-info">
                    Vous n'avez aucun rendez-vous à venir.
                </div>
            <?php endif; ?>
            
            <div class="text-center mt-3">
                <a href="/omnes-immobilier/appointment.php" class="btn btn-primary">Prendre un nouveau rendez-vous</a>
            </div>
        </div>
    </div>
    
    <!-- Historique des rendez-vous -->
    <div class="card" data-aos="fade-up">
        <div class="card-header bg-secondary text-white">
            Historique des rendez-vous
        </div>
        <div class="card-body">
            <?php 
            $past_appointments = array_filter($appointments, function($rdv) {
                return $rdv->statut == 'annulé' || (strtotime($rdv->date) < time() || (strtotime($rdv->date) == time() && strtotime($rdv->heure) < time()));
            });
            ?>
            
            <?php if (!empty($past_appointments)) : ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Heure</th>
                                <th>Agent</th>
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
                                    <td><?php echo $rdv->agent_prenom . ' ' . $rdv->agent_nom; ?></td>
                                    <td><?php echo $rdv->propriete_titre; ?><br>
                                        <small class="text-muted"><?php echo $rdv->propriete_adresse; ?></small>
                                    </td>
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

<?php include BASE_PATH . 'includes/footer.php'; ?>