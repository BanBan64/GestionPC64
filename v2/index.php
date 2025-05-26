<?php
/**
 * Page d'accueil Protection Civile 64 - v2
 * Tableau de bord principal avec statistiques et accès rapide
 */

// Définir la constante de sécurité
define('PROTEC64_V2', true);

// Inclure les fichiers de base
require_once 'shared/includes/config.php';
require_once 'shared/includes/db.php';
require_once 'shared/includes/auth.php';
require_once 'shared/includes/utils.php';

// Vérifier l'authentification
require_login();

// Variables pour la page
$page_title = 'Tableau de bord';
$current_section = 'dashboard';

// Récupérer l'utilisateur actuel avec toutes ses données
$current_user = get_current_user_data();

// Récupérer les données complètes depuis la BDD
if ($current_user) {
    try {
        $db = db_connect();
        $user_query = $db->prepare("
            SELECT u.*, a.nom as antenne_nom 
            FROM " . DB_PREFIX . "utilisateurs u 
            LEFT JOIN " . DB_PREFIX . "antennes a ON u.antenne_id = a.id 
            WHERE u.id = ?
        ");
        $user_query->execute([$current_user['id']]);
        $user_full = $user_query->fetch(PDO::FETCH_ASSOC);
        
        if ($user_full) {
            // Fusionner les données de session avec les données BDD
            $current_user = array_merge($current_user, $user_full);
        }
    } catch (Exception $e) {
        log_action("Erreur récupération utilisateur complet: " . $e->getMessage(), 'error');
    }
}

// Initialiser les statistiques
$stats = [
    'vehicules_total' => 0,
    'vehicules_disponibles' => 0,
    'vehicules_maintenance' => 0,
    'sorties_today' => 0,
    'sorties_en_cours' => 0,
    'sorties_ce_mois' => 0,
    'utilisateurs_total' => 0,
    'utilisateurs_actifs' => 0,
    'utilisateurs_mon_antenne' => 0
];

// Récupérer les statistiques
try {
    $db = db_connect();
    
    // Statistiques véhicules
    $vehicules_stats = $db->query("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN statut = 'disponible' THEN 1 END) as disponibles,
            COUNT(CASE WHEN statut = 'en_maintenance' THEN 1 END) as maintenance
        FROM " . DB_PREFIX . "vehicules
    ")->fetch(PDO::FETCH_ASSOC);
    
    if ($vehicules_stats) {
        $stats['vehicules_total'] = (int)$vehicules_stats['total'];
        $stats['vehicules_disponibles'] = (int)$vehicules_stats['disponibles'];
        $stats['vehicules_maintenance'] = (int)$vehicules_stats['maintenance'];
    }
    
    // Statistiques sorties
    $sorties_stats = $db->query("
        SELECT 
            COUNT(CASE WHEN DATE(date_sortie) = CURDATE() THEN 1 END) as today,
            COUNT(CASE WHEN statut = 'en_cours' THEN 1 END) as en_cours,
            COUNT(CASE WHEN MONTH(date_sortie) = MONTH(CURDATE()) AND YEAR(date_sortie) = YEAR(CURDATE()) THEN 1 END) as ce_mois
        FROM " . DB_PREFIX . "sorties
    ")->fetch(PDO::FETCH_ASSOC);
    
    if ($sorties_stats) {
        $stats['sorties_today'] = (int)$sorties_stats['today'];
        $stats['sorties_en_cours'] = (int)$sorties_stats['en_cours'];
        $stats['sorties_ce_mois'] = (int)$sorties_stats['ce_mois'];
    }
    
    // Statistiques utilisateurs (si admin ou responsable)
    if (is_admin() || is_admin_or_responsable()) {
        $users_stats = $db->query("
            SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN actif = 1 THEN 1 END) as actifs
            FROM " . DB_PREFIX . "utilisateurs
        ")->fetch(PDO::FETCH_ASSOC);
        
        if ($users_stats) {
            $stats['utilisateurs_total'] = (int)$users_stats['total'];
            $stats['utilisateurs_actifs'] = (int)$users_stats['actifs'];
        }
        
        // Utilisateurs de mon antenne
        if (isset($current_user['antenne_id']) && $current_user['antenne_id']) {
            $antenne_stats = $db->prepare("SELECT COUNT(*) as count FROM " . DB_PREFIX . "utilisateurs WHERE antenne_id = ? AND actif = 1");
            $antenne_stats->execute([$current_user['antenne_id']]);
            $antenne_result = $antenne_stats->fetch(PDO::FETCH_ASSOC);
            if ($antenne_result) {
                $stats['utilisateurs_mon_antenne'] = (int)$antenne_result['count'];
            }
        }
    }
    
} catch (Exception $e) {
    log_action("Erreur lors de la récupération des statistiques: " . $e->getMessage(), 'error');
}

// Récupérer les dernières sorties de l'utilisateur
$dernieres_sorties = [];
try {
    $sorties_query = $db->prepare("
        SELECT s.*, v.identifiant as vehicule_nom, v.type_vehicule
        FROM " . DB_PREFIX . "sorties s
        JOIN " . DB_PREFIX . "vehicules v ON s.vehicule_id = v.id
        WHERE s.conducteur_id = ?
        ORDER BY s.date_sortie DESC
        LIMIT 5
    ");
    $sorties_query->execute([$current_user['id']]);
    $dernieres_sorties = $sorties_query->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    log_action("Erreur lors de la récupération des dernières sorties: " . $e->getMessage(), 'error');
}

// Alertes importantes
$alertes = [];

// Vérifier les véhicules en maintenance
if ($stats['vehicules_maintenance'] > 0) {
    $alertes[] = [
        'type' => 'warning',
        'message' => $stats['vehicules_maintenance'] . ' véhicule(s) en maintenance',
        'icon' => 'tools'
    ];
}

// Vérifier les sorties longues (plus de 24h)
try {
    $sorties_longues = $db->query("
        SELECT COUNT(*) as count 
        FROM " . DB_PREFIX . "sorties 
        WHERE statut = 'en_cours' 
        AND TIMESTAMPDIFF(HOUR, date_sortie, NOW()) > 24
    ")->fetch(PDO::FETCH_ASSOC);
    
    if ($sorties_longues && $sorties_longues['count'] > 0) {
        $alertes[] = [
            'type' => 'danger',
            'message' => $sorties_longues['count'] . ' sortie(s) de plus de 24h sans retour',
            'icon' => 'exclamation-triangle'
        ];
    }
} catch (Exception $e) {
    // Ignorer cette erreur, ce n'est pas critique
}

// Inclure le header
include SHARED_PATH . '/templates/header.php';
?>

<body>
    <?php include SHARED_PATH . '/templates/navigation.php'; ?>
    
    <div class="d-flex">
        <?php include SHARED_PATH . '/templates/sidebar.php'; ?>
        
        <main class="flex-grow-1 p-4">
            <div class="container-fluid">
                
                <!-- En-tête avec salutation -->
                <div class="row mb-4">
                    <div class="col">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="h2 mb-1">
                                    <i class="bi bi-speedometer2 text-primary me-2"></i>
                                    Bonjour <?php echo h($current_user['prenom'] ?? 'Utilisateur'); ?> !
                                </h1>
                                <p class="text-muted mb-0">
                                    <?php echo ucfirst($current_user['role'] ?? 'Utilisateur'); ?> - 
                                    <?php echo h($current_user['antenne_nom'] ?? 'Aucune antenne'); ?> • 
                                    <?php echo format_datetime(date('Y-m-d H:i:s')); ?>
                                </p>
                            </div>
                            
                            <!-- Actions rapides -->
                            <div>
                                <a href="<?php echo BASE_URL; ?>/vehicules/sorties/nouvelle.php" class="btn btn-primary me-2">
                                    <i class="bi bi-plus-circle me-1"></i>
                                    Nouvelle sortie
                                </a>
                                <?php if (is_admin() || is_admin_or_responsable()): ?>
                                <a href="<?php echo BASE_URL; ?>/admin/utilisateurs.php" class="btn btn-outline-primary">
                                    <i class="bi bi-person-plus me-1"></i>
                                    Nouvel utilisateur
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alertes importantes -->
                <?php if (!empty($alertes)): ?>
                <div class="row mb-4">
                    <div class="col">
                        <?php foreach ($alertes as $alerte): ?>
                        <div class="alert alert-<?php echo $alerte['type']; ?> alert-dismissible fade show" role="alert">
                            <i class="bi bi-<?php echo $alerte['icon']; ?> me-2"></i>
                            <?php echo h($alerte['message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Statistiques principales -->
                <div class="row mb-4">
                    <!-- Véhicules -->
                    <div class="col-md-4">
                        <div class="card border-0 bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo number_format($stats['vehicules_disponibles']); ?></h3>
                                        <p class="mb-0">Véhicules disponibles</p>
                                        <small class="opacity-75">Total: <?php echo $stats['vehicules_total']; ?> véhicules</small>
                                    </div>
                                    <div class="opacity-75">
                                        <i class="bi bi-truck" style="font-size: 2.5rem;"></i>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <a href="<?php echo BASE_URL; ?>/vehicules/" class="btn btn-light btn-sm">
                                        <i class="bi bi-arrow-right me-1"></i>
                                        Voir tout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sorties -->
                    <div class="col-md-4">
                        <div class="card border-0 bg-warning text-dark">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo number_format($stats['sorties_today']); ?></h3>
                                        <p class="mb-0">Sorties aujourd'hui</p>
                                        <small class="opacity-75">En cours: <?php echo $stats['sorties_en_cours']; ?></small>
                                    </div>
                                    <div class="opacity-75">
                                        <i class="bi bi-calendar-check" style="font-size: 2.5rem;"></i>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <a href="<?php echo BASE_URL; ?>/vehicules/sorties/" class="btn btn-dark btn-sm">
                                        <i class="bi bi-arrow-right me-1"></i>
                                        Voir tout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Utilisateurs (si admin/responsable) -->
                    <?php if (is_admin() || is_admin_or_responsable()): ?>
                    <div class="col-md-4">
                        <div class="card border-0 bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo number_format($stats['utilisateurs_actifs']); ?></h3>
                                        <p class="mb-0">Utilisateurs actifs</p>
                                        <small class="opacity-75">Mon antenne: <?php echo $stats['utilisateurs_mon_antenne']; ?></small>
                                    </div>
                                    <div class="opacity-75">
                                        <i class="bi bi-people" style="font-size: 2.5rem;"></i>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <a href="<?php echo BASE_URL; ?>/admin/utilisateurs.php" class="btn btn-light btn-sm">
                                        <i class="bi bi-arrow-right me-1"></i>
                                        Gérer
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <!-- Mes dernières sorties -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-clock-history me-2"></i>
                                    Mes dernières sorties
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($dernieres_sorties)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Véhicule</th>
                                                <th>Date</th>
                                                <th>Destination</th>
                                                <th>Statut</th>
                                                <th>KM</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dernieres_sorties as $sortie): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary me-2"><?php echo h($sortie['type_vehicule']); ?></span>
                                                    <?php echo h($sortie['vehicule_nom']); ?>
                                                </td>
                                                <td>
                                                    <?php echo format_datetime($sortie['date_sortie']); ?>
                                                    <br><small class="text-muted"><?php echo time_ago($sortie['date_sortie']); ?></small>
                                                </td>
                                                <td><?php echo h($sortie['destination'] ?: 'Non précisée'); ?></td>
                                                <td>
                                                    <?php if ($sortie['statut'] === 'termine' || $sortie['date_retour']): ?>
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle me-1"></i>Terminée
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="bi bi-clock me-1"></i>En cours
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($sortie['kilometrage_retour'] && $sortie['kilometrage_depart']): ?>
                                                        <?php echo number_format($sortie['kilometrage_retour'] - $sortie['kilometrage_depart']); ?> km
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <a href="<?php echo BASE_URL; ?>/vehicules/sorties/" class="btn btn-outline-primary">
                                        <i class="bi bi-list-ul me-1"></i>
                                        Voir toutes mes sorties
                                    </a>
                                </div>
                                
                                <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-car-front text-muted" style="font-size: 4rem;"></i>
                                    <h5 class="text-muted mt-3">Aucune sortie enregistrée</h5>
                                    <p class="text-muted">Effectuez votre première sortie pour voir vos statistiques ici.</p>
                                    <a href="<?php echo BASE_URL; ?>/vehicules/sorties/nouvelle.php" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        Ma première sortie
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Accès rapide aux modules -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-lightning me-2"></i>
                                    Accès rapide
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    
                                    <!-- Véhicules -->
                                    <a href="<?php echo BASE_URL; ?>/vehicules/" class="btn btn-outline-primary text-start">
                                        <i class="bi bi-truck me-2"></i>
                                        <strong>Véhicules</strong>
                                        <br><small class="text-muted">Sorties et gestion véhicules</small>
                                    </a>
                                    
                                    <!-- Habillement -->
                                    <a href="<?php echo BASE_URL; ?>/habillement/" class="btn btn-outline-warning text-start">
                                        <i class="bi bi-person-badge me-2"></i>
                                        <strong>Habillement</strong>
                                        <br><small class="text-muted">Stocks uniformes par antenne</small>
                                    </a>
                                    
                                    <!-- Inventaires -->
                                    <a href="<?php echo BASE_URL; ?>/inventaires/" class="btn btn-outline-info text-start">
                                        <i class="bi bi-clipboard-check me-2"></i>
                                        <strong>Inventaires</strong>
                                        <br><small class="text-muted">Check-lists contrôles</small>
                                    </a>
                                    
                                    <!-- Pharmacie -->
                                    <a href="<?php echo BASE_URL; ?>/pharmacie/" class="btn btn-outline-danger text-start">
                                        <i class="bi bi-capsule me-2"></i>
                                        <strong>Pharmacie</strong>
                                        <br><small class="text-muted">Stocks médicaux avec scan</small>
                                    </a>
                                    
                                    <!-- Matériel -->
                                    <a href="<?php echo BASE_URL; ?>/materiel/" class="btn btn-outline-success text-start">
                                        <i class="bi bi-tools me-2"></i>
                                        <strong>Matériel</strong>
                                        <br><small class="text-muted">Équipements et prêts</small>
                                    </a>
                                    
                                    <!-- Notes de frais -->
                                    <a href="<?php echo BASE_URL; ?>/frais/" class="btn btn-outline-dark text-start">
                                        <i class="bi bi-receipt me-2"></i>
                                        <strong>Frais</strong>
                                        <br><small class="text-muted">Demandes remboursement</small>
                                    </a>
                                    
                                </div>
                                
                                <!-- Administration (si autorisé) -->
                                <?php if (is_admin() || is_admin_or_responsable()): ?>
                                <hr>
                                <div class="d-grid">
                                    <a href="<?php echo BASE_URL; ?>/admin/" class="btn btn-outline-secondary text-start">
                                        <i class="bi bi-gear me-2"></i>
                                        <strong>Administration</strong>
                                        <br><small class="text-muted">Gestion utilisateurs et système</small>
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include SHARED_PATH . '/templates/footer.php'; ?>

    <!-- CSS personnalisé -->
    <style>
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: box-shadow 0.15s ease-in-out;
        }
        
        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            font-weight: 600;
        }
        
        .bg-primary {
            background-color: var(--pc-blue) !important;
        }
        
        .bg-warning {
            background-color: var(--pc-orange) !important;
        }
        
        .text-primary {
            color: var(--pc-blue) !important;
        }
        
        .btn-primary {
            background-color: var(--pc-blue);
            border-color: var(--pc-blue);
        }
        
        .btn-primary:hover {
            background-color: #003366;
            border-color: #003366;
        }
        
        .btn-outline-primary {
            color: var(--pc-blue);
            border-color: var(--pc-blue);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--pc-blue);
            border-color: var(--pc-blue);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0, 64, 128, 0.05);
        }
        
        .badge {
            font-size: 0.75em;
        }
        
        /* Animations */
        .card {
            animation: fadeInUp 0.5s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Auto-refresh indicator */
        .auto-refresh {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(0, 64, 128, 0.9);
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .auto-refresh.show {
            opacity: 1;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 1rem;
            }
            
            .card-body {
                padding: 1rem;
            }
        }
    </style>

    <!-- JavaScript personnalisé -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-refresh des statistiques toutes les 5 minutes
            setInterval(function() {
                if (!document.hidden) {
                    // Ici on pourrait faire un appel AJAX pour mettre à jour les stats
                    console.log('Auto-refresh stats');
                }
            }, 300000); // 5 minutes
            
            // Auto-hide alerts après 10 secondes
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    if (alert.parentElement) {
                        bsAlert.close();
                    }
                }, 10000);
            });
            
            // Smooth scroll pour les liens internes
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });
            
            console.log('Dashboard Protection Civile 64 v2 loaded');
            console.log('User:', {
                id: <?php echo $current_user['id']; ?>,
                role: '<?php echo h($current_user['role']); ?>',
                antenne: '<?php echo h($current_user['antenne_nom'] ?? ''); ?>'
            });
        });
    </script>
</body>
</html>