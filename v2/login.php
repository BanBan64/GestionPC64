<?php
echo "VERSION TEST - " . date('H:i:s');
/**
 * Page de connexion Protection Civile 64 - v2
 * Style identique à l'ancien login.php
 */

// Définir la constante de sécurité
define('PROTEC64_V2', true);

// Inclure les fichiers de base
require_once 'shared/includes/config.php';
require_once 'shared/includes/db.php';
require_once 'shared/includes/auth.php';
require_once 'shared/includes/utils.php';

// Rediriger si déjà connecté
if (is_logged_in()) {
    redirect(BASE_URL . '/index.php');
}

$error = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['user_login'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($login)) {
        $error = 'Le champ identifiant/email est obligatoire.';
    } elseif (empty($password)) {
        $error = 'Le champ mot de passe est obligatoire.';
    } else {
        try {
            $db = db_connect();
            
            // Chercher l'utilisateur par email OU identifiant (comme votre ancien système)
            $query = $db->prepare("
                SELECT u.*, a.nom as antenne_nom 
                FROM " . DB_PREFIX . "utilisateurs u 
                LEFT JOIN " . DB_PREFIX . "antennes a ON u.antenne_id = a.id 
                WHERE (u.email = ? OR u.identifiant = ?) AND u.actif = 1
            ");
            $query->execute([$login, $login]);
            $user = $query->fetch();
            
            if ($user && password_verify($password, $user['mot_de_passe'])) {
                // Connexion réussie
                login_user($user);
                
                // Redirection vers la page demandée ou l'accueil
                $redirect_url = $_GET['redirect'] ?? BASE_URL . '/index.php';
                redirect($redirect_url);
            } else {
                $error = 'Identifiant/Email ou mot de passe incorrect.';
            }
            
        } catch (Exception $e) {
            // Log détaillé pour debug
            error_log("Erreur connexion v2: " . $e->getMessage());
            $error = 'Erreur de connexion : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Protection Civile 64</title>
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/images/favicon.png">
    <link rel="apple-touch-icon" href="<?php echo ASSETS_URL; ?>/images/favicon.png">
    <link rel="shortcut icon" href="<?php echo ASSETS_URL; ?>/images/favicon.png">
    <style>
        body {
            background: linear-gradient(rgba(0, 64, 128, 0.7), rgba(240, 135, 0, 0.3)), 
                        url('<?php echo ASSETS_URL; ?>/images/fond_vps.jpg') center center/cover no-repeat fixed;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 100%;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo img {
            width: 80px;
            height: 80px;
            border-radius: 10px;
        }
        .logo h1 {
            color: #004080;
            margin: 10px 0 5px 0;
            font-size: 24px;
        }
        .logo p {
            color: #F08700;
            margin: 0;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #004080;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input:focus {
            border-color: #F08700;
            outline: none;
        }
        .btn {
            background: linear-gradient(135deg, #F08700, #ff9500);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
        }
        .btn:hover {
            background: linear-gradient(135deg, #e07600, #F08700);
            transform: translateY(-2px);
        }
        .error {
            background: #ffe6e6;
            color: #d00;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #d00;
        }
        .info {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="logo">
            <img src="<?php echo ASSETS_URL; ?>/images/logo-protection-civile.png" alt="Logo">
            <h1>Protection Civile</h1>
            <p>Pyrénées-Atlantiques (64)</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="user_login">Identifiant ou Email :</label>
                <input type="text" 
                       id="user_login" 
                       name="user_login" 
                       value="<?php echo htmlspecialchars($_POST['user_login'] ?? ''); ?>"
                       placeholder="contact@protectioncivile64.org"
                       required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="Votre mot de passe"
                       required>
            </div>
            
            <button type="submit" class="btn">🔐 Se connecter</button>
        </form>
        
        <div class="info">
            Première connexion ? Contactez votre administrateur.
        </div>
    </div>
</body>
</html>