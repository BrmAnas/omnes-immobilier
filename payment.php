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

// Récupérer les infos du client
$client_info = $payment->getClientDetails($_SESSION['user_id']);
if (!$client_info) {
    set_alert('danger', 'Impossible de récupérer vos informations. Veuillez réessayer.');
    redirect('/omnes-immobilier/account.php');
}

// Vérifier si on a un service et un montant à payer
$service_type = isset($_GET['service']) ? $_GET['service'] : null;
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : null;

// Si pas de service ou montant spécifié, vérifier dans la session
if (!$service_type || !$amount) {
    if (isset($_SESSION['payment_service']) && isset($_SESSION['payment_amount'])) {
        $service_type = $_SESSION['payment_service'];
        $amount = $_SESSION['payment_amount'];
    } else {
        set_alert('danger', 'Aucun service ou montant spécifié pour le paiement.');
        redirect('/omnes-immobilier/index.php');
    }
}

// Vérifier si un code de réduction a été appliqué
$discount_code = isset($_POST['discount_code']) ? trim($_POST['discount_code']) : null;
$original_amount = $amount;

if ($discount_code) {
    $discounted_amount = $payment->applyDiscount($amount, $discount_code);
    if ($discounted_amount < $amount) {
        $amount = $discounted_amount;
        set_alert('success', 'Code de réduction appliqué avec succès !');
    } else {
        set_alert('warning', 'Code de réduction invalide ou expiré.');
    }
}

// Traitement du paiement
$payment_success = false;
if (isset($_POST['submit_payment'])) {
    $payment_method = $_POST['payment_method'];
    
    // Préparation des données client
// Préparation des données client
// Préparation des données client
$client_data = [
    'id_client' => $client_info->id_client ?? null,
    'email' => $_POST['email'] ?? $client_info->email ?? '',
    'nom' => $_POST['nom'] ?? $client_info->nom ?? '',
    'prenom' => $_POST['prenom'] ?? $client_info->prenom ?? '',
    'adresse' => $_POST['adresse'] ?? $client_info->adresse ?? '',
    'ville' => $_POST['ville'] ?? $client_info->ville ?? '',
    'code_postal' => $_POST['code_postal'] ?? $client_info->code_postal ?? '',
    'pays' => $_POST['pays'] ?? $client_info->pays ?? 'France',
    'telephone' => $_POST['telephone'] ?? $client_info->telephone ?? ''
];
    
    // Mettre à jour les informations du client si elles ont changé
    if (
        $client_data['adresse'] !== ($client_info->adresse ?? '') || 
        $client_data['ville'] !== ($client_info->ville ?? '') || 
        $client_data['code_postal'] !== ($client_info->code_postal ?? '') || 
        $client_data['pays'] !== ($client_info->pays ?? 'France')
    ) {
        $client->update([
            'id_client' => $client_info->id_client,
            'adresse' => $client_data['adresse'],
            'ville' => $client_data['ville'],
            'code_postal' => $client_data['code_postal'],
            'pays' => $client_data['pays']
        ]);
    }
    
    // Traitement selon le mode de paiement
    if ($payment_method === 'card') {
        // Paiement par carte
        $payment_data = [
            'card_type' => $_POST['card_type'],
            'card_number' => $_POST['card_number'],
            'card_name' => $_POST['card_name'],
            'expiry_month' => $_POST['expiry_month'],
            'expiry_year' => $_POST['expiry_year'],
            'cvv' => $_POST['cvv']
        ];
        
        $transaction_id = $payment->processCardPayment($payment_data, $client_data, $amount, $service_type);
        
        if ($transaction_id) {
            $payment_success = true;
            $_SESSION['transaction_id'] = $transaction_id;
        } else {
            set_alert('danger', 'Une erreur est survenue lors du traitement du paiement par carte.');
        }
    } 
    elseif ($payment_method === 'voucher') {
        // Paiement par chèque-cadeau
        $voucher_code = $_POST['voucher_code'];
        
        $transaction_id = $payment->processVoucherPayment($voucher_code, $client_data, $amount, $service_type);
        
        if ($transaction_id) {
            $payment_success = true;
            $_SESSION['transaction_id'] = $transaction_id;
        } else {
            set_alert('danger', 'Le chèque-cadeau est invalide ou insuffisant pour ce paiement.');
        }
    }
    
    // Redirection en cas de succès
    if ($payment_success) {
        // Nettoyer les variables de session liées au paiement
        unset($_SESSION['payment_service']);
        unset($_SESSION['payment_amount']);
        
        redirect('/omnes-immobilier/payment-confirmation.php');
    }
}

$page_title = "Paiement";
include BASE_PATH . 'includes/header.php';
?>

<div class="container mt-4">
    <h1 class="section-title mb-4" data-aos="fade-up">Paiement</h1>
    
    <div class="row">
        <div class="col-md-8">
            <!-- Formulaire de paiement -->
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informations de paiement</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="/omnes-immobilier/payment.php?service=<?php echo urlencode($service_type); ?>&amount=<?php echo $amount; ?>">
                        <!-- Coordonnées du client -->
                        <h4 class="mb-3">Vos coordonnées</h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Nom *</label>
                                <input type="text" class="form-control" id="nom" name="nom" 
                                       value="<?php echo htmlspecialchars($client_info->nom ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="prenom" class="form-label">Prénom *</label>
                                <input type="text" class="form-control" id="prenom" name="prenom" 
                                       value="<?php echo htmlspecialchars($client_info->prenom ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($client_info->email ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="telephone" class="form-label">Téléphone *</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" 
                                   value="<?php echo htmlspecialchars($client_info->telephone ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse *</label>
                            <input type="text" class="form-control" id="adresse" name="adresse" 
                                   value="<?php echo htmlspecialchars($client_info->adresse ?? ''); ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ville" class="form-label">Ville *</label>
                                <input type="text" class="form-control" id="ville" name="ville" 
                                       value="<?php echo htmlspecialchars($client_info->ville ?? ''); ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="code_postal" class="form-label">Code postal *</label>
                                <input type="text" class="form-control" id="code_postal" name="code_postal" 
                                       value="<?php echo htmlspecialchars($client_info->code_postal ?? ''); ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="pays" class="form-label">Pays *</label>
                                <input type="text" class="form-control" id="pays" name="pays" 
                                       value="<?php echo htmlspecialchars($client_info->pays ?? 'France'); ?>" required>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Méthode de paiement -->
                        <h4 class="mb-3">Méthode de paiement</h4>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment_card" value="card" checked onclick="togglePaymentMethod('card')">
                            <label class="form-check-label" for="payment_card">
                                Carte de crédit
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment_voucher" value="voucher" onclick="togglePaymentMethod('voucher')">
                            <label class="form-check-label" for="payment_voucher">
                                Chèque-cadeau Omnes Immobilier
                            </label>
                        </div>
                        
                        <!-- Détails carte de crédit -->
                        <div id="card_details">
                            <div class="mb-3">
                                <label for="card_type" class="form-label">Type de carte *</label>
                                <select class="form-select" id="card_type" name="card_type" required>
                                    <option value="visa">Visa</option>
                                    <option value="mastercard">MasterCard</option>
                                    <option value="amex">American Express</option>
                                    <option value="paypal">PayPal</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="card_number" class="form-label">Numéro de carte *</label>
                                <input type="text" class="form-control" id="card_number" name="card_number" placeholder="XXXX XXXX XXXX XXXX" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="card_name" class="form-label">Nom sur la carte *</label>
                                <input type="text" class="form-control" id="card_name" name="card_name" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="expiry_month" class="form-label">Mois d'expiration *</label>
                                    <select class="form-select" id="expiry_month" name="expiry_month" required>
                                        <?php for ($i = 1; $i <= 12; $i++) : ?>
                                            <option value="<?php echo sprintf('%02d', $i); ?>"><?php echo sprintf('%02d', $i); ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="expiry_year" class="form-label">Année d'expiration *</label>
                                    <select class="form-select" id="expiry_year" name="expiry_year" required>
                                        <?php $current_year = date('Y'); ?>
                                        <?php for ($i = $current_year; $i <= $current_year + 10; $i++) : ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="cvv" class="form-label">CVV *</label>
                                    <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Détails chèque-cadeau -->
                        <div id="voucher_details" style="display: none;">
                            <div class="mb-3">
                                <label for="voucher_code" class="form-label">Code du chèque-cadeau *</label>
                                <input type="text" class="form-control" id="voucher_code" name="voucher_code" placeholder="XXXX-XXXX-XXXX-XXXX">
                                <div class="form-text">Entrez le code du chèque-cadeau Omnes Immobilier que vous avez reçu.</div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Code de réduction -->
                        <div class="mb-3">
                            <label for="discount_code" class="form-label">Code de réduction</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="discount_code" name="discount_code" placeholder="Entrez votre code de réduction">
                                <button class="btn btn-outline-secondary" type="submit" name="apply_discount">Appliquer</button>
                            </div>
                            <div class="form-text">Si vous avez un code de réduction, entrez-le ici.</div>
                        </div>
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" name="submit_payment" class="btn btn-primary btn-lg">Payer <?php echo format_price($amount); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Récapitulatif de la commande -->
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Récapitulatif</h5>
                </div>
                <div class="card-body">
                    <p><strong>Service :</strong> <?php echo htmlspecialchars($service_type); ?></p>
                    
                    <?php if ($amount < $original_amount) : ?>
                        <p><strong>Montant initial :</strong> <s><?php echo format_price($original_amount); ?></s></p>
                        <p><strong>Réduction appliquée :</strong> <?php echo format_price($original_amount - $amount); ?></p>
                    <?php endif; ?>
                    
                    <h4 class="mt-3">Total à payer: <?php echo format_price($amount); ?></h4>
                    
                    <hr>
                    
                    <div class="alert alert-info">
                        <h6>Paiement sécurisé</h6>
                        <p class="mb-0"><small>Toutes vos informations de paiement sont sécurisées. Nous ne stockons pas les détails complets de votre carte.</small></p>
                    </div>
                </div>
            </div>
            
            <!-- Informations sur les modes de paiement -->
            <div class="card" data-aos="fade-up">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Moyens de paiement acceptés</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-around mb-3">
                        <i class="fab fa-cc-visa fa-2x"></i>
                        <i class="fab fa-cc-mastercard fa-2x"></i>
                        <i class="fab fa-cc-amex fa-2x"></i>
                        <i class="fab fa-paypal fa-2x"></i>
                    </div>
                    
                    <hr>
                    
                    <p><small>Vous pouvez également utiliser un chèque-cadeau Omnes Immobilier pour régler tout ou partie de vos frais.</small></p>
                    
                    <p><small>Si vous avez des questions concernant votre paiement, n'hésitez pas à contacter notre service client au <strong>01 23 45 67 89</strong>.</small></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePaymentMethod(method) {
    if (method === 'card') {
        document.getElementById('card_details').style.display = 'block';
        document.getElementById('voucher_details').style.display = 'none';
        
        // Rendre les champs de carte obligatoires
        document.getElementById('card_number').required = true;
        document.getElementById('card_name').required = true;
        document.getElementById('expiry_month').required = true;
        document.getElementById('expiry_year').required = true;
        document.getElementById('cvv').required = true;
        
        // Rendre les champs de chèque-cadeau non obligatoires
        document.getElementById('voucher_code').required = false;
    } else if (method === 'voucher') {
        document.getElementById('card_details').style.display = 'none';
        document.getElementById('voucher_details').style.display = 'block';
        
        // Rendre les champs de carte non obligatoires
        document.getElementById('card_number').required = false;
        document.getElementById('card_name').required = false;
        document.getElementById('expiry_month').required = false;
        document.getElementById('expiry_year').required = false;
        document.getElementById('cvv').required = false;
        
        // Rendre les champs de chèque-cadeau obligatoires
        document.getElementById('voucher_code').required = true;
    }
}

// Événement pour formater le numéro de carte
document.getElementById('card_number').addEventListener('input', function (e) {
    // Supprimer tous les caractères non-numériques
    let value = e.target.value.replace(/\D/g, '');
    
    // Formater avec des espaces tous les 4 chiffres
    let formattedValue = value.replace(/(.{4})/g, '$1 ').trim();
    
    // Limiter à 19 caractères (16 chiffres + 3 espaces)
    e.target.value = formattedValue.slice(0, 19);
});

// Événement pour le CVV (uniquement des chiffres)
document.getElementById('cvv').addEventListener('input', function (e) {
    // Supprimer tous les caractères non-numériques
    e.target.value = e.target.value.replace(/\D/g, '');
    
    // Limiter à 4 chiffres
    e.target.value = e.target.value.slice(0, 4);
});

// Validation du formulaire avant soumission
document.querySelector('form').addEventListener('submit', function(e) {
    // Récupérer la méthode de paiement sélectionnée
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    
    if (paymentMethod === 'card') {
        // Validation du numéro de carte
        const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
        if (cardNumber.length < 13 || cardNumber.length > 19) {
            e.preventDefault();
            alert('Veuillez saisir un numéro de carte valide.');
            return false;
        }
        
        // Validation du nom sur la carte
        const cardName = document.getElementById('card_name').value.trim();
        if (cardName.length < 2) {
            e.preventDefault();
            alert('Veuillez saisir le nom complet sur la carte.');
            return false;
        }
        
        // Validation du CVV
        const cvv = document.getElementById('cvv').value;
        if (cvv.length < 3 || cvv.length > 4) {
            e.preventDefault();
            alert('Veuillez saisir un code CVV valide (3 ou 4 chiffres).');
            return false;
        }
    } else if (paymentMethod === 'voucher') {
        // Validation du code de chèque-cadeau
        const voucherCode = document.getElementById('voucher_code').value.trim();
        if (voucherCode.length === 0) {
            e.preventDefault();
            alert('Veuillez saisir le code du chèque-cadeau.');
            return false;
        }
    }
});
</script>

<?php include BASE_PATH . 'includes/footer.php'; ?>