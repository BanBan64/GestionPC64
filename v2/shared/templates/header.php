<?php
/**
 * Template header Protection Civile 64 - v2
 * 
 * En-tête commun à toutes les pages avec HTML, CSS, JS
 * et définition des variables CSS Protection Civile
 */

// Empêcher l'accès direct
if (!defined('PROTEC64_V2')) {
    die('Accès interdit');
}

// Inclure les fichiers nécessaires
require_once SHARED_PATH . '/includes/config.php';
require_once SHARED_PATH . '/includes/auth.php';

// Variables pour le titre de la page
$page_title = $page_title ?? 'Protection Civile 64';
$page_description = $page_description ?? APP_DESCRIPTION;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo h($page_description); ?>">
    <meta name="author" content="Protection Civile 64">
    <title><?php echo h($page_title); ?> - <?php echo APP_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/images/favicon.png">
    <link rel="apple-touch-icon" href="<?php echo ASSETS_URL; ?>/images/favicon.png">
    <link rel="shortcut icon" href="<?php echo ASSETS_URL; ?>/images/favicon.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- CSS personnalisé Protection Civile -->
    <link href="<?php echo ASSETS_URL; ?>/css/protec64.css" rel="stylesheet">
    
    <!-- Variables CSS Protection Civile -->
    <style>
        :root {
            --pc-blue: <?php echo COLORS['primary']; ?>;
            --pc-orange: <?php echo COLORS['secondary']; ?>;
            --pc-success: <?php echo COLORS['success']; ?>;
            --pc-danger: <?php echo COLORS['danger']; ?>;
            --pc-warning: <?php echo COLORS['warning']; ?>;
            --pc-info: <?php echo COLORS['info']; ?>;
            --pc-light: <?php echo COLORS['light']; ?>;
            --pc-dark: <?php echo COLORS['dark']; ?>;
        }
        
        /* Styles de base Protection Civile */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        /* Navigation principale */
        .navbar-pc {
            background: var(--pc-blue) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-pc .navbar-brand {
            color: white !important;
            font-weight: bold;
            font-size: 1.3rem;
        }
        
        .navbar-pc .navbar-brand:hover {
            color: var(--pc-orange) !important;
        }
        
        .navbar-pc .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            padding: 0.8rem 1rem !important;
            transition: all 0.3s ease;
        }
        
        .navbar-pc .nav-link:hover {
            color: var(--pc-orange) !important;
            background-color: rgba(255,255,255,0.1);
            border-radius: 4px;
        }
        
        .navbar-pc .nav-link.active {
            color: var(--pc-orange) !important;
            background-color: rgba(240,135,0,0.2);
            border-radius: 4px;
        }
        
        /* Logo dans la navigation */
        .navbar-brand img {
            height: 32px;
            width: 32px;
            margin-right: 10px;
        }
        
        /* Dropdown utilisateur */
        .navbar-pc .dropdown-menu {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .navbar-pc .dropdown-item {
            color: #333;
            padding: 0.7rem 1.5rem;
        }
        
        .navbar-pc .dropdown-item:hover {
            background-color: var(--pc-orange);
            color: white;
        }
        
        /* Badge antenne */
        .badge-antenne {
            background-color: var(--pc-orange);
            color: white;
            font-size: 0.75rem;
            padding: 0.3rem 0.6rem;
            border-radius: 12px;
        }
        
        /* Boutons Protection Civile */
        .btn-pc-primary {
            background-color: var(--pc-blue);
            border-color: var(--pc-blue);
            color: white;
        }
        
        .btn-pc-primary:hover {
            background-color: #003366;
            border-color: #003366;
            color: white;
        }
        
        .btn-pc-secondary {
            background-color: var(--pc-orange);
            border-color: var(--pc-orange);
            color: white;
        }
        
        .btn-pc-secondary:hover {
            background-color: #e67600;
            border-color: #e67600;
            color: white;
        }
        
        .btn-outline-pc-primary {
            color: var(--pc-blue);
            border-color: var(--pc-blue);
        }
        
        .btn-outline-pc-primary:hover {
            background-color: var(--pc-blue);
            border-color: var(--pc-blue);
            color: white;
        }
        
        .btn-outline-pc-secondary {
            color: var(--pc-orange);
            border-color: var(--pc-orange);
        }
        
        .btn-outline-pc-secondary:hover {
            background-color: var(--pc-orange);
            border-color: var(--pc-orange);
            color: white;
        }
        
        /* Cards Protection Civile */
        .card-pc-header {
            background: var(--pc-orange);
            color: white;
            border: none;
        }
        
        .card-pc-blue-header {
            background: var(--pc-blue);
            color: white;
            border: none;
        }
        
        /* Alertes Protection Civile */
        .alert-pc-primary {
            background-color: rgba(0,64,128,0.1);
            border-color: var(--pc-blue);
            color: var(--pc-blue);
        }
        
        .alert-pc-secondary {
            background-color: rgba(240,135,0,0.1);
            border-color: var(--pc-orange);
            color: #b8610a;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .navbar-pc .navbar-nav {
                margin-top: 1rem;
            }
            
            .navbar-pc .nav-link {
                text-align: center;
                margin: 0.2rem 0;
            }
        }
        
        /* Animation de chargement */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        /* Styles pour les tableaux */
        .table-pc {
            --bs-table-striped-bg: rgba(0,64,128,0.05);
        }
        
        .table-pc thead th {
            background-color: var(--pc-blue);
            color: white;
            border: none;
            font-weight: 600;
        }
        
        /* Sidebar si utilisée */
        .sidebar-pc {
            background-color: white;
            border-right: 1px solid #dee2e6;
            min-height: calc(100vh - 76px);
        }
        
        .sidebar-pc .nav-link {
            color: #333;
            padding: 0.8rem 1.5rem;
            border-left: 3px solid transparent;
        }
        
        .sidebar-pc .nav-link:hover {
            background-color: #f8f9fa;
            border-left-color: var(--pc-orange);
            color: var(--pc-orange);
        }
        
        .sidebar-pc .nav-link.active {
            background-color: rgba(0,64,128,0.1);
            border-left-color: var(--pc-blue);
            color: var(--pc-blue);
            font-weight: 600;
        }
    </style>
    
    <!-- JavaScript Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript personnalisé -->
    <script>
        // Configuration globale JavaScript
        window.PROTEC64 = {
            BASE_URL: '<?php echo BASE_URL; ?>',
            ASSETS_URL: '<?php echo ASSETS_URL; ?>',
            USER_ID: <?php echo get_current_user_id() ?: 'null'; ?>,
            USER_ROLE: '<?php echo get_current_user_role() ?: ''; ?>',
            CSRF_TOKEN: '<?php echo generate_csrf_token(); ?>'
        };
        
        // Fonction utilitaire pour les requêtes AJAX
        function makeRequest(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.PROTEC64.CSRF_TOKEN
                }
            };
            
            return fetch(window.PROTEC64.BASE_URL + '/' + url.replace(/^\//, ''), {
                ...defaultOptions,
                ...options,
                headers: { ...defaultOptions.headers, ...options.headers }
            });
        }
        
        // Fonction pour afficher les notifications
        function showNotification(message, type = 'info') {
            const alertClass = {
                'success': 'alert-success',
                'error': 'alert-danger',
                'warning': 'alert-warning',
                'info': 'alert-info'
            };
            
            const alertHTML = `
                <div class="alert ${alertClass[type]} alert-dismissible fade show" role="alert">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'}"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Insérer l'alerte en haut du contenu principal
            const mainContent = document.querySelector('main') || document.querySelector('.container');
            if (mainContent) {
                mainContent.insertAdjacentHTML('afterbegin', alertHTML);
            }
        }
        
        // Auto-focus sur le premier champ de formulaire visible
        document.addEventListener('DOMContentLoaded', function() {
            const firstInput = document.querySelector('input:not([type="hidden"]):not([readonly]):not([disabled])');
            if (firstInput && !window.location.hash) {
                firstInput.focus();
            }
        });
    </script>
</head>
<body>
    <?php
    // Charger les messages flash
    $flash_messages = get_flash_messages();
    ?>
    
    <!-- Messages flash -->
    <?php if (!empty($flash_messages)): ?>
        <div id="flash-messages">
            <?php foreach ($flash_messages as $flash): ?>
                <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : h($flash['type']); ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-<?php echo $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-triangle' : 'info-circle'); ?>"></i>
                    <?php echo h($flash['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>