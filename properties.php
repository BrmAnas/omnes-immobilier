<?php
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
require_once BASE_PATH . 'config/init.php';

$page_title = "Propriétés";
include BASE_PATH . 'includes/header.php';

// Inclure les classes nécessaires
require_once BASE_PATH . 'classes/Property.php';
require_once BASE_PATH . 'classes/Agent.php';

// Initialiser les classes
$property = new Property();
$agent = new Agent();

// Déterminer si on filtre par type
$type = isset($_GET['type']) ? $_GET['type'] : null;

// Récupérer les propriétés selon le type ou toutes les propriétés
if ($type) {
    $properties = $property->getPropertiesByType($type);
} else {
    $properties = $property->getAllProperties();
}
?>

<div class="container mt-4">
    <h1 class="section-title mb-4" data-aos="fade-up">Tous nos biens immobiliers</h1>
    
    <!-- Filtres par type -->
    <div class="mb-4" data-aos="fade-up">
        <div class="btn-group" role="group">
            <a href="properties.php" class="btn <?php echo !$type ? 'btn-primary' : 'btn-outline-primary'; ?>">Tous</a>
            <a href="properties.php?type=résidentiel" class="btn <?php echo $type === 'résidentiel' ? 'btn-primary' : 'btn-outline-primary'; ?>">Résidentiel</a>
            <a href="properties.php?type=commercial" class="btn <?php echo $type === 'commercial' ? 'btn-primary' : 'btn-outline-primary'; ?>">Commercial</a>
            <a href="properties.php?type=terrain" class="btn <?php echo $type === 'terrain' ? 'btn-primary' : 'btn-outline-primary'; ?>">Terrain</a>
            <a href="properties.php?type=location" class="btn <?php echo $type === 'location' ? 'btn-primary' : 'btn-outline-primary'; ?>">Location</a>
        </div>
    </div>
    
    <!-- Liste des propriétés -->
    <div class="row">
        <?php if (!empty($properties)) : ?>
            <?php foreach ($properties as $index => $prop) : ?>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index % 3 * 100; ?>">
                    <div class="card property-card h-100">
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
                        </div>
                        <div class="card-footer bg-white">
                            <a href="property-detail.php?id=<?php echo $prop->id_propriete; ?>" class="btn btn-outline-primary w-100">Voir détails</a>
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
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>