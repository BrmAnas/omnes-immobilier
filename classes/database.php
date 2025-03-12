<?php
class Database {
    private $host;
    private $port;
    private $user;
    private $pass;
    private $dbname;

    private $dbh;
    private $error;
    private $stmt;

    public function __construct() {
        // Les constantes sont déjà définies par database.php inclus dans init.php
        $this->host = DB_HOST;
        $this->port = DB_PORT;
        $this->user = DB_USER;
        $this->pass = DB_PASS;
        $this->dbname = DB_NAME;
        
        // Configuration DSN
        $dsn = 'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->dbname . ';charset=utf8mb4';
        
        // Configuration des options PDO
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        );

        // Création d'une instance PDO
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Erreur de connexion à la base de données : " . $this->error);
            throw new Exception("Erreur de connexion à la base de données");
        }
    }

    // Préparation d'une requête
    public function query($query) {
        $this->stmt = $this->dbh->prepare($query);
    }

    // Liaison des paramètres pour les requêtes préparées
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    // Exécution de la requête
    public function execute() {
        try {
            return $this->stmt->execute();
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Erreur d'exécution de requête : " . $this->error);
            return false;
        }
    }

    // Récupération de tous les résultats
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    // Récupération d'un seul résultat
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }

    // Récupération du nombre de lignes affectées
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    // Récupération du dernier ID inséré
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }

    // Ajoutez ces méthodes à votre classe Database

/**
 * Commence une transaction de base de données
 * @return bool
 */
public function beginTransaction() {
    try {
        return $this->dbh->beginTransaction();
    } catch(PDOException $e) {
        $this->error = $e->getMessage();
        error_log("Erreur lors du début de la transaction : " . $this->error);
        return false;
    }
}

/**
 * Valide la transaction courante
 * @return bool
 */
public function commit() {
    try {
        return $this->dbh->commit();
    } catch(PDOException $e) {
        $this->error = $e->getMessage();
        error_log("Erreur lors de la validation de la transaction : " . $this->error);
        return false;
    }
}

/**
 * Annule la transaction courante
 * @return bool
 */
public function rollBack() {
    try {
        return $this->dbh->rollBack();
    } catch(PDOException $e) {
        $this->error = $e->getMessage();
        error_log("Erreur lors de l'annulation de la transaction : " . $this->error);
        return false;
    }
}

/**
 * Vérifie si une transaction est en cours
 * @return bool
 */
public function inTransaction() {
    return $this->dbh->inTransaction();
}
}
?>