<?php
/**
 * Diagnostic complet de la base de données Protection Civile 64
 * À utiliser pour analyser la structure avant de générer les codes
 */

// Définir la constante de sécurité
define('PROTEC64_V2', true);

// Inclure les fichiers de base
require_once 'shared/includes/config.php';
require_once 'shared/includes/db.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic BDD - Protection Civile 64</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { background: white; margin: 20px 0; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: #004080; color: white; padding: 15px; text-align: center; border-radius: 8px; margin-bottom: 20px; }
        .table-info { margin-bottom: 30px; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
        .table-header { background: #F08700; color: white; padding: 10px; font-weight: bold; }
        .table-content { padding: 15px; }
        .column { display: inline-block; margin: 2px 5px; padding: 4px 8px; background: #e3f2fd; border-radius: 4px; font-size: 12px; }
        .sample-data { background: #f9f9f9; padding: 10px; border-radius: 4px; margin-top: 10px; }
        .error { background: #ffebee; color: #c62828; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .success { background: #e8f5e8; color: #2e7d32; padding: 10px; border-radius: 4px; margin: 10px 0; }
        pre { white-space: pre-wrap; font-size: 11px; }
        .stats { display: flex; gap: 20px; flex-wrap: wrap; }
        .stat-box { background: #f0f8ff; padding: 15px; border-radius: 8px; flex: 1; min-width: 200px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Diagnostic Base de Données Protection Civile 64</h1>
            <p>Analyse complète de la structure pour développement v2</p>
        </div>

        <?php
        try {
            $db = db_connect();
            echo '<div class="success">✅ Connexion à la base de données réussie</div>';
            
            // 1. INFORMATIONS GÉNÉRALES
            echo '<div class="card">';
            echo '<h2>📊 Informations générales</h2>';
            echo '<div class="stats">';
            
            // Version MySQL
            $version_query = $db->query("SELECT VERSION() as version");
            $version = $version_query->fetch();
            echo '<div class="stat-box"><strong>Version MySQL:</strong><br>' . $version['version'] . '</div>';
            
            // Nom de la base
            echo '<div class="stat-box"><strong>Base de données:</strong><br>' . DB_NAME . '</div>';
            
            // Préfixe des tables
            echo '<div class="stat-box"><strong>Préfixe tables:</strong><br>' . DB_PREFIX . '</div>';
            
            // Charset
            $charset_query = $db->query("SELECT DEFAULT_CHARACTER_SET_NAME as charset FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
            $charset = $charset_query->fetch();
            echo '<div class="stat-box"><strong>Charset:</strong><br>' . ($charset['charset'] ?? 'Non défini') . '</div>';
            
            echo '</div></div>';
            
            // 2. LISTE DES TABLES
            echo '<div class="card">';
            echo '<h2>📋 Tables existantes</h2>';
            
            $tables_query = $db->query("SHOW TABLES");
            $tables = $tables_query->fetchAll(PDO::FETCH_COLUMN);
            
            echo '<p><strong>Nombre total de tables:</strong> ' . count($tables) . '</p>';
            echo '<div class="stats">';
            
            foreach ($tables as $table) {
                $count_query = $db->query("SELECT COUNT(*) as count FROM `$table`");
                $count = $count_query->fetch();
                
                echo '<div class="stat-box">';
                echo '<strong>' . $table . '</strong><br>';
                echo $count['count'] . ' enregistrements';
                echo '</div>';
            }
            echo '</div></div>';
            
            // 3. STRUCTURE DÉTAILLÉE DE CHAQUE TABLE
            foreach ($tables as $table) {
                echo '<div class="table-info">';
                echo '<div class="table-header">🗂️ Table: ' . $table . '</div>';
                echo '<div class="table-content">';
                
                // Structure de la table
                echo '<h4>Structure des colonnes:</h4>';
                $structure_query = $db->query("DESCRIBE `$table`");
                $columns = $structure_query->fetchAll();
                
                echo '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">';
                echo '<tr style="background: #f0f0f0;"><th>Colonne</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>';
                
                foreach ($columns as $col) {
                    echo '<tr>';
                    echo '<td><strong>' . $col['Field'] . '</strong></td>';
                    echo '<td>' . $col['Type'] . '</td>';
                    echo '<td>' . $col['Null'] . '</td>';
                    echo '<td>' . $col['Key'] . '</td>';
                    echo '<td>' . ($col['Default'] ?? 'NULL') . '</td>';
                    echo '<td>' . $col['Extra'] . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                
                // Données d'exemple (3 premiers enregistrements)
                echo '<h4>Exemple de données (3 premiers enregistrements):</h4>';
                try {
                    $sample_query = $db->query("SELECT * FROM `$table` LIMIT 3");
                    $samples = $sample_query->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (!empty($samples)) {
                        echo '<div class="sample-data">';
                        echo '<pre>' . json_encode($samples, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
                        echo '</div>';
                    } else {
                        echo '<p><em>Aucune donnée dans cette table</em></p>';
                    }
                } catch (Exception $e) {
                    echo '<div class="error">Erreur lors de la récupération des données: ' . $e->getMessage() . '</div>';
                }
                
                // Index et clés
                echo '<h4>Index et clés:</h4>';
                try {
                    $indexes_query = $db->query("SHOW INDEX FROM `$table`");
                    $indexes = $indexes_query->fetchAll();
                    
                    if (!empty($indexes)) {
                        echo '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">';
                        echo '<tr style="background: #f0f0f0;"><th>Nom</th><th>Colonne</th><th>Unique</th><th>Type</th></tr>';
                        
                        foreach ($indexes as $idx) {
                            echo '<tr>';
                            echo '<td>' . $idx['Key_name'] . '</td>';
                            echo '<td>' . $idx['Column_name'] . '</td>';
                            echo '<td>' . ($idx['Non_unique'] == 0 ? 'OUI' : 'NON') . '</td>';
                            echo '<td>' . $idx['Index_type'] . '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    } else {
                        echo '<p><em>Aucun index défini</em></p>';
                    }
                } catch (Exception $e) {
                    echo '<div class="error">Erreur lors de la récupération des index: ' . $e->getMessage() . '</div>';
                }
                
                echo '</div></div>';
            }
            
            // 4. RELATIONS ET CONTRAINTES
            echo '<div class="card">';
            echo '<h2>🔗 Relations et contraintes étrangères</h2>';
            
            try {
                $constraints_query = $db->query("
                    SELECT 
                        TABLE_NAME,
                        COLUMN_NAME,
                        CONSTRAINT_NAME,
                        REFERENCED_TABLE_NAME,
                        REFERENCED_COLUMN_NAME
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE REFERENCED_TABLE_SCHEMA = '" . DB_NAME . "'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                $constraints = $constraints_query->fetchAll();
                
                if (!empty($constraints)) {
                    echo '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">';
                    echo '<tr style="background: #f0f0f0;"><th>Table</th><th>Colonne</th><th>Référence</th><th>Colonne Réf</th></tr>';
                    
                    foreach ($constraints as $constraint) {
                        echo '<tr>';
                        echo '<td>' . $constraint['TABLE_NAME'] . '</td>';
                        echo '<td>' . $constraint['COLUMN_NAME'] . '</td>';
                        echo '<td>' . $constraint['REFERENCED_TABLE_NAME'] . '</td>';
                        echo '<td>' . $constraint['REFERENCED_COLUMN_NAME'] . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<p><em>Aucune contrainte de clé étrangère définie</em></p>';
                }
            } catch (Exception $e) {
                echo '<div class="error">Erreur lors de la récupération des contraintes: ' . $e->getMessage() . '</div>';
            }
            echo '</div>';
            
            // 5. RECOMMANDATIONS POUR LE DÉVELOPPEMENT
            echo '<div class="card">';
            echo '<h2>💡 Recommandations pour le développement v2</h2>';
            echo '<div class="success">';
            echo '<h4>✅ Points à retenir:</h4>';
            echo '<ul>';
            echo '<li><strong>Préfixe:</strong> Toutes les requêtes doivent utiliser DB_PREFIX = "' . DB_PREFIX . '"</li>';
            echo '<li><strong>Colonnes identifiant:</strong> Vérifier si "identifiant" ou "identifiant_eprotec"</li>';
            echo '<li><strong>Colonnes kilométrage:</strong> Vérifier les noms exacts pour les calculs</li>';
            echo '<li><strong>Relations:</strong> Identifier les jointures nécessaires entre tables</li>';
            echo '</ul>';
            echo '</div>';
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<div class="error">❌ Erreur de connexion: ' . $e->getMessage() . '</div>';
            echo '<div class="card">';
            echo '<h3>🔧 Paramètres de connexion actuels:</h3>';
            echo '<ul>';
            echo '<li><strong>Host:</strong> ' . DB_HOST . '</li>';
            echo '<li><strong>Database:</strong> ' . DB_NAME . '</li>';
            echo '<li><strong>User:</strong> ' . DB_USER . '</li>';
            echo '<li><strong>Prefix:</strong> ' . DB_PREFIX . '</li>';
            echo '</ul>';
            echo '</div>';
        }
        ?>
        
        <div class="card">
            <h3>⚠️ IMPORTANT</h3>
            <p><strong>Supprimez ce fichier après utilisation</strong> pour des raisons de sécurité !</p>
            <p>Ce diagnostic contient des informations sensibles sur votre base de données.</p>
        </div>
    </div>
</body>
</html>