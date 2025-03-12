<?php
// account-services.php - Page des services premium dans l'espace client

define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
require_once BASE_PATH . 'config/init.php';

// Vérifier si l'utilisateur est connecté
if (!is_logged_in()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    set_alert('warning', 'Veuillez vous connecter pour accéder à vos services.');
    redirect('/omnes-immobilier/login.php');
}

// Vérifier si l'utilisateur est un client
if (!is_user_type('client')) {
    set_alert('danger', 'Cette page est réservée aux clients.');
    redirect('/omnes-immobilier/account.php');
}

// Inclure les classes nécessaires
require_once BASE_PATH . 'classes/Client.php';
require_once BASE_PATH . 'classes/Payment.php';

// Initialiser les classes
$client = new Client();
$payment = new Payment();

// Récupérer les infos du client
$client_info = $client->getClientByUserId($_SESSION['user_id']);

// Récupérer les services payants disponibles
$db = new Database();
$db->query('SELECT * FROM ServicePayant WHERE statut = "actif" ORDER BY prix ASC');
$services = $db->resultSet();

// Récupérer l'historique des services achetés par le client
$db->query('SELECT t.*, s.nom_service 
            FROM Transaction t 
            LEFT JOIN ServicePayant s ON t.type_service = s.type_service 
            WHERE t.id_client = :id_client AND t.statut = "confirmé" 
            ORDER BY t.date_transaction DESC');
$db->bind(':id_client', $client_info->id_client);
$purchased_services = $db->resultSet();

// Traitement des demandes d'achat de service
if (isset($_GET['purchase']) && !empty($_GET['service'])) {
    $service_type = $_GET['service'];
    
    // Vérifier si le service existe et récupérer son prix
    $service_price = $payment->getServicePrice($service_type);
    
    if ($service_price !== false) {
        // Stocker les informations dans la session pour rediriger vers la page de paiement
        $_SESSION['payment_service'] = $service_type;
        $_SESSION['payment_amount'] = $service_price;
        
        // Rediriger vers la page de paiement
        redirect('/omnes-immobilier/payment.php');
    } else {
        set_alert('danger', 'Le service demandé n\'existe pas ou n\'est pas disponible.');
    }
}

$page_title = "Mes Services Premium";
include BASE_PATH . 'includes/header.php';
?>

<div class="container mt-4">
    <h1 class="section-title mb-4" data-aos="fade-up">Services Premium</h1>
    
    <div class="row">
        <!-- Menu latéral -->
        <div class="col-md-3">
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    Menu
                </div>
                <div class="list-group list-group-flush">
                    <a href="/omnes-immobilier/account.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-user"></i> Mon Profil
                    </a>
                    <a href="/omnes-immobilier/my-appointments.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-calendar-alt"></i> Mes Rendez-vous
                    </a>
                    <a href="/omnes-immobilier/account-services.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-star"></i> Services Premium
                    </a>
                    <a href="/omnes-immobilier/favorites.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-heart"></i> Mes Favoris
                    </a>
                    <a href="/omnes-immobilier/logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Contenu principal -->
        <div class="col-md-9">
            <!-- Service 1: Dossier Acheteur Premium -->
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Dossier Acheteur Premium</h5>
                    <span class="badge bg-light text-dark">99,00 €</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="/omnes-immobilier/assets/img/services/premium-buyer.jpg" class="img-fluid rounded" alt="Dossier Acheteur Premium" onerror="this.src='https://via.placeholder.com/300x200?text=Dossier+Premium'">
                        </div>
                        <div class="col-md-8">
                            <h5>Optimisez vos chances de succès</h5>
                            <p>Gagnez un avantage concurrentiel avec notre service de dossier acheteur premium. Nous préparons un dossier complet qui met en valeur votre profil auprès des vendeurs.</p>
                            
                            <h6 class="mt-3">Ce service comprend :</h6>
                            <ul>
                                <li>Attestation de solvabilité certifiée par nos experts</li>
                                <li>Accompagnement personnalisé pour obtenir un accord de principe bancaire</li>
                                <li>Mise en forme professionnelle de votre dossier</li>
                                <li>Lettre de recommandation d'Omnes Immobilier</li>
                                <li>Priorité sur les nouvelles annonces (24h d'avance)</li>
                            </ul>
                            
                            <div class="d-grid gap-2 mt-3">
                                <a href="/omnes-immobilier/account-services.php?purchase=true&service=dossier_acheteur_premium" class="btn btn-primary">
                                    <i class="fas fa-shopping-cart me-2"></i> Acheter ce service
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Service 2: Photos Professionnelles -->
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Photos Professionnelles</h5>
                    <span class="badge bg-light text-dark">149,00 €</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="/omnes-immobilier/assets/img/services/pro-photos.jpg" class="img-fluid rounded" alt="Photos Professionnelles" onerror="this.src='https://via.placeholder.com/300x200?text=Photos+Pro'">
                        </div>
                        <div class="col-md-8">
                            <h5>Sublimez votre bien immobilier</h5>
                            <p>Les photos professionnelles peuvent augmenter l'intérêt pour votre bien de plus de 60% ! Notre photographe immobilier certifié mettra en valeur chaque espace de votre propriété.</p>
                            
                            <h6 class="mt-3">Ce service comprend :</h6>
                            <ul>
                                <li>Séance photo complète de votre bien (jusqu'à 15 photos)</li>
                                <li>Retouches professionnelles et optimisation des images</li>
                                <li>Photos en haute résolution pour tous supports</li>
                                <li>Mise en valeur des atouts de votre propriété</li>
                                <li>Livraison sous 48h après la séance</li>
                            </ul>
                            
                            <div class="d-grid gap-2 mt-3">
                                <a href="/omnes-immobilier/account-services.php?purchase=true&service=photos_professionnelles" class="btn btn-primary">
                                    <i class="fas fa-shopping-cart me-2"></i> Acheter ce service
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Historique des services achetés -->
            <div class="card" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Mes services achetés</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($purchased_services)) : ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Service</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($purchased_services as $service) : ?>
                                        <tr>
                                            <td><?php echo format_date(date('Y-m-d', strtotime($service->date_transaction))); ?></td>
                                            <td><?php echo htmlspecialchars($service->nom_service ?? $service->type_service); ?></td>
                                            <td><?php echo format_price($service->montant); ?></td>
                                            <td>
                                                <span class="badge bg-success">Payé</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-info">
                            Vous n'avez pas encore acheté de services premium.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>