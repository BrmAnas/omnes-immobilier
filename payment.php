<?php
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
require_once BASE_PATH . 'config/init.php';

// Vérifier si l'utilisateur est connecté
if (!is_logged_in()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    set_alert('warning', 'Veuillez vous connecter pour accéder au paiement.');
    redirect('/omnes-immobilier/login.php');
}

// Inclure les classes nécessaires
require_once BASE_PATH . 'classes/Client.php';
require_once BASE_PATH . 'classes/Payment.php';

// Initialiser les classes
$client = new Client();
$payment = new Payment();

// Vérifier si l'utilisateur est un client
$user_client = $client->getClientByUserId($_SESSION['user_id']);
if (!$user_client) {
    set_alert('danger', 'Vous devez être un client pour effectuer un paiement.');
    redirect('/omnes-immobilier/index.php');
}

// Récupérer le service et le montant depuis les paramètres
$service_id = isset($_GET['service']) ? intval($_GET['service']) : 0;
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;
$type_service = isset($_GET['type']) ? $_GET['type'] : '';

// Vérifier que le service et le montant sont valides
if ($service_id <= 0 || $amount <= 0 || empty($type_service)) {
    set_alert('danger', 'Service ou montant invalide.');
    redirect('/omnes-immobilier/index.php');
}

// Liste des services disponibles (à adapter selon vos besoins)
$services = [
    1 => [
        'name' => 'Frais d\'agence immobilière',
        'description' => 'Frais pour les services d\'agence dans la recherche et la transaction immobilière.'
    ],
    2 => [
        'name' => 'Frais de dossier',
        'description' => 'Frais administratifs pour la constitution et le suivi de votre dossier.'
    ],
    3 => [
        'name' => 'Service de recherche personnalisée',
        'description' => 'Service sur mesure pour trouver le bien qui correspond exactement à vos critères.'
    ]
];

// Vérifier que le service existe
if (!isset($services[$service_id])) {
    set_alert('danger', 'Service invalide.');
    redirect('/omnes-immobilier/index.php');
}

$service = $services[$service_id];

// Traitement du formulaire de paiement
$payment_success = false;
$payment_id = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des données
    $errors = [];

    // Validation des informations de carte
    if (empty($_POST['card_type'])) {
        $errors[] = 'Veuillez sélectionner un type de carte.';
    }

    if (empty($_POST['card_number']) || !preg_match('/^\d{13,19}$/', $_POST['card_number'])) {
        $errors[] = 'Numéro de carte invalide.';
    }

    if (empty($_POST['card_name'])) {
        $errors[] = 'Veuillez indiquer le nom sur la carte.';
    }

    if (empty($_POST['card_expiry']) || !preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2}|[0-9]{4})$/', $_POST['card_expiry'])) {
        $errors[] = 'Date d\'expiration invalide (format MM/YY ou MM/YYYY).';
    }

    if (empty($_POST['card_cvv']) || !preg_match('/^\d{3,4}$/', $_POST['card_cvv'])) {
        $errors[] = 'Code de sécurité invalide.';
    }

    // Si pas d'erreurs, procéder au paiement
    if (empty($errors)) {
        $card_data = [
            'type_carte' => $_POST['card_type'],
            'numero_carte' => $_POST['card_number'],
            'nom_carte' => $_POST['card_name'],
            'date_expiration' => $_POST['card_expiry'],
            'code_securite' => $_POST['card_cvv']
        ];

        // Traiter le paiement
        if ($payment->processPayment($card_data, $amount)) {
            // Créer l'enregistrement de paiement
            $payment_data = [
                'id_client' => $user_client->id_client,
                'montant' => $amount,
                'type_service' => $type_service,
                'type_carte' => $card_data['type_carte'],
                'numero_carte' => $card_data['numero_carte'],
                'nom_carte' => $card_data['nom_carte'],
                'date_expiration' => $card_data['date_expiration'],
                'code_securite' => $card_data['code_securite'],
                'status' => 'completed'
            ];

            $payment_id = $payment->create($payment_data);

            if ($payment_id) {
                $payment_success = true;
                
                // On pourrait ici envoyer un email de confirmation
                // send_payment_confirmation_email($user_client->email, $payment_id, $amount, $service['name']);
                
                // Rediriger vers la page de confirmation après un court délai
                header("refresh:5;url=/omnes-immobilier/payment-confirmation.php?id=" . $payment_id);
            } else {
                $error = 'Une erreur est survenue lors de l\'enregistrement du paiement.';
            }
        } else {
            $error = 'Le paiement a échoué. Veuillez vérifier vos informations et réessayer.';
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

$page_title = "Paiement";
include BASE_PATH . 'includes/header.php';
?>

<div class="container mt-4">
    <h1 class="section-title mb-4" data-aos="fade-up">Paiement</h1>
    
    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($payment_success) : ?>
        <div class="alert alert-success">
            <h4 class="alert-heading">Paiement effectué avec succès!</h4>
            <p>Votre paiement de <?php echo format_price($amount); ?> pour <?php echo htmlspecialchars($service['name']); ?> a été traité avec succès.</p>
            <p>Un email de confirmation vous a été envoyé à l'adresse <?php echo htmlspecialchars($user_client->email); ?>.</p>
            <p>Vous allez être redirigé vers la page de confirmation...</p>
        </div>
    <?php else : ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4" data-aos="fade-up">
                    <div class="card-header bg-primary text-white">
                        Informations de paiement
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h5>Détails du service</h5>
                                    <p><strong>Service :</strong> <?php echo htmlspecialchars($service['name']); ?></p>
                                    <p><strong>Description :</strong> <?php echo htmlspecialchars($service['description']); ?></p>
                                    <p><strong>Montant :</strong> <?php echo format_price($amount); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h5>Informations client</h5>
                                    <p><strong>Nom :</strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                                    <p><strong>Email :</strong> <?php echo htmlspecialchars($user_client->email); ?></p>
                                    <p><strong>Adresse :</strong> <?php echo htmlspecialchars($user_client->adresse); ?></p>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h5>Informations de carte</h5>
                            <div class="mb-3">
                                <label for="card_type" class="form-label">Type de carte</label>
                                <select class="form-select" id="card_type" name="card_type" required>
                                    <option value="">Sélectionnez un type de carte</option>
                                    <option value="visa">Visa</option>
                                    <option value="mastercard">MasterCard</option>
                                    <option value="amex">American Express</option>
                                    <option value="paypal">PayPal</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="card_number" class="form-label">Numéro de carte</label>
                                <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="card_name" class="form-label">Nom sur la carte</label>
                                <input type="text" class="form-control" id="card_name" name="card_name" placeholder="JOHN DOE" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="card_expiry" class="form-label">Date d'expiration</label>
                                    <input type="text" class="form-control" id="card_expiry" name="card_expiry" placeholder="MM/YY" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="card_cvv" class="form-label">Code de sécurité (CVV)</label>
                                    <input type="text" class="form-control" id="card_cvv" name="card_cvv" placeholder="123" required>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="save_card" name="save_card">
                                <label class="form-check-label" for="save_card">Sauvegarder cette carte pour de futurs paiements</label>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Payer <?php echo format_price($amount); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card" data-aos="fade-up">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Résumé de la commande</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span><?php echo htmlspecialchars($service['name']); ?></span>
                            <span><?php echo format_price($amount); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <strong>Total</strong>
                            <strong><?php echo format_price($amount); ?></strong>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-lock me-2"></i> Vos données de paiement sont sécurisées et chiffrées.
                        </div>
                        
                        <div class="text-center mt-3">
                            <img src="/omnes-immobilier/assets/img/payment-methods.png" alt="Méthodes de paiement" class="img-fluid" style="max-height: 40px;">
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3" data-aos="fade-up">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Besoin d'aide ?</h5>
                    </div>
                    <div class="card-body">
                        <p><i class="fas fa-phone me-2"></i> +33 1 23 45 67 89</p>
                        <p><i class="fas fa-envelope me-2"></i> contact@omnesimmobilier.fr</p>
                        <p class="small text-muted">Nos conseillers sont disponibles du lundi au vendredi de 9h à 18h.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Formatage du numéro de carte
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 16) {
                value = value.slice(0, 16);
            }
            // Ajouter des espaces tous les 4 chiffres
            let formattedValue = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }
            e.target.value = formattedValue;
        });
    }
    
    // Formatage de la date d'expiration
    const expiryInput = document.getElementById('card_expiry');
    if (expiryInput) {
        expiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 4) {
                value = value.slice(0, 4);
            }
            // Format MM/YY
            if (value.length > 2) {
                value = value.slice(0, 2) + '/' + value.slice(2);
            }
            e.target.value = value;
        });
    }
    
    // Formatage du CVV
    const cvvInput = document.getElementById('card_cvv');
    if (cvvInput) {
        cvvInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 4) {
                value = value.slice(0, 4);
            }
            e.target.value = value;
        });
    }
});
</script>

<?php include BASE_PATH . 'includes/footer.php'; ?>