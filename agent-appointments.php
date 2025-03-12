<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
}

// Inclure les fichiers nécessaires
require_once BASE_PATH . 'config/init.php';
require_once BASE_PATH . 'classes/Agent.php';
require_once BASE_PATH . 'classes/Appointment.php';

// Vérifier si l'utilisateur est connecté et est un agent
if (!is_logged_in() || !is_user_type('agent')) {
    set_alert('danger', 'Accès refusé.');
    redirect('/omnes-immobilier/index.php');
}

// Titre de la page
$page_title = "Gestion des Rendez-vous";
include BASE_PATH . 'includes/header.php';

// Initialisation des objets
$agent = new Agent();
$appointment = new Appointment();

// Récupération des informations de l'agent connecté
$agent_info = $agent->getAgentByUserId($_SESSION['user_id']);

if (!$agent_info) {
    set_alert('danger', 'Impossible de récupérer vos informations d\'agent.');
    redirect('/omnes-immobilier/index.php');
}

// Récupération des disponibilités et rendez-vous
$availabilities = $agent->getAvailabilities($agent_info->id_agent);
$pending_appointments = $appointment->getPendingAppointments($agent_info->id_agent);
$upcoming_appointments = [];
$past_appointments = [];

// Récupérer tous les rendez-vous de l'agent
$all_appointments = $appointment->getAgentAppointments($agent_info->id_agent);
$today = date('Y-m-d');

// Séparer les rendez-vous à venir et passés
foreach ($all_appointments as $rdv) {
    if ($rdv->statut == 'annulé') {
        $past_appointments[] = $rdv;
    } elseif ($rdv->date > $today || ($rdv->date == $today && strtotime($rdv->heure) > time())) {
        $upcoming_appointments[] = $rdv;
    } else {
        $past_appointments[] = $rdv;
    }
}

// Gestion des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ajouter une disponibilité
    if (isset($_POST['add_availability'])) {
        $date = $_POST['date'];
        $period = $_POST['period']; // Récupérer la période (matin ou après-midi)
        
        // Validation des données
        if (empty($date) || empty($period)) {
            set_alert('danger', 'Veuillez remplir tous les champs.');
            redirect('/omnes-immobilier/agent-appointments.php');
        }
        
        // Vérifier que la date est future
        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            set_alert('danger', 'Vous ne pouvez pas ajouter une disponibilité pour une date passée.');
            redirect('/omnes-immobilier/agent-appointments.php');
        }

        // Définir les heures de début et de fin selon la période
        $start_time = ($period === 'morning') ? '08:00:00' : '14:00:00';
        $end_time = ($period === 'morning') ? '12:00:00' : '17:00:00';
        
        // Préparer le tableau de données pour la méthode addAvailability
        $availability_data = [
            'id_agent' => $agent_info->id_agent,
            'jour' => $date,
            'heure_debut' => $start_time,
            'heure_fin' => $end_time,
            'statut' => 'disponible'
        ];
        
        // Appeler la méthode avec le tableau de données
        if ($agent->addAvailability($availability_data)) {
            set_alert('success', 'Disponibilité ajoutée avec succès.');
        } else {
            set_alert('danger', 'Erreur lors de l\'ajout de la disponibilité. Peut-être que ce créneau existe déjà ?');
        }
        
        redirect('/omnes-immobilier/agent-appointments.php');
    }
    
    // Supprimer une disponibilité
    if (isset($_POST['delete_availability'])) {
        $availability_id = $_POST['availability_id'];
        
        if ($agent->deleteAvailability($availability_id)) {
            set_alert('success', 'Disponibilité supprimée avec succès.');
        } else {
            set_alert('danger', 'Erreur lors de la suppression de la disponibilité.');
        }
        
        redirect('/omnes-immobilier/agent-appointments.php');
    }
    
    // Accepter un rendez-vous
    if (isset($_POST['accept_appointment'])) {
        $appointment_id = $_POST['appointment_id'];
        
        if ($appointment->acceptAppointment($appointment_id)) {
            // Envoyer un email au client pour l'informer de l'acceptation du rendez-vous
            // Code pour envoyer un email ici si nécessaire
            
            set_alert('success', 'Rendez-vous accepté avec succès.');
        } else {
            set_alert('danger', 'Erreur lors de l\'acceptation du rendez-vous.');
        }
        
        redirect('/omnes-immobilier/agent-appointments.php');
    }
    
    // Rejeter un rendez-vous
    if (isset($_POST['reject_appointment'])) {
        $appointment_id = $_POST['appointment_id'];
        
        if ($appointment->rejectAppointment($appointment_id)) {
            // Envoyer un email au client pour l'informer du rejet du rendez-vous
            // Code pour envoyer un email ici si nécessaire
            
            set_alert('success', 'Rendez-vous refusé avec succès.');
        } else {
            set_alert('danger', 'Erreur lors du refus du rendez-vous.');
        }
        
        redirect('/omnes-immobilier/agent-appointments.php');
    }
}
?>

<div class="container mt-4">
    <!-- Section d'ajout de disponibilités -->
    <section class="agent-availabilities mb-5" data-aos="fade-up">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2 class="h5 mb-0">Gérer vos disponibilités</h2>
            </div>
            <div class="card-body">
                <form method="post" class="row g-3">
                    <div class="col-md-5">
                        <label for="date" class="form-label">Date :</label>
                        <input type="date" class="form-control" id="date" name="date" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-5">
                        <label for="period" class="form-label">Période :</label>
                        <select class="form-select" id="period" name="period" required>
                            <option value="morning">Matin (8h - 12h)</option>
                            <option value="afternoon">Après-midi (14h - 17h)</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" name="add_availability" class="btn btn-primary w-100">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Section des disponibilités actuelles -->
    <section class="current-availabilities mb-5" data-aos="fade-up">
        <div class="card">
            <div class="card-header bg-light">
                <h2 class="h5 mb-0">Vos disponibilités actuelles</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($availabilities)) : ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Période</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($availabilities as $avail) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars(format_date($avail->jour)) ?></td>
                                        <td>
                                            <?php if (substr($avail->heure_debut, 0, 2) === '08') : ?>
                                                Matin (8h - 12h)
                                            <?php elseif (substr($avail->heure_debut, 0, 2) === '14') : ?>
                                                Après-midi (14h - 17h)
                                            <?php else : ?>
                                                <?= htmlspecialchars($avail->heure_debut) ?> - <?= htmlspecialchars($avail->heure_fin) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="availability_id" value="<?= $avail->id_disponibilite ?>">
                                                <button type="submit" name="delete_availability" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette disponibilité ?')">
                                                    <i class="fas fa-trash"></i> Supprimer
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <div class="alert alert-info">
                        Aucune disponibilité enregistrée. Veuillez ajouter des créneaux pour permettre aux clients de prendre rendez-vous avec vous.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Section des rendez-vous en attente -->
    <section class="pending-appointments mb-5" data-aos="fade-up">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h2 class="h5 mb-0">Rendez-vous en attente (<?= count($pending_appointments) ?>)</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($pending_appointments)) : ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Heure</th>
                                    <th>Client</th>
                                    <th>Propriété</th>
                                    <th>Motif</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_appointments as $rdv) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars(format_date($rdv->date)) ?></td>
                                        <td><?= htmlspecialchars(format_time($rdv->heure)) ?></td>
                                        <td><?= htmlspecialchars($rdv->client_prenom . ' ' . $rdv->client_nom) ?></td>
                                        <td><?= htmlspecialchars($rdv->propriete_titre) ?></td>
                                        <td><?= htmlspecialchars($rdv->motif) ?></td>
                                        <td>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="appointment_id" value="<?= $rdv->id_rdv ?>">
                                                <button type="submit" name="accept_appointment" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i> Accepter
                                                </button>
                                            </form>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="appointment_id" value="<?= $rdv->id_rdv ?>">
                                                <button type="submit" name="reject_appointment" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir refuser ce rendez-vous ?')">
                                                    <i class="fas fa-times"></i> Refuser
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <div class="alert alert-info">
                        Aucun rendez-vous en attente.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Section des rendez-vous à venir -->
    <section class="upcoming-appointments mb-5" data-aos="fade-up">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h2 class="h5 mb-0">Rendez-vous à venir (<?= count($upcoming_appointments) ?>)</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($upcoming_appointments)) : ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Heure</th>
                                    <th>Client</th>
                                    <th>Téléphone</th>
                                    <th>Propriété</th>
                                    <th>Motif</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcoming_appointments as $rdv) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars(format_date($rdv->date)) ?></td>
                                        <td><?= htmlspecialchars(format_time($rdv->heure)) ?></td>
                                        <td><?= htmlspecialchars($rdv->client_prenom . ' ' . $rdv->client_nom) ?></td>
                                        <td><?= htmlspecialchars($rdv->client_telephone) ?></td>
                                        <td><?= htmlspecialchars($rdv->propriete_titre) ?></td>
                                        <td><?= htmlspecialchars($rdv->motif) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $rdv->statut === 'confirmé' ? 'success' : 'warning' ?>">
                                                <?= ucfirst($rdv->statut) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <div class="alert alert-info">
                        Aucun rendez-vous à venir.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Section des rendez-vous passés -->
    <section class="past-appointments" data-aos="fade-up">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h2 class="h5 mb-0">Historique des rendez-vous</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($past_appointments)) : ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
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
                                        <td><?= htmlspecialchars(format_date($rdv->date)) ?></td>
                                        <td><?= htmlspecialchars(format_time($rdv->heure)) ?></td>
                                        <td><?= htmlspecialchars($rdv->client_prenom . ' ' . $rdv->client_nom) ?></td>
                                        <td><?= htmlspecialchars($rdv->propriete_titre) ?></td>
                                        <td><?= htmlspecialchars($rdv->motif) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $rdv->statut === 'confirmé' ? 'success' : ($rdv->statut === 'annulé' ? 'danger' : 'warning') ?>">
                                                <?= ucfirst($rdv->statut) ?>
                                            </span>
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
    </section>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>