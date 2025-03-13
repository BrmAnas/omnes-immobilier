<?php
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
require_once BASE_PATH . 'config/init.php';

// Vérifier si l'utilisateur est connecté
if (!is_logged_in()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    set_alert('warning', 'Veuillez vous connecter pour accéder à ce service.');
    redirect('/omnes-immobilier/login.php');
}

// Vérifier si l'utilisateur est un client
if (!is_user_type('client')) {
    set_alert('danger', 'Cette page est réservée aux clients.');
    redirect('/omnes-immobilier/account.php');
}

// Inclure les classes nécessaires
require_once BASE_PATH . 'classes/Client.php';
require_once BASE_PATH . 'classes/Property.php';
require_once BASE_PATH . 'classes/Payment.php';

// Initialiser les classes
$client = new Client();
$property = new Property();
$payment = new Payment();

// Récupérer les infos du client
$client_info = $client->getClientByUserId($_SESSION['user_id']);

// Vérifier si l'ID de la propriété est spécifié
$property_id = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$property_id) {
    set_alert('danger', 'Aucune propriété spécifiée.');
    redirect('/omnes-immobilier/account.php');
}

// Récupérer les détails de la propriété
$property_info = $property->getPropertyById($property_id);
if (!$property_info) {
    set_alert('danger', 'Propriété non trouvée.');
    redirect('/omnes-immobilier/properties.php');
}

// Vérifier si la visite virtuelle est déjà achetée
$db = new Database();
$db->query('SELECT * FROM Transaction 
           WHERE id_client = :id_client 
           AND type_service = "visite_virtuelle" 
           AND (id_propriete = :id_propriete OR (id_propriete IS NULL AND :id_propriete IS NOT NULL))
           AND statut = "confirmé"');
$db->bind(':id_client', $client_info->id_client);
$db->bind(':id_propriete', $property_id);
$existing_purchase = $db->single();

// Si l'utilisateur veut acheter la visite virtuelle
if (isset($_GET['purchase']) && $_GET['purchase'] == 'true' && !$existing_purchase) {
    // Récupérer le prix du service de visite virtuelle
    $service_price = $payment->getServicePrice('visite_virtuelle');
    
    if ($service_price !== false) {
        // Stocker les informations dans la session pour rediriger vers la page de paiement
        $_SESSION['payment_service'] = 'visite_virtuelle';
        $_SESSION['payment_amount'] = $service_price;
        $_SESSION['payment_property_id'] = $property_id; // Ajouter l'ID de la propriété
        
        // Rediriger vers la page de paiement
        redirect('/omnes-immobilier/payment.php');
    } else {
        set_alert('danger', 'Le service de visite virtuelle n\'est pas disponible pour le moment.');
        redirect('/omnes-immobilier/property-detail.php?id=' . $property_id);
    }
}

$page_title = "Visite Virtuelle - " . $property_info->titre;
include BASE_PATH . 'includes/header.php';
?>

<div class="container mt-4">
    <h1 class="section-title mb-4" data-aos="fade-up">Visite Virtuelle</h1>
    
    <!-- Fil d'Ariane -->
    <nav aria-label="breadcrumb" data-aos="fade-up">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/omnes-immobilier/index.php">Accueil</a></li>
            <li class="breadcrumb-item"><a href="/omnes-immobilier/properties.php">Propriétés</a></li>
            <li class="breadcrumb-item"><a href="/omnes-immobilier/property-detail.php?id=<?php echo $property_id; ?>"><?php echo htmlspecialchars($property_info->titre); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Visite Virtuelle</li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-md-8">
            <!-- Affichage de la visite virtuelle ou proposition d'achat -->
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Visite Virtuelle - <?php echo htmlspecialchars($property_info->titre); ?></h5>
                </div>
                <div class="card-body">
                    <?php if ($existing_purchase) : ?>
                        <!-- Si le client a déjà acheté la visite virtuelle -->
                        <div class="virtual-tour-container" style="height: 500px; position: relative;">
                            <!-- Ici, on intégrerait normalement une vraie visite virtuelle 3D via un service comme Matterport -->
                            <!-- Pour ce prototype, nous utilisons une simulation simple -->
                            <div class="ratio ratio-16x9">
                                <iframe src="https://www.youtube.com/embed/nqn7RQX-gW8" title="Visite virtuelle" allowfullscreen></iframe>
                            </div>
                            
                            <div class="mt-4">
                                <h5>Instructions d'utilisation :</h5>
                                <ul>
                                    <li>Utilisez la souris pour regarder autour de vous (cliquer et glisser)</li>
                                    <li>Cliquez sur les points blancs pour vous déplacer dans la propriété</li>
                                    <li>Utilisez la molette de la souris pour zoomer</li>
                                    <li>Cliquez sur les icônes d'information pour des détails supplémentaires</li>
                                    <li>Utilisez le menu en bas pour accéder directement à différentes pièces</li>
                                </ul>
                            </div>
                            
                            <div class="mt-4">
                                <h5>Caractéristiques de la propriété :</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-check-circle text-success me-2"></i> Surface : <?php echo $property_info->surface; ?> m²</li>
                                            <li><i class="fas fa-check-circle text-success me-2"></i> Chambres : <?php echo $property_info->nb_chambres; ?></li>
                                            <li><i class="fas fa-check-circle text-success me-2"></i> Salles de bain : <?php echo $property_info->nb_salles_bain; ?></li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-check-circle text-success me-2"></i> Étage : <?php echo isset($property_info->etage) ? $property_info->etage : 'N/A'; ?></li>
                                            <li><i class="fas fa-check-circle text-success me-2"></i> Balcon : <?php echo $property_info->balcon ? 'Oui' : 'Non'; ?></li>
                                            <li><i class="fas fa-check-circle text-success me-2"></i> Parking : <?php echo $property_info->parking ? 'Oui' : 'Non'; ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                        <!-- Si le client n'a pas encore acheté la visite virtuelle -->
                        <div class="text-center py-4">
                            <img src="/omnes-immobilier/assets/img/services/virtual-tour.jpg" class="img-fluid rounded mb-4" style="max-height: 300px;" alt="Visite Virtuelle" onerror="this.src='https://via.placeholder.com/600x300?text=Visite+Virtuelle'">
                            
                            <h4 class="mb-3">Découvrez cette propriété en 3D !</h4>
                            <p class="lead mb-4">Explorez chaque recoin de cette propriété depuis le confort de votre canapé grâce à notre technologie de visite virtuelle immersive.</p>
                            
                            <div class="alert alert-info mb-4">
                                <h5><i class="fas fa-info-circle me-2"></i> Pourquoi acheter une visite virtuelle ?</h5>
                                <ul class="text-start">
                                    <li>Explorez la propriété à votre rythme, quand vous le souhaitez</li>
                                    <li>Visualisez chaque pièce en détail avec une vue à 360°</li>
                                    <li>Prenez des mesures précises directement dans l'application</li>
                                    <li>Partagez la visite avec votre famille ou vos amis</li>
                                    <li>Gagnez du temps en évitant des visites physiques inutiles</li>
                                </ul>
                            </div>
                            
                            <div class="pricing-box mb-4 p-4 border rounded bg-light">
                                <h5>Visite Virtuelle 3D</h5>
                                <h3 class="price my-3">39,90 €</h3>
                                <p class="text-muted mb-3">Accès illimité pendant 30 jours</p>
                                
                                <div class="d-grid gap-2">
                                    <a href="/omnes-immobilier/virtual-tour.php?id=<?php echo $property_id; ?>&purchase=true" class="btn btn-primary btn-lg">
                                        <i class="fas fa-shopping-cart me-2"></i> Acheter maintenant
                                    </a>
                                </div>
                            </div>
                            
                            <p class="small text-muted">En achetant ce service, vous aurez accès à la visite virtuelle complète de cette propriété pendant 30 jours. Vous pourrez y accéder autant de fois que vous le souhaitez depuis n'importe quel appareil.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Informations sur la propriété -->
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informations sur la propriété</h5>
                </div>
                <div class="card-body">
                    <h5><?php echo htmlspecialchars($property_info->titre); ?></h5>
                    <p><i class="fas fa-map-marker-alt me-2"></i> <?php echo htmlspecialchars($property_info->adresse . ', ' . $property_info->code_postal . ' ' . $property_info->ville); ?></p>
                    
                    <?php if ($property_info->type_propriete !== 'location') : ?>
                        <p class="property-price"><?php echo format_price($property_info->prix); ?></p>
                    <?php else : ?>
                        <p class="property-price"><?php echo format_price($property_info->prix); ?>/mois</p>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="d-grid gap-2">
                        <a href="/omnes-immobilier/property-detail.php?id=<?php echo $property_id; ?>" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i> Retour aux détails
                        </a>
                        
                        <a href="/omnes-immobilier/appointment.php?agent=<?php echo $property_info->id_agent; ?>&property=<?php echo $property_id; ?>" class="btn btn-primary">
                            <i class="fas fa-calendar-alt me-2"></i> Prendre rendez-vous
                        </a>
                    </div>
                </div>
            </div>
            

        </div>
    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>