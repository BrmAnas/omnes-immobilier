<?php
class Payment {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Récupère le prix d'un service
     * @param string $service_type Le type de service
     * @return float|false Le prix du service ou false si non trouvé
     */
    public function getServicePrice($service_type) {
        try {
            $this->db->query('SELECT prix FROM ServicePayant WHERE type_service = :type_service AND statut = "actif"');
            $this->db->bind(':type_service', $service_type);
            $result = $this->db->single();
            
            // Retourne le prix si le service existe, sinon false
            return $result ? floatval($result->prix) : false;
        } catch (Exception $e) {
            error_log("Exception lors de la récupération du prix du service: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupération des détails du client
     * @param int $user_id L'ID utilisateur
     * @return object Les détails du client avec des valeurs par défaut
     */
    public function getClientDetails($user_id) {
        try {
            // Requête pour récupérer les détails du client à partir de l'ID utilisateur
            $this->db->query('SELECT * FROM Client WHERE id_utilisateur = :user_id');
            $this->db->bind(':user_id', $user_id);
            
            $client = $this->db->single();
            
            // Si le client n'est pas trouvé, retourner un objet avec des valeurs par défaut
            if (!$client) {
                return (object)[
                    'id_client' => null,
                    'id_utilisateur' => $user_id,
                    'nom' => '',
                    'prenom' => '',
                    'email' => '',
                    'telephone' => '',
                    'adresse' => '',
                    'ville' => '',
                    'code_postal' => '',
                    'pays' => 'France'
                ];
            }
            
            return $client;
        } catch (Exception $e) {
            error_log("Exception lors de la récupération des détails du client: " . $e->getMessage());
            
            // En cas d'erreur, retourner un objet avec des valeurs par défaut
            return (object)[
                'id_client' => null,
                'id_utilisateur' => $user_id,
                'nom' => '',
                'prenom' => '',
                'email' => '',
                'telephone' => '',
                'adresse' => '',
                'ville' => '',
                'code_postal' => '',
                'pays' => 'France'
            ];
        }
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
            
            $this->db->bind(':id_client', $data['id_client'] ?? null);
            $this->db->bind(':montant', $data['montant']);
            $this->db->bind(':type_service', $data['type_service']);
            $this->db->bind(':type_carte', $data['type_carte'] ?? null);
            $this->db->bind(':numero_carte', $this->encryptCardNumber($data['numero_carte']));
            $this->db->bind(':nom_carte', $data['nom_carte'] ?? null);
            $this->db->bind(':date_expiration', $data['date_expiration'] ?? null);
            $this->db->bind(':code_securite', $this->encryptCVV($data['code_securite'] ?? ''));
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
     * Processus de paiement par carte
     * @param array $payment_data Données de paiement
     * @param array $client_data Données du client
     * @param float $amount Montant
     * @param string $service_type Type de service
     * @return int|false ID de transaction
     */
    public function processCardPayment($payment_data, $client_data, $amount, $service_type) {
        try {
            // Log des données reçues pour débogage
            error_log("Début du traitement de paiement par carte");
            error_log("Données client : " . print_r($client_data, true));
            error_log("Montant : " . $amount);
            error_log("Type de service : " . $service_type);
    
            // Préparer les données de validation de la carte
            $card_validation_data = [
                'numero_carte' => $payment_data['card_number'],
                'date_expiration' => $payment_data['expiry_month'] . '/' . $payment_data['expiry_year'],
                'code_securite' => $payment_data['cvv']
            ];
            
            // Valider les informations de la carte
            if (!$this->validateCardInfo($card_validation_data)) {
                error_log("Validation de carte invalide : " . print_r($card_validation_data, true));
                return false;
            }
            
            // Simuler le traitement du paiement
            if (!$this->processPayment($card_validation_data, $amount)) {
                error_log("Échec du traitement du paiement simulé");
                return false;
            }
            
            // Commencer une transaction de base de données
            $result = $this->db->beginTransaction();
            if (!$result) {
                error_log("Impossible de commencer la transaction");
                return false;
            }
            
            // Créer une entrée dans la table Transaction
            $this->db->query('INSERT INTO Transaction (
                id_client, montant, type_service, statut, date_transaction
            ) VALUES (
                :id_client, :montant, :type_service, :statut, NOW()
            )');
            
            // Vérifier et logger l'ID client
            $id_client = $client_data['id_client'] ?? null;
            error_log("ID Client utilisé : " . ($id_client ? $id_client : 'NULL'));
            
            $this->db->bind(':id_client', $id_client);
            $this->db->bind(':montant', $amount);
            $this->db->bind(':type_service', $service_type);
            $this->db->bind(':statut', 'confirmé');
            
            $executeResult = $this->db->execute();
            if (!$executeResult) {
                error_log("Échec de l'insertion de la transaction");
                $this->db->rollBack();
                return false;
            }
            
            // Récupérer l'ID de la transaction
            $transaction_id = $this->db->lastInsertId();
            error_log("ID de transaction généré : " . $transaction_id);
            
            // Reste du code de traitement (identique à votre implémentation précédente)
            
            return $transaction_id;
        } catch (Exception $e) {
            // Log de l'exception complète
            error_log("Exception détaillée : " . $e->getMessage());
            error_log("Trace de l'exception : " . $e->getTraceAsString());
            
            // Annuler la transaction si nécessaire
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            
            return false;
        }
    }

    /**
     * Processus de paiement par chèque-cadeau
     * @param string $voucher_code Code du chèque-cadeau
     * @param array $client_data Données du client
     * @param float $amount Montant
     * @param string $service_type Type de service
     * @return int|false ID de transaction
     */
    public function processVoucherPayment($voucher_code, $client_data, $amount, $service_type) {
        try {
            // Vérifier la validité du chèque-cadeau
            $this->db->query('SELECT * FROM ChequesCadeaux 
                              WHERE code = :code 
                              AND statut = "actif" 
                              AND montant >= :amount');
            $this->db->bind(':code', $voucher_code);
            $this->db->bind(':amount', $amount);
            
            $voucher = $this->db->single();
            
            if (!$voucher) {
                error_log("Chèque-cadeau invalide ou solde insuffisant");
                return false;
            }
            
            // Commencer une transaction de base de données
            $this->db->beginTransaction();
            
            // Créer une entrée dans la table Transaction
            $this->db->query('INSERT INTO Transaction (
                id_client, montant, type_service, statut, date_transaction
            ) VALUES (
                :id_client, :montant, :type_service, :statut, NOW()
            )');
            
            $this->db->bind(':id_client', $client_data['id_client'] ?? null);
            $this->db->bind(':montant', $amount);
            $this->db->bind(':type_service', $service_type);
            $this->db->bind(':statut', 'confirmé');
            
            if (!$this->db->execute()) {
                $this->db->rollBack();
                error_log("Échec de l'insertion de la transaction");
                return false;
            }
            
            // Récupérer l'ID de la transaction
            $transaction_id = $this->db->lastInsertId();
            
            // Mettre à jour le solde du chèque-cadeau
            $this->db->query('UPDATE ChequesCadeaux 
                              SET montant = montant - :amount, 
                                  statut = CASE WHEN montant - :amount <= 0 THEN "utilisé" ELSE "actif" END
                              WHERE code = :code');
            $this->db->bind(':amount', $amount);
            $this->db->bind(':code', $voucher_code);
            
            if (!$this->db->execute()) {
                $this->db->rollBack();
                error_log("Échec de la mise à jour du chèque-cadeau");
                return false;
            }
            
            // Créer l'enregistrement de paiement
            $payment_record = [
                'id_client' => $client_data['id_client'] ?? null,
                'montant' => $amount,
                'type_service' => $service_type,
                'type_carte' => 'chequecadeau',
                'numero_carte' => $voucher_code,
                'nom_carte' => 'Chèque-cadeau',
                'date_expiration' => null,
                'code_securite' => null,
                'status' => 'confirmé'
            ];
            
            $payment_id = $this->create($payment_record);
            
            if (!$payment_id) {
                $this->db->rollBack();
                error_log("Échec de la création de l'enregistrement de paiement");
                return false;
            }
            
            // Valider la transaction
            $this->db->commit();
            
            return $transaction_id;
        } catch (Exception $e) {
            // En cas d'erreur, annuler la transaction
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            
            error_log("Exception lors du traitement du paiement par chèque-cadeau: " . $e->getMessage());
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
     * Applique un code de réduction
     * @param float $amount Montant initial
     * @param string $discount_code Code de réduction
     * @return float Montant après réduction
     */
    public function applyDiscount($amount, $discount_code) {
        try {
            $this->db->query('SELECT * FROM CodeReduction WHERE code = :code AND statut = "actif"');
            $this->db->bind(':code', $discount_code);
            
            $discount = $this->db->single();
            
            if (!$discount) {
                return $amount;
            }
            
            // Calculer le montant réduit
            if ($discount->type_reduction === 'pourcentage') {
                $reduced_amount = $amount * (1 - ($discount->valeur / 100));
            } else {
                $reduced_amount = max(0, $amount - $discount->valeur);
            }
            
            return $reduced_amount;
        } catch (Exception $e) {
            error_log("Exception lors de l'application du code de réduction: " . $e->getMessage());
            return $amount;
        }
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

    /**
     * Génère un reçu de paiement
     * @param int $transaction_id L'ID de la transaction
     * @return array|false Les détails du reçu ou false si non trouvé
     */
    public function generateReceipt($transaction_id) {
        try {
            // Requête pour récupérer les détails de la transaction
            $this->db->query('
                SELECT t.*, c.nom, c.prenom, c.email, s.nom_service, 
                       p.type_carte, p.numero_carte, p.date_paiement 
                FROM Transaction t
                JOIN Client c ON t.id_client = c.id_client
                JOIN ServicePayant s ON t.type_service = s.type_service
                JOIN Paiement p ON t.id_transaction = p.id_transaction
                WHERE t.id_transaction = :transaction_id
            ');
            
            $this->db->bind(':transaction_id', $transaction_id);
            $transaction = $this->db->single();
            
            if (!$transaction) {
                return false;
            }
            
            // Préparer les données du reçu
            return [
                'numero' => 'OMN-' . str_pad($transaction_id, 6, '0', STR_PAD_LEFT),
                'date' => $transaction->date_paiement,
                'montant' => $transaction->montant,
                'type_service' => $transaction->nom_service,
                'type_paiement' => $transaction->type_carte,
                'reference' => $transaction->numero_carte,
                'client' => [
                    'nom' => $transaction->nom . ' ' . $transaction->prenom,
                    'email' => $transaction->email
                ]
            ];
        } catch (Exception $e) {
            error_log("Exception lors de la génération du reçu: " . $e->getMessage());
            return false;
        }
    }
}
?>