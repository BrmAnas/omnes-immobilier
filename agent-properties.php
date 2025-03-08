<?php
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
require_once BASE_PATH . 'config/init.php';

// Vérifier si l'utilisateur est connecté
if (!is_logged_in()) {
    set_alert('warning', 'Veuillez vous connecter pour accéder à vos propriétés.');
    redirect('/omnes-immobilier/login.php');
}

// Vérifier si l'utilisateur est un agent
if (!is_user_type('agent')) {
    set_alert('danger', 'Vous n\'êtes pas autorisé à accéder à cette page.');
    redirect('/omnes-immobilier/index.php');
}

// Inclure les classes nécessaires
require_once BASE_PATH . 'classes/Agent.php';
require_once BASE_PATH . 'classes/Property.php';

// Initialiser les classes
$agent = new Agent();
$property = new Property();

// Récupérer les infos de l'agent
$agent_info = $agent->getAgentByUserId($_SESSION['user_id']);
if (!$agent_info) {
    set_alert('danger', 'Une erreur est survenue lors de la récupération de vos informations.');
    redirect('/omnes-immobilier/index.php');
}

// Récupérer les propriétés gérées par l'agent
$properties = $property->getPropertiesByAgent($agent_info->id_agent);

$page_title = "Mes propriétés";
include BASE_PATH . 'includes/header.php';
?>

<div class="container mt-4">
    <h1 class="section-title mb-4" data-aos="fade-up">Mes propriétés</h1>
    
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
                    <a href="/omnes-immobilier/agent-appointments.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-calendar-alt"></i> Mes Rendez-vous
                    </a>
                    <a href="/omnes-immobilier/agent-properties.php" class="list-group-item list-group-item-action active">
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
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Mes propriétés</h5>
                    <div class="text-end">
                        <span class="badge bg-light text-dark">
                            Total: <?php echo count($properties); ?> propriété(s)
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($properties)) : ?>
                        <div class="row">
                            <?php foreach ($properties as $index => $prop) : ?>
                                <div class="col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index % 2 * 100; ?>">
                                    <div class="card h-100 property-card">
                                        <?php 
                                        // Récupérer la première image de la propriété
                                        $medias = $property->getMedia($prop->id_propriete);
                                        $image_path = !empty($medias) && isset($medias[0]->url_path) ? $medias[0]->url_path : '/omnes-immobilier/assets/img/properties/default.jpg';
                                        ?>
                                        
                                        <img src="<?php echo $image_path; ?>" class="card-img-top" alt="<?php echo $prop->titre; ?>" style="height: 200px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/400x200?text=Propriété'">
                                        
                                        <?php if ($prop->type_propriete) : ?>
                                            <div class="property-tag"><?php echo ucfirst($prop->type_propriete); ?></div>
                                        <?php endif; ?>
                                        
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo $prop->titre; ?></h5>
                                            <p class="property-location">
                                                <i class="fas fa-map-marker-alt"></i> <?php echo $prop->ville; ?>
                                            </p>
                                            <?php if ($prop->type_propriete !== 'location') : ?>
                                                <p class="property-price"><?php echo format_price($prop->prix); ?></p>
                                            <?php else : ?>
                                                <p class="property-price"><?php echo format_price($prop->prix); ?>/mois</p>
                                            <?php endif; ?>
                                            
                                            <p class="mb-2">
                                                <span class="badge bg-<?php echo $prop->statut == 'disponible' ? 'success' : ($prop->statut == 'vendu' ? 'danger' : 'warning'); ?>">
                                                    <?php echo ucfirst($prop->statut); ?>
                                                </span>
                                            </p>
                                            
                                            <div class="property-features mb-3">
                                                <div class="property-feature">
                                                    <i class="fas fa-expand"></i>
                                                    <span><?php echo $prop->surface; ?> m²</span>
                                                </div>
                                                <div class="property-feature">
                                                    <i class="fas fa-door-open"></i>
                                                    <span><?php echo $prop->nb_pieces; ?> pièces</span>
                                                </div>
                                                <div class="property-feature">
                                                    <i class="fas fa-bed"></i>
                                                    <span><?php echo $prop->nb_chambres; ?> ch.</span>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between">
                                                <a href="/omnes-immobilier/property-detail.php?id=<?php echo $prop->id_propriete; ?>" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i> Voir
                                                </a>
                                                <a href="#" class="btn btn-outline-secondary" onclick="alert('Cette fonctionnalité n\'est pas encore disponible.')">
                                                    <i class="fas fa-pencil-alt"></i> Gérer
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-info">
                            Vous ne gérez actuellement aucune propriété. Les propriétés sont attribuées par l'administrateur.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>