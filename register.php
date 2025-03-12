<?php
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
require_once BASE_PATH . 'config/init.php';

// Rediriger l'utilisateur s'il est déjà connecté
if (is_logged_in()) {
    redirect('/omnes-immobilier/index.php');
}

// Si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données du formulaire
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $code_postal = $_POST['code_postal'] ?? '';
    $pays = $_POST['pays'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Vérifier que les champs obligatoires ne sont pas vides
    if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($password_confirm)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } 
    // Vérifier que les mots de passe correspondent
    elseif ($password !== $password_confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } 
    // Vérifier la complexité du mot de passe
    elseif (strlen($password) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères.";
    } 
    else {
        // Vérifier si l'email existe déjà
        $user = new User();
        if ($user->findUserByEmail($email)) {
            $error = "Cet email est déjà utilisé. Veuillez en choisir un autre.";
        } else {
            // Créer l'utilisateur
            $user_data = [
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'telephone' => $telephone,
                'mot_de_passe' => $password,
                'type_utilisateur' => 'client'
            ];
            
            $user_id = $user->register($user_data);
            
            if ($user_id) {
                // Créer le client
                $client = new Client();
                $client_data = [
                    'id_utilisateur' => $user_id,
                    'adresse' => $adresse,
                    'ville' => $ville,
                    'code_postal' => $code_postal,
                    'pays' => $pays
                ];
                
                $client_id = $client->create($client_data);
                // Modifier dans la partie traitement du formulaire (après création réussie du compte)
                if ($client_id) {
                    set_alert('success', 'Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.');
                    // Ajouter une notification pour les services premium
                    $_SESSION['show_premium_services'] = true;
                    redirect('/omnes-immobilier/login.php');
                }
                } else {
                    $error = "Une erreur est survenue lors de la création de votre compte client.";
                }
            } else {
                $error = "Une erreur est survenue lors de la création de votre compte.";
            }
        }
    }
}

$page_title = "Inscription";
include BASE_PATH . 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Créer un compte</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)) : ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="post" action="/omnes-immobilier/register.php">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Nom *</label>
                                <input type="text" class="form-control" id="nom" name="nom" required value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="prenom" class="form-label">Prénom *</label>
                                <input type="text" class="form-control" id="prenom" name="prenom" required value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="telephone" name="telephone" value="<?php echo isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse</label>
                            <input type="text" class="form-control" id="adresse" name="adresse" value="<?php echo isset($_POST['adresse']) ? htmlspecialchars($_POST['adresse']) : ''; ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="ville" class="form-label">Ville</label>
                                <input type="text" class="form-control" id="ville" name="ville" value="<?php echo isset($_POST['ville']) ? htmlspecialchars($_POST['ville']) : ''; ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="code_postal" class="form-label">Code postal</label>
                                <input type="text" class="form-control" id="code_postal" name="code_postal" value="<?php echo isset($_POST['code_postal']) ? htmlspecialchars($_POST['code_postal']) : ''; ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="pays" class="form-label">Pays</label>
                                <input type="text" class="form-control" id="pays" name="pays" value="<?php echo isset($_POST['pays']) ? htmlspecialchars($_POST['pays']) : 'France'; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                        <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Mot de passe *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">Le mot de passe doit contenir au moins 8 caractères.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirm" class="form-label">Confirmer le mot de passe *</label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">S'inscrire</button>
                        </div>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <p>Vous avez déjà un compte ? <a href="/omnes-immobilier/login.php">Se connecter</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>