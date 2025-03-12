<?php
session_start();

// Database connection parameters
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "omnes_immobilier";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Process payment form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize payment information
    $card_type = sanitize_input($_POST['card_type']);
    $card_number = sanitize_input($_POST['card_number']);
    $card_name = sanitize_input($_POST['card_name']);
    $expiry_date = sanitize_input($_POST['expiry_date']);
    $security_code = sanitize_input($_POST['security_code']);

    // Validate payment information (basic validation)
    $errors = [];

    if (empty($card_type)) {
        $errors[] = "Veuillez sélectionner un type de carte.";
    }

    if (empty($card_number) || !preg_match("/^[0-9]{13,19}$/", $card_number)) {
        $errors[] = "Numéro de carte invalide.";
    }

    if (empty($card_name)) {
        $errors[] = "Nom sur la carte requis.";
    }

    if (empty($expiry_date) || !preg_match("/^(0[1-9]|1[0-2])\/[0-9]{2}$/", $expiry_date)) {
        $errors[] = "Date d'expiration invalide.";
    }

    if (empty($security_code) || !preg_match("/^[0-9]{3,4}$/", $security_code)) {
        $errors[] = "Code de sécurité invalide.";
    }

    // If no errors, process payment
    if (empty($errors)) {
        // In a real-world scenario, this is where you would:
        // 1. Communicate with a payment gateway
        // 2. Verify payment details
        // 3. Process the transaction

        // For this project, we'll simulate a payment validation
        $user_id = $_SESSION['user_id'];
        $service_id = $_SESSION['service_id']; // Assuming service details are stored in session
        $amount = $_SESSION['service_amount'];

        // Insert transaction into database
        $sql = "INSERT INTO transactions (user_id, service_id, amount, payment_method, transaction_date) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iids", $user_id, $service_id, $amount, $card_type);
        
        if ($stmt->execute()) {
            // Send confirmation email/SMS (simulated)
            $to = $_SESSION['user_email'];
            $subject = "Confirmation de paiement - Omnes Immobilier";
            $message = "Votre paiement de {$amount}€ a été traité avec succès.\n";
            $message .= "Détails du service: " . $_SESSION['service_description'];
            $headers = "From: confirmation@omnesimmobilier.fr";
            
            // In a real scenario, use a proper email sending library
            mail($to, $subject, $message, $headers);

            // Redirect to confirmation page
            $_SESSION['payment_success'] = true;
            header("Location: payment_success.php");
            exit();
        } else {
            $errors[] = "Erreur lors du traitement du paiement. Veuillez réessayer.";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation de Paiement - Omnes Immobilier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="text-center">Confirmation de Paiement</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        // Display any errors
                        if (!empty($errors)) {
                            echo '<div class="alert alert-danger">';
                            foreach ($errors as $error) {
                                echo '<p>' . $error . '</p>';
                            }
                            echo '</div>';
                        }
                        ?>

                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="mb-3">
                                <label for="card_type" class="form-label">Type de Carte</label>
                                <select name="card_type" id="card_type" class="form-select" required>
                                    <option value="">Sélectionnez un type de carte</option>
                                    <option value="Visa">Visa</option>
                                    <option value="MasterCard">MasterCard</option>
                                    <option value="AmericanExpress">American Express</option>
                                    <option value="PayPal">PayPal</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="card_number" class="form-label">Numéro de Carte</label>
                                <input type="text" name="card_number" id="card_number" class="form-control" 
                                       placeholder="Numéro de carte" required 
                                       pattern="[0-9]{13,19}" maxlength="19">
                            </div>

                            <div class="mb-3">
                                <label for="card_name" class="form-label">Nom sur la Carte</label>
                                <input type="text" name="card_name" id="card_name" class="form-control" 
                                       placeholder="Nom complet" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="expiry_date" class="form-label">Date d'Expiration</label>
                                    <input type="text" name="expiry_date" id="expiry_date" class="form-control" 
                                           placeholder="MM/AA" required 
                                           pattern="(0[1-9]|1[0-2])/[0-9]{2}" maxlength="5">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="security_code" class="form-label">Code de Sécurité</label>
                                    <input type="text" name="security_code" id="security_code" class="form-control" 
                                           placeholder="CVV" required 
                                           pattern="[0-9]{3,4}" maxlength="4">
                                </div>
                            </div>

                            <div class="mb-3">
                                <p><strong>Montant à payer:</strong> <?php echo $_SESSION['service_amount']; ?> €</p>
                                <p><strong>Service:</strong> <?php echo $_SESSION['service_description']; ?></p>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Confirmer le Paiement</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Additional client-side validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        form.addEventListener('submit', function(event) {
            const cardNumber = document.getElementById('card_number');
            const cardName = document.getElementById('card_name');
            const expiryDate = document.getElementById('expiry_date');
            const securityCode = document.getElementById('security_code');

            // Basic validation
            if (!cardNumber.value.match(/^[0-9]{13,19}$/)) {
                alert('Numéro de carte invalide');
                event.preventDefault();
            }

            if (cardName.value.trim() === '') {
                alert('Nom sur la carte requis');
                event.preventDefault();
            }

            if (!expiryDate.value.match(/^(0[1-9]|1[0-2])\/[0-9]{2}$/)) {
                alert('Date d\'expiration invalide');
                event.preventDefault();
            }

            if (!securityCode.value.match(/^[0-9]{3,4}$/)) {
                alert('Code de sécurité invalide');
                event.preventDefault();
            }
        });
    });
    </script>
</body>
</html>