<?php
/**
 * Page de profil utilisateur - v2
 * Fichier: profil.php
 */

// Définir la constante de sécurité
define('PROTEC64_V2', true);

// Inclure les fichiers nécessaires
require_once 'shared/includes/config.php';
require_once 'shared/includes/db.php';
require_once 'shared/includes/auth.php';
require_once 'shared/includes/utils.php';

// Vérifier l'authentification
require_login();

// Variables pour la page
$page_title = 'Mon profil';
$current_section = 'profil';

// Récupérer l'utilisateur actuel
$current_user = get_current_user();
$user_id = get_current_user_id();

// Pour la navigation entre utilisateurs (admin/responsable)
$nav_user_id = $user_id;
$nav_user = $current_user;

if ((is_admin() || has_role('responsable')) && isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $nav_user_id = (int)$_GET['user_id'];
    $nav_user_query = db_query("SELECT * FROM " . DB_PREFIX . "utilisateurs WHERE id = ?", [$nav_user_id]);
    if ($nav_user_query && $nav_user_data = $nav_user_query->fetch()) {
        $nav_user = $nav_user_data;
    } else {
        redirect('profil.php');
    }
}

// Traitement des formulaires
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'update_profile' && (is_admin() || is_admin_or_responsable() || $nav_user_id === $user_id)) {
                // Mise à jour des informations personnelles
                $nom = clean_name($_POST['nom'] ?? '');
                $prenom = clean_name($_POST['prenom'] ?? '');
                $email = validate_email($_POST['email'] ?? '');
                $identifiant_eprotec = trim($_POST['identifiant_eprotec'] ?? '');
                $telephone = trim($_POST['telephone'] ?? '');
                $date_naissance = $_POST['date_naissance'] ?? '';
                $adresse = trim($_POST['adresse'] ?? '');
                $ville = trim($_POST['ville'] ?? '');
                $code_postal = trim($_POST['code_postal'] ?? '');
                
                // Validation
                $errors = [];
                if (empty($nom)) $errors[] = "Le nom est obligatoire";
                if (empty($prenom)) $errors[] = "Le prénom est obligatoire";
                if (empty($email)) $errors[] = "L'email est obligatoire";
                
                // Vérifier unicité email et identifiant
                $email_check = db_query("SELECT id FROM " . DB_PREFIX . "utilisateurs WHERE email = ? AND id != ?", [$email, $nav_user_id]);
                if ($email_check && $email_check->fetch()) {
                    $errors[] = "Cet email est déjà utilisé";
                }
                
                if (!empty($identifiant_eprotec)) {
                    $eprotec_check = db_query("SELECT id FROM " . DB_PREFIX . "utilisateurs WHERE identifiant_eprotec = ? AND id != ?", [$identifiant_eprotec, $nav_user_id]);
                    if ($eprotec_check && $eprotec_check->fetch()) {
                        $errors[] = "Cet identifiant eProtec est déjà utilisé";
                    }
                }
                
                if (empty($errors)) {
                    $update_data = [
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'email' => $email,
                        'identifiant_eprotec' => $identifiant_eprotec,
                        'telephone' => $telephone,
                        'date_naissance' => $date_naissance ?: null,
                        'adresse' => $adresse,
                        'ville' => $ville,
                        'code_postal' => $code_postal
                    ];
                    
                    // Champs réservés aux admin/responsable
                    if (is_admin() || is_admin_or_responsable()) {
                        $update_data['role'] = $_POST['role'] ?? $nav_user['role'];
                        $update_data['antenne_id'] = (int)($_POST['antenne_id'] ?? $nav_user['antenne_id']);
                        $update_data['actif'] = isset($_POST['actif']) ? 1 : 0;
                    }
                    
                    $placeholders = implode(' = ?, ', array_keys($update_data)) . ' = ?';
                    $values = array_values($update_data);
                    $values[] = $nav_user_id;
                    
                    db_query("UPDATE " . DB_PREFIX . "utilisateurs SET $placeholders WHERE id = ?", $values);
                    
                    // Log de l'action
                    log_action("Modification profil utilisateur #$nav_user_id", 'info', [
                        'modified_user' => $nav_user['nom'] . ' ' . $nav_user['prenom'],
                        'modifier_user' => $current_user['nom'] . ' ' . $current_user['prenom']
                    ]);
                    
                    $message = 'Profil mis à jour avec succès !';
                    $message_type = 'success';
                    
                    // Recharger les données
                    $nav_user_query = db_query("SELECT * FROM " . DB_PREFIX . "utilisateurs WHERE id = ?", [$nav_user_id]);
                    if ($nav_user_query) {
                        $nav_user = $nav_user_query->fetch();
                    }
                } else {
                    $message = implode('<br>', $errors);
                    $message_type = 'danger';
                }
                
            } elseif ($_POST['action'] === 'change_password') {
                // Changement de mot de passe
                $current_password = $_POST['current_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                $errors = [];
                
                // Pour les conducteurs : vérifier l'ancien mot de passe
                if (!is_admin() && !is_admin_or_responsable() && $nav_user_id === $user_id) {
                    if (empty($current_password)) {
                        $errors[] = "L'ancien mot de passe est obligatoire";
                    } elseif (!password_verify($current_password, $nav_user['mot_de_passe'])) {
                        $errors[] = "L'ancien mot de passe est incorrect";
                    }
                }
                
                if (empty($new_password)) {
                    $errors[] = "Le nouveau mot de passe est obligatoire";
                } elseif (strlen($new_password) < 6) {
                    $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
                } elseif ($new_password !== $confirm_password) {
                    $errors[] = "La confirmation du mot de passe ne correspond pas";
                }
                
                if (empty($errors)) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    db_query("UPDATE " . DB_PREFIX . "utilisateurs SET mot_de_passe = ? WHERE id = ?", [$hashed_password, $nav_user_id]);
                    
                    log_action("Changement mot de passe utilisateur #$nav_user_id", 'info', [
                        'target_user' => $nav_user['nom'] . ' ' . $nav_user['prenom'],
                        'changed_by' => $current_user['nom'] . ' ' . $current_user['prenom']
                    ]);
                    
                    $message = 'Mot de passe modifié avec succès !';
                    $message_type = 'success';
                } else {
                    $message = implode('<br>', $errors);
                    $message_type = 'danger';
                }
            }
        }
    } catch (Exception $e) {
        log_action("Erreur lors de la modification du profil: " . $e->getMessage(), 'error');
        $message = 'Une erreur est survenue lors de la modification.';
        $message_type = 'danger';
    }
}

// Récupérer les antennes pour le formulaire
$antennes = [];
try {
    $antennes_query = db_query("SELECT * FROM " . DB_PREFIX . "antennes ORDER BY nom");
    if ($antennes_query) {
        $antennes = $antennes_query->fetchAll();
    }
} catch (Exception $e) {
    log_action("Erreur lors de la récupération des antennes: " . $e->getMessage(), 'error');
}

// Récupérer les statistiques de l'utilisateur
$stats = [
    'total_sorties' => 0,
    'sorties_terminees' => 0,
    'km_total' => 0,
    'km_moyen' => 0
];

try {
    // Statistiques des sorties
    $stats_query = db_query("
        SELECT 
            COUNT(*) as total_sorties,
            COUNT(CASE WHEN statut = 'termine' THEN 1 END) as sorties_terminees,
            COALESCE(SUM(CASE 
                WHEN s.kilometrage_retour > s.kilometrage_depart 
                AND s.kilometrage_retour > 0 
                AND s.kilometrage_depart > 0 
                THEN s.kilometrage_retour - s.kilometrage_depart 
                ELSE 0 
            END), 0) as km_total
        FROM " . DB_PREFIX . "sorties s 
        WHERE s.conducteur_id = ?
    ", [$nav_user_id]);
    
    if ($stats_query && $stats_data = $stats_query->fetch()) {
        $stats['total_sorties'] = (int)$stats_data['total_sorties'];
        $stats['sorties_terminees'] = (int)$stats_data['sorties_terminees'];
        $stats['km_total'] = (int)$stats_data['km_total'];
        
        if ($stats['sorties_terminees'] > 0) {
            $stats['km_moyen'] = round($stats['km_total'] / $stats['sorties_terminees'], 1);
        }
    }
} catch (Exception $e) {
    log_action("Erreur lors de la récupération des statistiques: " . $e->getMessage(), 'error');
}

// Récupérer les dernières sorties
$dernieres_sorties = [];
try {
    $sorties_query = db_query("
        SELECT s.*, v.immatriculation, v.type
        FROM " . DB_PREFIX . "sorties s
        JOIN " . DB_PREFIX . "vehicules v ON s.vehicule_id = v.id
        WHERE s.conducteur_id = ?
        ORDER BY s.date_sortie DESC
        LIMIT 5
    ", [$nav_user_id]);
    
    if ($sorties_query) {
        $dernieres_sorties = $sorties_query->fetchAll();
    }
} catch (Exception $e) {
    log_action("Erreur lors de la récupération des dernières sorties: " . $e->getMessage(), 'error');
}

// Récupérer la liste des utilisateurs pour navigation (admin/responsable)
$users_list = [];
if (is_admin() || is_admin_or_responsable()) {
    try {
        $users_query = "SELECT u.*, a.nom as antenne_nom FROM " . DB_PREFIX . "utilisateurs u 
                       LEFT JOIN " . DB_PREFIX . "antennes a ON u.antenne_id = a.id 
                       WHERE u.actif = 1 ORDER BY u.nom, u.prenom";
        $users_result = db_query($users_query);
        if ($users_result) {
            $users_list = $users_result->fetchAll();
        }
    } catch (Exception $e) {
        log_action("Erreur lors de la récupération de la liste des utilisateurs: " . $e->getMessage(), 'error');
    }
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
                
                <!-- En-tête de page -->
                <div class="row mb-4">
                    <div class="col">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="h2 mb-1">
                                    <i class="bi bi-person-circle text-primary me-2"></i>
                                    <?php if ($nav_user_id !== $user_id): ?>
                                        Profil de <?php echo h($nav_user['prenom'] . ' ' . $nav_user['nom']); ?>
                                    <?php else: ?>
                                        Mon profil
                                    <?php endif; ?>
                                </h1>
                                <p class="text-muted mb-0">
                                    <?php echo h($nav_user['role']); ?> - 
                                    <?php 
                                    $antenne_nav = array_filter($antennes, function($a) use ($nav_user) { 
                                        return $a['id'] == $nav_user['antenne_id']; 
                                    });
                                    echo h(reset($antenne_nav)['nom'] ?? 'Aucune antenne');
                                    ?>
                                </p>
                            </div>
                            
                            <!-- Navigation utilisateurs pour admin/responsable -->
                            <?php if (!empty($users_list) && count($users_list) > 1): ?>
                            <div class="dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-people me-1"></i>
                                    Changer d'utilisateur
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" style="max-height: 300px; overflow-y: auto;">
                                    <?php foreach ($users_list as $user): ?>
                                    <li>
                                        <a class="dropdown-item <?php echo $user['id'] == $nav_user_id ? 'active' : ''; ?>" 
                                           href="profil.php?user_id=<?php echo $user['id']; ?>">
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <?php if (!$user['actif']): ?>
                                                        <i class="bi bi-person-slash text-muted"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-person-check text-success"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <div class="fw-medium"><?php echo h($user['prenom'] . ' ' . $user['nom']); ?></div>
                                                    <small class="text-muted"><?php echo h($user['role'] . ' - ' . ($user['antenne_nom'] ?? 'Sans antenne')); ?></small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Messages d'alerte -->
                <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo h($message_type); ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Colonne gauche - Informations personnelles -->
                    <div class="col-lg-8">
                        
                        <!-- Formulaire informations personnelles -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-person-lines-fill me-2"></i>
                                    Informations personnelles
                                </h5>
                            </div>
                            <div class="card-body">
                                
                                <?php if (!is_admin() && !is_admin_or_responsable() && $nav_user_id === $user_id): ?>
                                <!-- Mode lecture seule pour conducteur -->
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Information :</strong> Vos données personnelles sont en lecture seule. 
                                    Contactez votre responsable d'antenne pour toute modification.
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-medium">Nom</label>
                                        <p class="form-control-plaintext"><?php echo h($nav_user['nom']); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-medium">Prénom</label>
                                        <p class="form-control-plaintext"><?php echo h($nav_user['prenom']); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-medium">Email</label>
                                        <p class="form-control-plaintext"><?php echo h($nav_user['email']); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-medium">Identifiant eProtec</label>
                                        <p class="form-control-plaintext"><?php echo h($nav_user['identifiant_eprotec'] ?: 'Non défini'); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-medium">Téléphone</label>
                                        <p class="form-control-plaintext"><?php echo h($nav_user['telephone'] ?: 'Non défini'); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-medium">Rôle</label>
                                        <p class="form-control-plaintext">
                                            <span class="badge bg-<?php echo $nav_user['role'] === 'admin' ? 'danger' : ($nav_user['role'] === 'responsable' ? 'warning' : 'secondary'); ?>">
                                                <?php echo h(ucfirst($nav_user['role'])); ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <?php else: ?>
                                <!-- Mode édition pour admin/responsable -->
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="update_profile">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nom" name="nom" 
                                                   value="<?php echo h($nav_user['nom']); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="prenom" name="prenom" 
                                                   value="<?php echo h($nav_user['prenom']); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo h($nav_user['email']); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="identifiant_eprotec" class="form-label">Identifiant eProtec</label>
                                            <input type="text" class="form-control" id="identifiant_eprotec" name="identifiant_eprotec" 
                                                   value="<?php echo h($nav_user['identifiant_eprotec']); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="telephone" class="form-label">Téléphone</label>
                                            <input type="tel" class="form-control" id="telephone" name="telephone" 
                                                   value="<?php echo h($nav_user['telephone']); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="date_naissance" class="form-label">Date de naissance</label>
                                            <input type="date" class="form-control" id="date_naissance" name="date_naissance" 
                                                   value="<?php echo h($nav_user['date_naissance']); ?>">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="adresse" class="form-label">Adresse</label>
                                            <input type="text" class="form-control" id="adresse" name="adresse" 
                                                   value="<?php echo h($nav_user['adresse']); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="ville" class="form-label">Ville</label>
                                            <input type="text" class="form-control" id="ville" name="ville" 
                                                   value="<?php echo h($nav_user['ville']); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="code_postal" class="form-label">Code postal</label>
                                            <input type="text" class="form-control" id="code_postal" name="code_postal" 
                                                   value="<?php echo h($nav_user['code_postal']); ?>" maxlength="5">
                                        </div>
                                        
                                        <!-- Champs admin/responsable -->
                                        <div class="col-md-4 mb-3">
                                            <label for="role" class="form-label">Rôle</label>
                                            <select class="form-select" id="role" name="role">
                                                <option value="conducteur" <?php echo $nav_user['role'] === 'conducteur' ? 'selected' : ''; ?>>Conducteur</option>
                                                <option value="responsable" <?php echo $nav_user['role'] === 'responsable' ? 'selected' : ''; ?>>Responsable</option>
                                                <?php if (is_admin()): ?>
                                                <option value="admin" <?php echo $nav_user['role'] === 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="antenne_id" class="form-label">Antenne</label>
                                            <select class="form-select" id="antenne_id" name="antenne_id">
                                                <option value="">Sélectionner une antenne</option>
                                                <?php foreach ($antennes as $antenne): ?>
                                                <option value="<?php echo $antenne['id']; ?>" 
                                                        <?php echo $nav_user['antenne_id'] == $antenne['id'] ? 'selected' : ''; ?>>
                                                    <?php echo h($antenne['nom']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Statut</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="actif" name="actif" 
                                                       <?php echo $nav_user['actif'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="actif">
                                                    Compte actif
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-lg me-1"></i>
                                            Enregistrer les modifications
                                        </button>
                                    </div>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Changement de mot de passe -->
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-key me-2"></i>
                                    Changement de mot de passe
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="change_password">
                                    
                                    <?php if (!is_admin() && !is_admin_or_responsable() && $nav_user_id === $user_id): ?>
                                    <!-- Conducteur : ancien mot de passe requis -->
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Mot de passe actuel <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    <?php else: ?>
                                    <!-- Admin/Responsable : réinitialisation directe -->
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        En tant qu'administrateur/responsable, vous pouvez réinitialiser le mot de passe sans connaître l'ancien.
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="new_password" class="form-label">Nouveau mot de passe <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                                   minlength="6" required>
                                            <small class="form-text text-muted">Au moins 6 caractères</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="confirm_password" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                                   minlength="6" required>
                                        </div>
                                    </div>
                                    
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="bi bi-key me-1"></i>
                                            Changer le mot de passe
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Colonne droite - Statistiques et activité -->
                    <div class="col-lg-4">
                        
                        <!-- Statistiques personnelles -->
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-graph-up me-2"></i>
                                    Mes statistiques
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                                            <div class="h3 text-primary mb-1"><?php echo number_format($stats['total_sorties']); ?></div>
                                            <div class="small text-muted">Total sorties</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                            <div class="h3 text-success mb-1"><?php echo number_format($stats['sorties_terminees']); ?></div>
                                            <div class="small text-muted">Terminées</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-3 bg-warning bg-opacity-10 rounded">
                                            <div class="h3 text-warning mb-1"><?php echo number_format($stats['km_total']); ?></div>
                                            <div class="small text-muted">KM total</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-3 bg-info bg-opacity-10 rounded">
                                            <div class="h3 text-info mb-1"><?php echo number_format($stats['km_moyen'], 1); ?></div>
                                            <div class="small text-muted">KM moyen</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dernières sorties -->
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-clock-history me-2"></i>
                                    Mes dernières sorties
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($dernieres_sorties)): ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($dernieres_sorties as $sortie): ?>
                                    <div class="list-group-item border-0 px-0">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <span class="badge bg-primary me-2"><?php echo h($sortie['type']); ?></span>
                                                    <?php echo h($sortie['immatriculation']); ?>
                                                </h6>
                                                <p class="mb-1 small text-muted">
                                                    <i class="bi bi-calendar me-1"></i>
                                                    <?php echo format_datetime($sortie['date_sortie']); ?>
                                                </p>
                                                <?php if (!empty($sortie['destination'])): ?>
                                                <p class="mb-1 small">
                                                    <i class="bi bi-geo-alt me-1"></i>
                                                    <?php echo h($sortie['destination']); ?>
                                                </p>
                                                <?php endif; ?>
                                                <?php if ($sortie['kilometrage_retour'] && $sortie['kilometrage_depart']): ?>
                                                <p class="mb-0 small text-muted">
                                                    <i class="bi bi-speedometer me-1"></i>
                                                    <?php echo number_format($sortie['kilometrage_retour'] - $sortie['kilometrage_depart']); ?> km
                                                </p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-end">
                                                <?php if ($sortie['statut'] === 'termine'): ?>
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle me-1"></i>Terminée
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-clock me-1"></i>En cours
                                                    </span>
                                                <?php endif; ?>
                                                <small class="d-block text-muted mt-1">
                                                    <?php echo time_ago($sortie['date_sortie']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <a href="<?php echo BASE_URL; ?>/vehicules/sorties/" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-list-ul me-1"></i>
                                        Voir toutes mes sorties
                                    </a>
                                </div>
                                
                                <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-car-front text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-3 mb-0">Aucune sortie enregistrée</p>
                                    <p class="small text-muted">Effectuez votre première sortie pour voir vos statistiques ici</p>
                                    <a href="<?php echo BASE_URL; ?>/vehicules/sorties/nouvelle.php" class="btn btn-primary btn-sm mt-2">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        Première sortie
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
        
        .text-warning {
            color: var(--pc-orange) !important;
        }
        
        .btn-primary {
            background-color: var(--pc-blue);
            border-color: var(--pc-blue);
        }
        
        .btn-primary:hover {
            background-color: #003366;
            border-color: #003366;
        }
        
        .btn-warning {
            background-color: var(--pc-orange);
            border-color: var(--pc-orange);
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #e67600;
            border-color: #e67600;
            color: white;
        }
        
        .form-control:focus,
        .form-select:focus {
            border-color: var(--pc-blue);
            box-shadow: 0 0 0 0.2rem rgba(0, 64, 128, 0.25);
        }
        
        .list-group-item {
            transition: background-color 0.15s ease-in-out;
        }
        
        .list-group-item:hover {
            background-color: rgba(0, 64, 128, 0.05);
        }
        
        .badge {
            font-size: 0.75em;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .dropdown-item.active {
            background-color: var(--pc-blue);
        }
        
        .dropdown-item:hover {
            background-color: rgba(0, 64, 128, 0.1);
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
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .card-body {
                padding: 1rem;
            }
            
            .h3 {
                font-size: 1.5rem;
            }
        }
    </style>

    <!-- JavaScript personnalisé -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Validation des mots de passe côté client
            const newPasswordInput = document.getElementById('new_password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            
            function validatePasswords() {
                if (newPasswordInput && confirmPasswordInput) {
                    const newPassword = newPasswordInput.value;
                    const confirmPassword = confirmPasswordInput.value;
                    
                    if (newPassword && confirmPassword && newPassword !== confirmPassword) {
                        confirmPasswordInput.setCustomValidity('Les mots de passe ne correspondent pas');
                    } else {
                        confirmPasswordInput.setCustomValidity('');
                    }
                }
            }
            
            if (newPasswordInput) {
                newPasswordInput.addEventListener('input', validatePasswords);
            }
            if (confirmPasswordInput) {
                confirmPasswordInput.addEventListener('input', validatePasswords);
            }
            
            // Auto-hide alerts après 5 secondes
            const alerts = document.querySelectorAll('.alert:not(.alert-info)');
            alerts.forEach(alert => {
                if (!alert.querySelector('.btn-close')) return;
                
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
            
            // Confirmation pour changement de mot de passe
            const passwordForm = document.querySelector('form[action=""][method="POST"]:has(input[name="action"][value="change_password"])');
            if (passwordForm) {
                passwordForm.addEventListener('submit', function(e) {
                    const isCurrentUser = <?php echo $nav_user_id === $user_id ? 'true' : 'false'; ?>;
                    const userName = '<?php echo h($nav_user['prenom'] . ' ' . $nav_user['nom']); ?>';
                    
                    let message = isCurrentUser 
                        ? 'Êtes-vous sûr de vouloir changer votre mot de passe ?'
                        : `Êtes-vous sûr de vouloir changer le mot de passe de ${userName} ?`;
                    
                    if (!confirm(message)) {
                        e.preventDefault();
                    }
                });
            }
            
            // Formatage automatique du code postal
            const codePostalInput = document.getElementById('code_postal');
            if (codePostalInput) {
                codePostalInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/\D/g, '').substring(0, 5);
                });
            }
            
            // Mise à jour temps réel des statistiques (optionnel)
            <?php if ($nav_user_id === $user_id): ?>
            // Recharger les stats toutes les 5 minutes pour l'utilisateur actuel
            setInterval(function() {
                // Ici on pourrait faire un appel AJAX pour mettre à jour les stats
                // Pour l'instant, on recharge juste la page si elle est restée ouverte longtemps
                if (document.hidden === false && performance.now() > 300000) { // 5 minutes
                    location.reload();
                }
            }, 300000);
            <?php endif; ?>
            
            console.log('Profil utilisateur chargé:', {
                user_id: <?php echo $nav_user_id; ?>,
                is_current_user: <?php echo $nav_user_id === $user_id ? 'true' : 'false'; ?>,
                role: '<?php echo h($nav_user['role']); ?>',
                total_sorties: <?php echo $stats['total_sorties']; ?>
            });
        });
    </script>
</body>
</html>