<?php
/**
 * Page d'accueil - Tableau de bord
 * Fichier: index.php
 */

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/utils.php';

// Vérifier l'authentification
require_login();

// Titre de la page
$page_title = 'Tableau de bord';

// Inclure l'en-tête
include_once 'templates/header.php';

// Récupérer les statistiques
try {
    $db = db_connect();
    
    // Statistiques des véhicules
    $stats_vehicules = $db->query("
        SELECT 
            COUNT(*) as total_vehicules,
            SUM(CASE WHEN statut = 'disponible' THEN 1 ELSE 0 END) as vehicules_disponibles,
            SUM(CASE WHEN statut = 'en_maintenance' THEN 1 ELSE 0 END) as vehicules_maintenance,
            SUM(CASE WHEN statut = 'hors_service' THEN 1 ELSE 0 END) as vehicules_hors_service
        FROM " . DB_PREFIX . "vehicules
    ")->fetch();
    
    // Statistiques des sorties
    $stats_sorties = $db->query("
        SELECT 
            COUNT(*) as total_sorties,
            SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END) as sorties_en_cours,
            SUM(CASE WHEN statut = 'terminee' THEN 1 ELSE 0 END) as sorties_terminees,
            SUM(CASE WHEN statut = 'terminee' AND kilometrage_retour IS NOT NULL AND kilometrage_depart IS NOT NULL 
                     THEN kilometrage_retour - kilometrage_depart ELSE 0 END) as kilometres_parcourus
        FROM " . DB_PREFIX . "sorties
    ")->fetch();
    
    // Sorties de ce mois
    $stats_mois = $db->query("
        SELECT 
            COUNT(*) as sorties_mois,
            SUM(CASE WHEN statut = 'terminee' AND kilometrage_retour IS NOT NULL AND kilometrage_depart IS NOT NULL 
                     THEN kilometrage_retour - kilometrage_depart ELSE 0 END) as km_mois
        FROM " . DB_PREFIX . "sorties 
        WHERE MONTH(date_sortie) = MONTH(CURRENT_DATE())
        AND YEAR(date_sortie) = YEAR(CURRENT_DATE())
    ")->fetch();
    
    // Véhicules en sortie actuellement
    $vehicules_en_sortie = $db->query("
        SELECT COUNT(DISTINCT vehicule_id) as en_sortie 
        FROM " . DB_PREFIX . "sorties 
        WHERE statut = 'en_cours'
    ")->fetch();
    
    // Dernières sorties
    $dernieres_sorties = $db->query("
        SELECT s.id, s.date_sortie, s.motif, s.statut,
               v.identifiant, v.type_vehicule,
               u.prenom, u.nom
        FROM " . DB_PREFIX . "sorties s
        JOIN " . DB_PREFIX . "vehicules v ON s.vehicule_id = v.id
        JOIN " . DB_PREFIX . "utilisateurs u ON s.conducteur_id = u.id
        ORDER BY s.date_sortie DESC
        LIMIT 5
    ")->fetchAll();
    
    // Véhicules par type
    $types_vehicules = $db->query("
        SELECT type_vehicule, COUNT(*) as nombre
        FROM " . DB_PREFIX . "vehicules
        GROUP BY type_vehicule
        ORDER BY nombre DESC
    ")->fetchAll();
    
} catch (Exception $e) {
    $error = "Erreur lors du chargement des statistiques : " . $e->getMessage();
}
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 style="color: var(--pc-orange);">
                    <i class="bi bi-speedometer2"></i> Tableau de bord
                </h1>
                <div class="text-muted">
                    <i class="bi bi-calendar-date"></i> <?php echo date('d/m/Y H:i'); ?>
                </div>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-pc-warning">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo h($error); ?>
                </div>
            <?php else: ?>
                
                <!-- Statistiques principales -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-pc-header text-center py-4">
                                <i class="bi bi-truck display-4 mb-2"></i>
                                <h3 class="mb-2"><?php echo number_format($stats_vehicules['total_vehicules']); ?></h3>
                                <h6 class="mb-0">Véhicules Total</h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <span class="badge bg-success"><?php echo $stats_vehicules['vehicules_disponibles']; ?></span>
                                        <small class="d-block text-muted">Disponibles</small>
                                    </div>
                                    <div class="col-4">
                                        <span class="badge bg-warning"><?php echo $stats_vehicules['vehicules_maintenance']; ?></span>
                                        <small class="d-block text-muted">Maintenance</small>
                                    </div>
                                    <div class="col-4">
                                        <span class="badge bg-danger"><?php echo $stats_vehicules['vehicules_hors_service']; ?></span>
                                        <small class="d-block text-muted">Hors service</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-pc-blue-header text-center py-4">
                                <i class="bi bi-arrow-left-right display-4 mb-2"></i>
                                <h3 class="mb-2"><?php echo number_format($stats_sorties['sorties_en_cours']); ?></h3>
                                <h6 class="mb-0">Sorties en cours</h6>
                            </div>
                            <div class="card-body text-center">
                                <p class="mb-0">
                                    <span class="text-muted">Sur</span>
                                    <strong><?php echo number_format($stats_sorties['total_sorties']); ?></strong>
                                    <span class="text-muted">sorties totales</span>
                                </p>
                                <small class="text-success">
                                    <?php echo number_format($stats_sorties['sorties_terminees']); ?> terminées
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-pc-header text-center py-4">
                                <i class="bi bi-speedometer display-4 mb-2"></i>
                                <h3 class="mb-2"><?php echo number_format($stats_sorties['kilometres_parcourus']); ?></h3>
                                <h6 class="mb-0">km parcourus</h6>
                            </div>
                            <div class="card-body text-center">
                                <p class="mb-0">
                                    <span class="badge badge-pc-orange"><?php echo number_format($stats_mois['km_mois']); ?> km</span>
                                </p>
                                <small class="text-muted">ce mois-ci</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-pc-blue-header text-center py-4">
                                <i class="bi bi-calendar-month display-4 mb-2"></i>
                                <h3 class="mb-2"><?php echo number_format($stats_mois['sorties_mois']); ?></h3>
                                <h6 class="mb-0">Sorties ce mois</h6>
                            </div>
                            <div class="card-body text-center">
                                <p class="mb-0 text-muted">
                                    <?php echo date('F Y'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Actions rapides -->
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-pc-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-lightning"></i> Actions rapides
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <a href="vehicules/nouveau.php" class="btn btn-pc-primary w-100 py-3">
                                            <i class="bi bi-plus-circle display-6"></i>
                                            <div class="mt-2">
                                                <strong>Ajouter véhicule</strong>
                                                <small class="d-block">Nouveau véhicule</small>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <a href="sorties/nouvelle.php" class="btn btn-pc-secondary w-100 py-3">
                                            <i class="bi bi-box-arrow-right display-6"></i>
                                            <div class="mt-2">
                                                <strong>Nouvelle sortie</strong>
                                                <small class="d-block">Sortir un véhicule</small>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <a href="sorties/retour.php" class="btn btn-outline-pc-primary w-100 py-3">
                                            <i class="bi bi-box-arrow-left display-6"></i>
                                            <div class="mt-2">
                                                <strong>Retour véhicule</strong>
                                                <small class="d-block">Terminer sortie</small>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <a href="rapports/" class="btn btn-outline-pc-secondary w-100 py-3">
                                            <i class="bi bi-file-earmark-text display-6"></i>
                                            <div class="mt-2">
                                                <strong>Rapports</strong>
                                                <small class="d-block">Statistiques</small>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dernières activités -->
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-pc-blue-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-clock-history"></i> Dernières sorties
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($dernieres_sorties)): ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="bi bi-info-circle display-1"></i>
                                        <p>Aucune sortie enregistrée</p>
                                        <a href="sorties/nouvelle.php" class="btn btn-pc-primary btn-sm">
                                            Créer la première sortie
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($dernieres_sorties as $sortie): ?>
                                            <div class="list-group-item px-0">
                                                <div class="d-flex w-100 justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">
                                                            <span class="badge badge-pc-blue"><?php echo h($sortie['identifiant']); ?></span>
                                                            <?php echo h($sortie['motif']); ?>
                                                        </h6>
                                                        <p class="mb-1 small text-muted">
                                                            <i class="bi bi-person"></i> <?php echo h($sortie['prenom'] . ' ' . $sortie['nom']); ?>
                                                        </p>
                                                    </div>
                                                    <div class="text-end">
                                                        <?php if ($sortie['statut'] == 'en_cours'): ?>
                                                            <span class="badge bg-warning text-dark">En cours</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-success">Terminée</span>
                                                        <?php endif; ?>
                                                        <small class="d-block text-muted">
                                                            <?php echo format_date($sortie['date_sortie']); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="text-center mt-3">
                                        <a href="sorties/" class="btn btn-outline-pc-secondary btn-sm">
                                            Voir toutes les sorties
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Répartition des véhicules -->
                <?php if (!empty($types_vehicules)): ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-pc-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-pie-chart"></i> Répartition des véhicules par type
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach ($types_vehicules as $type): ?>
                                            <div class="col-md-4 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <h3 class="text-primary"><?php echo $type['nombre']; ?></h3>
                                                        <h6 class="text-muted"><?php echo h($type['type_vehicule']); ?></h6>
                                                        <div class="progress mt-2" style="height: 8px;">
                                                            <?php 
                                                            $percentage = ($type['nombre'] / $stats_vehicules['total_vehicules']) * 100;
                                                            ?>
                                                            <div class="progress-bar-pc" style="width: <?php echo $percentage; ?>%;"></div>
                                                        </div>
                                                        <small class="text-muted"><?php echo round($percentage, 1); ?>%</small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Inclure le pied de page
include_once 'templates/footer.php';
?>