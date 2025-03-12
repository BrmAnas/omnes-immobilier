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

// Inclure les classes nécessaires pour les statistiques
require_once BASE_PATH . 'classes/Property.php';
require_once BASE_PATH . 'classes/User.php';
require_once BASE_PATH . 'classes/Client.php';
require_once BASE_PATH . 'classes/Agent.php';
require_once BASE_PATH . 'classes/Appointment.php';

// Créer les instances des classes
$property = new Property();
$user = new User();
$client = new Client();
$agent = new Agent();
$appointment = new Appointment();

// Récupérer des statistiques basiques
$properties_count = count($property->getAllProperties());
$agents_count = count($agent->getAllAgents());
$clients_count = 0; // Il faudrait créer une méthode getAllClients() pour obtenir ce chiffre

// Récupérer les dernières propriétés ajoutées
$recent_properties = $property->getAllProperties();
if (count($recent_properties) > 5) {
    $recent_properties = array_slice($recent_properties, 0, 5);
}

// Récupérer les derniers agents
$recent_agents = $agent->getAllAgents();
if (count($recent_agents) > 5) {
    $recent_agents = array_slice($recent_agents, 0, 5);
}

$page_title = "Administration - Tableau de bord";
include BASE_PATH . 'admin/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include BASE_PATH . 'admin/includes/sidebar.php'; ?>
        
        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Tableau de bord</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Exporter</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">Imprimer</button>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Propriétés</h6>
                                    <h2 class="mb-0"><?php echo $properties_count; ?></h2>
                                </div>
                                <i class="fas fa-home fa-3x"></i>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <a href="/omnes-immobilier/admin/properties.php" class="text-white text-decoration-none">Voir détails</a>
                            <a href="/omnes-immobilier/admin/properties.php?action=add" class="text-white text-decoration-none">Ajouter</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Agents</h6>
                                    <h2 class="mb-0"><?php echo $agents_count; ?></h2>
                                </div>
                                <i class="fas fa-user-tie fa-3x"></i>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <a href="/omnes-immobilier/admin/agents.php" class="text-white text-decoration-none">Voir détails</a>
                            <a href="/omnes-immobilier/admin/agents.php?action=add" class="text-white text-decoration-none">Ajouter</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card text-white bg-info mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Clients</h6>
                                    <h2 class="mb-0"><?php echo $clients_count; ?></h2>
                                </div>
                                <i class="fas fa-users fa-3x"></i>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <a href="/omnes-immobilier/admin/clients.php" class="text-white text-decoration-none">Voir détails</a>
                            <span class="text-white">Membre d'Omnes Education</span>
                        </div>
                    </div>
                </div>
            </div>
<div class="col-md-4">
    <div class="card text-white bg-info mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title">Services Premium</h6>
                    <h2 class="mb-0"><?php echo $services_count ?? 0; ?> vendus</h2>
                </div>
                <i class="fas fa-star fa-3x"></i>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="/omnes-immobilier/admin/services.php" class="text-white text-decoration-none">Voir détails</a>
            <span class="text-white"><?php echo format_price($services_revenue ?? 0); ?></span>
        </div>
    </div>
</div>

            <div class="row">
                <!-- Recent Properties -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-home me-1"></i>
                            Propriétés récentes
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recent_properties)) : ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Titre</th>
                                                <th>Type</th>
                                                <th>Prix</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_properties as $prop) : ?>
                                                <tr>
                                                    <td><?php echo $prop->id_propriete; ?></td>
                                                    <td><?php echo $prop->titre; ?></td>
                                                    <td><?php echo ucfirst($prop->type_propriete); ?></td>
                                                    <td><?php echo format_price($prop->prix); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $prop->statut == 'disponible' ? 'success' : ($prop->statut == 'vendu' ? 'danger' : 'warning'); ?>">
                                                            <?php echo ucfirst($prop->statut); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else : ?>
                                <div class="alert alert-info">
                                    Aucune propriété disponible pour le moment.
                                </div>
                            <?php endif; ?>
                            <div class="text-end mt-3">
                                <a href="/omnes-immobilier/admin/properties.php" class="btn btn-primary btn-sm">Voir toutes les propriétés</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Agents -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-user-tie me-1"></i>
                            Agents immobiliers
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recent_agents)) : ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nom</th>
                                                <th>Email</th>
                                                <th>Spécialité</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_agents as $agent_item) : ?>
                                                <tr>
                                                    <td><?php echo $agent_item->id_agent; ?></td>
                                                    <td><?php echo $agent_item->prenom . ' ' . $agent_item->nom; ?></td>
                                                    <td><?php echo $agent_item->email; ?></td>
                                                    <td><?php echo $agent_item->specialite; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else : ?>
                                <div class="alert alert-info">
                                    Aucun agent disponible pour le moment.
                                </div>
                            <?php endif; ?>
                            <div class="text-end mt-3">
                                <a href="/omnes-immobilier/admin/agents.php" class="btn btn-primary btn-sm">Voir tous les agents</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include BASE_PATH . 'admin/includes/footer.php'; ?>