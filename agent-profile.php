<?php
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
require_once BASE_PATH . 'config/init.php';

$page_title = "Profil de l'agent";
include BASE_PATH . 'includes/header.php';

// Inclure les classes nécessaires
require_once BASE_PATH . 'classes/Agent.php';
require_once BASE_PATH . 'classes/Property.php';

// Vérifier si l'ID est présent
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('/omnes-immobilier/index.php');
}

$id_agent = $_GET['id'];

// Initialiser les classes
$agent = new Agent();
$property = new Property();

// Récupérer les infos de l'agent
$agent_info = $agent->getAgentById($id_agent);
if (!$agent_info) {
    redirect('/omnes-immobilier/index.php');
}

// Récupérer les disponibilités de l'agent
$availabilities = $agent->getAvailabilities($id_agent);

// Récupérer les propriétés gérées par l'agent
$properties = $property->getPropertiesByAgent($id_agent);

// Définir le titre de la page après avoir récupéré les infos
$page_title = $agent_info->prenom . ' ' . $agent_info->nom;
?>

<div class="container mt-4 agent-profile">
    <!-- Fil d'Ariane -->
    <nav aria-label="breadcrumb" data-aos="fade-up">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/omnes-immobilier/index.php">Accueil</a></li>
            <li class="breadcrumb-item"><a href="/omnes-immobilier/agents.php">Agents</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $agent_info->prenom . ' ' . $agent_info->nom; ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-4">
            <!-- Profil de l'agent -->
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    Profil
                </div>
                <div class="card-body text-center">
                    <?php if (isset($agent_info->photo_path) && $agent_info->photo_path) : ?>
                        <img src="<?php echo $agent_info->photo_path; ?>" class="agent-photo mb-3" alt="<?php echo $agent_info->prenom . ' ' . $agent_info->nom; ?>" onerror="this.src='/omnes-immobilier/assets/img/agents/default.jpg'">
                    <?php else : ?>
                        <img src="/omnes-immobilier/assets/img/agents/default.jpg" class="agent-photo mb-3" alt="<?php echo $agent_info->prenom . ' ' . $agent_info->nom; ?>" onerror="this.src='https://via.placeholder.com/200?text=Agent'">
                    <?php endif; ?>
                    
                    <h3 class="agent-name"><?php echo $agent_info->prenom . ' ' . $agent_info->nom; ?></h3>
                    <p class="agent-title badge bg-secondary"><?php echo $agent_info->specialite; ?></p>
                    
                    <p class="agent-contact"><i class="fas fa-phone"></i> <?php echo $agent_info->telephone; ?><br>
                    <i class="fas fa-envelope"></i> <?php echo $agent_info->email; ?></p>
                    
                    <div class="d-grid gap-2">
                        <a href="/omnes-immobilier/appointment.php?agent=<?php echo $agent_info->id_agent; ?>" class="btn btn-primary">Prendre rendez-vous</a>
                        <?php if (isset($agent_info->cv_path) && $agent_info->cv_path) : ?>
                            <a href="<?php echo $agent_info->cv_path; ?>" class="btn btn-outline-primary" target="_blank">Voir le CV</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Biographie si disponible -->
            <?php if (isset($agent_info->biographie) && $agent_info->biographie) : ?>
                <div class="card mb-4" data-aos="fade-up">
                    <div class="card-header bg-primary text-white">
                        À propos de moi
                    </div>
                    <div class="card-body agent-bio">
                        <p><?php echo nl2br($agent_info->biographie); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-8">
            <!-- Disponibilités -->
            <div class="card mb-4 agent-calendar" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    Disponibilités
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-center">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Lundi</th>
                                    <th>Mardi</th>
                                    <th>Mercredi</th>
                                    <th>Jeudi</th>
                                    <th>Vendredi</th>
                                    <th>Samedi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Matin</th>
                                    <?php for ($i = 1; $i <= 6; $i++) : ?>
                                        <td class="
                                            <?php
                                            $available = false;
                                            if (!empty($availabilities)) {
                                                foreach ($availabilities as $availability) {
                                                    $day_of_week = date('N', strtotime($availability->jour));
                                                    $is_morning = strtotime($availability->heure_debut) < strtotime('12:00:00');
                                                    if ($day_of_week == $i && $is_morning && $availability->statut == 'disponible') {
                                                        $available = true;
                                                        break;
                                                    }
                                                }
                                            }
                                            echo $available ? 'bg-success text-white' : 'bg-danger text-white';
                                            ?>
                                        ">
                                            <?php echo $available ? 'Disponible' : 'Indisponible'; ?>
                                        </td>
                                    <?php endfor; ?>
                                </tr>
                                <tr>
                                    <th>Après-midi</th>
                                    <?php for ($i = 1; $i <= 6; $i++) : ?>
                                        <td class="
                                            <?php
                                            $available = false;
                                            if (!empty($availabilities)) {
                                                foreach ($availabilities as $availability) {
                                                    $day_of_week = date('N', strtotime($availability->jour));
                                                    $is_afternoon = strtotime($availability->heure_debut) >= strtotime('12:00:00');
                                                    if ($day_of_week == $i && $is_afternoon && $availability->statut == 'disponible') {
                                                        $available = true;
                                                        break;
                                                    }
                                                }
                                            }
                                            echo $available ? 'bg-success text-white' : 'bg-danger text-white';
                                            ?>
                                        ">
                                            <?php echo $available ? 'Disponible' : 'Indisponible'; ?>
                                        </td>
                                    <?php endfor; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="/omnes-immobilier/appointment.php?agent=<?php echo $agent_info->id_agent; ?>" class="btn btn-primary">Prendre rendez-vous</a>
                    </div>
                </div>
            </div>
            
            <!-- Propriétés gérées -->
            <div class="card" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    Propriétés gérées par cet agent
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if (!empty($properties)) : ?>
                            <?php foreach ($properties as $index => $prop) : ?>
                                <div class="col-md-6 mb-3" data-aos="fade-up" data-aos-delay="<?php echo $index % 2 * 100; ?>">
                                    <div class="card h-100 property-card">
                                        <?php 
                                        // Récupérer la première image de la propriété
                                        $medias = $property->getMedia($prop->id_propriete);
                                        $image_path = !empty($medias) && isset($medias[0]->url_path) ? $medias[0]->url_path : '/omnes-immobilier/assets/img/properties/default.jpg';
                                        ?>
                                        
                                        <img src="<?php echo $image_path; ?>" class="card-img-top" alt="<?php echo $prop->titre; ?>" style="height: 150px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/300x150?text=Propriété'">
                                        
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
                                            
                                            <a href="/omnes-immobilier/property-detail.php?id=<?php echo $prop->id_propriete; ?>" class="btn btn-sm btn-outline-primary w-100 mt-2">Voir détails</a>
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
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>