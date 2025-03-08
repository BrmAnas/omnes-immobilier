<?php
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
require_once BASE_PATH . 'config/init.php';

$page_title = "Détail de la propriété";
include BASE_PATH . 'includes/header.php';

// Inclure les classes nécessaires
require_once BASE_PATH . 'classes/Property.php';
require_once BASE_PATH . 'classes/Agent.php';

// Vérifier si l'ID est présent
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('/omnes-immobilier/properties.php');
}

$id_propriete = $_GET['id'];

// Initialiser les classes
$property = new Property();
$agent = new Agent();

// Récupérer les infos de la propriété
$prop = $property->getPropertyById($id_propriete);
if (!$prop) {
    redirect('/omnes-immobilier/properties.php');
}

// Récupérer les médias de la propriété
$medias = $property->getMedia($id_propriete);

// Récupérer les infos de l'agent
$agent_info = $agent->getAgentById($prop->id_agent);

// Définir le titre de la page après avoir récupéré les infos
$page_title = $prop->titre;
?>

<div class="container mt-4 property-details">
    <!-- Fil d'Ariane -->
    <nav aria-label="breadcrumb" data-aos="fade-up">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/omnes-immobilier/index.php">Accueil</a></li>
            <li class="breadcrumb-item"><a href="/omnes-immobilier/properties.php">Propriétés</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $prop->titre; ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <h1 class="property-title" data-aos="fade-up"><?php echo $prop->titre; ?></h1>
            <p class="property-location" data-aos="fade-up"><i class="fas fa-map-marker-alt"></i> <?php echo $prop->ville; ?> - <?php echo $prop->code_postal; ?></p>
            
            <!-- Galerie d'images -->
            <div id="propertyCarousel" class="carousel slide mb-4" data-bs-ride="carousel" data-aos="fade-up">
                <div class="carousel-inner">
                    <?php if (!empty($medias)) : ?>
                        <?php foreach ($medias as $index => $media) : ?>
                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                <img src="<?php echo $media->url_path; ?>" class="d-block w-100" alt="<?php echo $media->titre ?? $prop->titre; ?>" onerror="this.src='https://via.placeholder.com/800x400?text=Propriété'">
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="carousel-item active">
                            <<img src="/omnes-immobilier/assets/img/properties/default.jpg" class="d-block w-100" alt="<?php echo $prop->titre; ?>" onerror="this.src='https://via.placeholder.com/800x400?text=Propriété'">
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (count($medias) > 1) : ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Précédent</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Suivant</span>
                    </button>
                <?php endif; ?>
            </div>
            
            <!-- Description -->
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    Description
                </div>
                <div class="card-body">
                    <p><?php echo nl2br($prop->description); ?></p>
                </div>
            </div>
            
            <!-- Adresse et carte -->
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    Localisation
                </div>
                <div class="card-body">
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo $prop->adresse; ?>, <?php echo $prop->code_postal; ?> <?php echo $prop->ville; ?>, <?php echo $prop->pays; ?></p>
                    <!-- Ici, on pourrait intégrer une carte Google Maps -->
                    <div class="ratio ratio-16x9">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2624.9916256937394!2d2.292292615483097!3d48.85837007928757!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e66e2964e34e2d%3A0x8ddca9ee380ef7e0!2sTour%20Eiffel!5e0!3m2!1sfr!2sfr!4v1647531555654!5m2!1sfr!2sfr" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Prix et caractéristiques -->
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    Informations principales
                </div>
                <div class="card-body">
                    <?php if ($prop->type_propriete !== 'location') : ?>
                        <h3 class="property-price"><?php echo format_price($prop->prix); ?></h3>
                    <?php else : ?>
                        <h3 class="property-price"><?php echo format_price($prop->prix); ?>/mois</h3>
                    <?php endif; ?>
                    
                    <ul class="list-group list-group-flush mt-3">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Type
                            <span class="badge bg-secondary rounded-pill"><?php echo ucfirst($prop->type_propriete); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Surface
                            <span class="badge bg-secondary rounded-pill"><?php echo $prop->surface; ?> m²</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Pièces
                            <span class="badge bg-secondary rounded-pill"><?php echo $prop->nb_pieces; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Chambres
                            <span class="badge bg-secondary rounded-pill"><?php echo $prop->nb_chambres; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Salles de bain
                            <span class="badge bg-secondary rounded-pill"><?php echo $prop->nb_salles_bain; ?></span>
                        </li>
                        <?php if (isset($prop->etage)) : ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Étage
                                <span class="badge bg-secondary rounded-pill"><?php echo $prop->etage; ?></span>
                            </li>
                        <?php endif; ?>
                    </ul>
                    
                    <div class="mt-3">
                        <p class="mb-1"><strong>Équipements :</strong></p>
                        <div class="d-flex flex-wrap">
                            <?php if ($prop->balcon) : ?>
                                <span class="badge bg-secondary me-2 mb-2">Balcon</span>
                            <?php endif; ?>
                            <?php if ($prop->parking) : ?>
                                <span class="badge bg-secondary me-2 mb-2">Parking</span>
                            <?php endif; ?>
                            <?php if ($prop->ascenseur) : ?>
                                <span class="badge bg-secondary me-2 mb-2">Ascenseur</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Agent -->
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    Agent immobilier
                </div>
                <div class="card-body text-center">
                    <?php if (isset($agent_info->photo_path) && $agent_info->photo_path) : ?>
                        <img src="<?php echo $agent_info->photo_path; ?>" class="rounded-circle img-thumbnail mb-3" alt="<?php echo $agent_info->prenom . ' ' . $agent_info->nom; ?>" style="width: 150px; height: 150px; object-fit: cover;" onerror="this.src='/omnes-immobilier/assets/img/agents/default.jpg'">
                    <?php else : ?>
                        <img src="/omnes-immobilier/assets/img/agents/default.jpg" class="rounded-circle img-thumbnail mb-3" alt="<?php echo $agent_info->prenom . ' ' . $agent_info->nom; ?>" onerror="this.src='https://via.placeholder.com/150?text=Agent'">
                    <?php endif; ?>
                    
                    <h5><?php echo $agent_info->prenom . ' ' . $agent_info->nom; ?></h5>
                    <p class="text-muted"><?php echo $agent_info->specialite; ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo $agent_info->telephone; ?><br>
                    <i class="fas fa-envelope"></i> <?php echo $agent_info->email; ?></p>
                    
                    <div class="d-grid gap-2">
                        <a href="/omnes-immobilier/appointment.php?agent=<?php echo $agent_info->id_agent; ?>&property=<?php echo $prop->id_propriete; ?>" class="btn btn-primary">Prendre rendez-vous</a>
                        <a href="/omnes-immobilier/agent-profile.php?id=<?php echo $agent_info->id_agent; ?>" class="btn btn-outline-primary">Voir profil</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>