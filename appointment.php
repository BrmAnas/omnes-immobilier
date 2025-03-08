<?php
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
require_once BASE_PATH . 'config/init.php';

// Vérifier si l'utilisateur est connecté
if (!is_logged_in()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    set_alert('warning', 'Veuillez vous connecter pour prendre un rendez-vous.');
    redirect('/omnes-immobilier/login.php');
}

// Inclure les classes nécessaires
require_once BASE_PATH . 'classes/Agent.php';
require_once BASE_PATH . 'classes/Property.php';
require_once BASE_PATH . 'classes/Client.php';
require_once BASE_PATH . 'classes/Appointment.php';

// Initialiser les classes
$agent = new Agent();
$property = new Property();
$client = new Client();
$appointment = new Appointment();

// Vérifier si l'utilisateur est un client
$user_client = $client->getClientByUserId($_SESSION['user_id']);
if (!$user_client) {
    set_alert('danger', 'Vous devez être un client pour prendre rendez-vous.');
    redirect('/omnes-immobilier/index.php');
}

// Si on a un agent et une propriété spécifiés dans l'URL
$id_agent = isset($_GET['agent']) ? $_GET['agent'] : null;
$id_propriete = isset($_GET['property']) ? $_GET['property'] : null;

// Si les données du formulaire ont été soumises
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données du formulaire
    $appointment_data = [
        'id_client' => $user_client->id_client,
        'id_agent' => $_POST['id_agent'],
        'id_propriete' => $_POST['id_propriete'],
        'date' => $_POST['date'],
        'heure' => $_POST['heure'],
        'motif' => $_POST['motif'],
        'commentaire' => $_POST['commentaire'] ?? null
    ];
    
    // Vérifier si le créneau est disponible
    if ($appointment->isSlotAvailable($appointment_data['id_agent'], $appointment_data['date'], $appointment_data['heure'])) {
        // Créer le rendez-vous
        $rdv_id = $appointment->create($appointment_data);
        
        if ($rdv_id) {
            set_alert('success', 'Votre rendez-vous a été pris avec succès.');
            redirect('/omnes-immobilier/my-appointments.php');
        } else {
            $error = "Une erreur est survenue lors de la prise de rendez-vous.";
        }
    } else {
        $error = "Ce créneau n'est plus disponible. Veuillez en choisir un autre.";
    }
}

// Si un agent est spécifié, récupérer ses infos
$agent_info = $id_agent ? $agent->getAgentById($id_agent) : null;

// Si une propriété est spécifiée, récupérer ses infos
$property_info = $id_propriete ? $property->getPropertyById($id_propriete) : null;

// Si on n'a pas d'agent spécifié, récupérer tous les agents
$all_agents = $id_agent ? null : $agent->getAllAgents();

// Si on a un agent mais pas de propriété, récupérer les propriétés gérées par cet agent
$agent_properties = ($id_agent && !$id_propriete) ? $property->getPropertiesByAgent($id_agent) : null;

// Récupérer les disponibilités de l'agent si un agent est spécifié
$availabilities = $id_agent ? $agent->getAvailabilities($id_agent) : null;

$page_title = "Prise de rendez-vous";
include BASE_PATH . 'includes/header.php';
?>

<div class="container mt-4">
    <h1 class="section-title mb-4" data-aos="fade-up">Prendre un rendez-vous</h1>
    
    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="post" action="/omnes-immobilier/appointment.php">
        <!-- Étape 1: Sélection de l'agent -->
        <?php if (!$agent_info) : ?>
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    Étape 1: Choisissez un agent immobilier
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if (!empty($all_agents)) : ?>
                            <?php foreach ($all_agents as $index => $a) : ?>
                                <div class="col-md-4 mb-3" data-aos="fade-up" data-aos-delay="<?php echo $index % 3 * 100; ?>">
                                    <div class="card h-100 agent-card">
                                        <div class="card-body text-center">
                                            <?php if (isset($a->photo_path) && $a->photo_path) : ?>
                                                <img src="<?php echo $a->photo_path; ?>" class="agent-img" alt="<?php echo $a->prenom . ' ' . $a->nom; ?>" onerror="this.src='/omnes-immobilier/assets/img/agents/default.jpg'">
                                            <?php else : ?>
                                                <img src="/omnes-immobilier/assets/img/agents/default.jpg" class="agent-img" alt="<?php echo $a->prenom . ' ' . $a->nom; ?>" onerror="this.src='https://via.placeholder.com/150?text=Agent'">
                                            <?php endif; ?>
                                            
                                            <h5 class="agent-name"><?php echo $a->prenom . ' ' . $a->nom; ?></h5>
                                            <p class="agent-title"><?php echo $a->specialite; ?></p>
                                            
                                            <a href="/omnes-immobilier/appointment.php?agent=<?php echo $a->id_agent; ?><?php echo $id_propriete ? '&property=' . $id_propriete : ''; ?>" class="btn btn-primary w-100">Sélectionner</a>
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
                </div>
            </div>
        <?php else : ?>
            <input type="hidden" name="id_agent" value="<?php echo $agent_info->id_agent; ?>">
            
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    Agent sélectionné
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <?php if (isset($agent_info->photo_path) && $agent_info->photo_path) : ?>
                            <img src="<?php echo $agent_info->photo_path; ?>" class="rounded-circle me-3" alt="<?php echo $agent_info->prenom . ' ' . $agent_info->nom; ?>" style="width: 80px; height: 80px; object-fit: cover;" onerror="this.src='/omnes-immobilier/assets/img/agents/default.jpg'">
                        <?php else : ?>
                            <img src="/omnes-immobilier/assets/img/agents/default.jpg" class="rounded-circle me-3" alt="<?php echo $agent_info->prenom . ' ' . $agent_info->nom; ?>" style="width: 80px; height: 80px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/80?text=Agent'">
                        <?php endif; ?>
                        <div>
                            <h5 class="mb-1"><?php echo $agent_info->prenom . ' ' . $agent_info->nom; ?></h5>
                            <p class="mb-0"><?php echo $agent_info->specialite; ?></p>
                        </div>
                        <a href="/omnes-immobilier/appointment.php<?php echo $id_propriete ? '?property=' . $id_propriete : ''; ?>" class="btn btn-outline-primary ms-auto">Changer</a>
                    </div>
                </div>
            </div>
            
            <!-- Étape 2: Sélection de la propriété -->
            <?php if (!$property_info && $agent_properties) : ?>
                <div class="card mb-4" data-aos="fade-up">
                    <div class="card-header bg-primary text-white">
                        Étape 2: Choisissez une propriété
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if (!empty($agent_properties)) : ?>
                                <?php foreach ($agent_properties as $index => $p) : ?>
                                    <div class="col-md-4 mb-3" data-aos="fade-up" data-aos-delay="<?php echo $index % 3 * 100; ?>">
                                        <div class="card h-100 property-card">
                                            <?php 
                                            // Récupérer la première image de la propriété
                                            $medias = $property->getMedia($p->id_propriete);
                                            $image_path = !empty($medias) && isset($medias[0]->url_path) ? $medias[0]->url_path : '/omnes-immobilier/assets/img/properties/default.jpg';
                                            ?>
                                            
                                            <img src="<?php echo $image_path; ?>" class="card-img-top" alt="<?php echo $p->titre; ?>" style="height: 150px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/300x150?text=Propriété'">
                                            
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo $p->titre; ?></h5>
                                                <p class="property-location"><i class="fas fa-map-marker-alt"></i> <?php echo $p->ville; ?></p>
                                                    <?php if ($p->type_propriete !== 'location') : ?>
                                                        <p class="property-price"><?php echo format_price($p->prix); ?></p>
                                                    <?php else : ?>
                                                        <p class="property-price"><?php echo format_price($p->prix); ?>/mois</p>
                                                    <?php endif; ?>
                                                    
                                                    <a href="/omnes-immobilier/appointment.php?agent=<?php echo $agent_info->id_agent; ?>&property=<?php echo $p->id_propriete; ?>" class="btn btn-primary w-100 mt-2">Sélectionner</a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <!-- Option pour rendez-vous général -->
                                <div class="col-md-4 mb-3" data-aos="fade-up">
                                    <div class="card h-100">
                                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                                            <i class="fas fa-calendar-alt fa-5x mb-3 text-muted"></i>
                                            <h5 class="card-title">Rendez-vous général</h5>
                                            <p class="card-text">Prendre un rendez-vous sans propriété spécifique</p>
                                            <a href="/omnes-immobilier/appointment.php?agent=<?php echo $agent_info->id_agent; ?>&property=0" class="btn btn-outline-primary w-100">Sélectionner</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else : ?>
                    <?php if ($property_info) : ?>
                        <input type="hidden" name="id_propriete" value="<?php echo $property_info->id_propriete; ?>">
                        
                        <div class="card mb-4" data-aos="fade-up">
                            <div class="card-header bg-primary text-white">
                                Propriété sélectionnée
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <?php 
                                    // Récupérer la première image de la propriété
                                    $medias = $property->getMedia($property_info->id_propriete);
                                    $image_path = !empty($medias) && isset($medias[0]->url_path) ? $medias[0]->url_path : '/omnes-immobilier/assets/img/properties/default.jpg';
                                    ?>
                                    
                                    <img src="<?php echo $image_path; ?>" class="me-3" alt="<?php echo $property_info->titre; ?>" style="width: 80px; height: 80px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/80?text=Propriété'">
                                    <div>
                                        <h5 class="mb-1"><?php echo $property_info->titre; ?></h5>
                                        <p class="mb-0"><?php echo $property_info->ville; ?></p>
                                    </div>
                                    <a href="/omnes-immobilier/appointment.php?agent=<?php echo $agent_info->id_agent; ?>" class="btn btn-outline-primary ms-auto">Changer</a>
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                        <input type="hidden" name="id_propriete" value="0">
                        
                        <div class="card mb-4" data-aos="fade-up">
                            <div class="card-header bg-primary text-white">
                                Rendez-vous général
                            </div>
                            <div class="card-body">
                                <p>Vous avez choisi de prendre un rendez-vous général avec l'agent, sans propriété spécifique.</p>
                                <a href="/omnes-immobilier/appointment.php?agent=<?php echo $agent_info->id_agent; ?>" class="btn btn-outline-primary">Choisir une propriété</a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Étape 3: Sélection date et heure -->
                    <div class="card mb-4" data-aos="fade-up">
                        <div class="card-header bg-primary text-white">
                            Étape 3: Choisissez une date et une heure
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="date" class="form-label">Date du rendez-vous</label>
                                        <input type="date" class="form-control" id="date" name="date" required min="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="heure" class="form-label">Heure du rendez-vous</label>
                                        <select class="form-select" id="heure" name="heure" required>
                                            <option value="">Sélectionnez une heure</option>
                                            <?php for ($h = 9; $h < 18; $h++) : ?>
                                                <?php if ($h != 12) : // Pas de rendez-vous à midi ?>
                                                    <option value="<?php echo sprintf('%02d', $h); ?>:00"><?php echo sprintf('%02d', $h); ?>:00</option>
                                                    <option value="<?php echo sprintf('%02d', $h); ?>:30"><?php echo sprintf('%02d', $h); ?>:30</option>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <h5>Disponibilités de l'agent:</h5>
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
                                                        <?php echo $available ? '✓' : '×'; ?>
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
                                                        <?php echo $available ? '✓' : '×'; ?>
                                                    </td>
                                                <?php endfor; ?>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-2">
                                    <small>Légende: ✓ = Disponible, × = Indisponible</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Étape 4: Détails du rendez-vous -->
                    <div class="card mb-4" data-aos="fade-up">
                        <div class="card-header bg-primary text-white">
                            Étape 4: Détails du rendez-vous
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="motif" class="form-label">Motif du rendez-vous</label>
                                <select class="form-select" id="motif" name="motif" required>
                                    <option value="">Sélectionnez un motif</option>
                                    <option value="Visite du bien">Visite du bien</option>
                                    <option value="Information sur le bien">Information sur le bien</option>
                                    <option value="Négociation">Négociation</option>
                                    <option value="Signature de documents">Signature de documents</option>
                                    <option value="Conseil immobilier">Conseil immobilier</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="commentaire" class="form-label">Commentaire (optionnel)</label>
                                <textarea class="form-control" id="commentaire" name="commentaire" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Confirmer le rendez-vous</button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </form>
    </div>
    
    <?php include BASE_PATH . 'includes/footer.php'; ?>