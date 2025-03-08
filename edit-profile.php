<?php
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
require_once BASE_PATH . 'config/init.php';

// Vérifier si l'utilisateur est connecté
if (!is_logged_in()) {
    set_alert('warning', 'Veuillez vous connecter pour modifier votre profil.');
    redirect('/omnes-immobilier/login.php');
}

// Inclure les classes nécessaires
require_once BASE_PATH . 'classes/User.php';

// Initialiser la classe User
$user = new User();

// Récupérer les infos de l'utilisateur
$user_info = $user->getUserById($_SESSION['user_id']);

// Déterminer le type d'utilisateur et inclure les classes appropriées
if ($user_info->type_utilisateur == 'client') {
    require_once BASE_PATH . 'classes/Client.php';
    $client = new Client();
    $client_info = $client->getClientByUserId($_SESSION['user_id']);
} elseif ($user_info->type_utilisateur == 'agent') {
    require_once BASE_PATH . 'classes/Agent.php';
    $agent = new Agent();
    $agent_info = $agent->getAgentByUserId($_SESSION['user_id']);
}

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Données communes à tous les utilisateurs
    $user_data = [
        'id_utilisateur' => $_SESSION['user_id'],
        'nom' => $_POST['nom'],
        'prenom' => $_POST['prenom'],
        'telephone' => $_POST['telephone']
    ];
    
    // Mise à jour des informations de l'utilisateur
    if ($user->updateUser($user_data)) {
        
        // Si l'utilisateur est un client, mettre à jour ses informations spécifiques
        if ($user_info->type_utilisateur == 'client' && isset($client_info)) {
            $client_data = [
                'id_client' => $client_info->id_client,
                'adresse' => $_POST['adresse'],
                'ville' => $_POST['ville'],
                'code_postal' => $_POST['code_postal'],
                'pays' => $_POST['pays']
            ];
            
            if ($client->update($client_data)) {
                set_alert('success', 'Votre profil a été mis à jour avec succès.');
            } else {
                set_alert('danger', 'Une erreur est survenue lors de la mise à jour de vos informations client.');
            }
        }
        // Si l'utilisateur est un agent, mettre à jour ses informations spécifiques
        elseif ($user_info->type_utilisateur == 'agent' && isset($agent_info)) {
            $agent_data = [
                'id_agent' => $agent_info->id_agent,
                'specialite' => $_POST['specialite'],
                'biographie' => $_POST['biographie'],
                'cv_path' => $agent_info->cv_path, // Conserver la valeur existante
                'photo_path' => $agent_info->photo_path // Conserver la valeur existante
            ];
            
            // Traitement de l'upload de la photo de profil
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                $upload_dir = BASE_PATH . 'assets/uploads/agents/';
                
                // Créer le dossier s'il n'existe pas
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $file_name = 'agent_' . $agent_info->id_agent . '_' . time() . '.' . $file_ext;
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
                $file_name = 'cv_agent_' . $agent_info->id_agent . '_' . time() . '.' . $file_ext;
                $file_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['cv']['tmp_name'], $file_path)) {
                    $agent_data['cv_path'] = '/omnes-immobilier/assets/uploads/cv/' . $file_name;
                }
            }
            
            if ($agent->update($agent_data)) {
                set_alert('success', 'Votre profil a été mis à jour avec succès.');
            } else {
                set_alert('danger', 'Une erreur est survenue lors de la mise à jour de vos informations agent.');
            }
        }
        else {
            set_alert('success', 'Votre profil a été mis à jour avec succès.');
        }
        
        // Mise à jour du mot de passe si demandé
        if (!empty($_POST['password']) && !empty($_POST['password_confirm'])) {
            if ($_POST['password'] === $_POST['password_confirm']) {
                if (strlen($_POST['password']) >= 8) {
                    if ($user->updatePassword($_SESSION['user_id'], $_POST['password'])) {
                        set_alert('success', 'Votre mot de passe a été mis à jour avec succès.');
                    } else {
                        set_alert('danger', 'Une erreur est survenue lors de la mise à jour de votre mot de passe.');
                    }
                } else {
                    set_alert('danger', 'Le mot de passe doit contenir au moins 8 caractères.');
                }
            } else {
                set_alert('danger', 'Les mots de passe ne correspondent pas.');
            }
        }
        
        // Rediriger vers la page du compte
        redirect('/omnes-immobilier/account.php');
    } else {
        set_alert('danger', 'Une erreur est survenue lors de la mise à jour de votre profil.');
    }
}

$page_title = "Modifier mon profil";
include BASE_PATH . 'includes/header.php';
?>

<div class="container mt-4">
    <h1 class="section-title mb-4" data-aos="fade-up">Modifier mon profil</h1>
    
    <div class="card" data-aos="fade-up">
        <div class="card-header bg-primary text-white">
            Informations personnelles
        </div>
        <div class="card-body">
            <form method="post" action="/omnes-immobilier/edit-profile.php" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nom" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($user_info->nom); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="prenom" class="form-label">Prénom</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user_info->prenom); ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user_info->email); ?>" disabled>
                    <div class="form-text">L'adresse email ne peut pas être modifiée.</div>
                </div>
                
                <div class="mb-3">
                    <label for="telephone" class="form-label">Téléphone</label>
                    <input type="tel" class="form-control" id="telephone" name="telephone" value="<?php echo htmlspecialchars($user_info->telephone ?? ''); ?>">
                </div>
                
                <?php if ($user_info->type_utilisateur == 'client' && isset($client_info)) : ?>
                    <div class="mb-3">
                        <label for="adresse" class="form-label">Adresse</label>
                        <input type="text" class="form-control" id="adresse" name="adresse" value="<?php echo htmlspecialchars($client_info->adresse ?? ''); ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="ville" class="form-label">Ville</label>
                            <input type="text" class="form-control" id="ville" name="ville" value="<?php echo htmlspecialchars($client_info->ville ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="code_postal" class="form-label">Code postal</label>
                            <input type="text" class="form-control" id="code_postal" name="code_postal" value="<?php echo htmlspecialchars($client_info->code_postal ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="pays" class="form-label">Pays</label>
                            <input type="text" class="form-control" id="pays" name="pays" value="<?php echo htmlspecialchars($client_info->pays ?? 'France'); ?>">
                        </div>
                    </div>
                <?php elseif ($user_info->type_utilisateur == 'agent' && isset($agent_info)) : ?>
                    <div class="mb-3">
                        <label for="specialite" class="form-label">Spécialité</label>
                        <input type="text" class="form-control" id="specialite" name="specialite" value="<?php echo htmlspecialchars($agent_info->specialite ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="biographie" class="form-label">Biographie</label>
                        <textarea class="form-control" id="biographie" name="biographie" rows="4"><?php echo htmlspecialchars($agent_info->biographie ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="photo" class="form-label">Photo de profil</label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                            <?php if (isset($agent_info->photo_path) && $agent_info->photo_path) : ?>
                                <div class="mt-2">
                                    <img src="<?php echo $agent_info->photo_path; ?>" alt="Photo actuelle" class="img-thumbnail" style="max-height: 100px;">
                                    <div class="form-text">Photo actuelle</div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cv" class="form-label">CV (PDF)</label>
                            <input type="file" class="form-control" id="cv" name="cv" accept="application/pdf">
                            <?php if (isset($agent_info->cv_path) && $agent_info->cv_path) : ?>
                                <div class="mt-2">
                                    <a href="<?php echo $agent_info->cv_path; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-file-pdf"></i> Voir le CV actuel
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <hr class="my-4">
                
                <h5>Changer de mot de passe</h5>
                <div class="form-text mb-3">Laissez ces champs vides si vous ne souhaitez pas modifier votre mot de passe.</div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <div class="form-text">Le mot de passe doit contenir au moins 8 caractères.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password_confirm" class="form-label">Confirmer le nouveau mot de passe</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm">
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="/omnes-immobilier/account.php" class="btn btn-outline-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>