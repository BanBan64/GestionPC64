<?php
/**
 * Page de déconnexion Protection Civile 64 - v2
 * 
 * Déconnexion sécurisée avec nettoyage de session
 */

// Définir la constante de sécurité
define('PROTEC64_V2', true);

// Inclure les fichiers de base
require_once 'shared/includes/config.php';
require_once 'shared/includes/db.php';
require_once 'shared/includes/auth.php';
require_once 'shared/includes/utils.php';

// Vérifier si l'utilisateur est connecté
$was_logged_in = is_logged_in();
$user_name = '';

if ($was_logged_in) {
    $current_user = get_current_user();
    $user_name = $current_user['prenom'] . ' ' . $current_user['nom'];
    
    // Logger la déconnexion
    log_action("Déconnexion de l'utilisateur: " . $user_name, 'info', [
        'user_id' => $current_user['id'],
        'ip' => get_client_ip(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}

// Effectuer la déconnexion
logout_user();

// Définir un message flash pour la page de connexion
init_session();
set_flash_message('Vous avez été déconnecté avec succès.', 'success');

// Variables pour la page
$page_title = 'Déconnexion';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Déconnexion du système Protection Civile 64">
    <title><?php echo h($page_title); ?> - <?php echo APP_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/images/favicon.png">
    <link rel="apple-touch-icon" href="<?php echo ASSETS_URL; ?>/images/favicon.png">
    <link rel="shortcut icon" href="<?php echo ASSETS_URL; ?>/images/favicon.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --pc-blue: <?php echo COLORS['primary']; ?>;
            --pc-orange: <?php echo COLORS['secondary']; ?>;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--pc-blue) 0%, #003366 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Image de fond avec overlay */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('<?php echo ASSETS_URL; ?>/images/fond_vps.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.1;
            z-index: -2;
        }
        
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--pc-blue) 0%, rgba(0,51,102,0.95) 100%);
            z-index: -1;
        }
        
        .logout-container {
            text-align: center;
            color: white;
            animation: fadeInUp 0.8s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logout-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 3rem 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            margin: 2rem;
        }
        
        .logout-icon {
            font-size: 4rem;
            color: var(--pc-orange);
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }
        
        .logout-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: white;
        }
        
        .logout-message {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
        }
        
        .countdown {
            font-size: 1.1rem;
            color: var(--pc-orange);
            font-weight: 600;
            margin-bottom: 2rem;
        }
        
        .btn-login-again {
            background: linear-gradient(135deg, var(--pc-orange) 0%, #e67600 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin: 0.5rem;
        }
        
        .btn-login-again:hover {
            background: linear-gradient(135deg, #e67600 0%, var(--pc-orange) 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(240, 135, 0, 0.4);
            color: white;
            text-decoration: none;
        }
        
        .security-info {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .security-info i {
            color: var(--pc-orange);
            margin-right: 0.5rem;
        }
        
        /* Particules flottantes */
        .particle {
            position: absolute;
            background: rgba(240, 135, 0, 0.3);
            border-radius: 50%;
            pointer-events: none;
            animation: floatParticle 15s infinite linear;
        }
        
        .particle:nth-child(1) {
            width: 10px;
            height: 10px;
            left: 10%;
            animation-delay: 0s;
        }
        
        .particle:nth-child(2) {
            width: 15px;
            height: 15px;
            left: 20%;
            animation-delay: 3s;
        }
        
        .particle:nth-child(3) {
            width: 8px;
            height: 8px;
            left: 80%;
            animation-delay: 6s;
        }
        
        .particle:nth-child(4) {
            width: 12px;
            height: 12px;
            left: 70%;
            animation-delay: 9s;
        }
        
        @keyframes floatParticle {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .logout-card {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }
            
            .logout-title {
                font-size: 2rem;
            }
            
            .logout-icon {
                font-size: 3rem;
            }
        }
    </style>
    
    <!-- Meta refresh pour redirection automatique -->
    <meta http-equiv="refresh" content="5;url=<?php echo BASE_URL; ?>/login.php">
</head>
<body>
    <!-- Particules flottantes -->
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    
    <div class="logout-container">
        <div class="logout-card">
            <!-- Icône de déconnexion -->
            <div class="logout-icon">
                <i class="bi bi-shield-check"></i>
            </div>
            
            <!-- Titre -->
            <h1 class="logout-title">Déconnexion réussie</h1>
            
            <!-- Message personnalisé -->
            <div class="logout-message">
                <?php if ($was_logged_in && !empty($user_name)): ?>
                    <p>Au revoir <strong><?php echo h($user_name); ?></strong> !</p>
                    <p>Merci pour votre engagement au service de la Protection Civile 64.</p>
                <?php else: ?>
                    <p>Vous avez été déconnecté du système.</p>
                    <p>Merci d'avoir utilisé notre plateforme.</p>
                <?php endif; ?>
            </div>
            
            <!-- Compteur de redirection -->
            <div class="countdown">
                <i class="bi bi-clock"></i>
                Redirection automatique dans <span id="countdown">5</span> secondes
            </div>
            
            <!-- Bouton de reconnexion -->
            <div>
                <a href="<?php echo BASE_URL; ?>/login.php" class="btn-login-again">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Se reconnecter
                </a>
            </div>
            
            <!-- Informations de sécurité -->
            <div class="security-info">
                <p>
                    <i class="bi bi-info-circle"></i>
                    Votre session a été fermée en toute sécurité.
                </p>
                <p>
                    <i class="bi bi-shield-lock"></i>
                    Pour votre sécurité, fermez votre navigateur si vous utilisez un ordinateur partagé.
                </p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Compteur de redirection
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(function() {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = '<?php echo BASE_URL; ?>/login.php';
            }
        }, 1000);
        
        // Permettre l'annulation de la redirection
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                clearInterval(timer);
                countdownElement.parentElement.innerHTML = '<i class="bi bi-pause-circle"></i> Redirection annulée';
            }
        });
        
        // Animation des particules supplémentaires au clic
        document.addEventListener('click', function(e) {
            createClickParticle(e.clientX, e.clientY);
        });
        
        function createClickParticle(x, y) {
            const particle = document.createElement('div');
            particle.style.position = 'fixed';
            particle.style.left = x + 'px';
            particle.style.top = y + 'px';
            particle.style.width = '6px';
            particle.style.height = '6px';
            particle.style.background = 'var(--pc-orange)';
            particle.style.borderRadius = '50%';
            particle.style.pointerEvents = 'none';
            particle.style.zIndex = '1000';
            particle.style.animation = 'clickParticle 1s ease-out forwards';
            
            document.body.appendChild(particle);
            
            setTimeout(() => {
                particle.remove();
            }, 1000);
        }
        
        // CSS pour l'animation des particules de clic
        const style = document.createElement('style');
        style.textContent = `
            @keyframes clickParticle {
                0% {
                    transform: scale(1) translate(0, 0);
                    opacity: 1;
                }
                100% {
                    transform: scale(3) translate(${Math.random() * 100 - 50}px, ${Math.random() * 100 - 50}px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
        
        // Log de la déconnexion côté client (si nécessaire)
        console.log('<?php echo $was_logged_in ? "Déconnexion utilisateur: " . h($user_name) : "Déconnexion système"; ?>');
        console.log('Timestamp: ' + new Date().toISOString());
    </script>
</body>
</html>