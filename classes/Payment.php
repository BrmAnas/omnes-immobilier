<?php
// Cette classe traite tout ce qui concerne les paiements, transactions, et services payants

class Payment {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    
    // Traiter un paiement par chèque-cadeau
    public function processVoucherPayment($voucher_code, $client_data, $amount, $service_type, $property_id = null) {
        // Vérifier si le chèque-cadeau existe et est valide
        $this->db->query('SELECT * FROM ChequeCadeau 
                         WHERE code = :code 
                         AND statut = "actif" 
                         AND (date_expiration IS NULL OR date_expiration > NOW())');
        $this->db->bind(':code', $voucher_code);
        $voucher = $this->db->single();
        
        if (!$voucher || $voucher->montant < $amount) {
            return false; // Chèque-cadeau invalide ou montant insuffisant
        }
        
        // Débuter la transaction
        $this->db->beginTransaction();
        
        try {
            $reference = 'VOU-' . strtoupper(substr(uniqid(), -8)) . '-' . date('Ymd');
            
            // Insérer la transaction
            $this->db->query('INSERT INTO Transaction (id_client, id_propriete, montant, type_service, type_paiement, reference_paiement, statut) 
                             VALUES (:id_client, :id_propriete, :montant, :type_service, :type_paiement, :reference, :statut)');
            $this->db->bind(':id_client', $client_data['id_client']);
            $this->db->bind(':id_propriete', $property_id);
            $this->db->bind(':montant', $amount);
            $this->db->bind(':type_service', $service_type);
            $this->db->bind(':type_paiement', 'chequecadeau');
            $this->db->bind(':reference', $reference);
            $this->db->bind(':statut', 'confirmé');
            $this->db->execute();
            
            $transaction_id = $this->db->lastInsertId();
            
            // Mettre à jour le montant du chèque-cadeau ou le marquer comme utilisé
            $new_amount = $voucher->montant - $amount;
            if ($new_amount <= 0) {
                $this->db->query('UPDATE ChequeCadeau SET statut = "utilisé" WHERE id_cheque = :id_cheque');
                $this->db->bind(':id_cheque', $voucher->id_cheque);
            } else {
                $this->db->query('UPDATE ChequeCadeau SET montant = :montant WHERE id_cheque = :id_cheque');
                $this->db->bind(':montant', $new_amount);
                $this->db->bind(':id_cheque', $voucher->id_cheque);
            }
            $this->db->execute();
            
            // Valider la transaction
            $this->db->commit();
            
            return $transaction_id;
        } catch (Exception $e) {
            // En cas d'erreur, annuler la transaction
            $this->db->rollback();
            error_log('Erreur lors du traitement du paiement par chèque-cadeau: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Génère un reçu pour une transaction spécifique
     * 
     * @param int $transaction_id ID de la transaction
     * @return array|bool Array contenant les détails du reçu ou false en cas d'erreur
     */
    public function generateReceipt($transaction_id) {
        $this->db->query('SELECT t.*, u.email, u.nom, u.prenom, p.titre as nom_propriete 
                         FROM Transaction t 
                         JOIN Client c ON t.id_client = c.id_client 
                         JOIN Utilisateur u ON c.id_utilisateur = u.id_utilisateur 
                         LEFT JOIN Propriete p ON t.id_propriete = p.id_propriete
                         WHERE t.id_transaction = :id_transaction');
        $this->db->bind(':id_transaction', $transaction_id);
        $transaction = $this->db->single();
        
        if (!$transaction) {
            return false;
        }
        
        // Formatage du reçu
        $receipt = [
            'numero' => 'RECU-' . str_pad($transaction_id, 6, '0', STR_PAD_LEFT),
            'date' => $transaction->date_transaction,
            'client' => [
                'nom' => $transaction->nom . ' ' . $transaction->prenom,
                'email' => $transaction->email
            ],
            'type_service' => $transaction->type_service,
            'type_paiement' => $transaction->type_paiement,
            'reference' => $transaction->reference_paiement,
            'montant' => $transaction->montant,
            'propriete' => $transaction->nom_propriete ?? null
        ];
        
        return $receipt;
    }

    // Récupérer les détails du client pour le paiement
    public function getClientDetails($user_id) {
        $this->db->query('SELECT c.*, u.* 
                         FROM Client c 
                         JOIN Utilisateur u ON c.id_utilisateur = u.id_utilisateur 
                         WHERE u.id_utilisateur = :user_id');
        $this->db->bind(':user_id', $user_id);
        return $this->db->single();
    }

    // Récupérer le prix d'un service par son type
    public function getServicePrice($service_type) {
        $this->db->query('SELECT prix FROM ServicePayant WHERE type_service = :type_service AND statut = "actif"');
        $this->db->bind(':type_service', $service_type);
        $result = $this->db->single();
        
        if ($result) {
            return floatval($result->prix);
        }
        return false;
    }

    // Appliquer un code de réduction
    public function applyDiscount($amount, $code) {
        // Vérifier si le code existe et est valide
        $this->db->query('SELECT * FROM Reduction 
                         WHERE code = :code 
                         AND statut = "actif" 
                         AND (date_expiration IS NULL OR date_expiration > NOW()) 
                         AND (nombre_utilisations_max IS NULL OR nombre_utilisations_actuelles < nombre_utilisations_max)');
        $this->db->bind(':code', $code);
        $reduction = $this->db->single();
        
        if ($reduction) {
            // Calculer le montant après réduction
            if ($reduction->type_reduction === 'pourcentage') {
                $discount_amount = $amount * ($reduction->valeur / 100);
                $final_amount = $amount - $discount_amount;
            } else { // montant fixe
                $discount_amount = $reduction->valeur;
                $final_amount = $amount - $discount_amount;
                if ($final_amount < 0) {
                    $final_amount = 0;
                }
            }
            
            // Incrémenter le nombre d'utilisations
            $this->db->query('UPDATE Reduction SET nombre_utilisations_actuelles = nombre_utilisations_actuelles + 1 WHERE id_reduction = :id_reduction');
            $this->db->bind(':id_reduction', $reduction->id_reduction);
            $this->db->execute();
            
            return $final_amount;
        }
        
        return $amount; // Pas de réduction appliquée
    }

    // Traiter un paiement par carte
    public function processCardPayment($payment_data, $client_data, $amount, $service_type, $property_id = null) {
        $reference = 'PAY-' . strtoupper(substr(uniqid(), -8)) . '-' . date('Ymd');
        
        // Débuter la transaction
        $this->db->beginTransaction();
        
        try {
            // Insérer la transaction
            $this->db->query('INSERT INTO Transaction (id_client, id_propriete, montant, type_service, type_paiement, reference_paiement, statut) 
                             VALUES (:id_client, :id_propriete, :montant, :type_service, :type_paiement, :reference, :statut)');
            $this->db->bind(':id_client', $client_data['id_client']);
            $this->db->bind(':id_propriete', $property_id);
            $this->db->bind(':montant', $amount);
            $this->db->bind(':type_service', $service_type);
            $this->db->bind(':type_paiement', $payment_data['card_type']);
            $this->db->bind(':reference', $reference);
            $this->db->bind(':statut', 'confirmé');
            $this->db->execute();
            
            $transaction_id = $this->db->lastInsertId();
            
            // Enregistrer les détails du paiement
            $this->db->query('INSERT INTO DetailPaiement (id_transaction, nom_titulaire, type_carte) 
                             VALUES (:id_transaction, :nom_titulaire, :type_carte)');
            $this->db->bind(':id_transaction', $transaction_id);
            $this->db->bind(':nom_titulaire', $payment_data['card_name']);
            $this->db->bind(':type_carte', $payment_data['card_type']);
            $this->db->execute();
            
            // Valider la transaction
            $this->db->commit();
            
            return $transaction_id;
        } catch (Exception $e) {
            // En cas d'erreur, annuler la transaction
            $this->db->rollback();
            error_log('Erreur lors du traitement du paiement: ' . $e->getMessage());
            return false;
        }
    }}