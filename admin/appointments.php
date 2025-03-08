<?php
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
require_once BASE_PATH . 'config/init.php';

// Vérifier si l'utilisateur est connecté
if (!is_logged_in()) {
    set_alert('warning', 'Veuillez vous connecter pour accéder à l\'administration.');
    redirect('/omnes-immobilier/login.php');
}

// Vérifier si l'utilisateur est un administrateur
if (!is_user_type('admin')) {
    set_alert('danger', 'Vous n\'êtes pas autorisé à accéder à cette page.');
    redirect('/omnes-immobilier/index.php');
}

// Inclure les classes nécessaires
require_once BASE_PATH . 'classes/Appointment.php';
require_once BASE_PATH . 'classes/Agent.php';
require_once BASE_PATH . 'classes/Client.php';

// Initialiser les classes
$appointment = new Appointment();
$agent = new Agent();
$client = new Client();

// Créer une fonction temporaire pour récupérer tous les rendez-vous
function getAllAppointments() {
    $db = new Database();
    $db->query('SELECT r.*, 
                p.titre as property_title, 
                c.id_client, ca.nom as client_nom, ca.prenom as client_prenom, 
                a.id_agent, aa.nom as agent_nom, aa.prenom as agent_prenom 
                FROM Rendez_Vous r 
                INNER JOIN Propriete p ON r.id_propriete = p.id_propriete 
                INNER JOIN Client c ON r.id_client = c.id_client 
                INNER JOIN Utilisateur ca ON c.id_utilisateur = ca.id_utilisateur 
                INNER JOIN Agent_Immobilier a ON r.id_agent = a.id_agent 
                INNER JOIN Utilisateur aa ON a.id_utilisateur = aa.id_utilisateur 
                ORDER BY r.date DESC, r.heure DESC');
    return $db->resultSet();
}

// Actions à traiter
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id_rdv = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'confirm') {
        if ($appointment->updateStatus($id_rdv, 'confirmé')) {
            set_alert('success', 'Le rendez-vous a été confirmé avec succès.');
        } else {
            set_alert('danger', 'Une erreur est survenue lors de la confirmation du rendez-vous.');
        }
    } elseif ($action == 'cancel') {
        if ($appointment->updateStatus($id_rdv, 'annulé')) {
            set_alert('success', 'Le rendez-vous a été annulé avec succès.');
        } else {
            set_alert('danger', 'Une erreur est survenue lors de l\'annulation du rendez-vous.');
        }
    }
    
    redirect('/omnes-immobilier/admin/appointments.php');
}

// Récupérer tous les rendez-vous
$all_appointments = getAllAppointments();

// Filtrer les rendez-vous
$today = [];
$upcoming = [];
$past = [];

foreach ($all_appointments as $rdv) {
    $date = $rdv->date;
    $today_date = date('Y-m-d');
    
    if ($date == $today_date) {
        $today[] = $rdv;
    } elseif ($date > $today_date) {
        $upcoming[] = $rdv;
    } else {
        $past[] = $rdv;
    }
}

$page_title = "Gestion des rendez-vous";
include BASE_PATH . 'admin/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include BASE_PATH . 'admin/includes/sidebar.php'; ?>
        
        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gestion des rendez-vous</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportTable()">
                            <i class="fas fa-download"></i> Exporter
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Onglets pour les différentes catégories de rendez-vous -->
            <ul class="nav nav-tabs mb-4" id="appointmentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="today-tab" data-bs-toggle="tab" data-bs-target="#today" type="button" role="tab" aria-controls="today" aria-selected="true">
                        Aujourd'hui (<?php echo count($today); ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab" aria-controls="upcoming" aria-selected="false">
                        À venir (<?php echo count($upcoming); ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past" type="button" role="tab" aria-controls="past" aria-selected="false">
                        Passés (<?php echo count($past); ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="false">
                        Tous (<?php echo count($all_appointments); ?>)
                    </button>
                </li>
            </ul>
            
            <!-- Contenu des onglets -->
            <div class="tab-content" id="appointmentTabsContent">
                <!-- Rendez-vous d'aujourd'hui -->
                <div class="tab-pane fade show active" id="today" role="tabpanel" aria-labelledby="today-tab">
                    <?php if (!empty($today)) : ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Heure</th>
                                        <th>Client</th>
                                        <th>Agent</th>
                                        <th>Propriété</th>
                                        <th>Motif</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($today as $rdv) : ?>
                                        <tr>
                                            <td><?php echo format_time($rdv->heure); ?></td>
                                            <td><?php echo htmlspecialchars($rdv->client_prenom . ' ' . $rdv->client_nom); ?></td>
                                            <td><?php echo htmlspecialchars($rdv->agent_prenom . ' ' . $rdv->agent_nom); ?></td>
                                            <td><?php echo htmlspecialchars($rdv->property_title); ?></td>
                                            <td><?php echo htmlspecialchars($rdv->motif); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $rdv->statut == 'confirmé' ? 'success' : ($rdv->statut == 'annulé' ? 'danger' : 'warning'); ?>">
                                                    <?php echo ucfirst($rdv->statut); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($rdv->statut == 'en attente') : ?>
                                                    <div class="btn-group">
                                                        <a href="/omnes-immobilier/admin/appointments.php?action=confirm&id=<?php echo $rdv->id_rdv; ?>" class="btn btn-sm btn-success" title="Confirmer">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                        <a href="/omnes-immobilier/admin/appointments.php?action=cancel&id=<?php echo $rdv->id_rdv; ?>" class="btn btn-sm btn-danger" title="Annuler" onclick="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?')">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                    </div>
                                                <?php elseif ($rdv->statut == 'confirmé') : ?>
                                                    <a href="/omnes-immobilier/admin/appointments.php?action=cancel&id=<?php echo $rdv->id_rdv; ?>" class="btn btn-sm btn-danger" title="Annuler" onclick="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?')">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                <?php else : ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-info">
                            Aucun rendez-vous prévu aujourd'hui.
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Rendez-vous à venir -->
                <div class="tab-pane fade" id="upcoming" role="tabpanel" aria-labelledby="upcoming-tab">
                    <?php if (!empty($upcoming)) : ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Heure</th>
                                        <th>Client</th>
                                        <th>Agent</th>
                                        <th>Propriété</th>
                                        <th>Motif</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcoming as $rdv) : ?>
                                        <tr>
                                            <td><?php echo format_date($rdv->date); ?></td>
                                            <td><?php echo format_time($rdv->heure); ?></td>
                                            <td><?php echo htmlspecialchars($rdv->client_prenom . ' ' . $rdv->client_nom); ?></td>
                                            <td><?php echo htmlspecialchars($rdv->agent_prenom . ' ' . $rdv->agent_nom); ?></td>
                                            <td><?php echo htmlspecialchars($rdv->property_title); ?></td>
                                            <td><?php echo htmlspecialchars($rdv->motif); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $rdv->statut == 'confirmé' ? 'success' : ($rdv->statut == 'annulé' ? 'danger' : 'warning'); ?>">
                                                    <?php echo ucfirst($rdv->statut); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($rdv->statut == 'en attente') : ?>
                                                    <div class="btn-group">
                                                        <a href="/omnes-immobilier/admin/appointments.php?action=confirm&id=<?php echo $rdv->id_rdv; ?>" class="btn btn-sm btn-success" title="Confirmer">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                        <a href="/omnes-immobilier/admin/appointments.php?action=cancel&id=<?php echo $rdv->id_rdv; ?>" class="btn btn-sm btn-danger" title="Annuler" onclick="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?')">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                    </div>
                                                <?php elseif ($rdv->statut == 'confirmé') : ?>
                                                    <a href="/omnes-immobilier/admin/appointments.php?action=cancel&id=<?php echo $rdv->id_rdv; ?>" class="btn btn-sm btn-danger" title="Annuler" onclick="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?')">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                <?php else : ?>
                                                    -
                                                <?php endif; ?>
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
                
                <!-- Rendez-vous passés -->
                <div class="tab-pane fade" id="past" role="tabpanel" aria-labelledby="past-tab">
                    <?php if (!empty($past)) : ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Heure</th>
                                        <th>Client</th>
                                        <th>Agent</th>
                                        <th>Propriété</th>
                                        <th>Motif</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($past as $rdv) : ?>
                                        <tr>
                                            <td><?php echo format_date($rdv->date); ?></td>
                                            <td><?php echo format_time($rdv->heure); ?></td>
                                            <td><?php echo htmlspecialchars($rdv->client_prenom . ' ' . $rdv->client_nom); ?></td>
                                            <td><?php echo htmlspecialchars($rdv->agent_prenom . ' ' . $rdv->agent_nom); ?></td>
                                            <td><?php echo htmlspecialchars($rdv->property_title); ?></td>
                                            <td><?php echo htmlspecialchars($rdv->motif); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $rdv->statut == 'confirmé' ? 'success' : ($rdv->statut == 'annulé' ? 'danger' : 'warning'); ?>">
                                                    <?php echo ucfirst($rdv->statut); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-info">
                            Aucun rendez-vous passé.
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Tous les rendez-vous -->
                <div class="tab-pane fade" id="all" role="tabpanel" aria-labelledby="all-tab">
                    <?php if (!empty($all_appointments)) : ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="appointmentsTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Heure</th>
                                        <th>Client</th>
                                        <th>Agent</th>
                                        <th>Propriété</th>
                                        <th>Motif</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_appointments as $rdv) : ?>
                                        <tr>
                                            <td><?php echo format_date($rdv->date); ?></td>
                                            <td><?php echo format_time($rdv->heure); ?></td>
                                            <td><?php echo htmlspecialchars($rdv->client_prenom . ' ' . $rdv->client_nom); ?></td>
                                            <td><?php echo htmlspecialchars($rdv->agent_prenom . ' ' . $rdv->agent_nom); ?></td>
                                            <td><?php echo htmlspecialchars($rdv->property_title); ?></td>
                                            <td><?php echo htmlspecialchars($rdv->motif); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $rdv->statut == 'confirmé' ? 'success' : ($rdv->statut == 'annulé' ? 'danger' : 'warning'); ?>">
                                                    <?php echo ucfirst($rdv->statut); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($rdv->statut == 'en attente') : ?>
                                                    <div class="btn-group">
                                                        <a href="/omnes-immobilier/admin/appointments.php?action=confirm&id=<?php echo $rdv->id_rdv; ?>" class="btn btn-sm btn-success" title="Confirmer">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                        <a href="/omnes-immobilier/admin/appointments.php?action=cancel&id=<?php echo $rdv->id_rdv; ?>" class="btn btn-sm btn-danger" title="Annuler" onclick="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?')">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                    </div>
                                                <?php elseif ($rdv->statut == 'confirmé' && $rdv->date >= date('Y-m-d')) : ?>
                                                    <a href="/omnes-immobilier/admin/appointments.php?action=cancel&id=<?php echo $rdv->id_rdv; ?>" class="btn btn-sm btn-danger" title="Annuler" onclick="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?')">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                <?php else : ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-info">
                            Aucun rendez-vous disponible.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function exportTable() {
    // Fonction simple pour exporter le tableau en CSV
    let csv = [];
    const rows = document.querySelectorAll('#appointmentsTable tr');
    
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length - 1; j++) { // -1 pour exclure la colonne Actions
            let text = cols[j].innerText.replace(/"/g, '""'); // Échapper les guillemets
            row.push('"' + text + '"');
        }
        csv.push(row.join(','));
    }
    
    // Télécharger le CSV
    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.href = URL.createObjectURL(csvFile);
    downloadLink.download = 'rendez-vous.csv';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>

<?php include BASE_PATH . 'admin/includes/footer.php'; ?>