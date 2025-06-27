<?php
/**
 * API pour récupérer les localisations géocodées depuis la base de données.
 * Répond au format JSON.
 */

// =================================================================
// 1. CONFIGURATION DE LA RÉPONSE HTTP
// =================================================================

// Indique que le contenu retourné sera du JSON.
header('Content-Type: application/json; charset=utf-8');

// Autorise l'accès à cette API depuis n'importe quelle origine (utile pour le développement).
// Pour la production, il est RECOMMANDÉ de remplacer '*' par le domaine de votre site web.
// Exemple : header('Access-Control-Allow-Origin: https://www.mon-site-web.com');
header('Access-Control-Allow-Origin: *');


// =================================================================
// 2. CONFIGURATION DE LA BASE DE DONNÉES (À MODIFIER)
// =================================================================
//PROD distante
$db_host = 'mdconsulsexperts.mysql.db';      // ou l'adresse IP de votre serveur de base de données
$db_name = 'mdconsulsexperts'; // Le nom de votre base de données
$db_user = 'mdconsulsexperts';  // Votre nom d'utilisateur pour la BDD
$db_pass = 'sDw4YxL9eqOnw37n';   // Votre mot de passe
//locale
$db_host = 'localhost';      // ou l'adresse IP de votre serveur de base de données
$db_name = 'geolocation'; // Le nom de votre base de données
$db_user = 'root';  // Votre nom d'utilisateur pour la BDD
$db_pass = '';   // Votre mot de passe

// =================================================================
// 3. CONNEXION À LA BASE DE DONNÉES
// =================================================================

try {
    // On crée une nouvelle instance de PDO. Le 'charset=utf8' est important pour les accents.
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);

    // On configure PDO pour qu'il lance des exceptions en cas d'erreur.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // En cas d'échec de la connexion, on renvoie une erreur 500 (Erreur Interne du Serveur)
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Échec de la connexion à la base de données.'
        // Pour le débogage, vous pouvez ajouter : 'details' => $e->getMessage()
    ]);
    // On arrête le script.
    exit();
}

// =================================================================
// 4. RÉCUPÉRATION DES DONNÉES
// =================================================================
//`id_expert`, `full_name`, `first_name`, `last_name`, `email`, `phone`, `team`, `archive`, `valid`, `skill_set`, `supplier_evaluation`, `qrcode`, `qrcode_admin`, `date_upd`, `company`, `address`, `postcode`, `city`, `country`, `latitude`, `longitude`, `date_upd_admin`
try {
    // Préparez votre requête SQL.
    // MODIFIEZ 'votre_table' et les noms de colonnes si nécessaire.
    $sql = "
        SELECT 
            latitude AS lat, 
            longitude AS lon, 
            first_name AS prenom, 
            last_name AS nom,
            concat(address,postcode,city,country) AS adresse
        FROM 
            md101_experts_geoloc
        WHERE 
            latitude IS NOT NULL 
            AND longitude IS NOT NULL
    ";

    // Exécution de la requête
    $stmt = $pdo->query($sql);

    // Récupération de tous les résultats sous forme de tableau associatif
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // On vérifie que les coordonnées sont bien des nombres (float)
    foreach ($locations as &$loc) {
        $loc['lat'] = (float)$loc['lat'];
        $loc['lon'] = (float)$loc['lon'];
    }

    // =================================================================
    // 5. ENVOI DE LA RÉPONSE JSON
    // =================================================================
    
    // On encode le tableau PHP en une chaîne de caractères JSON et on l'affiche.
    echo json_encode($locations);

} catch (PDOException $e) {
    // Si la requête SQL échoue...
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur lors de la récupération des données.'
        // Pour le débogage : 'details' => $e->getMessage()
    ]);
    exit();
}

?>
