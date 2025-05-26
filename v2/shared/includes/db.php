<?php
/**
 * Connexion à la base de données Protection Civile 64 - v2
 * 
 * Ce fichier gère la connexion PDO à la base de données
 * et fournit les fonctions utilitaires pour les requêtes.
 */

// Empêcher l'accès direct
if (!defined('PROTEC64_V2')) {
    die('Accès interdit');
}

/**
 * Fonction principale de connexion à la base de données
 * 
 * @return PDO Instance PDO de connexion
 * @throws Exception En cas d'erreur de connexion
 */
function db_connect() {
    static $pdo = null;
    
    // Si la connexion existe déjà, la retourner
    if ($pdo !== null) {
        return $pdo;
    }
    
    try {
        // Construction du DSN (Data Source Name)
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        // Options PDO pour optimiser les performances et la sécurité
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET,
            PDO::ATTR_PERSISTENT         => false, // Pas de connexions persistantes
            PDO::ATTR_TIMEOUT            => 30 // Timeout de 30 secondes
        ];
        
        // Création de la connexion PDO
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        
        // Log de succès en mode debug
        if (DEBUG_MODE) {
            error_log("Connexion à la base de données réussie - " . DB_NAME);
        }
        
        return $pdo;
        
    } catch (PDOException $e) {
        // Log de l'erreur
        error_log("Erreur de connexion à la base de données : " . $e->getMessage());
        
        // En production, on cache les détails de l'erreur
        if (DEBUG_MODE) {
            throw new Exception("Erreur de connexion à la base de données : " . $e->getMessage());
        } else {
            throw new Exception("Erreur de connexion à la base de données. Veuillez contacter l'administrateur.");
        }
    }
}

/**
 * Fonction utilitaire pour exécuter une requête simple
 * 
 * @param string $sql Requête SQL
 * @param array $params Paramètres de la requête
 * @return PDOStatement Résultat de la requête
 */
function db_query($sql, $params = []) {
    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Erreur lors de l'exécution de la requête : " . $e->getMessage());
        error_log("SQL : " . $sql);
        error_log("Params : " . print_r($params, true));
        throw new Exception("Erreur lors de l'exécution de la requête.");
    }
}

/**
 * Fonction pour récupérer un seul enregistrement
 * 
 * @param string $sql Requête SQL
 * @param array $params Paramètres de la requête
 * @return array|false Enregistrement trouvé ou false
 */
function db_fetch($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt->fetch();
}

/**
 * Fonction pour récupérer tous les enregistrements
 * 
 * @param string $sql Requête SQL
 * @param array $params Paramètres de la requête
 * @return array Tableau des enregistrements
 */
function db_fetch_all($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Fonction pour récupérer une seule valeur
 * 
 * @param string $sql Requête SQL
 * @param array $params Paramètres de la requête
 * @return mixed Valeur de la première colonne du premier enregistrement
 */
function db_fetch_column($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt->fetchColumn();
}

/**
 * Fonction pour insérer un enregistrement et retourner l'ID
 * 
 * @param string $sql Requête INSERT
 * @param array $params Paramètres de la requête
 * @return int ID du dernier enregistrement inséré
 */
function db_insert($sql, $params = []) {
    db_query($sql, $params);
    $pdo = db_connect();
    return $pdo->lastInsertId();
}

/**
 * Fonction pour commencer une transaction
 */
function db_transaction_begin() {
    $pdo = db_connect();
    return $pdo->beginTransaction();
}

/**
 * Fonction pour valider une transaction
 */
function db_transaction_commit() {
    $pdo = db_connect();
    return $pdo->commit();
}

/**
 * Fonction pour annuler une transaction
 */
function db_transaction_rollback() {
    $pdo = db_connect();
    return $pdo->rollBack();
}

/**
 * Fonction pour vérifier si une table existe
 * 
 * @param string $table_name Nom de la table (sans préfixe)
 * @return bool True si la table existe
 */
function db_table_exists($table_name) {
    $full_table_name = DB_PREFIX . $table_name;
    $sql = "SHOW TABLES LIKE ?";
    $result = db_fetch_column($sql, [$full_table_name]);
    return $result !== false;
}

/**
 * Fonction pour compter les enregistrements d'une table
 * 
 * @param string $table_name Nom de la table (sans préfixe)
 * @param string $where Condition WHERE optionnelle
 * @param array $params Paramètres pour la condition WHERE
 * @return int Nombre d'enregistrements
 */
function db_count($table_name, $where = '', $params = []) {
    $full_table_name = DB_PREFIX . $table_name;
    $sql = "SELECT COUNT(*) FROM `$full_table_name`";
    
    if (!empty($where)) {
        $sql .= " WHERE " . $where;
    }
    
    return (int) db_fetch_column($sql, $params);
}

/**
 * Fonction pour vérifier la connexion à la base de données
 * 
 * @return bool True si la connexion est OK
 */
function db_check_connection() {
    try {
        $pdo = db_connect();
        $stmt = $pdo->query("SELECT 1");
        return $stmt !== false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Fonction pour récupérer les informations sur la base de données
 * 
 * @return array Informations sur la BDD
 */
function db_get_info() {
    try {
        $pdo = db_connect();
        
        // Version MySQL
        $version = $pdo->query("SELECT VERSION()")->fetchColumn();
        
        // Nombre de tables avec le préfixe
        $tables_count = $pdo->query("SHOW TABLES LIKE '" . DB_PREFIX . "%'")->rowCount();
        
        return [
            'host' => DB_HOST,
            'database' => DB_NAME,
            'version' => $version,
            'tables_count' => $tables_count,
            'prefix' => DB_PREFIX,
            'charset' => DB_CHARSET,
            'connection_status' => 'OK'
        ];
        
    } catch (Exception $e) {
        return [
            'connection_status' => 'ERROR',
            'error' => $e->getMessage()
        ];
    }
}

// Test automatique de la connexion si en mode debug
if (DEBUG_MODE && !defined('DB_TEST_SKIP')) {
    try {
        $connection_test = db_check_connection();
        if ($connection_test) {
            error_log("Test de connexion BDD : OK");
        } else {
            error_log("Test de connexion BDD : ÉCHEC");
        }
    } catch (Exception $e) {
        error_log("Erreur lors du test de connexion : " . $e->getMessage());
    }
}
?>