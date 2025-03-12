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
require_once BASE_PATH . 'classes/Payment.php';
require_once BASE_PATH . 'classes/Client.php';

// Initialiser les classes
$payment = new Payment();
$client = new Client();

// Fonction pour récupérer toutes les transactions de services
function getAllServiceTransactions() {
    $db = new Database();
    $db->query('SELECT t.*, c.id_client, 
                u.nom as client_nom, u.prenom as client_prenom, u.email as client_email,
                p.titre as property_title, s.nom_service, s.prix
                FROM Transaction t 
                LEFT JOIN Client c ON t.id_client = c.id_client 
                LEFT JOIN Utilisateur u ON c.id_utilisateur = u.id_utilisateur 
                LEFT JOIN Propriete p ON t.id_propriete = p.id_propriete
                LEFT JOIN ServicePayant s ON t.type_service = s.type_service
                ORDER BY t.date_transaction DESC');
    
    return $db->resultSet();
}

// Fonction pour récupérer les statistiques de vente de services
function getServiceStats() {
    $db = new Database();
    
    // Total des ventes
    $db->query('SELECT SUM(montant) as total FROM Transaction');
    $total_sales = $db->single()->total ?? 0;
    
    // Nombre de transactions
    $db->query('SELECT COUNT(*) as count FROM Transaction');
    $transaction_count = $db->single()->count ?? 0;
    
    // Ventes par type de service
    $db->query('SELECT t.type_service, s.nom_service, COUNT(*) as count, SUM(t.montant) as total 
                FROM Transaction t 
                LEFT JOIN ServicePayant s ON t.type_service = s.type_service 
                GROUP BY t.type_service 
                ORDER BY total DESC');
    $sales_by_service = $db->resultSet();
    
    // Ventes par méthode de paiement
    $db->query('SELECT type_paiement, COUNT(*) as count, SUM(montant) as total 
                FROM Transaction 
                GROUP BY type_paiement 
                ORDER BY total DESC');
    $sales_by_payment = $db->resultSet();
    
    return [
        'total_sales' => $total_sales,
        'transaction_count' => $transaction_count,
        'sales_by_service' => $sales_by_service,
        'sales_by_payment' => $sales_by_payment
    ];
}

// Fonction pour récupérer les services disponibles
function getAvailableServices() {
    $db = new Database();
    $db->query('SELECT * FROM ServicePayant ORDER BY prix ASC');
    return $db->resultSet();
}

// Actions sur les transactions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Formulaire pour modifier un service
    if (isset($_POST['update_service'])) {
        $service_data = [
            'id_service' => $_POST['id_service'],
            'nom_service' => $_POST['nom_service'],
            'description' => $_POST['description'],
            'prix' => $_POST['prix'],
            'statut' => isset($_POST['statut']) ? 'actif' : 'inactif'
        ];
        
        $db = new Database();
        $db->query('UPDATE ServicePayant 
                   SET nom_service = :nom_service, description = :description, 
                   prix = :prix, statut = :statut 
                   WHERE id_service = :id_service');
        
        $db->bind(':id_service', $service_data['id_service']);
        $db->bind(':nom_service', $service_data['nom_service']);
        $db->bind(':description', $service_data['description']);
        $db->bind(':prix', $service_data['prix']);
        $db->bind(':statut', $service_data['statut']);
        
        if ($db->execute()) {
            set_alert('success', 'Le service a été mis à jour avec succès.');
        } else {
            set_alert('danger', 'Une erreur est survenue lors de la mise à jour du service.');
        }
        
        redirect('/omnes-immobilier/admin/services-transactions.php');
    }
}

// Récupérer les données nécessaires
$transactions = getAllServiceTransactions();
$stats = getServiceStats();
$services = getAvailableServices();

// Pour la modification d'un service
$service_to_edit = null;
if ($action === 'edit_service' && $id > 0) {
    foreach ($services as $service) {
        if ($service->id_service == $id) {
            $service_to_edit = $service;
            break;
        }
    }
    
    if (!$service_to_edit) {
        set_alert('danger', 'Le service demandé n\'existe pas.');
        redirect('/omnes-immobilier/admin/services-transactions.php');
    }
}

$page_title = $action === 'edit_service' ? "Modifier un service" : "Services Premium et Transactions";
include BASE_PATH . 'admin/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include BASE_PATH . 'admin/includes/sidebar.php'; ?>
        
        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $page_title; ?></h1>
            </div>
            
            <?php if ($action === 'edit_service') : ?>
                <!-- Formulaire de modification d'un service -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        Modifier un service premium
                    </div>
                    <div class="card-body">
                        <form method="post" action="/omnes-immobilier/admin/services-transactions.php">
                            <input type="hidden" name="id_service" value="<?php echo $service_to_edit->id_service; ?>">
                            
                            <div class="mb-3">
                                <label for="nom_service" class="form-label">Nom du service *</label>
                                <input type="text" class="form-control" id="nom_service" name="nom_service" required value="<?php echo htmlspecialchars($service_to_edit->nom_service); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($service_to_edit->description ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="prix" class="form-label">Prix *</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="prix" name="prix" required value="<?php echo $service_to_edit->prix; ?>" step="0.01" min="0">
                                    <span class="input-group-text">€</span>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="statut" name="statut" <?php echo $service_to_edit->statut === 'actif' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="statut">Service actif</label>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="/omnes-immobilier/admin/services-transactions.php" class="btn btn-outline-secondary">Annuler</a>
                                <button type="submit" name="update_service" class="btn btn-primary">Mettre à jour</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else : ?>
                <!-- Statistiques des services -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Total des ventes</h5>
                                <h2 class="mb-0"><?php echo format_price($stats['total_sales']); ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Nombre de transactions</h5>
                                <h2 class="mb-0"><?php echo $stats['transaction_count']; ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Panier moyen</h5>
                                <h2 class="mb-0"><?php echo $stats['transaction_count'] > 0 ? format_price($stats['total_sales'] / $stats['transaction_count']) : '0 €'; ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Services actifs</h5>
                                <h2 class="mb-0"><?php echo count(array_filter($services, function($s) { return $s->statut === 'actif'; })); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Liste des services disponibles -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        Services Premium Disponibles
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Service</th>
                                        <th>Description</th>
                                        <th>Prix</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($services as $service) : ?>
                                        <tr>
                                            <td><?php echo $service->id_service; ?></td>
                                            <td><?php echo htmlspecialchars($service->nom_service); ?></td>
                                            <td><?php echo htmlspecialchars($service->description ?? ''); ?></td>
                                            <td><?php echo format_price($service->prix); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $service->statut === 'actif' ? 'success' : 'danger'; ?>">
                                                    <?php echo $service->statut === 'actif' ? 'Actif' : 'Inactif'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="/omnes-immobilier/admin/services-transactions.php?action=edit_service&id=<?php echo $service->id_service; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i> Modifier
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Graphique des ventes par service -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                Ventes par service
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Service</th>
                                                <th>Nombre</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($stats['sales_by_service'] as $service) : ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($service->nom_service ?? $service->type_service); ?></td>
                                                    <td><?php echo $service->count; ?></td>
                                                    <td><?php echo format_price($service->total); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                Ventes par méthode de paiement
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Méthode</th>
                                                <th>Nombre</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($stats['sales_by_payment'] as $payment) : ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($payment->type_paiement); ?></td>
                                                    <td><?php echo $payment->count; ?></td>
                                                    <td><?php echo format_price($payment->total); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Liste des transactions -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        Historique des transactions
                    </div>
                    <div class="card-body">
                        <?php if (!empty($transactions)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="transactionsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Date</th>
                                            <th>Client</th>
                                            <th>Service</th>
                                            <th>Propriété</th>
                                            <th>Montant</th>
                                            <th>Paiement</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($transactions as $transaction) : ?>
                                            <tr>
                                                <td><?php echo $transaction->id_transaction; ?></td>
                                                <td><?php echo format_date(date('Y-m-d', strtotime($transaction->date_transaction))); ?></td>
                                                <td><?php echo htmlspecialchars($transaction->client_prenom . ' ' . $transaction->client_nom); ?></td>
                                                <td><?php echo htmlspecialchars($transaction->nom_service ?? $transaction->type_service); ?></td>
                                                <td><?php echo $transaction->property_title ? htmlspecialchars($transaction->property_title) : 'N/A'; ?></td>
                                                <td><?php echo format_price($transaction->montant); ?></td>
                                                <td><?php echo htmlspecialchars($transaction->type_paiement); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $transaction->statut === 'confirmé' ? 'success' : ($transaction->statut === 'annulé' ? 'danger' : ($transaction->statut === 'remboursé' ? 'warning' : 'info')); ?>">
                                                        <?php echo ucfirst($transaction->statut); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#transactionModal<?php echo $transaction->id_transaction; ?>">
                                                        <i class="fas fa-eye"></i> Détails
                                                    </button>
                                                    
                                                    <!-- Modal pour les détails de la transaction -->
                                                    <div class="modal fade" id="transactionModal<?php echo $transaction->id_transaction; ?>" tabindex="-1" aria-labelledby="transactionModalLabel<?php echo $transaction->id_transaction; ?>" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="transactionModalLabel<?php echo $transaction->id_transaction; ?>">
                                                                        Détails de la transaction #<?php echo $transaction->id_transaction; ?>
                                                                    </h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <h6>Informations générales</h6>
                                                                    <ul class="list-group mb-3">
                                                                        <li class="list-group-item"><strong>Date:</strong> <?php echo date('d/m/Y H:i', strtotime($transaction->date_transaction)); ?></li>
                                                                        <li class="list-group-item"><strong>Service:</strong> <?php echo htmlspecialchars($transaction->nom_service ?? $transaction->type_service); ?></li>
                                                                        <li class="list-group-item"><strong>Montant:</strong> <?php echo format_price($transaction->montant); ?></li>
                                                                        <li class="list-group-item"><strong>Statut:</strong> 
                                                                            <span class="badge bg-<?php echo $transaction->statut === 'confirmé' ? 'success' : ($transaction->statut === 'annulé' ? 'danger' : ($transaction->statut === 'remboursé' ? 'warning' : 'info')); ?>">
                                                                                <?php echo ucfirst($transaction->statut); ?>
                                                                            </span>
                                                                        </li>
                                                                    </ul>
                                                                    
                                                                    <h6>Informations client</h6>
                                                                    <ul class="list-group mb-3">
                                                                        <li class="list-group-item"><strong>Nom:</strong> <?php echo htmlspecialchars($transaction->client_prenom . ' ' . $transaction->client_nom); ?></li>
                                                                        <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($transaction->client_email); ?></li>
                                                                    </ul>
                                                                    
                                                                    <h6>Informations paiement</h6>
                                                                    <ul class="list-group mb-3">
                                                                        <li class="list-group-item"><strong>Méthode:</strong> <?php echo htmlspecialchars($transaction->type_paiement); ?></li>
                                                                        <li class="list-group-item"><strong>Référence:</strong> <?php echo htmlspecialchars($transaction->reference_paiement ?? 'N/A'); ?></li>
                                                                    </ul>
                                                                    
                                                                    <?php if ($transaction->property_title) : ?>
                                                                        <h6>Propriété associée</h6>
                                                                        <ul class="list-group">
                                                                            <li class="list-group-item"><strong>Titre:</strong> <?php echo htmlspecialchars($transaction->property_title); ?></li>
                                                                        </ul>
                                                                    <?php endif; ?>
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
                                Aucune transaction n'a été enregistrée pour le moment.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
    // Fonction pour exporter le tableau des transactions au format CSV
    function exportTransactions() {
        const table = document.getElementById('transactionsTable');
        if (!table) return;
        
        let csv = [];
        const rows = table.querySelectorAll('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = [], cols = rows[i].querySelectorAll('td, th');
            
            for (let j = 0; j < cols.length - 1; j++) { // Exclure la colonne Actions
                let text = cols[j].innerText.replace(/"/g, '""');
                row.push('"' + text + '"');
            }
            
            csv.push(row.join(','));
        }
        
        const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
        const downloadLink = document.createElement('a');
        
        downloadLink.href = URL.createObjectURL(csvFile);
        downloadLink.download = 'transactions.csv';
        downloadLink.style.display = 'none';
        
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const exportButton = document.querySelector('button[onclick="exportTransactions()"]');
        if (exportButton) {
            exportButton.addEventListener('click', exportTransactions);
        }
    });
</script>

<?php include BASE_PATH . 'admin/includes/footer.php'; ?>