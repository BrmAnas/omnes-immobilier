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
require_once BASE_PATH . 'classes/Property.php';
require_once BASE_PATH . 'classes/Agent.php';

// Initialiser les classes
$property = new Property();
$agent = new Agent();

// Actions de CRUD
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Pour le formulaire d'ajout/modification
$agents = $agent->getAllAgents();

// Traitement de l'action
if ($action === 'delete' && $id > 0) {
    if ($property->delete($id)) {
        set_alert('success', 'La propriété a été supprimée avec succès.');
    } else {
        set_alert('danger', 'Une erreur est survenue lors de la suppression de la propriété.');
    }
    redirect('/omnes-immobilier/admin/properties.php');
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $property_data = [
        'titre' => $_POST['titre'],
        'description' => $_POST['description'],
        'prix' => $_POST['prix'],
        'surface' => $_POST['surface'],
        'nb_pieces' => $_POST['nb_pieces'],
        'nb_chambres' => $_POST['nb_chambres'],
        'nb_salles_bain' => $_POST['nb_salles_bain'],
        'etage' => $_POST['etage'],
        'balcon' => isset($_POST['balcon']) ? 1 : 0,
        'parking' => isset($_POST['parking']) ? 1 : 0,
        'ascenseur' => isset($_POST['ascenseur']) ? 1 : 0,
        'adresse' => $_POST['adresse'],
        'ville' => $_POST['ville'],
        'code_postal' => $_POST['code_postal'],
        'pays' => $_POST['pays'],
        'statut' => $_POST['statut'],
        'type_propriete' => $_POST['type_propriete'],
        'id_agent' => $_POST['id_agent']
    ];
    
    if ($action === 'edit' && $id > 0) {
        // Mise à jour d'une propriété existante
        $property_data['id_propriete'] = $id;
        if ($property->update($property_data)) {
            set_alert('success', 'La propriété a été mise à jour avec succès.');
            redirect('/omnes-immobilier/admin/properties.php');
        } else {
            $error = 'Une erreur est survenue lors de la mise à jour de la propriété.';
        }
    } else {
        // Ajout d'une nouvelle propriété
        $property_id = $property->create($property_data);
        if ($property_id) {
            // Traitement des images si présentes
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $upload_dir = BASE_PATH . 'assets/uploads/properties/';
                
                // Créer le dossier s'il n'existe pas
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] == 0) {
                        $file_ext = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                        $file_name = 'property_' . $property_id . '_' . time() . '_' . $key . '.' . $file_ext;
                        $file_path = $upload_dir . $file_name;
                        
                        if (move_uploaded_file($tmp_name, $file_path)) {
                            // Ajouter l'image à la base de données
                            $media_data = [
                                'id_propriete' => $property_id,
                                'type' => 'photo',
                                'url_path' => '/omnes-immobilier/assets/uploads/properties/' . $file_name,
                                'est_principale' => $key == 0 ? 1 : 0, // La première image est principale
                                'titre' => $_POST['titre'] . ' - Image ' . ($key + 1)
                            ];
                            $property->addMedia($media_data);
                        }
                    }
                }
            }
            
            set_alert('success', 'La propriété a été ajoutée avec succès.');
            redirect('/omnes-immobilier/admin/properties.php');
        } else {
            $error = 'Une erreur est survenue lors de l\'ajout de la propriété.';
        }
    }
}

// Récupérer la propriété à modifier si nécessaire
$property_to_edit = null;
if ($action === 'edit' && $id > 0) {
    $property_to_edit = $property->getPropertyById($id);
    if (!$property_to_edit) {
        set_alert('danger', 'La propriété demandée n\'existe pas.');
        redirect('/omnes-immobilier/admin/properties.php');
    }
}

// Récupérer toutes les propriétés pour l'affichage
$properties = $property->getAllProperties();

$page_title = $action === 'add' ? "Ajouter une propriété" : ($action === 'edit' ? "Modifier une propriété" : "Gestion des propriétés");
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
                <?php if (!$action) : ?>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="/omnes-immobilier/admin/properties.php?action=add" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus"></i> Ajouter une propriété
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (isset($error)) : ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($action === 'add' || $action === 'edit') : ?>
                <!-- Formulaire d'ajout/modification -->
                <div class="card">
                    <div class="card-body">
                        <form method="post" action="/omnes-immobilier/admin/properties.php?action=<?php echo $action . ($id ? '&id=' . $id : ''); ?>" enctype="multipart/form-data">
                            <div class="row">
                                <!-- Informations de base -->
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            Informations générales
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="titre" class="form-label">Titre *</label>
                                                <input type="text" class="form-control" id="titre" name="titre" required value="<?php echo $property_to_edit ? htmlspecialchars($property_to_edit->titre) : ''; ?>">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description *</label>
                                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo $property_to_edit ? htmlspecialchars($property_to_edit->description) : ''; ?></textarea>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="prix" class="form-label">Prix *</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" id="prix" name="prix" required value="<?php echo $property_to_edit ? $property_to_edit->prix : ''; ?>">
                                                        <span class="input-group-text">€</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="surface" class="form-label">Surface (m²) *</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" id="surface" name="surface" required value="<?php echo $property_to_edit ? $property_to_edit->surface : ''; ?>">
                                                        <span class="input-group-text">m²</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label for="type_propriete" class="form-label">Type de bien *</label>
                                                    <select class="form-select" id="type_propriete" name="type_propriete" required>
                                                        <option value="">Choisir...</option>
                                                        <option value="résidentiel" <?php echo $property_to_edit && $property_to_edit->type_propriete === 'résidentiel' ? 'selected' : ''; ?>>Résidentiel</option>
                                                        <option value="commercial" <?php echo $property_to_edit && $property_to_edit->type_propriete === 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                                                        <option value="terrain" <?php echo $property_to_edit && $property_to_edit->type_propriete === 'terrain' ? 'selected' : ''; ?>>Terrain</option>
                                                        <option value="location" <?php echo $property_to_edit && $property_to_edit->type_propriete === 'location' ? 'selected' : ''; ?>>Location</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="statut" class="form-label">Statut *</label>
                                                    <select class="form-select" id="statut" name="statut" required>
                                                        <option value="disponible" <?php echo $property_to_edit && $property_to_edit->statut === 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                                                        <option value="vendu" <?php echo $property_to_edit && $property_to_edit->statut === 'vendu' ? 'selected' : ''; ?>>Vendu</option>
                                                        <option value="loué" <?php echo $property_to_edit && $property_to_edit->statut === 'loué' ? 'selected' : ''; ?>>Loué</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="id_agent" class="form-label">Agent *</label>
                                                    <select class="form-select" id="id_agent" name="id_agent" required>
                                                        <option value="">Choisir...</option>
                                                        <?php foreach ($agents as $agent_item) : ?>
                                                            <option value="<?php echo $agent_item->id_agent; ?>" <?php echo $property_to_edit && $property_to_edit->id_agent == $agent_item->id_agent ? 'selected' : ''; ?>>
                                                                <?php echo $agent_item->prenom . ' ' . $agent_item->nom; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Caractéristiques et localisation -->
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            Caractéristiques et localisation
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label for="nb_pieces" class="form-label">Pièces *</label>
                                                    <input type="number" class="form-control" id="nb_pieces" name="nb_pieces" required value="<?php echo $property_to_edit ? $property_to_edit->nb_pieces : ''; ?>">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="nb_chambres" class="form-label">Chambres *</label>
                                                    <input type="number" class="form-control" id="nb_chambres" name="nb_chambres" required value="<?php echo $property_to_edit ? $property_to_edit->nb_chambres : ''; ?>">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="nb_salles_bain" class="form-label">Salles de bain *</label>
                                                    <input type="number" class="form-control" id="nb_salles_bain" name="nb_salles_bain" required value="<?php echo $property_to_edit ? $property_to_edit->nb_salles_bain : ''; ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label for="etage" class="form-label">Étage</label>
                                                    <input type="number" class="form-control" id="etage" name="etage" value="<?php echo $property_to_edit && $property_to_edit->etage !== null ? $property_to_edit->etage : ''; ?>">
                                                </div>
                                                <div class="col-md-8 mb-3 d-flex align-items-end">
                                                    <div class="form-check me-3">
                                                        <input class="form-check-input" type="checkbox" id="balcon" name="balcon" <?php echo $property_to_edit && $property_to_edit->balcon ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="balcon">Balcon</label>
                                                    </div>
                                                    <div class="form-check me-3">
                                                        <input class="form-check-input" type="checkbox" id="parking" name="parking" <?php echo $property_to_edit && $property_to_edit->parking ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="parking">Parking</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="ascenseur" name="ascenseur" <?php echo $property_to_edit && $property_to_edit->ascenseur ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="ascenseur">Ascenseur</label>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="adresse" class="form-label">Adresse *</label>
                                                <input type="text" class="form-control" id="adresse" name="adresse" required value="<?php echo $property_to_edit ? htmlspecialchars($property_to_edit->adresse) : ''; ?>">
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="ville" class="form-label">Ville *</label>
                                                    <input type="text" class="form-control" id="ville" name="ville" required value="<?php echo $property_to_edit ? htmlspecialchars($property_to_edit->ville) : ''; ?>">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label for="code_postal" class="form-label">Code postal *</label>
                                                    <input type="text" class="form-control" id="code_postal" name="code_postal" required value="<?php echo $property_to_edit ? htmlspecialchars($property_to_edit->code_postal) : ''; ?>">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label for="pays" class="form-label">Pays *</label>
                                                    <input type="text" class="form-control" id="pays" name="pays" required value="<?php echo $property_to_edit ? htmlspecialchars($property_to_edit->pays) : 'France'; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Photos de la propriété -->
                                <div class="col-12 mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            Photos
                                        </div>
                                        <div class="card-body">
                                            <?php if ($action === 'edit') : ?>
                                                <?php 
                                                $medias = $property->getMedia($id);
                                                if (!empty($medias)) : 
                                                ?>
                                                    <div class="row mb-3">
                                                        <div class="col-12">
                                                            <p>Photos existantes :</p>
                                                            <div class="d-flex flex-wrap">
                                                                <?php foreach ($medias as $media) : ?>
                                                                    <div class="me-2 mb-2 position-relative">
                                                                        <img src="<?php echo $media->url_path; ?>" alt="Photo" class="img-thumbnail" style="width: 150px; height: 100px; object-fit: cover;">
                                                                        <?php if ($media->est_principale) : ?>
                                                                            <span class="badge bg-primary position-absolute top-0 end-0">Principale</span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            
                                            <div class="mb-3">
                                                <label for="images" class="form-label">Ajouter des photos</label>
                                                <input class="form-control" type="file" id="images" name="images[]" multiple accept="image/*">
                                                <div class="form-text">Vous pouvez sélectionner plusieurs photos à la fois. La première photo sera définie comme principale.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="/omnes-immobilier/admin/properties.php" class="btn btn-outline-secondary">Annuler</a>
                                <button type="submit" class="btn btn-primary"><?php echo $action === 'edit' ? 'Mettre à jour' : 'Ajouter'; ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else : ?>
                <!-- Liste des propriétés -->
                <div class="card">
                    <div class="card-body">
                        <?php if (!empty($properties)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Titre</th>
                                            <th>Type</th>
                                            <th>Prix</th>
                                            <th>Surface</th>
                                            <th>Ville</th>
                                            <th>Statut</th>
                                            <th>Agent</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($properties as $prop) : ?>
                                            <tr>
                                                <td><?php echo $prop->id_propriete; ?></td>
                                                <td><?php echo htmlspecialchars($prop->titre); ?></td>
                                                <td><?php echo ucfirst($prop->type_propriete); ?></td>
                                                <td><?php echo format_price($prop->prix); ?></td>
                                                <td><?php echo $prop->surface; ?> m²</td>
                                                <td><?php echo htmlspecialchars($prop->ville); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $prop->statut == 'disponible' ? 'success' : ($prop->statut == 'vendu' ? 'danger' : 'warning'); ?>">
                                                        <?php echo ucfirst($prop->statut); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $agent_info = $agent->getAgentById($prop->id_agent);
                                                    echo $agent_info ? $agent_info->prenom . ' ' . $agent_info->nom : 'N/A';
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="/omnes-immobilier/property-detail.php?id=<?php echo $prop->id_propriete; ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="Voir">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="/omnes-immobilier/admin/properties.php?action=edit&id=<?php echo $prop->id_propriete; ?>" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="/omnes-immobilier/admin/properties.php?action=delete&id=<?php echo $prop->id_propriete; ?>" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette propriété ?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else : ?>
                            <div class="alert alert-info">
                                Aucune propriété disponible pour le moment.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include BASE_PATH . 'admin/includes/footer.php'; ?>