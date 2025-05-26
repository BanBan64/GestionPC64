<?php
/**
 * Template footer Protection Civile 64 - v2
 * 
 * Pied de page commun avec informations, liens utiles et scripts
 */

// Empêcher l'accès direct
if (!defined('PROTEC64_V2')) {
    die('Accès interdit');
}

// Récupérer les informations utilisateur si connecté
$current_user = get_current_user();
$current_antenne = get_current_user_antenne();
?>

<footer class="footer mt-auto py-4" style="background-color: var(--pc-blue); color: white;">
    <div class="container-fluid">
        <div class="row">
            <!-- Informations Protection Civile -->
            <div class="col-md-4 mb-3">
                <div class="d-flex align-items-center mb-3">
                    <img src="<?php echo ASSETS_URL; ?>/images/favicon.png" alt="Logo Protection Civile" 
                         style="height: 40px; width: 40px; margin-right: 12px;">
                    <h6 class="mb-0"><?php echo APP_NAME; ?></h6>
                </div>
                <p class="small mb-2">
                    Système de gestion unifié pour les bénévoles de la Protection Civile des Pyrénées-Atlantiques.
                </p>
                <p class="small mb-0">
                    <i class="bi bi-geo-alt text-warning"></i> 
                    Pyrénées-Atlantiques (64)
                </p>
            </div>

            <!-- Navigation rapide -->
            <div class="col-md-4 mb-3">
                <h6><i class="bi bi-list-ul text-warning me-2"></i>Navigation</h6>
                <ul class="list-unstyled small">
                    <li><a href="<?php echo BASE_URL; ?>/" class="text-white-50 text-decoration-none hover-orange">
                        <i class="bi bi-house me-1"></i> Accueil
                    </a></li>
                    
                    <?php if (is_logged_in()): ?>
                        <?php if (can_access_module('vehicules')): ?>
                        <li><a href="<?php echo BASE_URL; ?>/vehicules/" class="text-white-50 text-decoration-none hover-orange">
                            <i class="bi bi-truck me-1"></i> Véhicules
                        </a></li>
                        <?php endif; ?>
                        
                        <?php if (can_access_module('inventaires')): ?>
                        <li><a href="<?php echo BASE_URL; ?>/inventaires/" class="text-white-50 text-decoration-none hover-orange">
                            <i class="bi bi-clipboard-check me-1"></i> Inventaires
                        </a></li>
                        <?php endif; ?>
                        
                        <?php if (is_admin_or_responsable()): ?>
                        <li><a href="<?php echo BASE_URL; ?>/admin/" class="text-white-50 text-decoration-none hover-orange">
                            <i class="bi bi-gear me-1"></i> Administration
                        </a></li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="<?php echo BASE_URL; ?>/login.php" class="text-white-50 text-decoration-none hover-orange">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Connexion
                        </a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Informations utilisateur et système -->
            <div class="col-md-4 mb-3">
                <?php if (is_logged_in()): ?>
                    <h6><i class="bi bi-person-circle text-warning me-2"></i>Informations utilisateur</h6>
                    <div class="small">
                        <p class="mb-1">
                            <strong><?php echo h($current_user['prenom'] . ' ' . $current_user['nom']); ?></strong>
                        </p>
                        <p class="mb-1 text-white-50">
                            <?php 
                            $roles_display = ROLES;
                            echo h($roles_display[$current_user['role']] ?? ucfirst($current_user['role'])); 
                            ?>
                            <?php if ($current_antenne): ?>
                                - <?php echo h($current_antenne['nom']); ?>
                            <?php endif; ?>
                        </p>
                        <p class="mb-2 text-white-50">
                            <i class="bi bi-clock me-1"></i>
                            <span id="current-time"><?php echo date('d/m/Y H:i:s'); ?></span>
                        </p>
                    </div>
                <?php else: ?>
                    <h6><i class="bi bi-info-circle text-warning me-2"></i>Système</h6>
                    <div class="small text-white-50">
                        <p class="mb-1"><?php echo APP_DESCRIPTION; ?></p>
                        <p class="mb-0">Version <?php echo APP_VERSION; ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">

        <!-- Copyright et informations techniques -->
        <div class="row align-items-center">
            <div class="col-md-8">
                <p class="small mb-0 text-white-50">
                    © <?php echo date('Y'); ?> Protection Civile des Pyrénées-Atlantiques. 
                    Tous droits réservés.
                    <?php if (DEBUG_MODE): ?>
                        <span class="ms-2 badge bg-warning text-dark">MODE DEBUG</span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <small class="text-white-50">
                    <?php if (is_logged_in() && (is_admin() || DEBUG_MODE)): ?>
                        <span id="system-info" class="cursor-pointer" onclick="toggleSystemInfo()" 
                              title="Cliquer pour afficher les informations système">
                            <i class="bi bi-info-circle me-1"></i>Système
                        </span>
                    <?php endif; ?>
                    <span class="ms-2">v<?php echo APP_VERSION; ?></span>
                </small>
            </div>
        </div>
    </div>
</footer>

<!-- Modal informations système (admin uniquement) -->
<?php if (is_logged_in() && (is_admin() || DEBUG_MODE)): ?>
<div class="modal fade" id="systemInfoModal" tabindex="-1" aria-labelledby="systemInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--pc-blue); color: white;">
                <h5 class="modal-title" id="systemInfoModalLabel">
                    <i class="bi bi-gear me-2"></i>Informations système
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="system-info-content">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="mt-2">Chargement des informations système...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-pc-primary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- JavaScript du footer -->
<script>
// Mise à jour de l'heure en temps réel
function updateTime() {
    const timeElement = document.getElementById('current-time');
    if (timeElement) {
        const now = new Date();
        const formatted = now.toLocaleDateString('fr-FR') + ' ' + now.toLocaleTimeString('fr-FR');
        timeElement.textContent = formatted;
    }
}

// Mettre à jour l'heure toutes les secondes
if (document.getElementById('current-time')) {
    setInterval(updateTime, 1000);
}

<?php if (is_logged_in() && (is_admin() || DEBUG_MODE)): ?>
// Fonction pour afficher les informations système
function toggleSystemInfo() {
    const modal = new bootstrap.Modal(document.getElementById('systemInfoModal'));
    modal.show();
    
    // Charger les informations système via AJAX
    fetch(window.PROTEC64.BASE_URL + '/api/system-info.php', {
        headers: {
            'X-CSRF-Token': window.PROTEC64.CSRF_TOKEN
        }
    })
    .then(response => response.json())
    .then(data => {
        const content = document.getElementById('system-info-content');
        content.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="bi bi-server text-primary me-2"></i>Serveur</h6>
                    <table class="table table-sm">
                        <tr><td>PHP Version</td><td><strong>${data.php_version}</strong></td></tr>
                        <tr><td>Serveur</td><td>${data.server_software}</td></tr>
                        <tr><td>Mémoire utilisée</td><td>${data.memory_usage}</td></tr>
                        <tr><td>Pic mémoire</td><td>${data.memory_peak}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6><i class="bi bi-database text-primary me-2"></i>Base de données</h6>
                    <table class="table table-sm">
                        <tr><td>Host</td><td>${data.database_info.host}</td></tr>
                        <tr><td>Base</td><td>${data.database_info.database}</td></tr>
                        <tr><td>Version</td><td>${data.database_info.version}</td></tr>
                        <tr><td>Tables</td><td><strong>${data.database_info.tables_count}</strong></td></tr>
                    </table>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <h6><i class="bi bi-speedometer2 text-primary me-2"></i>Performance</h6>
                    <p class="small">Temps d'exécution: <strong>${data.execution_time}</strong></p>
                </div>
            </div>
        `;
    })
    .catch(error => {
        const content = document.getElementById('system-info-content');
        content.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Erreur lors du chargement des informations système.
            </div>
        `;
    });
}
<?php endif; ?>

// Fonction utilitaire pour le retour en haut de page
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Afficher le bouton retour en haut si nécessaire
window.addEventListener('scroll', function() {
    const scrollBtn = document.getElementById('scroll-to-top');
    if (scrollBtn) {
        if (window.pageYOffset > 300) {
            scrollBtn.style.display = 'block';
        } else {
            scrollBtn.style.display = 'none';
        }
    }
});

// Auto-masquer les alertes après 5 secondes
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.classList.contains('show')) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    });
});

// Log des erreurs JavaScript côté serveur (si admin)
<?php if (is_logged_in() && is_admin() && DEBUG_MODE): ?>
window.addEventListener('error', function(e) {
    fetch(window.PROTEC64.BASE_URL + '/api/log-error.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': window.PROTEC64.CSRF_TOKEN
        },
        body: JSON.stringify({
            message: e.message,
            source: e.filename,
            line: e.lineno,
            url: window.location.href,
            user_agent: navigator.userAgent
        })
    });
});
<?php endif; ?>
</script>

<!-- Bouton retour en haut (optionnel) -->
<button id="scroll-to-top" onclick="scrollToTop()" 
        style="display: none; position: fixed; bottom: 20px; right: 20px; z-index: 1000; 
               background: var(--pc-orange); color: white; border: none; border-radius: 50%; 
               width: 50px; height: 50px; font-size: 20px; cursor: pointer; box-shadow: 0 2px 10px rgba(0,0,0,0.3);"
        title="Retour en haut">
    <i class="bi bi-arrow-up"></i>
</button>

<style>
.hover-orange:hover {
    color: var(--pc-orange) !important;
    transition: color 0.3s ease;
}

.cursor-pointer {
    cursor: pointer;
}

#scroll-to-top:hover {
    background: #e67600 !important;
    transform: scale(1.1);
    transition: all 0.3s ease;
}

/* Assurer que le footer reste en bas */
html, body {
    height: 100%;
}

body {
    display: flex;
    flex-direction: column;
}

main {
    flex: 1 0 auto;
}

.footer {
    flex-shrink: 0;
}
</style>

</body>
</html>