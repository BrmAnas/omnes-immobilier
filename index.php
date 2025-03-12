<?php
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
require_once BASE_PATH . 'config/init.php';

// Titre de la page
$page_title = "Accueil";
include BASE_PATH . 'includes/header.php';

// Inclure les classes nécessaires
require_once BASE_PATH . 'classes/Property.php';
require_once BASE_PATH . 'classes/Agent.php';

// Initialiser les classes
$property = new Property();
$agent = new Agent();

// Récupérer les propriétés à la une (les 4 plus récentes)
$featured_properties = $property->getAllProperties();
// Garder uniquement 4 propriétés
if (count($featured_properties) > 4) {
    $featured_properties = array_slice($featured_properties, 0, 4);
}

// Récupérer quelques agents (les 3 plus récents)
$agents = $agent->getAllAgents();
// Garder uniquement 3 agents
if (count($agents) > 3) {
    $agents = array_slice($agents, 0, 3);
}
?>

<section class="hero-section">
    <div class="row">
        <div class="col-md-8" data-aos="fade-right">
            <div class="card bg-light">
                <div class="card-body hero-content">
                    <h1 class="card-title">Bienvenue chez Omnes Immobilier</h1>
                    <p class="card-text">Trouvez votre bien idéal parmi notre sélection de propriétés résidentielles, commerciales, terrains et locations.</p>
                    <a href="properties.php" class="btn btn-primary">Découvrir nos biens</a>
                </div>
            </div>
        </div>

    </div>
</section>

<section class="event-section mt-5" data-aos="fade-up">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2>Événement de la semaine</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <img src="/omnes-immobilier/assets/img/banners/event-banner.jpg" alt="Événement" class="img-fluid" onerror="this.src='https://via.placeholder.com/300x200?text=Événement'">
                </div>
                <div class="col-md-8">
                    <h3>Portes Ouvertes - Résidence Les Cèdres</h3>
                    <p>Venez découvrir nos nouveaux appartements disponibles à la vente. Notre équipe sera présente pour répondre à toutes vos questions et vous guider dans votre projet immobilier.</p>
                    <p><strong>Date:</strong> Samedi 15 mars 2025, de 10h à 17h</p>
                    <p><strong>Adresse:</strong> 15 Avenue des Lilas, Paris 15ème</p>
                    <a href="#" class="btn btn-secondary">En savoir plus</a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Ajouter après la section des propriétés à la une -->
<section class="services-section mt-5">
    <h2 class="section-title" data-aos="fade-up">Nos Services Premium</h2>
    <div class="row mt-3">
        <!-- Carte Dossier Acheteur Premium -->
        <div class="col-md-4" data-aos="fade-up">
            <div class="card service-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-file-invoice fa-3x text-primary mb-3"></i>
                    <h4>Dossier Acheteur Premium</h4>
                    <p>Optimisez vos chances avec un dossier acheteur qui vous démarque.</p>
                    <p class="service-price">99,00 €</p>
                    <a href="/omnes-immobilier/account-services.php" class="btn btn-outline-primary">En savoir plus</a>
                </div>
            </div>
        </div>
        
        <!-- Carte Photos Professionnelles -->
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card service-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-camera fa-3x text-primary mb-3"></i>
                    <h4>Photos Professionnelles</h4>
                    <p>Sublimez votre bien avec des photos de qualité professionnelle.</p>
                    <p class="service-price">149,00 €</p>
                    <a href="/omnes-immobilier/account-services.php" class="btn btn-outline-primary">En savoir plus</a>
                </div>
            </div>
        </div>
        
        <!-- Carte Visite Virtuelle -->
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card service-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-vr-cardboard fa-3x text-primary mb-3"></i>
                    <h4>Visite Virtuelle 3D</h4>
                    <p>Explorez les propriétés en détail avec notre technologie 3D immersive.</p>
                    <p class="service-price">39,90 €</p>
                    <a href="/omnes-immobilier/properties.php" class="btn btn-outline-primary">Voir les propriétés</a>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="featured-properties mt-5">
    <h2 class="section-title" data-aos="fade-up">Nos biens à la une</h2>
    <div class="row mt-3">
        <?php if (!empty($featured_properties)) : ?>
            <?php foreach ($featured_properties as $index => $prop) : ?>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                    <div class="card property-card">
                        <?php 
                        // Récupérer la première image de la propriété
                        $medias = $property->getMedia($prop->id_propriete);
                        $image_path = !empty($medias) && isset($medias[0]->url_path) ? $medias[0]->url_path : '/omnes-immobilier/assets/img/properties/default.jpg';
                        ?>
                        
                        <img src="<?php echo $image_path; ?>" class="card-img-top" alt="<?php echo $prop->titre; ?>" onerror="this.src='https://via.placeholder.com/300x200?text=Propriété'">
                        
                        <?php if ($prop->type_propriete) : ?>
                            <div class="property-tag"><?php echo ucfirst($prop->type_propriete); ?></div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $prop->titre; ?></h5>
                            <p class="property-location"><i class="fas fa-map-marker-alt"></i> <?php echo $prop->ville; ?></p>
                            <?php if ($prop->type_propriete !== 'location') : ?>
                                <p class="property-price"><?php echo format_price($prop->prix); ?></p>
                            <?php else : ?>
                                <p class="property-price"><?php echo format_price($prop->prix); ?>/mois</p>
                            <?php endif; ?>
                            
                            <div class="property-features">
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
                            
                            <a href="property-detail.php?id=<?php echo $prop->id_propriete; ?>" class="btn btn-outline-primary w-100 mt-3">Voir détails</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="col-12">
                <div class="alert alert-info">
                    Aucune propriété disponible pour le moment.
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="text-center mt-4" data-aos="fade-up">
        <a href="properties.php" class="btn btn-outline-primary">Voir tous nos biens</a>
    </div>
</section>

<section class="featured-agents mt-5">
    <h2 class="section-title" data-aos="fade-up">Nos agents immobiliers</h2>
    <div class="row mt-3">
        <?php if (!empty($agents)) : ?>
            <?php foreach ($agents as $index => $agent_item) : ?>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                    <div class="card agent-card">
                        <div class="card-body">
                            <?php if ($agent_item->photo_path) : ?>
                                <img src="<?php echo $agent_item->photo_path; ?>" class="agent-img" alt="<?php echo $agent_item->prenom . ' ' . $agent_item->nom; ?>" onerror="this.src='https://via.placeholder.com/150x150?text=Agent'">
                            <?php else : ?>
                                <img src="/omnes-immobilier/assets/img/agents/default.jpg" class="agent-img" alt="<?php echo $agent_item->prenom . ' ' . $agent_item->nom; ?>" onerror="this.src='https://via.placeholder.com/150x150?text=Agent'">
                            <?php endif; ?>
                            
                            <h4 class="agent-name"><?php echo $agent_item->prenom . ' ' . $agent_item->nom; ?></h4>
                            <p class="agent-title"><?php echo $agent_item->specialite; ?></p>
                            
                            <div class="agent-contact">
                                <p><i class="fas fa-phone"></i> <?php echo $agent_item->telephone; ?></p>
                                <p><i class="fas fa-envelope"></i> <?php echo $agent_item->email; ?></p>
                            </div>
                            
                            <a href="agent-profile.php?id=<?php echo $agent_item->id_agent; ?>" class="btn btn-outline-primary w-100 mt-3">Voir profil</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="col-12">
                <div class="alert alert-info">
                    Aucun agent disponible pour le moment.
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="text-center mt-4" data-aos="fade-up">
        <a href="agents.php" class="btn btn-outline-primary">Voir tous nos agents</a>
    </div>
</section>

<?php include BASE_PATH . 'includes/footer.php'; ?>