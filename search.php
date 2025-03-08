<?php
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
require_once BASE_PATH . 'config/init.php';

$page_title = "Recherche";
include BASE_PATH . 'includes/header.php';

// Inclure les classes nécessaires
require_once BASE_PATH . 'classes/Property.php';

// Initialiser la classe Property
$property = new Property();

// Récupérer les paramètres de recherche
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';
$filter_ville = isset($_GET['ville']) ? $_GET['ville'] : '';
$filter_prix_min = isset($_GET['prix_min']) ? $_GET['prix_min'] : '';
$filter_prix_max = isset($_GET['prix_max']) ? $_GET['prix_max'] : '';
$filter_surface_min = isset($_GET['surface_min']) ? $_GET['surface_min'] : '';
$filter_nb_pieces_min = isset($_GET['nb_pieces_min']) ? $_GET['nb_pieces_min'] : '';

// Si un terme de recherche est spécifié, faire une recherche simple
if (!empty($search_term)) {
    $properties = $property->searchProperties($search_term);
} 
// Sinon, si des filtres sont spécifiés, appliquer les filtres
elseif (!empty($filter_type) || !empty($filter_ville) || !empty($filter_prix_min) || !empty($filter_prix_max) || !empty($filter_surface_min) || !empty($filter_nb_pieces_min)) {
    $filters = [
        'type_propriete' => $filter_type,
        'ville' => $filter_ville,
        'prix_min' => $filter_prix_min,
        'prix_max' => $filter_prix_max,
        'surface_min' => $filter_surface_min,
        'nb_pieces_min' => $filter_nb_pieces_min
    ];
    $properties = $property->filterProperties($filters);
} 
// Sinon, afficher toutes les propriétés
else {
    $properties = $property->getAllProperties();
}
?>

<div class="container mt-4">
    <h1 class="section-title mb-4" data-aos="fade-up">Recherche de biens immobiliers</h1>
    
    <!-- Formulaire de recherche simple -->
    <div class="card mb-4" data-aos="fade-up">
        <div class="card-body">
            <form action="/omnes-immobilier/search.php" method="get" class="row g-3">
                <div class="col-md-10">
                    <input type="text" class="form-control" name="search" placeholder="Rechercher par titre, description ou ville" value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Recherche avancée -->
    <div class="card mb-4" data-aos="fade-up">
        <div class="card-header bg-primary text-white">
            Recherche avancée
        </div>
        <div class="card-body">
            <form action="/omnes-immobilier/search.php" method="get" class="row g-3">
                <div class="col-md-4">
                    <label for="type" class="form-label">Type de bien</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">Tous les types</option>
                        <option value="résidentiel" <?php echo $filter_type === 'résidentiel' ? 'selected' : ''; ?>>Résidentiel</option>
                        <option value="commercial" <?php echo $filter_type === 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                        <option value="terrain" <?php echo $filter_type === 'terrain' ? 'selected' : ''; ?>>Terrain</option>
                        <option value="location" <?php echo $filter_type === 'location' ? 'selected' : ''; ?>>Location</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label for="ville" class="form-label">Ville</label>
                    <input type="text" class="form-control" id="ville" name="ville" value="<?php echo htmlspecialchars($filter_ville); ?>">
                </div>
                
                <div class="col-md-4">
                    <label for="nb_pieces_min" class="form-label">Nombre de pièces min.</label>
                    <select class="form-select" id="nb_pieces_min" name="nb_pieces_min">
                        <option value="">Indifférent</option>
                        <?php for ($i = 1; $i <= 5; $i++) : ?>
                            <option value="<?php echo $i; ?>" <?php echo $filter_nb_pieces_min == $i ? 'selected' : ''; ?>><?php echo $i; ?> pièce<?php echo $i > 1 ? 's' : ''; ?> ou plus</option>
                        <?php endfor; ?>
                        <option value="6" <?php echo $filter_nb_pieces_min == 6 ? 'selected' : ''; ?>>6 pièces ou plus</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Prix</label>
                    <div class="row g-2">
                        <div class="col">
                            <input type="number" class="form-control" name="prix_min" placeholder="Min." value="<?php echo htmlspecialchars($filter_prix_min); ?>">
                        </div>
                        <div class="col-auto">
                            <span class="form-text">à</span>
                        </div>
                        <div class="col">
                            <input type="number" class="form-control" name="prix_max" placeholder="Max." value="<?php echo htmlspecialchars($filter_prix_max); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label for="surface_min" class="form-label">Surface minimale (m²)</label>
                    <input type="number" class="form-control" id="surface_min" name="surface_min" placeholder="Surface min." value="<?php echo htmlspecialchars($filter_surface_min); ?>">
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    <a href="/omnes-immobilier/search.php" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Résultats de recherche -->
    <div class="card" data-aos="fade-up">
        <div class="card-header bg-primary text-white">
            Résultats de recherche
        </div>
        <div class="card-body">
            <?php if (!empty($properties)) : ?>
                <div class="row">
                    <?php foreach ($properties as $index => $prop) : ?>
                        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index % 3 * 100; ?>">
                            <div class="card h-100 property-card">
                                <?php 
                                // Récupérer la première image de la propriété
                                $medias = $property->getMedia($prop->id_propriete);
                                $image_path = !empty($medias) && isset($medias[0]->url_path) ? $medias[0]->url_path : '/omnes-immobilier/assets/img/properties/default.jpg';
                                ?>
                                
                                <img src="<?php echo $image_path; ?>" class="card-img-top" alt="<?php echo $prop->titre; ?>" style="height: 200px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/300x200?text=Propriété'">
                                
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
                                    <a href="/omnes-immobilier/property-detail.php?id=<?php echo $prop->id_propriete; ?>" class="btn btn-outline-primary w-100">Voir détails</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="alert alert-info">
                    Aucun bien immobilier ne correspond à votre recherche.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>