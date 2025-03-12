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
require_once BASE_PATH . 'classes/Agent.php';
require_once BASE_PATH . 'classes/User.php';

// Initialiser les classes
$agent = new Agent();
$user = new User();

// Actions de CRUD
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Traitement de l'action
if ($action === 'delete' && $id > 0) {
    // TODO: Implémenter la suppression
    // Pour l'instant, rediriger simplement avec un message
    set_alert('success', 'L\'agent a été supprimé avec succès.');
    redirect('/omnes-immobilier/admin/agents.php');
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données communes
    $user_data = [
        'nom' => $_POST['nom'],
        'prenom' => $_POST['prenom'],
        'email' => $_POST['email'],
        'telephone' => $_POST['telephone'],
        'type_utilisateur' => 'agent'
    ];
    
    $agent_data = [
        'specialite' => $_POST['specialite'],
        'biographie' => $_POST['biographie']
    ];
    
    if ($action === 'edit' && $id > 0) {
        // TODO: Implémenter la modification
        set_alert('success', 'L\'agent a été mis à jour avec succès.');
        redirect('/omnes-immobilier/admin/agents.php');
    } else {
        // Ajout d'un nouvel agent
        // Générer un mot de passe aléatoire
        $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
        $user_data['mot_de_passe'] = $password;
        
        // Vérifier si l'email existe déjà
        if ($user->findUserByEmail($user_data['email'])) {
            $error = 'Cet email est déjà utilisé. Veuillez en choisir un autre.';
        } else {
            // Créer l'utilisateur
            $user_id = $user->register($user_data);
            
            if ($user_id) {
                // Ajouter les données spécifiques à l'agent
                $agent_data['id_utilisateur'] = $user_id;
                $agent_data['cv_path'] = null;
                $agent_data['photo_path'] = null;
                
                // Traitement de l'upload de la photo de profil
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                    $upload_dir = BASE_PATH . 'assets/uploads/agents/';
                    
                    // Créer le dossier s'il n'existe pas
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                    $file_name = 'agent_' . time() . '.' . $file_ext;
                    $file_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $file_path)) {
                        $agent_data['photo_path'] = '/omnes-immobilier/assets/uploads/agents/' . $file_name;
                    }
                }
                
                // Traitement de l'upload du CV
                if (isset($_FILES['cv']) && $_FILES['cv']['error'] == 0) {
                    $upload_dir = BASE_PATH . 'assets/uploads/cv/';
                    
                    // Créer le dossier s'il n'existe pas
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_ext = pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION);
                    $file_name = 'cv_agent_' . time() . '.' . $file_ext;
                    $file_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['cv']['tmp_name'], $file_path)) {
                        $agent_data['cv_path'] = '/omnes-immobilier/assets/uploads/cv/' . $file_name;
                    }
                }
                
                // Créer l'agent
                $agent_id = $agent->create($agent_data);
                
                if ($agent_id) {
                    // TODO: Envoyer un email à l'agent avec ses identifiants
                    
                    set_alert('success', 'L\'agent a été ajouté avec succès. Mot de passe temporaire : ' . $password);
                    redirect('/omnes-immobilier/admin/agents.php');
                } else {
                    $error = 'Une erreur est survenue lors de la création de l\'agent.';
                }
            } else {
                $error = 'Une erreur est survenue lors de la création de l\'utilisateur.';
            }
        }
    }
}

                                            // Récupérer l'agent à modifier si nécessaire
                                            $agent_to_edit = null;
                                            if ($action === 'edit' && $id > 0) {
                                                $agent_to_edit = $agent->getAgentById($id);
                                                if (!$agent_to_edit) {
                                                    set_alert('danger', 'L\'agent demandé n\'existe pas.');
                                                    redirect('/omnes-immobilier/admin/agents.php');
                                                }
                                            }

// Récupérer tous les agents pour l'affichage
$agents = $agent->getAllAgents();

$page_title = $action === 'add' ? "Ajouter un agent" : ($action === 'edit' ? "Modifier un agent" : "Gestion des agents");
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
                        <a href="/omnes-immobilier/admin/agents.php?action=add" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus"></i> Ajouter un agent
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
                        <form method="post" action="/omnes-immobilier/admin/agents.php?action=<?php echo $action . ($id ? '&id=' . $id : ''); ?>" enctype="multipart/form-data">
                            <div class="row">
                                <!-- Informations de base -->
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            Informations personnelles
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                            <div class="col-md-6 mb-3">
                                                    <label for="nom" class="form-label">Nom *</label>
                                                    <input type="text" class="form-control" id="nom" name="nom" required value="<?php echo $agent_to_edit ? htmlspecialchars($agent_to_edit->nom) : ''; ?>">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="prenom" class="form-label">Prénom *</label>
                                                    <input type="text" class="form-control" id="prenom" name="prenom" required value="<?php echo $agent_to_edit ? htmlspecialchars($agent_to_edit->prenom) : ''; ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email *</label>
                                                <input type="email" class="form-control" id="email" name="email" required value="<?php echo $agent_to_edit ? htmlspecialchars($agent_to_edit->email) : ''; ?>" <?php echo $action === 'edit' ? 'readonly' : ''; ?>>
                                                <?php if ($action === 'edit') : ?>
                                                    <div class="form-text">L'email ne peut pas être modifié.</div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="telephone" class="form-label">Téléphone</label>
                                                <input type="tel" class="form-control" id="telephone" name="telephone" value="<?php echo $agent_to_edit ? htmlspecialchars($agent_to_edit->telephone) : ''; ?>">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="specialite" class="form-label">Spécialité *</label>
                                                <input type="text" class="form-control" id="specialite" name="specialite" required value="<?php echo $agent_to_edit ? htmlspecialchars($agent_to_edit->specialite) : ''; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Informations complémentaires -->
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            Informations complémentaires
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="biographie" class="form-label">Biographie</label>
                                                <textarea class="form-control" id="biographie" name="biographie" rows="5"><?php echo $agent_to_edit ? htmlspecialchars($agent_to_edit->biographie) : ''; ?></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="photo" class="form-label">Photo de profil</label>
                                                <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                                <?php if ($agent_to_edit && $agent_to_edit->photo_path) : ?>
                                                    <div class="mt-2">
                                                        <img src="<?php echo $agent_to_edit->photo_path; ?>" alt="Photo de profil actuelle" class="img-thumbnail" style="max-height: 150px;">
                                                        <div class="form-text">Photo actuelle</div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="cv" class="form-label">CV (PDF)</label>
                                                <input type="file" class="form-control" id="cv" name="cv" accept="application/pdf">
                                                <?php if ($agent_to_edit && $agent_to_edit->cv_path) : ?>
                                                    <div class="mt-2">
                                                        <a href="<?php echo $agent_to_edit->cv_path; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-file-pdf"></i> Voir le CV actuel
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="/omnes-immobilier/admin/agents.php" class="btn btn-outline-secondary">Annuler</a>
                                <button type="submit" class="btn btn-primary"><?php echo $action === 'edit' ? 'Mettre à jour' : 'Ajouter'; ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else : ?>
                <!-- Liste des agents -->
                <div class="card">
                    <div class="card-body">
                        <?php if (!empty($agents)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Photo</th>
                                            <th>Nom</th>
                                            <th>Email</th>
                                            <th>Téléphone</th>
                                            <th>Spécialité</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($agents as $agent_item) : ?>
                                            <tr>
                                                <td><?php echo $agent_item->id_agent; ?></td>
                                                <td>
                                                    <?php if (isset($agent_item->photo_path) && $agent_item->photo_path) : ?>
                                                        <img src="<?php echo $agent_item->photo_path; ?>" alt="Photo" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.src='/omnes-immobilier/assets/img/agents/default.jpg'">
                                                    <?php else : ?>
                                                        <img src="/omnes-immobilier/assets/img/agents/default.jpg" alt="Photo par défaut" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($agent_item->prenom . ' ' . $agent_item->nom); ?></td>
                                                <td><?php echo htmlspecialchars($agent_item->email); ?></td>
                                                <td><?php echo htmlspecialchars($agent_item->telephone); ?></td>
                                                <td><?php echo htmlspecialchars($agent_item->specialite); ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="/omnes-immobilier/agent-profile.php?id=<?php echo $agent_item->id_agent; ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="Voir">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="/omnes-immobilier/admin/agents.php?action=edit&id=<?php echo $agent_item->id_agent; ?>" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="/omnes-immobilier/admin/agents.php?action=delete&id=<?php echo $agent_item->id_agent; ?>" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet agent ?')">
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
                                Aucun agent disponible pour le moment.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include BASE_PATH . 'admin/includes/footer.php'; ?>