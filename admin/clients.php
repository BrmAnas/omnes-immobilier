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
require_once BASE_PATH . 'classes/User.php';

// Initialiser les classes
$user = new User();

// Créer une fonction temporaire pour récupérer les clients jusqu'à ce que nous ayons une méthode dédiée
function getAllClients() {
    $db = new Database();
    $db->query('SELECT c.*, u.id_utilisateur, u.nom, u.prenom, u.email, u.telephone 
                FROM Client c 
                INNER JOIN Utilisateur u ON c.id_utilisateur = u.id_utilisateur 
                WHERE u.type_utilisateur = "client" 
                ORDER BY u.nom, u.prenom');
    return $db->resultSet();
}

// Récupérer tous les clients
$clients = getAllClients();

$page_title = "Gestion des clients";
include BASE_PATH . 'admin/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include BASE_PATH . 'admin/includes/sidebar.php'; ?>
        
        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gestion des clients</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportTable()">
                            <i class="fas fa-download"></i> Exporter
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Liste des clients -->
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($clients)) : ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="clientsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Téléphone</th>
                                        <th>Ville</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clients as $client) : ?>
                                        <tr>
                                            <td><?php echo $client->id_client; ?></td>
                                            <td><?php echo htmlspecialchars($client->prenom . ' ' . $client->nom); ?></td>
                                            <td><?php echo htmlspecialchars($client->email); ?></td>
                                            <td><?php echo htmlspecialchars($client->telephone); ?></td>
                                            <td><?php echo htmlspecialchars($client->ville); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#clientModal<?php echo $client->id_client; ?>" title="Voir détails">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                                
                                                <!-- Modal pour les détails du client -->
                                                <div class="modal fade" id="clientModal<?php echo $client->id_client; ?>" tabindex="-1" aria-labelledby="clientModalLabel<?php echo $client->id_client; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="clientModalLabel<?php echo $client->id_client; ?>">
                                                                    <?php echo htmlspecialchars($client->prenom . ' ' . $client->nom); ?>
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p><strong>ID Client:</strong> <?php echo $client->id_client; ?></p>
                                                                <p><strong>ID Utilisateur:</strong> <?php echo $client->id_utilisateur; ?></p>
                                                                <p><strong>Email:</strong> <?php echo htmlspecialchars($client->email); ?></p>
                                                                <p><strong>Téléphone:</strong> <?php echo htmlspecialchars($client->telephone); ?></p>
                                                                <p><strong>Adresse:</strong> <?php echo htmlspecialchars($client->adresse); ?></p>
                                                                <p><strong>Ville:</strong> <?php echo htmlspecialchars($client->ville); ?></p>
                                                                <p><strong>Code postal:</strong> <?php echo htmlspecialchars($client->code_postal); ?></p>
                                                                <p><strong>Pays:</strong> <?php echo htmlspecialchars($client->pays); ?></p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-info">
                            Aucun client disponible pour le moment.
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
    const rows = document.querySelectorAll('table tr');
    
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
    downloadLink.download = 'clients.csv';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>

<?php include BASE_PATH . 'admin/includes/footer.php'; ?>