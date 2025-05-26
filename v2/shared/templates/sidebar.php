<?php
/**
 * Template sidebar Protection Civile 64 - v2
 * 
 * Menu latéral contextuel pour les modules et l'administration
 * Affichage conditionnel selon le module actuel
 */

// Empêcher l'accès direct
if (!defined('PROTEC64_V2')) {
    die('Accès interdit');
}

// Vérifier si l'utilisateur est connecté
if (!is_logged_in()) {
    return; // Pas de sidebar si non connecté
}

// Récupérer les informations utilisateur
$current_user_role = get_current_user_role();

// Déterminer le module actuel depuis l'URL
$current_page = $_SERVER['REQUEST_URI'];
$current_module = '';
$current_section = '';

if (strpos($current_page, '/vehicules/') !== false) {
    $current_module = 'vehicules';
    if (strpos($current_page, '/gestion/') !== false) $current_section = 'gestion';
    elseif (strpos($current_page, '/sorties/') !== false) $current_section = 'sorties';
    elseif (strpos($current_page, '/rapports/') !== false) $current_section = 'rapports';
    elseif (strpos($current_page, '/mobile/') !== false) $current_section = 'mobile';
} elseif (strpos($current_page, '/habillement/') !== false) {
    $current_module = 'habillement';
    if (strpos($current_page, '/stocks/') !== false) $current_section = 'stocks';
    elseif (strpos($current_page, '/attribution/') !== false) $current_section = 'attribution';
    elseif (strpos($current_page, '/rapports/') !== false) $current_section = 'rapports';
} elseif (strpos($current_page, '/inventaires/') !== false) {
    $current_module = 'inventaires';
    if (strpos($current_page, '/modeles/') !== false) $current_section = 'modeles';
    elseif (strpos($current_page, '/controles/') !== false) $current_section = 'controles';
    elseif (strpos($current_page, '/mobile/') !== false) $current_section = 'mobile';
} elseif (strpos($current_page, '/pharmacie/') !== false) {
    $current_module = 'pharmacie';
    if (strpos($current_page, '/stocks/') !== false) $current_section = 'stocks';
    elseif (strpos($current_page, '/scan/') !== false) $current_section = 'scan';
    elseif (strpos($current_page, '/alertes/') !== false) $current_section = 'alertes';
} elseif (strpos($current_page, '/materiel/') !== false) {
    $current_module = 'materiel';
    if (strpos($current_page, '/stocks/') !== false) $current_section = 'stocks';
    elseif (strpos($current_page, '/attribution/') !== false) $current_section = 'attribution';
} elseif (strpos($current_page, '/frais/') !== false) {
    $current_module = 'frais';
    if (strpos($current_page, '/demandes/') !== false) $current_section = 'demandes';
    elseif (strpos($current_page, '/validation/') !== false) $current_section = 'validation';
    elseif (strpos($current_page, '/mobile/') !== false) $current_section = 'mobile';
} elseif (strpos($current_page, '/admin/') !== false) {
    $current_module = 'admin';
    if (strpos($current_page, '/utilisateurs') !== false) $current_section = 'utilisateurs';
}

// Fonction pour vérifier si un lien de sidebar est actif
function is_active_sidebar($section, $current) {
    return $section === $current ? 'active' : '';
}

// Si aucun module détecté, ne pas afficher la sidebar
if (empty($current_module)) {
    return;
}
?>

<div class="sidebar-pc">
    <?php if ($current_module === 'vehicules' && can_access_module('vehicules')): ?>
        <!-- SIDEBAR MODULE VÉHICULES -->
        <div class="sidebar-header p-3 border-bottom">
            <h6 class="mb-0 text-pc-blue">
                <i class="bi bi-truck me-2"></i>Module Véhicules
            </h6>
        </div>
        
        <nav class="nav flex-column">
            <!-- Dashboard véhicules -->
            <a class="nav-link <?php echo ($current_section === '' && $current_module === 'vehicules') ? 'active' : ''; ?>" 
               href="<?php echo BASE_URL; ?>/vehicules/">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            
            <!-- Gestion véhicules -->
            <a class="nav-link <?php echo is_active_sidebar('gestion', $current_section); ?>" 
               href="<?php echo BASE_URL; ?>/vehicules/gestion/">
                <i class="bi bi-car-front"></i> Gestion véhicules
            </a>
            
            <!-- Sorties véhicules -->
            <a class="nav-link <?php echo is_active_sidebar('sorties', $current_section); ?>" 
               href="<?php echo BASE_URL; ?>/vehicules/sorties/">
                <i class="bi bi-arrow-right-circle"></i> Sorties véhicules
            </a>
            
            <!-- Nouvelle sortie (accès rapide) -->
            <a class="nav-link ps-4" href="<?php echo BASE_URL; ?>/vehicules/sorties/nouvelle.php">
                <i class="bi bi-plus-circle"></i> Nouvelle sortie
            </a>
            
            <!-- Interface mobile -->
            <?php if (is_mobile() || DEBUG_MODE): ?>
            <a class="nav-link <?php echo is_active_sidebar('mobile', $current_section); ?>" 
               href="<?php echo BASE_URL; ?>/vehicules/mobile/">
                <i class="bi bi-phone"></i> Interface mobile
            </a>
            <?php endif; ?>
            
            <!-- Rapports -->
            <a class="nav-link <?php echo is_active_sidebar('rapports', $current_section); ?>" 
               href="<?php echo BASE_URL; ?>/vehicules/rapports/">
                <i class="bi bi-bar-chart-line"></i> Rapports
            </a>
        </nav>

    <?php elseif ($current_module === 'habillement' && can_access_module('habillement')): ?>
        <!-- SIDEBAR MODULE HABILLEMENT -->
        <div class="sidebar-header p-3 border-bottom">
            <h6 class="mb-0 text-pc-orange">
                <i class="bi bi-person-badge me-2"></i>Module Habillement
            </h6>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link <?php echo ($current_section === '' && $current_module === 'habillement') ? 'active' : ''; ?>" 
               href="<?php echo BASE_URL; ?>/habillement/">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            
            <a class="nav-link <?php echo is_active_sidebar('stocks', $current_section); ?>" 
               href="<?php echo BASE_URL; ?>/habillement/stocks/">
                <i class="bi bi-boxes"></i> Gestion stocks
            </a>
            
            <a class="nav-link <?php echo is_active_sidebar('attribution', $current_section); ?>" 
               href="<?php echo BASE_URL; ?>/habillement/attribution/">
                <i class="bi bi-person-check"></i> Attributions
            </a>
            
            <a class="nav-link <?php echo is_active_sidebar('rapports', $current_section); ?>" 
               href="<?php echo BASE_URL; ?>/habillement/rapports/">
                <i class="bi bi-bar-chart-line"></i> Rapports
            </a>
        </nav>

    <?php elseif ($current_module === 'inventaires' && can_access_module('inventaires')): ?>
        <!-- SIDEBAR MODULE INVENTAIRES -->
        <div class="sidebar-header p-3 border-bottom">
            <h6 class="mb-0 text-success">
                <i class="bi bi-clipboard-check me-2"></i>Module Inventaires
            </h6>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link <?php echo ($current_section === '' && $current_module === 'inventaires') ? 'active' : ''; ?>" 
               href="<?php echo BASE_URL; ?>/inventaires/">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            
            <?php if (is_admin_or_responsable()): ?>
            <a class="nav-link <?php echo is_active_sidebar('modeles', $current_section); ?>" 
               href="<?php echo BASE_URL; ?>/inventaires/modeles/">
                <i class="bi bi-file-earmark-text"></i> Modèles check-lists
            </a>
            <?php endif; ?>
            
            <a class="nav-link <?php echo is_active_sidebar('controles', $current_section); ?>" 
               href="<?php echo BASE_URL; ?>/inventaires/controles/">
                <i class="bi bi-check2-square"></i> Contrôles
            </a>
            
            <a class="nav-link <?php echo is_active_sidebar('mobile', $current_section); ?>" 
               href="<?php echo BASE_URL; ?>/inventaires/mobile/">
                <i class="bi bi-phone"></i> Check-lists mobiles
            </a>
        </nav>

    <?php elseif ($current_module === 'pharmacie' && can_access_module('pharmacie')): ?>
        <!-- SIDEBAR MODULE PHARMACIE -->
        <div class="sidebar-header p-3 border-bottom">
            <h6 class="mb-0 text-danger">
                <i class="bi bi-heart-pulse me-2"></i>Module Pharmacie
            </h6>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link <?php echo ($current_section === '' && $current_module === 'pharmacie') ? 'active' : ''; ?>" 
               href="<?php echo BASE_URL; ?>/pharmacie/">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            
            <a class="nav-link <?php echo is_active_sidebar('stocks', $current_section); ?>" 
               href="<?php echo BASE_URL; ?>/pharmacie/stocks/">
                <i class="bi bi-box2"></i> Gestion stocks
            </a>
            
            <a class="nav-link <?php echo is_active_sidebar('scan', $current_section); ?>" 
               href="<?php echo BASE_URL; ?>/pharmacie/scan/">
                <i class="bi bi-qr-code-scan"></i> Scanner produits
            </a>
            
            <a class="nav-link <?php echo is_active_sidebar('alertes', $current_section); ?>" 
               href="<?php echo BASE_URL; ?>/pharmacie/alertes/">
                <i class="bi bi-exclamation-triangle"></i> Alertes
            </a>
        </nav>

    <?php elseif ($current_module === 'materiel' && can_access_module('materiel')): ?>
        <!-- SIDEBAR MODULE MATÉRIEL -->
        <div class="sidebar-header p-3 border-bottom">
            <h6 class="mb-0 text-purple">
                <i class="bi bi-tools me-2"></i>Module Matériel
            </h6>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link <?php echo ($current_section === '' && $current_module === 'materiel') ? 'active' : ''; ?>" 
               href="<?php echo BASE_URL; ?>/materiel/">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            
            <a class="nav-link <?php echo is_active_sidebar('stocks', $current_section); ?>" 
               href="<?php echo BASE_URL; ?>/materiel/stocks/">
                <i class="bi bi-box2"></i> Inventaire
            </a>
            
            <a class="nav-link <?php echo is_active_sidebar('attribution', $current_section); ?>" 
               href="<?php echo BASE_URL; ?>/materiel/attribution/">
                <i class="bi bi-arrow-left-right"></i> Prêts matériel
            </a>
        </nav>

    <?php elseif ($current_module === 'frais' && can_access_module('frais')): ?>
        <!-- SIDEBAR MODULE FRAIS -->
        <div class="sidebar-header p-3 border-bottom">
            <h6 class="mb-0 text-warning">
                <i class="bi bi-receipt me-2"></i>Module Frais
            </h6>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link <?php echo ($current_section === '' && $current_module === 'frais') ? 'active' : ''; ?>" 
               href="<?php echo BASE_URL; ?>/frais/">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            
            <a class="nav-link <?php echo is_active_sidebar('demandes', $current_section); ?>" 
               href="<?php echo BASE_URL; ?>/frais/demandes/">
                <i class="bi bi-file-earmark-plus"></i> Mes demandes
            </a>
            
            <!-- Nouvelle demande (accès rapide) -->
            <a class="nav-link ps-4" href="<?php echo BASE_URL; ?>/frais/demandes/nouvelle.php">
                <i class="bi bi-plus-circle"></i> Nouvelle demande
            </a>
            
            <?php if (is_admin_or_responsable()): ?>
            <a class="nav-link <?php echo is_active_sidebar('validation', $current_section); ?>" 
               href="<?php echo BASE_URL; ?>/frais/validation/">
                <i class="bi bi-check-circle"></i> Validation
            </a>
            <?php endif; ?>
            
            <a class="nav-link <?php echo is_active_sidebar('mobile', $current_section); ?>" 
               href="<?php echo BASE_URL; ?>/frais/mobile/">
                <i class="bi bi-camera"></i> Photos factures
            </a>
        </nav>

    <?php elseif ($current_module === 'admin' && is_admin_or_responsable()): ?>
        <!-- SIDEBAR ADMINISTRATION -->
        <div class="sidebar-header p-3 border-bottom">
            <h6 class="mb-0 text-secondary">
                <i class="bi bi-gear-fill me-2"></i>Administration
            </h6>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link <?php echo ($current_section === '' && $current_module === 'admin') ? 'active' : ''; ?>" 
               href="<?php echo BASE_URL; ?>/admin/">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            
            <a class="nav-link <?php echo is_active_sidebar('utilisateurs', $current_section); ?>" 
               href="<?php echo BASE_URL; ?>/admin/utilisateurs.php">
                <i class="bi bi-people"></i> Utilisateurs
            </a>
            
            <!-- Liens rapides administration -->
            <a class="nav-link ps-4" href="<?php echo BASE_URL; ?>/admin/utilisateur_nouveau.php">
                <i class="bi bi-person-plus"></i> Nouvel utilisateur
            </a>
            
            <a class="nav-link ps-4" href="<?php echo BASE_URL; ?>/admin/import_utilisateurs.php">
                <i class="bi bi-upload"></i> Import Excel
            </a>
        </nav>
    <?php endif; ?>

    <!-- Section statistiques rapides en bas de sidebar -->
    <?php if (!empty($current_module)): ?>
    <div class="sidebar-footer mt-auto p-3 border-top bg-light">
        <h6 class="mb-2 text-muted small">Statistiques rapides</h6>
        <div class="small text-muted">
            <?php if ($current_module === 'vehicules'): ?>
                <?php
                $stats_vehicules = [
                    'total' => db_count('vehicules'),
                    'disponibles' => db_count('vehicules', 'statut = ?', ['disponible']),
                    'en_sortie' => db_count('vehicules', 'statut = ?', ['en_sortie'])
                ];
                ?>
                <div class="d-flex justify-content-between">
                    <span>Véhicules total :</span>
                    <strong><?php echo $stats_vehicules['total']; ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Disponibles :</span>
                    <strong class="text-success"><?php echo $stats_vehicules['disponibles']; ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>En sortie :</span>
                    <strong class="text-warning"><?php echo $stats_vehicules['en_sortie']; ?></strong>
                </div>
            <?php elseif ($current_module === 'admin'): ?>
                <?php
                $stats_users = [
                    'total' => db_count('utilisateurs'),
                    'actifs' => db_count('utilisateurs', 'actif = 1'),
                    'admins' => db_count('utilisateurs', 'role = ?', ['admin'])
                ];
                ?>
                <div class="d-flex justify-content-between">
                    <span>Utilisateurs :</span>
                    <strong><?php echo $stats_users['total']; ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Actifs :</span>
                    <strong class="text-success"><?php echo $stats_users['actifs']; ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Admins :</span>
                    <strong class="text-info"><?php echo $stats_users['admins']; ?></strong>
                </div>
            <?php else: ?>
                <div class="text-center">
                    <i class="bi bi-graph-up text-muted"></i>
                    <br>Statistiques<br>à venir
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.text-purple {
    color: #6f42c1 !important;
}

.sidebar-footer {
    position: sticky;
    bottom: 0;
}

@media (max-width: 768px) {
    .sidebar-pc {
        position: fixed;
        top: 76px;
        left: -250px;
        width: 250px;
        height: calc(100vh - 76px);
        background: white;
        z-index: 1000;
        transition: left 0.3s ease;
        overflow-y: auto;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }
    
    .sidebar-pc.show {
        left: 0;
    }
    
    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 999;
        display: none;
    }
    
    .sidebar-overlay.show {
        display: block;
    }
}
</style>