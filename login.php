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
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Vérifier que les champs ne sont pas vides
    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        // Tenter de connecter l'utilisateur
        $user = new User();
        $logged_user = $user->login($email, $password);
        
        if ($logged_user) {
            // Stocker les infos de l'utilisateur en session
            $_SESSION['user_id'] = $logged_user->id_utilisateur;
            $_SESSION['user_type'] = $logged_user->type_utilisateur;
            $_SESSION['user_name'] = $logged_user->prenom . ' ' . $logged_user->nom;
            
            // Rediriger vers la page précédente ou la page d'accueil
            $redirect_url = $_SESSION['redirect_url'] ?? '/omnes-immobilier/index.php';
            unset($_SESSION['redirect_url']);
            redirect($redirect_url);
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    }
}

$page_title = "Connexion";
include BASE_PATH . 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Connexion</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)) : ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="post" action="/omnes-immobilier/login.php">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Se connecter</button>
                        </div>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <p>Vous n'avez pas de compte ? <a href="/omnes-immobilier/register.php">Créer un compte</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>