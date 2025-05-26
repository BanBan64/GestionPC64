<?php
/**
 * Configuration générale Protection Civile 64 - v2
 * 
 * Ce fichier contient toutes les constantes et paramètres
 * de configuration pour l'ensemble du système unifié.
 */

// Empêcher l'accès direct
if (!defined('PROTEC64_V2')) {
    define('PROTEC64_V2', true);
}

// Gestion des erreurs (à adapter selon environnement)
error_reporting(E_ALL);
ini_set('display_errors', 1); // Mettre à 0 en production

// Configuration de la base de données
define('DB_HOST', 'protenkbdpc64.mysql.db');
define('DB_NAME', 'protenkbdpc64');
define('DB_USER', 'protenkbdpc64');
define('DB_PASS', 'BanBan0453');
define('DB_CHARSET', 'utf8mb4');
define('DB_PREFIX', 'veh_'); // Préfixe de vos tables existantes

// Configuration des chemins
define('BASE_PATH', dirname(dirname(__DIR__))); // Chemin vers v2/
define('SHARED_PATH', BASE_PATH . '/shared');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('ASSETS_PATH', BASE_PATH . '/shared/assets');

// URLs de base
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . $host . '/v2'; // À adapter selon votre structure
define('BASE_URL', $base_url);
define('ASSETS_URL', BASE_URL . '/shared/assets');

// Configuration de l'application
define('APP_NAME', 'Protection Civile 64');
define('APP_VERSION', '2.0');
define('APP_DESCRIPTION', 'Système de gestion unifié Protection Civile 64');

// Configuration des sessions
define('SESSION_NAME', 'PROTEC64_V2_SESSION');
define('SESSION_LIFETIME', 7200); // 2 heures en secondes
define('SESSION_REGENERATE', 1800); // Régénérer l'ID toutes les 30 minutes

// Configuration des antennes
define('ANTENNES', [
    1 => 'Antenne Pays Basque',
    2 => 'Antenne Béarn', 
    3 => 'Réserve dept'
]);

// Configuration des rôles utilisateurs
define('ROLES', [
    'conducteur' => 'Conducteur',
    'responsable' => 'Responsable',
    'admin' => 'Administrateur'
]);

// Configuration des modules
define('MODULES', [
    'vehicules' => [
        'name' => 'Véhicules',
        'icon' => 'bi-truck',
        'color' => '#004080',
        'roles' => ['conducteur', 'responsable', 'admin']
    ],
    'habillement' => [
        'name' => 'Habillement', 
        'icon' => 'bi-person-badge',
        'color' => '#F08700',
        'roles' => ['responsable', 'admin']
    ],
    'inventaires' => [
        'name' => 'Inventaires',
        'icon' => 'bi-clipboard-check',
        'color' => '#198754',
        'roles' => ['conducteur', 'responsable', 'admin']
    ],
    'pharmacie' => [
        'name' => 'Pharmacie',
        'icon' => 'bi-heart-pulse',
        'color' => '#dc3545',
        'roles' => ['responsable', 'admin']
    ],
    'materiel' => [
        'name' => 'Matériel',
        'icon' => 'bi-tools',
        'color' => '#6f42c1',
        'roles' => ['responsable', 'admin']
    ],
    'frais' => [
        'name' => 'Frais',
        'icon' => 'bi-receipt',
        'color' => '#fd7e14',
        'roles' => ['conducteur', 'responsable', 'admin']
    ]
]);

// Configuration des types de véhicules
define('TYPES_VEHICULES', [
    'VPS' => 'Véhicule de Première Secours',
    'VL' => 'Véhicule Léger',
    'VTUTP' => 'Véhicule Tout Usage Transport de Personnel'  
]);

// Configuration des statuts de véhicules
define('STATUTS_VEHICULES', [
    'disponible' => 'Disponible',
    'en_sortie' => 'En sortie',
    'maintenance' => 'En maintenance',
    'hors_service' => 'Hors service'
]);

// Configuration des uploads
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10 MB
define('UPLOAD_ALLOWED_TYPES', [
    'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
    'archive' => ['zip', 'rar']
]);

// Configuration de sécurité
define('PASSWORD_MIN_LENGTH', 6);
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Configuration email (si besoin pour notifications)
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_FROM_EMAIL', 'noreply@protectioncivile64.org');
define('SMTP_FROM_NAME', 'Protection Civile 64');

// Configuration de debug et logs
define('DEBUG_MODE', true); // Passer à false en production
define('LOG_ERRORS', true);
define('LOG_PATH', BASE_PATH . '/logs');

// Timezone
date_default_timezone_set('Europe/Paris');

// Configuration des couleurs Protection Civile
define('COLORS', [
    'primary' => '#004080',   // Bleu PC
    'secondary' => '#F08700', // Orange PC
    'success' => '#198754',
    'danger' => '#dc3545',
    'warning' => '#ffc107',
    'info' => '#0dcaf0',
    'light' => '#f8f9fa',
    'dark' => '#212529'
]);

// Fonction utilitaire pour vérifier la configuration
function check_config() {
    $required_constants = [
        'DB_HOST', 'DB_NAME', 'DB_USER', 'BASE_PATH', 'BASE_URL'
    ];
    
    foreach ($required_constants as $constant) {
        if (!defined($constant)) {
            die("Erreur de configuration : La constante $constant n'est pas définie.");
        }
    }
    
    return true;
}

// Vérification automatique de la configuration
check_config();

// Configuration chargée avec succès
if (DEBUG_MODE) {
    // En mode debug, on peut afficher des infos
    // error_log("Configuration Protection Civile 64 v2 chargée avec succès");
}
?>