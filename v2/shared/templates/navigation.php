<?php
/**
 * Template navigation Protection Civile 64 - v2
 * 
 * Barre de navigation principale avec menu adaptatif selon les rôles
 */

// Empêcher l'accès direct
if (!defined('PROTEC64_V2')) {
    die('Accès interdit');
}

// Vérifier si l'utilisateur est connecté
if (!is_logged_in()) {
    return; // Pas de navigation si non connecté
}

// Récupérer les informations utilisateur
$current_user = get_current_user();
$current_user_role = get_current_user_role();
$current_antenne = get_current_user_antenne();

// Déterminer la page courante pour le menu actif
$current_page = $_SERVER['REQUEST_URI'];
$current_module = '';

// Détecter le module actuel
if (strpos($current_page, '/vehicules/') !== false) {
    $current_module = 'vehicules';
} elseif (strpos($current_page, '/habillement/') !== false) {
    $current_module = 'habillement';
} elseif (strpos($current_page, '/inventaires/') !== false) {
    $current_module = 'inventaires';
} elseif (strpos($current_page, '/pharmacie/') !== false) {
    $current_module = 'pharmacie';
} elseif (strpos($current_page, '/materiel/') !== false) {
    $current_module = 'materiel';
} elseif (strpos($current_page, '/frais/') !== false) {
    $current_module = 'frais';
} elseif (strpos($current_page, '/admin/') !== false) {
    $current_module = 'admin';
}

// Fonction pour vérifier si un lien est actif
function is_active_nav($module, $current) {
    return $module === $current ? 'active' : '';
}
?>

<nav class="navbar navbar-expand-lg navbar-pc">
    <div class="container-fluid">
        <!-- Logo et nom de l'application -->
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>/">
            <img src="<?php echo ASSETS_URL; ?>/images/favicon.png" alt="Logo Protection Civile">
            <?php echo APP_NAME; ?>
        </a>

        <!-- Bouton toggle pour mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon">
                <i class="bi bi-list text-white fs-4"></i>
            </span>
        </button>

        <!-- Menu principal -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <!-- Accueil -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_module === '' ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>/">
                        <i class="bi bi-house-fill"></i> Accueil
                    </a>
                </li>

                <!-- Module Véhicules -->
                <?php if (can_access_module('vehicules')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo is_active_nav('vehicules', $current_module); ?>" 
                       href="<?php echo BASE_URL; ?>/vehicules/">
                        <i class="bi bi-truck"></i> Véhicules
                    </a>
                </li>
                <?php endif; ?>

                <!-- Module Habillement -->
                <?php if (can_access_module('habillement')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo is_active_nav('habillement', $current_module); ?>" 
                       href="<?php echo BASE_URL; ?>/habillement/">
                        <i class="bi bi-person-badge"></i> Habillement
                    </a>
                </li>
                <?php endif; ?>

                <!-- Module Inventaires -->
                <?php if (can_access_module('inventaires')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo is_active_nav('inventaires', $current_module); ?>" 
                       href="<?php echo BASE_URL; ?>/inventaires/">
                        <i class="bi bi-clipboard-check"></i> Inventaires
                    </a>
                </li>
                <?php endif; ?>

                <!-- Module Pharmacie -->
                <?php if (can_access_module('pharmacie')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo is_active_nav('pharmacie', $current_module); ?>" 
                       href="<?php echo BASE_URL; ?>/pharmacie/">
                        <i class="bi bi-heart-pulse"></i> Pharmacie
                    </a>
                </li>
                <?php endif; ?>

                <!-- Module Matériel -->
                <?php if (can_access_module('materiel')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo is_active_nav('materiel', $current_module); ?>" 
                       href="<?php echo BASE_URL; ?>/materiel/">
                        <i class="bi bi-tools"></i> Matériel
                    </a>
                </li>
                <?php endif; ?>

                <!-- Module Frais -->
                <?php if (can_access_module('frais')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo is_active_nav('frais', $current_module); ?>" 
                       href="<?php echo BASE_URL; ?>/frais/">
                        <i class="bi bi-receipt"></i> Frais
                    </a>
                </li>
                <?php endif; ?>

                <!-- Administration (Admin et Responsables uniquement) -->
                <?php if (is_admin_or_responsable()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo is_active_nav('admin', $current_module); ?>" 
                       href="<?php echo BASE_URL; ?>/admin/">
                        <i class="bi bi-gear-fill"></i> Administration
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Menu utilisateur à droite -->
            <ul class="navbar-nav">
                <!-- Informations utilisateur -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarUserDropdown" 
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-2"></i>
                        <span class="d-none d-md-inline">
                            <?php echo h($current_user['prenom'] . ' ' . $current_user['nom']); ?>
                        </span>
                        <?php if ($current_antenne): ?>
                            <span class="badge-antenne ms-2 d-none d-lg-inline">
                                <?php echo h($current_antenne['nom']); ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <!-- Informations utilisateur -->
                        <li class="dropdown-header">
                            <div class="fw-bold"><?php echo h($current_user['prenom'] . ' ' . $current_user['nom']); ?></div>
                            <small class="text-muted">
                                <?php 
                                $roles_display = ROLES;
                                echo h($roles_display[$current_user_role] ?? ucfirst($current_user_role)); 
                                ?>
                                <?php if ($current_antenne): ?>
                                    - <?php echo h($current_antenne['nom']); ?>
                                <?php endif; ?>
                            </small>
                        </li>
                        
                        <li><hr class="dropdown-divider"></li>
                        
                        <!-- Mon profil -->
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>/profil.php">
                                <i class="bi bi-person me-2"></i> Mon profil
                            </a>
                        </li>
                        
                        <!-- Liens rapides selon le rôle -->
                        <?php if ($current_user_role === 'conducteur'): ?>
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>/vehicules/sorties/nouvelle.php">
                                    <i class="bi bi-plus-circle me-2"></i> Nouvelle sortie
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (is_admin_or_responsable()): ?>
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/utilisateurs.php">
                                    <i class="bi bi-people me-2"></i> Gestion utilisateurs
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li><hr class="dropdown-divider"></li>
                        
                        <!-- Aide et documentation -->
                        <li>
                            <a class="dropdown-item" href="#" onclick="showHelp()">
                                <i class="bi bi-question-circle me-2"></i> Aide
                            </a>
                        </li>
                        
                        <!-- Déconnexion -->
                        <li>
                            <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/logout.php" 
                               onclick="return confirm('Voulez-vous vraiment vous déconnecter ?')">
                                <i class="bi bi-box-arrow-right me-2"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Fil d'Ariane (breadcrumb) si défini -->
<?php if (isset($breadcrumb) && !empty($breadcrumb)): ?>
<nav aria-label="breadcrumb" class="bg-light border-bottom">
    <div class="container-fluid">
        <ol class="breadcrumb mb-0 py-2">
            <li class="breadcrumb-item">
                <a href="<?php echo BASE_URL; ?>/" class="text-decoration-none">
                    <i class="bi bi-house"></i> Accueil
                </a>
            </li>
            <?php foreach ($breadcrumb as $index => $item): ?>
                <?php if ($index === count($breadcrumb) - 1): ?>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?php echo h($item['title']); ?>
                    </li>
                <?php else: ?>
                    <li class="breadcrumb-item">
                        <a href="<?php echo h($item['url']); ?>" class="text-decoration-none">
                            <?php echo h($item['title']); ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>
<?php endif; ?>

<script>
// Fonction pour afficher l'aide
function showHelp() {
    const helpContent = `
        <div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background: var(--pc-blue); color: white;">
                        <h5 class="modal-title" id="helpModalLabel">
                            <i class="bi bi-question-circle me-2"></i>Aide - ${document.title}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h6><i class="bi bi-info-circle text-primary me-2"></i>Navigation</h6>
                        <ul>
                            <li><strong>Véhicules :</strong> Gestion des sorties et retours de véhicules</li>
                            <li><strong>Habillement :</strong> Gestion des stocks d'uniformes par antenne</li>
                            <li><strong>Inventaires :</strong> Check-lists et contrôles de matériel</li>
                            <li><strong>Pharmacie :</strong> Gestion des stocks médicaux avec scan</li>
                            <li><strong>Matériel :</strong> Gestion des équipements et prêts</li>
                            <li><strong>Frais :</strong> Demandes de remboursement avec photos</li>
                        </ul>
                        
                        <h6><i class="bi bi-person-circle text-primary me-2"></i>Votre profil</h6>
                        <p>Rôle : <strong>${window.PROTEC64.USER_ROLE || 'Non défini'}</strong></p>
                        <p>Cliquez sur votre nom en haut à droite pour accéder à votre profil et vous déconnecter.</p>
                        
                        <h6><i class="bi bi-telephone text-primary me-2"></i>Support</h6>
                        <p>Pour toute assistance technique, contactez votre responsable d'antenne.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-pc-primary" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Supprimer un éventuel modal existant
    const existingModal = document.getElementById('helpModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Ajouter le modal au body
    document.body.insertAdjacentHTML('beforeend', helpContent);
    
    // Afficher le modal
    const modal = new bootstrap.Modal(document.getElementById('helpModal'));
    modal.show();
    
    // Supprimer le modal du DOM quand il est fermé
    document.getElementById('helpModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Fermer automatiquement le menu mobile après clic sur un lien
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link:not(.dropdown-toggle)');
    const navbarCollapse = document.getElementById('navbarNav');
    
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (navbarCollapse.classList.contains('show')) {
                const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                bsCollapse.hide();
            }
        });
    });
});
</script>