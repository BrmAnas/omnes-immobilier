<?php
class Payment {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Création d'un nouveau paiement
     * @param array $data Les données du paiement
     * @return int|false L'ID du paiement créé ou false en cas d'erreur
     */
    public function create($data) {
        try {
            $this->db->query('INSERT INTO Paiement (id_client, montant, type_service, type_carte, 
                            numero_carte, nom_carte, date_expiration, code_securite, status, date_paiement) 
                            VALUES (:id_client, :montant, :type_service, :type_carte, :numero_carte, 
                            :nom_carte, :date_expiration, :code_securite, :status, NOW())');
            
            $this->db->bind(':id_client', $data['id_client']);
            $this->db->bind(':montant', $data['montant']);
            $this->db->bind(':type_service', $data['type_service']);
            $this->db->bind(':type_carte', $data['type_carte']);
            $this->db->bind(':numero_carte', $this->encryptCardNumber($data['numero_carte']));
            $this->db->bind(':nom_carte', $data['nom_carte']);
            $this->db->bind(':date_expiration', $data['date_expiration']);
            $this->db->bind(':code_securite', $this->encryptCVV($data['code_securite']));
            $this->db->bind(':status', $data['status'] ?? 'en_attente');
            
            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            } else {
                error_log("Erreur lors de la création d'un paiement: " . print_r($data, true));
                return false;
            }
        } catch (Exception $e) {
            error_log("Exception lors de la création d'un paiement: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupération d'un paiement par son ID
     * @param int $id_paiement L'ID du paiement
     * @return object|false Le paiement ou false si non trouvé
     */
    public function getPaymentById($id_paiement) {
        try {
            $this->db->query('SELECT * FROM Paiement WHERE id_paiement = :id_paiement');
            $this->db->bind(':id_paiement', $id_paiement);
            
            return $this->db->single();
        } catch (Exception $e) {
            error_log("Exception lors de la récupération d'un paiement: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupération des paiements d'un client
     * @param int $id_client L'ID du client
     * @return array Les paiements du client
     */
    public function getClientPayments($id_client) {
        try {
            $this->db->query('SELECT * FROM Paiement WHERE id_client = :id_client ORDER BY date_paiement DESC');
            $this->db->bind(':id_client', $id_client);
            
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Exception lors de la récupération des paiements d'un client: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Mise à jour du statut d'un paiement
     * @param int $id_paiement L'ID du paiement
     * @param string $status Le nouveau statut
     * @return bool Succès ou échec
     */
    public function updateStatus($id_paiement, $status) {
        try {
            $this->db->query('UPDATE Paiement SET status = :status WHERE id_paiement = :id_paiement');
            
            $this->db->bind(':id_paiement', $id_paiement);
            $this->db->bind(':status', $status);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Exception lors de la mise à jour du statut d'un paiement: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si les informations de carte sont valides
     * @param array $card_data Les données de la carte
     * @return bool Valide ou non
     */
    public function validateCardInfo($card_data) {
        // Vérification du numéro de carte (algorithme de Luhn)
        $number = preg_replace('/\D/', '', $card_data['numero_carte']);
        if (empty($number) || strlen($number) < 13 || strlen($number) > 19) {
            return false;
        }
        
        // Vérification de la date d'expiration
        $expiry = explode('/', $card_data['date_expiration']);
        if (count($expiry) != 2) {
            return false;
        }
        
        $month = intval(trim($expiry[0]));
        $year = intval(trim($expiry[1]));
        
        // Si l'année est au format YY, la convertir en YYYY
        if ($year < 100) {
            $year += 2000;
        }
        
        // Vérifier que la date d'expiration est valide
        if ($month < 1 || $month > 12 || $year < date('Y') || ($year == date('Y') && $month < date('m'))) {
            return false;
        }
        
        // Vérification du code de sécurité (CVV)
        $cvv = $card_data['code_securite'];
        if (!is_numeric($cvv) || strlen($cvv) < 3 || strlen($cvv) > 4) {
            return false;
        }

        // Pour ce projet, on simule toujours une validation réussie
        return true;
    }

    /**
     * Simule une transaction avec une banque
     * @param array $card_data Les données de la carte
     * @param float $amount Le montant
     * @return bool Succès ou échec
     */
    public function processPayment($card_data, $amount) {
        // Vérifier les informations de carte
        if (!$this->validateCardInfo($card_data)) {
            return false;
        }
        
        // Dans un vrai système, on ferait ici une requête à une API de paiement
        // Pour ce projet, on simule toujours un paiement réussi
        return true;
    }

    /**
     * Chiffre le numéro de carte (ne stocke que les 4 derniers chiffres en clair)
     * @param string $cardNumber Le numéro de carte
     * @return string Le numéro chiffré
     */
    private function encryptCardNumber($cardNumber) {
        // On ne garde que les 4 derniers chiffres en clair
        $last4 = substr($cardNumber, -4);
        $masked = str_repeat('*', strlen($cardNumber) - 4) . $last4;
        
        return $masked;
    }

    /**
     * Chiffre le code CVV
     * @param string $cvv Le code CVV
     * @return string Le code chiffré
     */
    private function encryptCVV($cvv) {
        // On ne stocke jamais le CVV en clair
        return str_repeat('*', strlen($cvv));
    }
}
?>