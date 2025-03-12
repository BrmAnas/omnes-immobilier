<?php
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
require_once BASE_PATH . 'config/init.php';

// Vérifier si l'utilisateur est connecté
if (!is_logged_in()) {
    redirect('/omnes-immobilier/login.php');
}

// Vérifier si un ID de transaction est présent
if (!isset($_SESSION['transaction_id'])) {
    redirect('/omnes-immobilier/account.php');
}

$transaction_id = $_SESSION['transaction_id'];

// Inclure les classes nécessaires
require_once BASE_PATH . 'classes/Payment.php';
require_once BASE_PATH . 'classes/Client.php';

// Initialiser les classes
$payment = new Payment();
$client = new Client();

// Récupérer les détails de la transaction pour le reçu
$receipt = $payment->generateReceipt($transaction_id);

if (!$receipt) {
    set_alert('danger', 'Impossible de récupérer les détails de votre transaction.');
    redirect('/omnes-immobilier/account.php');
}

// Supprimer l'ID de transaction de la session après utilisation
unset($_SESSION['transaction_id']);

$page_title = "Confirmation de paiement";
include BASE_PATH . 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-success text-white">
                    <h2 class="h4 mb-0"><i class="fas fa-check-circle me-2"></i> Paiement réussi</h2>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-check-circle text-success fa-5x mb-3"></i>
                        <h3>Merci pour votre paiement !</h3>
                        <p class="lead">Votre paiement a été traité avec succès.</p>
                    </div>
                    
                    <div class="alert alert-info">
                        <p class="mb-0">Une confirmation a été envoyée à votre adresse email: <strong><?php echo htmlspecialchars($receipt['client']['email']); ?></strong></p>
                    </div>
                    
                    <div class="receipt mt-4">
                        <h4>Détails de la transaction</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Numéro de reçu:</th>
                                    <td><?php echo htmlspecialchars($receipt['numero']); ?></td>
                                </tr>
                                <tr>
                                    <th>Date:</th>
                                    <td><?php echo format_date(date('Y-m-d', strtotime($receipt['date']))); ?></td>
                                </tr>
                                <tr>
                                    <th>Client:</th>
                                    <td><?php echo htmlspecialchars($receipt['client']['nom']); ?></td>
                                </tr>
                                <tr>
                                    <th>Service:</th>
                                    <td><?php echo htmlspecialchars($receipt['type_service']); ?></td>
                                </tr>
                                <tr>
                                    <th>Mode de paiement:</th>
                                    <td>
                                        <?php 
                                        switch($receipt['type_paiement']) {
                                            case 'visa':
                                                echo 'Carte Visa';
                                                break;
                                            case 'mastercard':
                                                echo 'MasterCard';
                                                break;
                                            case 'amex':
                                                echo 'American Express';
                                                break;
                                            case 'paypal':
                                                echo 'PayPal';
                                                break;
                                            case 'chequecadeau':
                                                echo 'Chèque-cadeau Omnes Immobilier';
                                                break;
                                            default:
                                                echo htmlspecialchars($receipt['type_paiement']);
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Référence:</th>
                                    <td><?php echo htmlspecialchars($receipt['reference']); ?></td>
                                </tr>
                                <tr>
                                    <th>Montant:</th>
                                    <td class="fw-bold"><?php echo format_price($receipt['montant']); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mt-4 d-grid gap-2">
                        <button class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i> Imprimer le reçu
                        </button>
                        <a href="/omnes-immobilier/account.php" class="btn btn-primary">
                            <i class="fas fa-user me-2"></i> Aller à mon compte
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header bg-primary text-white">
                    <h3 class="h5 mb-0">Et maintenant ?</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <div class="me-3">
                            <i class="fas fa-calendar-check fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h5>Consultez vos rendez-vous</h5>
                            <p>Accédez à votre espace personnel pour voir tous vos rendez-vous et suivre votre parcours immobilier.</p>
                            <a href="/omnes-immobilier/my-appointments.php" class="btn btn-sm btn-outline-primary">Mes rendez-vous</a>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start mb-3">
                        <div class="me-3">
                            <i class="fas fa-home fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h5>Découvrez d'autres biens</h5>
                            <p>Continuez votre recherche immobilière en explorant notre catalogue de propriétés.</p>
                            <a href="/omnes-immobilier/properties.php" class="btn btn-sm btn-outline-primary">Voir les propriétés</a>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start">
                        <div class="me-3">
                            <i class="fas fa-headset fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h5>Besoin d'aide ?</h5>
                            <p>Notre équipe est disponible pour répondre à toutes vos questions.</p>
                            <a href="/omnes-immobilier/contact.php" class="btn btn-sm btn-outline-primary">Contactez-nous</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    header, footer, .card-header, .mt-4, .alert, nav, .no-print {
        display: none !important;
    }
    body {
        padding: 0;
        margin: 0;
    }
    .card {
        border: none !important;
    }
    .card-body {
        padding: 0 !important;
    }
}
</style>

<?php include BASE_PATH . 'includes/footer.php'; ?>