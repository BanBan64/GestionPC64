<?php
/**
 * Fonctions utilitaires Protection Civile 64 - v2
 * 
 * Ce fichier contient toutes les fonctions utilitaires
 * utilisées dans l'ensemble de l'application.
 */

// Empêcher l'accès direct
if (!defined('PROTEC64_V2')) {
    die('Accès interdit');
}

/**
 * Échapper et sécuriser une chaîne pour l'affichage HTML
 * 
 * @param string $string Chaîne à échapper
 * @param int $flags Flags htmlspecialchars
 * @return string Chaîne échappée
 */
function escape_html($string, $flags = ENT_QUOTES | ENT_HTML5) {
    return htmlspecialchars($string, $flags, 'UTF-8');
}

/**
 * Raccourci pour escape_html (compatibilité)
 * 
 * @param string $string Chaîne à échapper
 * @return string Chaîne échappée
 */
function h($string) {
    return escape_html($string);
}

/**
 * Rediriger vers une URL
 * 
 * @param string $url URL de redirection
 * @param int $status_code Code de statut HTTP
 */
function redirect($url, $status_code = 302) {
    // Nettoyer les buffers de sortie
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Ajouter l'URL de base si l'URL est relative
    if (!preg_match('/^https?:\/\//', $url)) {
        $url = BASE_URL . '/' . ltrim($url, '/');
    }
    
    http_response_code($status_code);
    header('Location: ' . $url);
    exit();
}

/**
 * Formater une date selon le format français
 * 
 * @param string $date Date à formater
 * @param string $format Format de sortie
 * @return string Date formatée
 */
function format_date($date, $format = 'd/m/Y H:i') {
    if (empty($date) || $date === '0000-00-00 00:00:00') {
        return '-';
    }
    
    try {
        $datetime = new DateTime($date);
        return $datetime->format($format);
    } catch (Exception $e) {
        return '-';
    }
}

/**
 * Formater une date courte (jj/mm/aaaa)
 * 
 * @param string $date Date à formater
 * @return string Date formatée
 */
function format_date_short($date) {
    return format_date($date, 'd/m/Y');
}

/**
 * Formater une date avec heure (jj/mm/aaaa hh:mm)
 * 
 * @param string $date Date à formater
 * @return string Date formatée
 */
function format_datetime($date) {
    return format_date($date, 'd/m/Y H:i');
}

/**
 * Calculer la différence entre deux dates en texte
 * 
 * @param string $date Date à comparer
 * @param string $reference Date de référence (défaut: maintenant)
 * @return string Différence en texte
 */
function time_ago($date, $reference = null) {
    if (empty($date)) {
        return '-';
    }
    
    try {
        $datetime = new DateTime($date);
        $reference = $reference ? new DateTime($reference) : new DateTime();
        
        $interval = $reference->diff($datetime);
        
        if ($interval->days > 365) {
            return $interval->y . ' an' . ($interval->y > 1 ? 's' : '');
        } elseif ($interval->days > 30) {
            return floor($interval->days / 30) . ' mois';
        } elseif ($interval->days > 0) {
            return $interval->days . ' jour' . ($interval->days > 1 ? 's' : '');
        } elseif ($interval->h > 0) {
            return $interval->h . ' heure' . ($interval->h > 1 ? 's' : '');
        } elseif ($interval->i > 0) {
            return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '');
        } else {
            return 'À l\'instant';
        }
    } catch (Exception $e) {
        return '-';
    }
}

/**
 * Formater un nombre avec séparateurs français
 * 
 * @param float $number Nombre à formater
 * @param int $decimals Nombre de décimales
 * @return string Nombre formaté
 */
function format_number($number, $decimals = 0) {
    if (!is_numeric($number)) {
        return '-';
    }
    
    return number_format($number, $decimals, ',', ' ');
}

/**
 * Formater une taille de fichier
 * 
 * @param int $bytes Taille en octets
 * @param int $precision Précision des décimales
 * @return string Taille formatée
 */
function format_file_size($bytes, $precision = 2) {
    $units = ['o', 'Ko', 'Mo', 'Go', 'To'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Générer un mot de passe aléatoire
 * 
 * @param int $length Longueur du mot de passe
 * @param bool $include_symbols Inclure les symboles
 * @return string Mot de passe généré
 */
function generate_password($length = 8, $include_symbols = false) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    
    if ($include_symbols) {
        $chars .= '!@#$%^&*()_+-=[]{}|;:,.<>?';
    }
    
    return substr(str_shuffle(str_repeat($chars, ceil($length / strlen($chars)))), 0, $length);
}

/**
 * Générer un mot de passe Protection Civile (format PC64-XXXX)
 * 
 * @return string Mot de passe PC64
 */
function generate_pc_password() {
    return 'PC64-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
}

/**
 * Nettoyer et valider un email
 * 
 * @param string $email Email à valider
 * @return string|false Email nettoyé ou false si invalide
 */
function validate_email($email) {
    $email = trim(strtolower($email));
    return filter_var($email, FILTER_VALIDATE_EMAIL) ?: false;
}

/**
 * Nettoyer une chaîne pour un nom/prénom
 * 
 * @param string $name Nom à nettoyer
 * @return string Nom nettoyé
 */
function clean_name($name) {
    $name = trim($name);
    $name = preg_replace('/\s+/', ' ', $name); // Espaces multiples
    return ucwords(strtolower($name));
}

/**
 * Générer un slug à partir d'une chaîne
 * 
 * @param string $string Chaîne à convertir
 * @return string Slug généré
 */
function generate_slug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[àáâãäå]/', 'a', $string);
    $string = preg_replace('/[èéêë]/', 'e', $string);
    $string = preg_replace('/[ìíîï]/', 'i', $string);
    $string = preg_replace('/[òóôõö]/', 'o', $string);
    $string = preg_replace('/[ùúûü]/', 'u', $string);
    $string = preg_replace('/[ç]/', 'c', $string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}

/**
 * Vérifier si une chaîne contient des mots interdits
 * 
 * @param string $text Texte à vérifier
 * @param array $forbidden_words Mots interdits
 * @return bool True si contient des mots interdits
 */
function contains_forbidden_words($text, $forbidden_words = []) {
    $default_forbidden = ['<script', 'javascript:', 'onload=', 'onerror='];
    $forbidden_words = array_merge($default_forbidden, $forbidden_words);
    
    $text = strtolower($text);
    
    foreach ($forbidden_words as $word) {
        if (strpos($text, strtolower($word)) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * Créer un répertoire s'il n'existe pas
 * 
 * @param string $directory Chemin du répertoire
 * @param int $permissions Permissions du répertoire
 * @return bool True si créé ou existe
 */
function ensure_directory($directory, $permissions = 0755) {
    if (!is_dir($directory)) {
        return mkdir($directory, $permissions, true);
    }
    return true;
}

/**
 * Obtenir l'extension d'un fichier
 * 
 * @param string $filename Nom du fichier
 * @return string Extension en minuscules
 */
function get_file_extension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Vérifier si un fichier est autorisé selon son extension
 * 
 * @param string $filename Nom du fichier
 * @param array $allowed_types Types autorisés
 * @return bool True si autorisé
 */
function is_allowed_file_type($filename, $allowed_types = []) {
    if (empty($allowed_types)) {
        $allowed_types = array_merge(
            UPLOAD_ALLOWED_TYPES['image'],
            UPLOAD_ALLOWED_TYPES['document'],
            UPLOAD_ALLOWED_TYPES['archive']
        );
    }
    
    $extension = get_file_extension($filename);
    return in_array($extension, $allowed_types);
}

/**
 * Obtenir l'adresse IP du client
 * 
 * @return string Adresse IP
 */
function get_client_ip() {
    $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ip_keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Logger une action ou erreur
 * 
 * @param string $message Message à logger
 * @param string $level Niveau (info, warning, error)
 * @param array $context Contexte supplémentaire
 */
function log_action($message, $level = 'info', $context = []) {
    if (!LOG_ERRORS) {
        return;
    }
    
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'level' => $level,
        'message' => $message,
        'user_id' => get_current_user_id(),
        'ip' => get_client_ip(),
        'context' => $context
    ];
    
    $log_line = json_encode($log_entry) . "\n";
    
    ensure_directory(LOG_PATH);
    $log_file = LOG_PATH . '/' . date('Y-m-d') . '.log';
    
    file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
}

/**
 * Afficher un message flash en session
 * 
 * @param string $message Message à afficher
 * @param string $type Type de message (success, error, warning, info)
 */
function set_flash_message($message, $type = 'info') {
    init_session();
    $_SESSION['flash_messages'][] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Récupérer et supprimer les messages flash
 * 
 * @return array Messages flash
 */
function get_flash_messages() {
    init_session();
    
    if (!isset($_SESSION['flash_messages'])) {
        return [];
    }
    
    $messages = $_SESSION['flash_messages'];
    unset($_SESSION['flash_messages']);
    
    return $messages;
}

/**
 * Vérifier si c'est un appareil mobile
 * 
 * @return bool True si mobile
 */
function is_mobile() {
    return preg_match('/Mobile|Android|iPhone|iPad/', $_SERVER['HTTP_USER_AGENT'] ?? '');
}

/**
 * Générer une couleur hexadécimale aléatoire
 * 
 * @return string Couleur hexadécimale
 */
function random_color() {
    return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
}

/**
 * Convertir une chaîne en booléen
 * 
 * @param mixed $value Valeur à convertir
 * @return bool Valeur booléenne
 */
function to_boolean($value) {
    if (is_bool($value)) {
        return $value;
    }
    
    if (is_numeric($value)) {
        return (bool) $value;
    }
    
    $value = strtolower(trim($value));
    return in_array($value, ['true', '1', 'yes', 'on', 'oui']);
}

/**
 * Obtenir les informations sur l'application
 * 
 * @return array Informations système
 */
function get_system_info() {
    return [
        'app_name' => APP_NAME,
        'app_version' => APP_VERSION,
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'database_info' => db_get_info(),
        'memory_usage' => format_file_size(memory_get_usage(true)),
        'memory_peak' => format_file_size(memory_get_peak_usage(true)),
        'execution_time' => round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) . ' ms'
    ];
}
?>