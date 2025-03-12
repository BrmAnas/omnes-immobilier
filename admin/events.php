<?php
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
require_once BASE_PATH . 'config/init.php';

// Permet de vérifier si l'utilisateur est connecté
if (!is_logged_in()) {
    set_alert('warning', 'Veuillez vous connecter pour accéder à l\'administration.');
    redirect('/omnes-immobilier/login.php');
}

// Permet de si l'utilisateur est un administrateur
if (!is_user_type('admin')) {
    set_alert('danger', 'Vous n\'êtes pas autorisé à accéder à cette page.');
    redirect('/omnes-immobilier/index.php');
}

// Créer une fonction  pour récupérer tous les événements
function getAllEvents() {
    $db = new Database();
    $db->query('SELECT * FROM Evenement ORDER BY date_debut DESC');
    return $db->resultSet();
}

// Créer une fonction  pour récupérer un événement par son ID
function getEventById($id) {
    $db = new Database();
    $db->query('SELECT * FROM Evenement WHERE id_evenement = :id');
    $db->bind(':id', $id);
    return $db->single();
}

// Créer une fonction  pour ajouter un événement
function addEvent($data) {
    $db = new Database();
    $db->query('INSERT INTO Evenement (titre, description, date_debut, date_fin, image_path, actif) 
                VALUES (:titre, :description, :date_debut, :date_fin, :image_path, :actif)');
    
    $db->bind(':titre', $data['titre']);
    $db->bind(':description', $data['description']);
    $db->bind(':date_debut', $data['date_debut']);
    $db->bind(':date_fin', $data['date_fin']);
    $db->bind(':image_path', $data['image_path']);
    $db->bind(':actif', $data['actif']);
    
    if ($db->execute()) {
        return $db->lastInsertId();
    } else {
        return false;
    }
}

// Créer une fonction  pour mettre à jour un événement
function updateEvent($data) {
    $db = new Database();
    $db->query('UPDATE Evenement 
                SET titre = :titre, description = :description, date_debut = :date_debut, 
                date_fin = :date_fin, image_path = :image_path, actif = :actif 
                WHERE id_evenement = :id');
    
    $db->bind(':id', $data['id_evenement']);
    $db->bind(':titre', $data['titre']);
    $db->bind(':description', $data['description']);
    $db->bind(':date_debut', $data['date_debut']);
    $db->bind(':date_fin', $data['date_fin']);
    $db->bind(':image_path', $data['image_path']);
    $db->bind(':actif', $data['actif']);
    
    return $db->execute();
}

// Créer une fonction temporaire pour supprimer un événement
function deleteEvent($id) {
    $db = new Database();
    $db->query('DELETE FROM Evenement WHERE id_evenement = :id');
    $db->bind(':id', $id);
    return $db->execute();
}

// Actions de CRUD
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Traitement de l'action de suppression
if ($action === 'delete' && $id > 0) {
    if (deleteEvent($id)) {
        set_alert('success', 'L\'événement a été supprimé avec succès.');
    } else {
        set_alert('danger', 'Une erreur est survenue lors de la suppression de l\'événement.');
    }
    redirect('/omnes-immobilier/admin/events.php');
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $event_data = [
        'titre' => $_POST['titre'],
        'description' => $_POST['description'],
        'date_debut' => $_POST['date_debut'],
        'date_fin' => $_POST['date_fin'],
        'actif' => isset($_POST['actif']) ? 1 : 0,
        'image_path' => null
    ];
    
    // Si un ID est présent, on l'ajoute pour la mise à jour
    if ($action === 'edit' && $id > 0) {
        $event_data['id_evenement'] = $id;
        
        // Récupérer l'événement existant pour son image
        $event = getEventById($id);
        if ($event) {
            $event_data['image_path'] = $event->image_path;
        }
    }
    
    // Traitement de l'upload d'image
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = BASE_PATH . 'assets/uploads/events/';
        
        // Créer le dossier s'il n'existe pas
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = 'event_' . time() . '.' . $file_ext;
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
            $event_data['image_path'] = '/omnes-immobilier/assets/uploads/events/' . $file_name;
        }
    }
    
    if ($action === 'edit' && $id > 0) {
        // Mise à jour d'un événement existant
        if (updateEvent($event_data)) {
            set_alert('success', 'L\'événement a été mis à jour avec succès.');
            redirect('/omnes-immobilier/admin/events.php');
        } else {
            $error = 'Une erreur est survenue lors de la mise à jour de l\'événement.';
        }
    } else {
        // Ajout d'un nouvel événement
        $event_id = addEvent($event_data);
        if ($event_id) {
            set_alert('success', 'L\'événement a été ajouté avec succès.');
            redirect('/omnes-immobilier/admin/events.php');
        } else {
            $error = 'Une erreur est survenue lors de l\'ajout de l\'événement.';
        }
    }
}

// Récupérer l'événement à modifier si nécessaire
$event_to_edit = null;
if ($action === 'edit' && $id > 0) {
    $event_to_edit = getEventById($id);
    if (!$event_to_edit) {
        set_alert('danger', 'L\'événement demandé n\'existe pas.');
        redirect('/omnes-immobilier/admin/events.php');
    }
}

// Récupérer tous les événements pour l'affichage
$events = getAllEvents();

$page_title = $action === 'add' ? "Ajouter un événement" : ($action === 'edit' ? "Modifier un événement" : "Gestion des événements");
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
                        <a href="/omnes-immobilier/admin/events.php?action=add" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus"></i> Ajouter un événement
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
                        <form method="post" action="/omnes-immobilier/admin/events.php?action=<?php echo $action . ($id ? '&id=' . $id : ''); ?>" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="titre" class="form-label">Titre *</label>
                                    <input type="text" class="form-control" id="titre" name="titre" required value="<?php echo $event_to_edit ? htmlspecialchars($event_to_edit->titre) : ''; ?>">
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label for="date_debut" class="form-label">Date de début *</label>
                                    <input type="datetime-local" class="form-control" id="date_debut" name="date_debut" required value="<?php echo $event_to_edit ? date('Y-m-d\TH:i', strtotime($event_to_edit->date_debut)) : ''; ?>">
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label for="date_fin" class="form-label">Date de fin *</label>
                                    <input type="datetime-local" class="form-control" id="date_fin" name="date_fin" required value="<?php echo $event_to_edit ? date('Y-m-d\TH:i', strtotime($event_to_edit->date_fin)) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="5" required><?php echo $event_to_edit ? htmlspecialchars($event_to_edit->description) : ''; ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image" class="form-label">Image de l'événement</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <?php if ($event_to_edit && $event_to_edit->image_path) : ?>
                                    <div class="mt-2">
                                        <img src="<?php echo $event_to_edit->image_path; ?>" alt="Image de l'événement" class="img-thumbnail" style="max-height: 200px;">
                                        <div class="form-text">Image actuelle</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="actif" name="actif" <?php echo $event_to_edit && $event_to_edit->actif ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="actif">Événement actif</label>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="/omnes-immobilier/admin/events.php" class="btn btn-outline-secondary">Annuler</a>
                                <button type="submit" class="btn btn-primary"><?php echo $action === 'edit' ? 'Mettre à jour' : 'Ajouter'; ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else : ?>
                <!-- Liste des événements -->
                <div class="card">
                    <div class="card-body">
                        <?php if (!empty($events)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Titre</th>
                                            <th>Début</th>
                                            <th>Fin</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($events as $event) : ?>
                                            <tr>
                                                <td><?php echo $event->id_evenement; ?></td>
                                                <td><?php echo htmlspecialchars($event->titre); ?></td>
                                                <td><?php echo format_date(date('Y-m-d', strtotime($event->date_debut))) . ' ' . format_time(date('H:i:s', strtotime($event->date_debut))); ?></td>
                                                <td><?php echo format_date(date('Y-m-d', strtotime($event->date_fin))) . ' ' . format_time(date('H:i:s', strtotime($event->date_fin))); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $event->actif ? 'success' : 'secondary'; ?>">
                                                        <?php echo $event->actif ? 'Actif' : 'Inactif'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#eventModal<?php echo $event->id_evenement; ?>" title="Voir détails">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <a href="/omnes-immobilier/admin/events.php?action=edit&id=<?php echo $event->id_evenement; ?>" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="/omnes-immobilier/admin/events.php?action=delete&id=<?php echo $event->id_evenement; ?>" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                    
                                                    <!-- Modal pour les détails de l'événement -->
                                                    <div class="modal fade" id="eventModal<?php echo $event->id_evenement; ?>" tabindex="-1" aria-labelledby="eventModalLabel<?php echo $event->id_evenement; ?>" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="eventModalLabel<?php echo $event->id_evenement; ?>">
                                                                        <?php echo htmlspecialchars($event->titre); ?>
                                                                    </h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <p><strong>Date de début:</strong> <?php echo format_date(date('Y-m-d', strtotime($event->date_debut))) . ' ' . format_time(date('H:i:s', strtotime($event->date_debut))); ?></p>
                                                                            <p><strong>Date de fin:</strong> <?php echo format_date(date('Y-m-d', strtotime($event->date_fin))) . ' ' . format_time(date('H:i:s', strtotime($event->date_fin))); ?></p>
                                                                            <p><strong>Statut:</strong> 
                                                                                <span class="badge bg-<?php echo $event->actif ? 'success' : 'secondary'; ?>">
                                                                                    <?php echo $event->actif ? 'Actif' : 'Inactif'; ?>
                                                                                </span>
                                                                            </p>
                                                                            <div>
                                                                                <strong>Description:</strong>
                                                                                <p><?php echo nl2br(htmlspecialchars($event->description)); ?></p>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <?php if ($event->image_path) : ?>
                                                                                <img src="<?php echo $event->image_path; ?>" alt="Image de l'événement" class="img-fluid">
                                                                            <?php else : ?>
                                                                                <div class="alert alert-info">
                                                                                    Aucune image pour cet événement.
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                                    <a href="/omnes-immobilier/admin/events.php?action=edit&id=<?php echo $event->id_evenement; ?>" class="btn btn-primary">Modifier</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else : ?>
                            <div class="alert alert-info">
                                Aucun événement disponible pour le moment.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include BASE_PATH . 'admin/includes/footer.php'; ?>