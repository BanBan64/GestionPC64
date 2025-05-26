<?php
/**
 * Système d'authentification Protection Civile 64 - v2
 * Fichier: shared/includes/auth.php
 */

// Sécurité : vérifier que le fichier est inclus correctement
if (!defined('PROTEC64_V2')) {
    die('Accès direct interdit');
}

/**
 * Initialiser la session si elle n'est pas déjà démarrée
 */
function init_session() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configuration sécurisée de la session
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Lax');
        
        session_start();
    }
}

// Initialiser la session automatiquement
init_session();

/**
 * Vérifier si un utilisateur est connecté
 */
function is_logged_in() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_id']);
}

/**
 * Connecter un utilisateur
 */
function login_user($user) {
    // Régénérer l'ID de session pour la sécurité
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
    
    // Stocker les informations utilisateur en session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_nom'] = $user['nom'];
    $_SESSION['user_prenom'] = $user['prenom'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_antenne_id'] = $user['antenne_id'];
    $_SESSION['user_actif'] = $user['actif'];
    $_SESSION['user_identifiant_eprotec'] = $user['identifiant_eprotec'] ?? '';
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    
    // Log de la connexion
    log_action("Connexion utilisateur: " . $user['nom'] . ' ' . $user['prenom'], 'info', [
        'user_id' => $user['id'],
        'ip' => get_client_ip(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
    
    return true;
}

/**
 * Déconnecter un utilisateur
 */
function logout_user() {
    if (is_logged_in()) {
        // Log de la déconnexion
        log_action("Déconnexion utilisateur: " . $_SESSION['user_nom'] . ' ' . $_SESSION['user_prenom'], 'info', [
            'user_id' => $_SESSION['user_id'],
            'ip' => get_client_ip()
        ]);
    }
    
    // Détruire toutes les variables de session
    $_SESSION = array();
    
    // Détruire le cookie de session si il existe
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Détruire la session
    session_destroy();
    
    return true;
}

/**
 * Obtenir les informations de l'utilisateur connecté depuis la session
 */
function get_current_user_data() {
    if (!is_logged_in()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'nom' => $_SESSION['user_nom'],
        'prenom' => $_SESSION['user_prenom'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role'],
        'antenne_id' => $_SESSION['user_antenne_id'],
        'actif' => $_SESSION['user_actif'],
        'identifiant_eprotec' => $_SESSION['user_identifiant_eprotec'] ?? ''
    ];
}

/**
 * Obtenir l'ID de l'utilisateur connecté
 */
function get_current_user_id() {
    return is_logged_in() ? $_SESSION['user_id'] : null;
}

/**
 * Obtenir le rôle de l'utilisateur connecté
 */
function get_current_user_role() {
    return is_logged_in() ? $_SESSION['user_role'] : null;
}

/**
 * Obtenir l'antenne de l'utilisateur connecté
 */
function get_current_user_antenne() {
    return is_logged_in() ? $_SESSION['user_antenne_id'] : null;
}

/**
 * Vérifier si l'utilisateur a un rôle spécifique
 */
function has_role($role) {
    if (!is_logged_in()) {
        return false;
    }
    
    $user_role = $_SESSION['user_role'];
    
    // L'admin a tous les droits
    if ($user_role === 'admin') {
        return true;
    }
    
    // Le responsable peut accéder aux fonctions conducteur
    if ($user_role === 'responsable' && $role === 'conducteur') {
        return true;
    }
    
    return $user_role === $role;
}

/**
 * Vérifier si l'utilisateur est administrateur
 */
function is_admin() {
    return has_role('admin');
}

/**
 * Vérifier si l'utilisateur est administrateur ou responsable
 */
function is_admin_or_responsable() {
    return has_role('admin') || has_role('responsable');
}

/**
 * Vérifier l'accès à un module selon le rôle
 */
function can_access_module($module) {
    if (!is_logged_in()) {
        return false;
    }
    
    $user_role = $_SESSION['user_role'];
    
    // Configuration des accès par rôle
    $module_access = [
        'admin' => ['vehicules', 'habillement', 'inventaires', 'pharmacie', 'materiel', 'frais', 'admin'],
        'responsable' => ['vehicules', 'habillement', 'inventaires', 'pharmacie', 'materiel', 'frais'],
        'conducteur' => ['vehicules', 'inventaires', 'frais']
    ];
    
    return isset($module_access[$user_role]) && in_array($module, $module_access[$user_role]);
}

/**
 * Forcer la connexion (redirection si non connecté)
 */
function require_login() {
    if (!is_logged_in()) {
        // Stocker l'URL demandée pour redirection après connexion
        $redirect_url = $_SERVER['REQUEST_URI'] ?? '';
        redirect(BASE_URL . '/login.php?redirect=' . urlencode($redirect_url));
    }
    
    // Vérifier si la session n'a pas expiré
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > SESSION_LIFETIME) {
        logout_user();
        redirect(BASE_URL . '/login.php?message=session_expired');
    }
}

/**
 * Forcer l'accès administrateur
 */
function require_admin() {
    require_login();
    
    if (!is_admin()) {
        redirect(BASE_URL . '/index.php?error=access_denied');
    }
}

/**
 * Forcer l'accès administrateur ou responsable
 */
function require_admin_or_responsable() {
    require_login();
    
    if (!is_admin_or_responsable()) {
        redirect(BASE_URL . '/index.php?error=access_denied');
    }
}

/**
 * Vérifier l'accès à un module spécifique
 */
function require_module_access($module) {
    require_login();
    
    if (!can_access_module($module)) {
        redirect(BASE_URL . '/index.php?error=module_access_denied');
    }
}

/**
 * Générer un token CSRF
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifier un token CSRF
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Valider un mot de passe selon les critères
 */
function validate_password($password) {
    $errors = [];
    
    if (strlen($password) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
    }
    
    if (strlen($password) > 100) {
        $errors[] = "Le mot de passe ne peut pas dépasser 100 caractères";
    }
    
    return $errors;
}

/**
 * Vérifier si l'utilisateur peut modifier un autre utilisateur
 */
function can_edit_user($target_user_id) {
    if (!is_logged_in()) {
        return false;
    }
    
    $current_user_id = get_current_user_id();
    
    // L'utilisateur peut toujours modifier son propre profil (partiellement)
    if ($current_user_id == $target_user_id) {
        return true;
    }
    
    // Seuls les admin et responsables peuvent modifier d'autres utilisateurs
    return is_admin_or_responsable();
}

/**
 * Vérifier les permissions d'antenne
 */
function can_access_antenne($antenne_id) {
    if (!is_logged_in()) {
        return false;
    }
    
    // Les admins peuvent accéder à toutes les antennes
    if (is_admin()) {
        return true;
    }
    
    // Les responsables peuvent accéder à leur antenne
    if (is_admin_or_responsable()) {
        return $_SESSION['user_antenne_id'] == $antenne_id;
    }
    
    // Les conducteurs peuvent accéder à leur antenne
    return $_SESSION['user_antenne_id'] == $antenne_id;
}

/**
 * Obtenir les antennes accessibles par l'utilisateur
 */
function get_accessible_antennes() {
    if (!is_logged_in()) {
        return [];
    }
    
    try {
        if (is_admin()) {
            // Admin : toutes les antennes
            return db_fetch_all("SELECT * FROM " . DB_PREFIX . "antennes ORDER BY nom");
        } else {
            // Autres : seulement leur antenne
            return db_fetch_all("SELECT * FROM " . DB_PREFIX . "antennes WHERE id = ? ORDER BY nom", [$_SESSION['user_antenne_id']]);
        }
    } catch (Exception $e) {
        log_action("Erreur lors de la récupération des antennes: " . $e->getMessage(), 'error');
        return [];
    }
}

/**
 * Vérifier si une session est valide et active
 */
function is_session_valid() {
    if (!is_logged_in()) {
        return false;
    }
    
    // Vérifier l'expiration de la session
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > SESSION_LIFETIME) {
        return false;
    }
    
    // Vérifier que l'utilisateur est toujours actif
    try {
        $user = db_fetch("SELECT actif FROM " . DB_PREFIX . "utilisateurs WHERE id = ?", [$_SESSION['user_id']]);
        return $user && $user['actif'] == 1;
    } catch (Exception $e) {
        log_action("Erreur lors de la vérification de session: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Nettoyer les sessions expirées (à appeler périodiquement)
 */
function cleanup_expired_sessions() {
    // Cette fonction pourrait être appelée par un cron job
    // Pour l'instant, on se contente de vérifier la session actuelle
    if (!is_session_valid()) {
        logout_user();
        return false;
    }
    
    return true;
}