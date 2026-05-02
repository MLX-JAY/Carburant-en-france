<?php

/**
 * @file fonction.inc.php
 * @brief Fonctions utilitaires pour la gestion des régions, départements et villes de France.
 * 
 * Ce fichier contient les fonctions permettant de gérer l'affichage des régions et départements,
 * ainsi que la récupération des villes à partir d'un fichier CSV optimisé par indexation,
 * et la recherche des stations de carburant.
 * 
 * @author ANURAJAN Thenuxshan, FERAOUN Mohamed Amine
 * @version 2.0
 * @date 2026
 */

// Configuration pour les carburants
$remoteZipUrl = 'https://donnees.roulez-eco.fr/opendata/instantane';
$xmlLocalPath = dirname(__DIR__) . '/données/carburants.xml';
$zipTempPath = dirname(__DIR__) . '/données/temp_carburants.zip';
$cacheValidity = 10 * 60;

/**
 * @brief Tableau associatif des départements par région.
 * 
 * Indexé par numéro de région (0-12), contient un tableau de départements
 * avec leur ID et nom.
 * 
 * @var array<int, array<int, array{id: string, nom: string}>>
 */
$regionsDepartements = [
    0 => [  // Bretagne
        ['id' => '22', 'nom' => 'Côtes-d\'Armor'],
        ['id' => '29', 'nom' => 'Finistère'],
        ['id' => '35', 'nom' => 'Ille-et-Vilaine'],
        ['id' => '56', 'nom' => 'Morbihan'],
    ],
    1 => [  // Normandie
        ['id' => '14', 'nom' => 'Calvados'],
        ['id' => '27', 'nom' => 'Eure'],
        ['id' => '50', 'nom' => 'Manche'],
        ['id' => '61', 'nom' => 'Orne'],
        ['id' => '76', 'nom' => 'Seine-Maritime'],
    ],
    2 => [  // Hauts-de-France
        ['id' => '59', 'nom' => 'Nord'],
        ['id' => '62', 'nom' => 'Pas-de-Calais'],
        ['id' => '80', 'nom' => 'Somme'],
        ['id' => '60', 'nom' => 'Oise'],
        ['id' => '02', 'nom' => 'Aisne'],
    ],
    3 => [  // Grand Est
        ['id' => '08', 'nom' => 'Ardennes'],
        ['id' => '10', 'nom' => 'Aube'],
        ['id' => '51', 'nom' => 'Marne'],
        ['id' => '52', 'nom' => 'Haute-Marne'],
        ['id' => '54', 'nom' => 'Meurthe-et-Moselle'],
        ['id' => '55', 'nom' => 'Meuse'],
        ['id' => '57', 'nom' => 'Moselle'],
        ['id' => '67', 'nom' => 'Bas-Rhin'],
        ['id' => '68', 'nom' => 'Haut-Rhin'],
        ['id' => '88', 'nom' => 'Vosges'],
    ],
    4 => [  // Île-de-France
        ['id' => '75', 'nom' => 'Paris'],
        ['id' => '77', 'nom' => 'Seine-et-Marne'],
        ['id' => '78', 'nom' => 'Yvelines'],
        ['id' => '91', 'nom' => 'Essonne'],
        ['id' => '92', 'nom' => 'Hauts-de-Seine'],
        ['id' => '93', 'nom' => 'Seine-Saint-Denis'],
        ['id' => '94', 'nom' => 'Val-de-Marne'],
        ['id' => '95', 'nom' => 'Val-d\'Oise'],
    ],
    5 => [  // Bourgogne
        ['id' => '21', 'nom' => 'Côte-d\'Or'],
        ['id' => '58', 'nom' => 'Nièvre'],
        ['id' => '71', 'nom' => 'Saône-et-Loire'],
        ['id' => '89', 'nom' => 'Yonne'],
        ['id' => '63', 'nom' => 'Puy-de-Dôme'],
    ],
    6 => [  // Val-de-Loire
        ['id' => '18', 'nom' => 'Cher'],
        ['id' => '28', 'nom' => 'Eure-et-Loir'],
        ['id' => '36', 'nom' => 'Indre'],
        ['id' => '37', 'nom' => 'Indre-et-Loire'],
        ['id' => '41', 'nom' => 'Loir-et-Cher'],
        ['id' => '45', 'nom' => 'Loiret'],
        ['id' => '72', 'nom' => 'Sarthe'],
        ['id' => '86', 'nom' => 'Vienne'],
    ],
    7 => [  // Pays de la Loire
        ['id' => '44', 'nom' => 'Loire-Atlantique'],
        ['id' => '49', 'nom' => 'Maine-et-Loire'],
        ['id' => '53', 'nom' => 'Mayenne'],
        ['id' => '72', 'nom' => 'Sarthe'],
        ['id' => '85', 'nom' => 'Vendée'],
    ],
    8 => [  // Nouvelle-Aquitaine
        ['id' => '16', 'nom' => 'Charente'],
        ['id' => '17', 'nom' => 'Charente-Maritime'],
        ['id' => '19', 'nom' => 'Corrèze'],
        ['id' => '24', 'nom' => 'Dordogne'],
        ['id' => '33', 'nom' => 'Gironde'],
        ['id' => '40', 'nom' => 'Landes'],
        ['id' => '47', 'nom' => 'Lot-et-Garonne'],
        ['id' => '64', 'nom' => 'Pyrénées-Atlantiques'],
        ['id' => '79', 'nom' => 'Deux-Sèvres'],
        ['id' => '87', 'nom' => 'Haute-Vienne'],
        ['id' => '86', 'nom' => 'Vienne'],
    ],
    9 => [  // Occitanie
        ['id' => '09', 'nom' => 'Ariège'],
        ['id' => '11', 'nom' => 'Aude'],
        ['id' => '12', 'nom' => 'Aveyron'],
        ['id' => '30', 'nom' => 'Gard'],
        ['id' => '31', 'nom' => 'Haute-Garonne'],
        ['id' => '32', 'nom' => 'Gers'],
        ['id' => '34', 'nom' => 'Hérault'],
        ['id' => '46', 'nom' => 'Lot'],
        ['id' => '48', 'nom' => 'Lozère'],
        ['id' => '65', 'nom' => 'Hautes-Pyrénées'],
        ['id' => '66', 'nom' => 'Pyrénées-Orientales'],
        ['id' => '81', 'nom' => 'Tarn'],
        ['id' => '82', 'nom' => 'Tarn-et-Garonne'],
    ],
    10 => [  // Côte d'Azur
        ['id' => '04', 'nom' => 'Alpes-de-Haute-Provence'],
        ['id' => '05', 'nom' => 'Hautes-Alpes'],
        ['id' => '06', 'nom' => 'Alpes-Maritimes'],
        ['id' => '83', 'nom' => 'Var'],
        ['id' => '84', 'nom' => 'Vaucluse'],
    ],
    11 => [  // Auvergne
        ['id' => '03', 'nom' => 'Allier'],
        ['id' => '15', 'nom' => 'Cantal'],
        ['id' => '43', 'nom' => 'Haute-Loire'],
        ['id' => '63', 'nom' => 'Puy-de-Dôme'],
    ],
    12 => [  // Corse
        ['id' => '2A', 'nom' => 'Corse-du-Sud'],
        ['id' => '2B', 'nom' => 'Haute-Corse'],
    ],
];

/**
 * @brief Tableau des noms de régions.
 * 
 * Indexé par numéro de région (0-12), associe le nom lisible de chaque région.
 * 
 * @var array<int, string>
 */
$regionsNoms = [
    0 => 'Bretagne',
    1 => 'Normandie',
    2 => 'Hauts-de-France',
    3 => 'Grand Est',
    4 => 'Île-de-France',
    5 => 'Bourgogne',
    6 => 'Val-de-Loire',
    7 => 'Pays de la Loire',
    8 => 'Nouvelle-Aquitaine',
    9 => 'Occitanie',
    10 => 'Côte d\'Azur',
    11 => 'Auvergne',
    12 => 'Corse',
];

/**
 * @brief Affiche les cartes des départements d'une région.
 * 
 * Cette fonction génère le HTML pour afficher une grille de cartes de départements.
 * Chaque carte contient le numéro du département, son nom et un lien vers la page
 * de sélection des villes.
 * 
 * @param array $departements Tableau des départements à afficher.
 *                             Chaque élément doit contenir 'id' et 'nom'.
 * 
 * @return void Cette fonction utilise echo pour afficher le HTML directement.
 * 
 * @global string $style Style actuel (clair ou sombre) pour les liens.
 * @global string $lang Langue actuelle pour les liens.
 * @global int|null $index Index de la région sélectionnée.
 * 
 * @note Utilise les variables globales $style, $lang et $index pour construire les URLs.
 * 
 * @example
 * @code
 * afficherDepartements($regionsDepartements[0]); // Affiche les départements de Bretagne
 * @endcode
 */
function afficherDepartements(array $departements): void {
    global $style, $lang, $index;
    
    echo '<div class="cartes-departements">';
    
    foreach ($departements as $dept) {
        echo '<article class="carte-departement" id="departements">';
        echo '<span class="departement-numero">' . $dept['id'] . '</span>';
        echo '<h3 class="departement-nom">' . htmlspecialchars($dept['nom']) . '</h3>';
        echo '<div class="boutons-departement">';
        echo '<a href="?code_postal=' . $dept['id'] . '000&afficher=villes&lang=' . $lang . '&style=' . $style . '&index=' . $index . '#form-villes" class="bouton-departement">Voir les villes</a>';
        echo '</div>';
        echo '</article>';
    }
    
    echo '</div>';
}

/**
 * @brief Récupère la liste des villes d'un département à partir du fichier CSV.
 * 
 * Cette fonction lit séquentiellement le fichier CSV des communes de France
 * et retourne toutes les villes du département spécifié en utilisant
 * le préfixe du code postal.
 * 
 * @warning Cette fonction est lente pour les fichiers volumineux.
 *          Utiliser getVillesByDepartementFast() pour de meilleures performances.
 * 
 * @param string $depCode Code du département (ex: "28", "02", "59").
 *                         Peut être au format string ou int.
 * 
 * @return array<string, string> Tableau associatif [nom_ville => code_postal] trié par nom de ville.
 *                       Retourne un tableau vide si le département n'existe pas
 *                       ou si le fichier CSV n'est pas trouvé.
 * 
 * @since Version 1.0
 * @see getVillesByDepartementFast() Pour une version optimisée avec indexation.
 * 
 * @note Le code département est normalisé (ajout du zéro initial si nécessaire).
 *       Par exemple, "2" devient "02" pour l'Aisne.
 * @note La recherche se fait par préfixe du code postal (ex: "28" pour 28000-28999).
 * 
 * @example
 * @code
 * $villes = getVillesByDepartement("28"); // Retourne ['Lyon' => '69002', ...]
 * @endcode
 */
function getVillesByDepartement(string $depCode): array {
    $villes = [];
    $baseDir = dirname(__DIR__);
    $csvPath = $baseDir . '/données/clean_postcodes.csv';
    
    if (!file_exists($csvPath)) {
        return $villes;
    }
    
    if (strlen($depCode) == 1 && is_numeric($depCode)) {
        $depCode = str_pad($depCode, 2, '0', STR_PAD_LEFT);
    }
    
    $prefix = $depCode;
    
    if (($handle = fopen($csvPath, "r")) !== FALSE) {
        fgetcsv($handle, 1000, ",", '"', "\\");
        
        while (($data = fgetcsv($handle, 1000, ",", '"', "\\")) !== FALSE) {
            if (isset($data[2]) && isset($data[1])) {
                $codePostal = trim($data[2]);
                $nomVille = trim($data[1]);
                if (strpos($codePostal, $prefix) === 0 && $nomVille !== '') {
                    if (!isset($villes[$nomVille])) {
                        $villes[$nomVille] = $codePostal;
                    }
                }
            }
        }
        fclose($handle);
    }
    ksort($villes);
    return $villes;
}

/**
 * @brief Génère le fichier d'index des positions de départ de chaque préfixe de département.
 * 
 * Cette fonction parcourt le fichier CSV une seule fois et crée un fichier JSON
 * contenant la position de départ de chaque préfixe de code postal dans le fichier.
 * Ce fichier d'index permet ensuite un accès direct aux données avec fseek().
 * 
 * @return bool true si l'index a été généré avec succès, false en cas d'erreur.
 * 
 * @note Le fichier d'index est enregistré dans données/index_villes.json
 * @note Cette fonction n'est normalement pas appelée directement.
 *       Elle est invoquée automatiquement par getVillesByDepartementFast().
 * 
 * @warning Cette opération peut prendre quelques secondes lors de la première exécution.
 * 
 * @example
 * @code
 * $result = genererIndexVilles(); // Génère index_villes.json
 * @endcode
 * 
 * @see getVillesByDepartementFast() Utilise cet index pour un accès rapide.
 */
function genererIndexVilles(): bool {
    $baseDir = dirname(__DIR__);
    $csvPath = $baseDir . '/données/clean_postcodes.csv';
    $indexPath = $baseDir . '/données/index_villes.json';
    
    if (!file_exists($csvPath)) {
        error_log("CSV file not found");
        return false;
    }
    
    $index = [];
    $handle = fopen($csvPath, 'r');
    
    if ($handle === false) {
        return false;
    }
    
    fgetcsv($handle, 1000, ",", '"', "\\");
    
    while (!feof($handle)) {
        $position = ftell($handle);
        $data = fgetcsv($handle, 1000, ",", '"', "\\");
        
        if ($data !== FALSE && isset($data[2])) {
            $codePostal = trim($data[2]);
            $prefix = substr($codePostal, 0, 2);
            
            if (!isset($index[$prefix])) {
                $index[$prefix] = $position;
            }
        }
    }
    
    fclose($handle);
    file_put_contents($indexPath, json_encode($index));
    return true;
}

/**
 * @brief Récupère la liste des villes d'un département avec accès optimisé.
 * 
 * Cette fonction utilise un fichier d'index (index_villes.json) pour accéder
 * directement à la position du préfixe de code postal dans le fichier CSV, sans lire
 * toutes les lignes précédentes. C'est beaucoup plus rapide que la version
 * séquentielle pour les fichiers volumineux.
 * 
 * La recherche utilise le préfixe du code postal (ex: "28" pour 28000-28999).
 * Si le fichier d'index n'existe pas, il est généré automatiquement lors
 * du premier appel.
 * 
 * @param string $depCode Code du département (ex: "28", "02", "59").
 *                         Peut être au format string ou int.
 * 
 * @return array<string, string> Tableau associatif [nom_ville => code_postal] trié par nom de ville.
 *                       Retourne un tableau vide si le département n'existe pas
 *                       ou si le fichier CSV n'est pas trouvé.
 * 
 * @since Version 1.0 (optimisée)
 * @see getVillesByDepartement() Version séquentielle (plus lente).
 * 
 * @note Le code département est normalisé (ajout du zéro initial si nécessaire).
 * 
 * @note L'index est automatiquement généré si le fichier n'existe pas.
 * 
 * @example
 * @code
 * $villes = getVillesByDepartementFast("28"); // Retourne ['Lyon' => '69002', ...]
 * @endcode
 */
function getVillesByDepartementFast(string $depCode): array {
    $villes = [];
    $baseDir = dirname(__DIR__);
    $csvPath = $baseDir . '/données/clean_postcodes.csv';
    $indexPath = $baseDir . '/données/index_villes.json';
    
    if (!file_exists($csvPath)) {
        return $villes;
    }
    
    if (!file_exists($indexPath)) {
        genererIndexVilles();
    }
    
    $index = json_decode(file_get_contents($indexPath), true);
    
    if ($index === null) {
        return $villes;
    }
    
    if (strlen($depCode) == 1 && is_numeric($depCode)) {
        $depCode = str_pad($depCode, 2, '0', STR_PAD_LEFT);
    }
    
    $prefix = $depCode;
    
    if (!isset($index[$prefix])) {
        return $villes;
    }
    
    $handle = fopen($csvPath, 'r');
    fseek($handle, $index[$prefix]);
    
    while (($data = fgetcsv($handle, 1000, ",", '"', "\\")) !== FALSE) {
        if (!isset($data[2]) || !isset($data[1])) {
            break;
        }
        
        $codePostal = trim($data[2]);
        $nomVille = trim($data[1]);
        
        if (strpos($codePostal, $prefix) !== 0) {
            break;
        }
        
        if ($nomVille !== '' && !isset($villes[$nomVille])) {
            $villes[$nomVille] = $codePostal;
        }
    }
    
    fclose($handle);
    ksort($villes);
    return $villes;
}

/**
 * @brief Récupère la géolocalisation IP de l'utilisateur via API JSON
 * 
 * Utilise l'API ip2location.io qui retourne un flux JSON avec les informations
 * de géolocalisation (ville, région, pays).
 * 
 * @return array|null Array avec ville, region, pays, ip ou null si échec.
 * 
 * @note Si l'IP est localhost (::1 ou 127.0.0.1), utilise une IP de test en France.
 * @note Utilise json_decode pour parser la réponse JSON de l'API.
 * 
 * @example
 * @code
 * $geo = getGeolocationIP();
 * if ($geo !== null) {
 *     echo $geo['ville']; // Affiche la ville détectée
 * }
 * @endcode
 */

/**
 * @brief Normalise une chaîne pour comparaison :去掉 accents, remplace tirets par espaces, met en majuscules
 * @param string $chaine La chaîne à normaliser
 * @return string Chaîne normalisée
 */
function normaliserChaine(string $chaine): string {
    $chaine = str_replace('-', ' ', $chaine);
    $chaine = strtoupper($chaine);
    $chaine = str_replace(['É', 'È', 'Ê', 'Ë'], 'E', $chaine);
    $chaine = str_replace(['À', 'Â', 'Ä'], 'A', $chaine);
    $chaine = str_replace(['Î', 'Ï'], 'I', $chaine);
    $chaine = str_replace(['Ô', 'Ö'], 'O', $chaine);
    $chaine = str_replace(['Ù', 'Û', 'Ü'], 'U', $chaine);
    return $chaine;
}

function getGeolocationIP(): ?array {
    // 1. Récupérer l'IP
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // Fallback pour localhost
    if ($user_ip === '::1' || $user_ip === '127.0.0.1') {
        $user_ip = '176.158.125.167'; // IP de test en France
    }
    
    // 2. Appel API en JSON (ip2location.io gratuit sans clé pour tests)
    $url = "https://api.ip2location.io/?ip=" . $user_ip;
    
    try {
        $json = @file_get_contents($url);
        
        if ($json === false) {
            return null;
        }
        
        $data = json_decode($json, true);
        
        // ip2location.io retourne un champ 'error' en cas d'erreur
        // sinon elle retourne directement les données
        if ($data === null || isset($data['error'])) {
            return null;
        }

        $bonzip = substr($data['zip_code'] ?? '', 0, 5);

        return [
            'ville' => $data['city_name'] ?? '',
            'region' => $data['region_name'] ?? '',
            'pays' => $data['country_name'] ?? '',
            'ip' => $user_ip,
            'zip_code' => $bonzip
        ];
        
    } catch (Exception $e) {
        error_log("Erreur géolocalisation: " . $e->getMessage());
        return null;
    }
}

/**
 * @brief Télécharge et extrait le fichier XML des prix des carburants.
 * 
 * Cette fonction vérifie si le fichier XML local est expire (plus vieux que cacheValidity).
 * Si nécessaire, elle telecharge le fichier ZIP depuis la source officielle,
 * l'extrait et sauvegard le XML localement.
 * 
 * @note Le fichier XML est telecharge depuis donnees.roulez-eco.fr
 * @note La validite du cache est definie a 10 minutes par defaut
 * 
 * @return void
 * 
 * @global string $remoteZipUrl URL du fichier ZIP distant
 * @global string $xmlLocalPath Chemin local du fichier XML
 * @global string $zipTempPath Chemin temporaire pour le ZIP
 * @global int $cacheValidity Duree de validite du cache en secondes
 */
/**
 * @brief Cache (L'intendant)
 * Gère le téléchargement et l'extraction du fichier XML
 */
function refreshCacheSiNecessaire(): void {
    global $remoteZipUrl, $xmlLocalPath, $zipTempPath, $cacheValidity;

    $needsRefresh = true;
    if (file_exists($xmlLocalPath)) {
        $xmlLastMod = filemtime($xmlLocalPath);
        $currentTime = time();
        if ($currentTime - $xmlLastMod < $cacheValidity) {
            $needsRefresh = false;
        }
    }

    if ($needsRefresh) {
        $zipContent = file_get_contents($remoteZipUrl);
        if ($zipContent === false) {
            return;
        }
        file_put_contents($zipTempPath, $zipContent);

        $zip = new ZipArchive;
        if ($zip->open($zipTempPath) === true) {
            $xmlExtracted = false;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);
                if (pathinfo($entryName, PATHINFO_EXTENSION) === 'xml') {
                    $xmlContent = $zip->getFromIndex($i);
                    file_put_contents($xmlLocalPath, $xmlContent);
                    $xmlExtracted = true;
                    break;
                }
            }
            $zip->close();

            if (!$xmlExtracted) {
                return;
            }
        }

        if (file_exists($zipTempPath)) {
            unlink($zipTempPath);
        }
    }
}

/**
 * Recherche et extrait les données des stations de carburant depuis un fichier XML local.
 * 
 * Cette fonction filtre les points de vente (PDV) selon un code postal de base, 
 * un périmètre de recherche et un type de carburant spécifique. Elle gère l'extraction 
 * complète des coordonnées GPS, horaires, fermetures, services et ruptures.
 *
 * @global string $xmlLocalPath     Chemin vers le fichier XML contenant les données des stations.
 *
 * @param string $code_postal       Code postal de référence pour la recherche (ex: "28000").
 * @param string $perimetre         Étendue de la recherche géographique. Valeurs possibles : 
 *                                  - 'ville' (défaut) : correspondance exacte (5 chiffres).
 *                                  - 'departement' : correspondance sur les 2 premiers chiffres.
 *                                  - 'environs' : correspondance sur les 3 premiers chiffres.
 * @param string $carburant_choisi  Filtre par type de carburant (ex: "Gazole", "SP95-E10"). 
 *                                  Valeur 'Tous' (défaut) pour désactiver le filtre.
 *
 * @return array[] Tableau multidimensionnel des stations trouvées. 
 *                 Retourne un tableau vide si le XML est introuvable ou invalide.
 *                 Structure d'une station (tableau associatif) :
 *                 - adresse     (string)  : Adresse physique de la station.
 *                 - ville       (string)  : Nom de la ville.
 *                 - cp          (string)  : Code postal.
 *                 - automate_24 (bool)    : true si automate 24/24h présent (vérifié via attribut ou texte service).
 *                 - latitude    (float)   : Latitude GPS (convertie depuis le format brut).
 *                 - longitude   (float)   : Longitude GPS (convertie depuis le format brut).
 *                 - type_route  (string)  : Type de voie (ex: "A", "R") classique ou autoroute.
 *                 - fermeture   (?array)  : null ou tableau ['type' => string, 'debut' => string], le type est temporaire ou permanente ou definitif.
 *                 - horaires    (array)   : Clé = jour, Valeur = '06h00 - 20h00' ou 'Fermé'.
 *                 - maj         (?string) : Date/heure de la dernière mise à jour du prix on prend la date du premier carburant du tableau.
 *                 - services    (array)   : Liste des noms des services proposés (strings).
 *                 - ruptures    (array)   : Liste des ruptures ['nom' => string, 'type' => string] le type est toujours soit temporaire ou permanant je prend la valeur que si c'est tomporaire.
 *                 - carburants  (array)   : Liste des carburants disponible ['nom' => string, 'valeur' => float].
 */
function rechercherStations(string $code_postal, string $perimetre = 'ville', string $carburant_choisi = 'Tous'): array {
    global $xmlLocalPath;
    $stations_trouvees = [];

    if (!file_exists($xmlLocalPath)) return $stations_trouvees;
    $xml = simplexml_load_file($xmlLocalPath);
    if ($xml === false) return $stations_trouvees;

    if ($perimetre === 'departement') {
        $prefixe_recherche = substr($code_postal, 0, 2);
    } elseif ($perimetre === 'environs') {
        $prefixe_recherche = substr($code_postal, 0, 3);
    } else {
        $prefixe_recherche = $code_postal;
    }

    foreach ($xml->pdv as $pdv) {
        $cp = (string) $pdv['cp'];
        
        if (strpos($cp, $prefixe_recherche) === 0) {
            $station = [
                'adresse' => (string) $pdv->adresse,
                'ville' => (string) $pdv->ville,
                'cp' => $cp,
                'automate_24' => (string)$pdv['automate-24-24'] === '1',
                
                // NOUVEAU 1 : Coordonnées GPS (divisées par 100 000 comme dit la doc)
                'latitude' => (float)$pdv['latitude'] / 100000,
                'longitude' => (float)$pdv['longitude'] / 100000,
                
                // NOUVEAU 2 : Type de route (Autoroute ou Route)
                'type_route' => (string)$pdv['pop'],
                
                // NOUVEAU 3 & 4 : Initialisation pour Fermetures et Horaires
                'fermeture' => null,
                'horaires' => [],
                
                'maj' => null,
                'services' => [],
                'ruptures' => [],
                'carburants' => []
            ];

            // Extraction de la fermeture totale de la station
            if (isset($pdv->fermeture)) {
                $station['fermeture'] = [
                    'type' => (string)$pdv->fermeture['type'],
                    'debut' => (string)$pdv->fermeture['debut']
                ];
            }

            // Extraction des vrais horaires du guichet
            if (isset($pdv->horaires) && isset($pdv->horaires->jour)) {
                foreach ($pdv->horaires->jour as $jour) {
                    $nom_jour = (string)$jour['nom'];
                    $est_ferme = (string)$jour['ferme'] === '1';
                    
                    if ($est_ferme) {
                        $station['horaires'][$nom_jour] = 'Fermé';
                    } elseif (isset($jour->horaire)) {
                        $ouv = (string)$jour->horaire['ouverture'];
                        $ferm = (string)$jour->horaire['fermeture'];
                        // On remplace le point par un "h" (ex: 06.00 devient 06h00)
                        $station['horaires'][$nom_jour] = str_replace('.', 'h', $ouv) . ' - ' . str_replace('.', 'h', $ferm);
                    }
                }
            }

            $possede_le_carburant = false;
            $maj = null;

            foreach ($pdv->prix as $prix) {
                $nom_carb = (string) $prix['nom'];
                $station['carburants'][] = [
                    'nom' => $nom_carb,
                    'valeur' => (float) $prix['valeur']
                ];
                
                if ($carburant_choisi === 'Tous' || $nom_carb === $carburant_choisi) {
                    $possede_le_carburant = true;
                }

                if ($maj === null && isset($prix['maj'])) {
                    $maj = (string) $prix['maj'];
                }
            }

            if (!$possede_le_carburant) continue; 

            $station['maj'] = $maj;

            if (isset($pdv->services)) {
                foreach ($pdv->services->service as $service) {
                    $nom_service = (string) $service;
                    $station['services'][] = $nom_service;
                    
                    // NOUVEAU : Si le gouvernement a oublié l'attribut mais l'a mis dans le texte
                    if (strpos($nom_service, '24/24') !== false) {
                        $station['automate_24'] = true;
                    }
                }
            }

            if (isset($pdv->ruptures)) {
                foreach ($pdv->ruptures->rupture as $rupture) {
                    $station['ruptures'][] = [
                        'nom' => (string) $rupture['nom'],
                        'type' => (string) $rupture['type']
                    ];
                }
            }

            if (!empty($station['carburants'])) {
                $stations_trouvees[] = $station;
            }
        }
    }
    return $stations_trouvees;
}

/**
 * Génère le code HTML complet pour afficher la grille des stations-service.
 * 
 * Construit l'interface des cartes (articles) incluant les en-têtes, les badges 
 * (Autoroute/Route), les liens d'itinéraire GPS (Google Maps), les alertes 
 * de fermeture totale, les prix principaux, et un volet dépliant pour les détails.
 * 
 * @param array $stations Tableau multidimensionnel des stations (format strict 
 *                        généré par la fonction rechercherStations).
 * 
 * @return string Le flux HTML complet et sécurisé (via htmlspecialchars) prêt 
 *                à être injecté dans la page. Retourne un paragraphe d'erreur si vide.
 */
function construireCartesHtml(array $stations, int $page = 1, string $paginationBaseUrl = ''): string {
    if (empty($stations)) {
        return '<p class="message-erreur">Aucune station trouvée pour cette zone.</p>';
    }

    $stationsParPage = 10;
    $totalStations = count($stations);
    $totalPages = ceil($totalStations / $stationsParPage);
    
    if ($page < 1) $page = 1;
    if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
    
    $debut = ($page - 1) * $stationsParPage;
    $stationsPage = array_slice($stations, $debut, $stationsParPage);

    $carburants_principaux = ['Gazole', 'E10', 'SP95', 'SP98'];
    $html = '<div class="stations-grid">';
    $index = $debut;

    foreach ($stationsPage as $station) {
        $principaux = array_filter($station['carburants'], function($c) use ($carburants_principaux) {
            return in_array($c['nom'], $carburants_principaux);
        });

        $html .= '<article class="station-card" id="station-' . $index . '">';
        $html .= '<header>';
        
        // Entête avec le titre ET les nouveaux badges/boutons alignés
        $html .= '<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">';
        $html .= '<h3>' . htmlspecialchars($station['ville']) . ' (' . htmlspecialchars($station['cp']) . ')</h3>';
        
        $html .= '<div>';
        // NOUVEAU 2 : Badge Autoroute ou Route
        if ($station['type_route'] === 'A') {
            $html .= '<span style="background-color: #0055A4; color: white; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; margin-right: 10px;">🛣️ Station Autoroute</span>';
        } elseif ($station['type_route'] === 'R') {
            // Badge gris et discret pour les routes classiques
            $html .= '<span style="background-color: #6c757d; color: white; padding: 4px 10px; border-radius: 4px; font-size: 0.8em; font-weight: bold; margin-right: 10px;">🛣️ Route classique</span>';
        }
        // NOUVEAU 1 : Bouton GPS Waze/Google Maps
        $html .= '<a href="https://www.google.com/maps/dir/?api=1&destination=' . $station['latitude'] . ',' . $station['longitude'] . '" target="_blank" style="text-decoration: none; background: #e9ecef; padding: 5px 12px; border-radius: 20px; font-size: 0.9em; color: #333; font-weight: 500; border: 1px solid #ccc; display: inline-block;">🚗 Y aller</a>';
        $html .= '</div>';
        $html .= '</div>'; // Fin du flex
        
        $html .= '<p class="adresse">' . htmlspecialchars($station['adresse']) . '</p>';
        
        // NOUVEAU 3 : Alerte de fermeture Totale
        if (!empty($station['fermeture'])) {
            $html .= '<div style="background-color: #dc3545; color: white; padding: 15px; border-radius: 8px; font-weight: bold; margin-bottom: 15px; text-align: center;">';
            $html .= '⛔ STATION FERMÉE (' . ucfirst(htmlspecialchars($station['fermeture']['type'])) . ')';
            $html .= '</div>';
        } else {
            // Affichage normal des prix seulement si la station n'est pas fermée
            if (!empty($principaux)) {
                $html .= '<div class="carburants-principaux" style="margin-bottom: 15px;">';
                foreach ($principaux as $carburant) {
                    $html .= '<span class="prix-carburant" style="margin-right: 15px; font-weight: 500;">' . htmlspecialchars($carburant['nom']) . ' : ' . number_format($carburant['valeur'], 3, ',', ' ') . ' €</span>';
                }
                $html .= '</div>';
            }

        if ($station['automate_24']) {
            $html .= '<span class="badge-24h" style="background-color: #28a745; color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.85em; font-weight: bold; margin-bottom: 15px; display: inline-block;">💳 Automate CB 24h/24</span><br>';
        }
        }
        
        $html .= '<button class="btn-details" data-target="details-' . $index . '" onclick="toggleDetails(' . $index . ')">Voir tous les détails</button>';
        $html .= '</header>';
        
        $html .= '<div class="station-details" id="details-' . $index . '" hidden>';
        $html .= '<h4>Tous les carburants</h4><ul>';
        foreach ($station['carburants'] as $carburant) {
            $html .= '<li>' . htmlspecialchars($carburant['nom']) . ' : ' . number_format($carburant['valeur'], 3, ',', ' ') . ' €</li>';
        }
        $html .= '</ul>';
        
        // NOUVEAU 4 : Affichage des horaires du guichet
        if (!empty($station['horaires'])) {
            $html .= '<h4>Horaires du guichet</h4><ul style="list-style-type: none; padding-left: 0;">';
            foreach ($station['horaires'] as $jour => $horaire) {
                $html .= '<li style="margin-bottom: 5px;"><strong>' . htmlspecialchars($jour) . ' :</strong> ' . htmlspecialchars($horaire) . '</li>';
            }
            $html .= '</ul>';
        }
        
        if ($station['maj']) {
            $maj_timestamp = strtotime($station['maj']);
            if ($maj_timestamp !== false) {
                $html .= '<p class="maj">Prix mis à jour le ' . date('d/m/Y à H\hi', $maj_timestamp) . '</p>';
            }
        }
        if (!empty($station['services'])) {
            $html .= '<h4>Services</h4><ul class="services-list">';
            foreach ($station['services'] as $service) {
                $html .= '<li>' . htmlspecialchars($service) . '</li>';
            }
            $html .= '</ul>';
        }
        if (!empty($station['ruptures'])) {
            $html .= '<h4>Ruptures de stock</h4>';
            foreach ($station['ruptures'] as $rupture) {
                if ($rupture['type'] === 'temporaire') {
                    $html .= '<p class="rupture-alerte">' . htmlspecialchars($rupture['nom']) . ' : Rupture temporaire</p>';
                }
            }
        }
        $html .= '</div></article>';
        $index++;
    }
    $html .= '</div>';
    
    if ($totalPages > 1 && !empty($paginationBaseUrl)) {
        $html .= '<div class="pagination" style="margin-top: 30px; display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">';
        
        if ($page > 1) {
            $html .= '<a href="' . $paginationBaseUrl . '&page=' . ($page - 1) . '" class="btn-pagination" style="padding: 8px 16px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">← Précédent</a>';
        }
        
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == $page) {
                $html .= '<span class="btn-pagination" style="padding: 8px 16px; background: #0056b3; color: white; border-radius: 5px;">' . $i . '</span>';
            } else {
                $html .= '<a href="' . $paginationBaseUrl . '&page=' . $i . '" class="btn-pagination" style="padding: 8px 16px; background: #e9ecef; color: #333; text-decoration: none; border-radius: 5px;">' . $i . '</a>';
            }
        }
        
        if ($page < $totalPages) {
            $html .= '<a href="' . $paginationBaseUrl . '&page=' . ($page + 1) . '" class="btn-pagination" style="padding: 8px 16px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">Suivant →</a>';
        }
        
        $html .= '</div>';
        $html .= '<p style="text-align: center; margin-top: 15px; color: #666;">Station ' . ($debut + 1) . ' à ' . min($debut + $stationsParPage, $totalStations) . ' sur ' . $totalStations . '</p>';
    }
    
    return $html;
}

/**
 * @brief Extrait une valeur de référence pour le tri des stations selon le carburant.
 * @param array $station La station contenant la liste des carburants.
 * @param string $carburant_choisi Le nom du carburant ciblé, ou 'Tous'.
 * @return float La valeur de tri (prix exact, moyenne, ou 999.0 si invalide).
 */
function getPrixCarburant(array $station, string $carburant_choisi): float {
    // Sécurité : si la station n'a pas de carburants, on l'envoie à la fin
    if (empty($station['carburants'])) {
        return 999.0;
    }

    // Création d'un tableau associatif ['Nom du carburant' => Prix]
    // Ça remplace toutes tes boucles foreach d'un seul coup !
    $prix_carburants = array_column($station['carburants'], 'valeur', 'nom');

    // 1. Cas où un carburant précis est demandé
    if ($carburant_choisi !== 'Tous') {
        // S'il existe on retourne son prix, sinon on retourne 999.0 (opérateur null coalescing)
        return $prix_carburants[$carburant_choisi] ?? 999.0;
    }

    // 2. Cas "Tous" : On utilise la moyenne des prix de cette station
    $total_prix = array_sum($prix_carburants);
    $nombre_carburants = count($prix_carburants);

    return $total_prix / $nombre_carburants;
}

/**
 * Trie un tableau de stations selon un critère spécifique (prix ou ordre alphabétique).
 * 
 * @param array  $stations         Le tableau contenant les données des stations à trier.
 * @param string $tri              Le critère de tri souhaité ('az', 'za', 'prix_asc', 'prix_desc').
 * @param string $carburant_choisi Le nom du carburant ciblé pour le tri par prix (ou 'Tous').
 * 
 * @return array Le tableau des stations réorganisé selon le critère demandé.
 */
function trierStations(array $stations, string $tri, string $carburant_choisi): array {
    usort($stations, function($a, $b) use ($tri, $carburant_choisi) {
        switch ($tri) {
            case 'az':
                return strcasecmp($a['ville'], $b['ville']);
            case 'za':
                return strcasecmp($b['ville'], $a['ville']);
            case 'prix_desc':
                return getPrixCarburant($b, $carburant_choisi) <=> getPrixCarburant($a, $carburant_choisi);
            case 'prix_asc':
            default:
                // Tri par prix croissant par défaut
                return getPrixCarburant($a, $carburant_choisi) <=> getPrixCarburant($b, $carburant_choisi);
        }
    });

    return $stations;
}

/**
 * Fonction d'orchestration générant l'interface HTML des stations pour une zone donnée.
 * 
 * Cette fonction gère le cycle de vie complet de l'affichage : 
 * mise à jour du cache, recherche des stations, application du tri, 
 * journalisation de la consultation (CSV) et construction du rendu HTML final.
 * 
 * @param string $code_postal      Le code postal ciblé (par défaut : '95000').
 * @param string $perimetre        Le périmètre de recherche géographique (par défaut : 'ville').
 * @param string $carburant_choisi Le filtre de carburant appliqué (par défaut : 'Tous').
 * @param string $tri              Le critère de tri appliqué à la liste (par défaut : 'prix_asc').
 * 
 * @return string Le code HTML complet incluant l'en-tête, la barre de tri et les cartes des stations.
 */
function genererHtmlStations(string $code_postal = '95000', string $perimetre = 'ville', string $carburant_choisi = 'Tous', string $tri = 'prix_asc', ?string $index = null, int $page = 1): string {
    global $lang, $style;
    
    refreshCacheSiNecessaire();
    $stations = rechercherStations($code_postal, $perimetre, $carburant_choisi);
    
    // 1. On délègue le tri à notre nouvelle sous-fonction !
    if (!empty($stations)) {
        $stations = trierStations($stations, $tri, $carburant_choisi);
    }
    
    // 2. Gestion de l'historique
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ville_trouvee = !empty($stations) ? $stations[0]['ville'] : "Inconnue (CP: $code_postal)";
    enregistrerConsultationCsv($ville_trouvee, $user_ip);
    
    // 3. Génération de l'interface HTML
    $baseUrl = 'stations.php?afficher=prix&code_postal=' . urlencode($code_postal) . '&perimetre=' . urlencode($perimetre) . '&carburant=' . urlencode($carburant_choisi);
    if ($index !== null) {
        $baseUrl .= '&index=' . urlencode($index);
    }
    if (!empty($lang)) {
        $baseUrl .= '&lang=' . $lang;
    }
    if (!empty($style)) {
        $baseUrl .= '&style=' . $style;
    }
    
    $html = '<div class="stations-header">';
    $html .= '<h2>Stations de carburant à proximité du ' . htmlspecialchars($code_postal) . '</h2>';
    
    // Barre de tri (Toolbar)
    if (!empty($stations)) {
        $html .= '<div class="stations-toolbar" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">';
        $html .= '<span style="font-weight: bold;">Trier par :</span>';
        $html .= '<a href="' . $baseUrl . '&tri=prix_asc#exo-2" class="btn-tri" style="' . ($tri === 'prix_asc' ? 'background: #007bff; color: white;' : 'background: #e9ecef; color: black;') . ' text-decoration: none; padding: 6px 12px; border-radius: 20px; font-size: 0.9em;">Prix ↗</a>';
        $html .= '<a href="' . $baseUrl . '&tri=prix_desc#exo-2" class="btn-tri" style="' . ($tri === 'prix_desc' ? 'background: #007bff; color: white;' : 'background: #e9ecef; color: black;') . ' text-decoration: none; padding: 6px 12px; border-radius: 20px; font-size: 0.9em;">Prix ↘</a>';
        $html .= '<a href="' . $baseUrl . '&tri=az#exo-2" class="btn-tri" style="' . ($tri === 'az' ? 'background: #007bff; color: white;' : 'background: #e9ecef; color: black;') . ' text-decoration: none; padding: 6px 12px; border-radius: 20px; font-size: 0.9em;">Ville A-Z</a>';
        $html .= '<a href="' . $baseUrl . '&tri=za#exo-2" class="btn-tri" style="' . ($tri === 'za' ? 'background: #007bff; color: white;' : 'background: #e9ecef; color: black;') . ' text-decoration: none; padding: 6px 12px; border-radius: 20px; font-size: 0.9em;">Ville Z-A</a>';
        $html .= '</div>';
    }
    $html .= '</div>';
    
    // 4. On ajoute les cartes
    $html .= construireCartesHtml($stations, $page, $baseUrl);
    
    // 5. Script JS pour les détails
    $html .= '<script>
    function toggleDetails(id) {
        const detailsDiv = document.getElementById("details-" + id);
        const button = document.querySelector("button[data-target=\"details-" + id + "\"]");
        detailsDiv.hidden = !detailsDiv.hidden;
        button.textContent = detailsDiv.hidden ? "Voir tous les détails" : "Masquer les détails";
    }
    </script>';
    
    return $html;
}

/**
 * @brief Historique CSV (Les Logs)
 * Enregistre chaque recherche dans un fichier CSV avec la date, l'heure et l'IP
 * @param string $ville Ville recherchée
 * @param string $ip Adresse IP de l'utilisateur
 * @note ou pourrait ne pas demander l'adress ip et utiliser REMOTE_ADDR
 */
function enregistrerConsultationCsv(string $ville, string $ip = ''): void {
    $fichierCsv = dirname(__DIR__) . '/données/historique_recherches.csv';
    $fichierExiste = file_exists($fichierCsv);
    
    $fichier = fopen($fichierCsv, 'a');
    
    if ($fichier !== false) {
        if (!$fichierExiste) {
            fputcsv($fichier, ['Date', 'Heure', 'Ville_Consultee', 'Adresse_IP']);
        }
        
        $date = date('d/m/Y');
        $heure = date('H:i:s');
        $villePropre = ucfirst(strtolower($ville));
        
        $ligne = [$date, $heure, $villePropre, $ip];
        fputcsv($fichier, $ligne, ',', '"', "\\");
        
        fclose($fichier);
    }
}

/**
 * @brief Retourne le nombre total de visiteurs (nombre de recherches dans le CSV)
 * @return int Nombre total de recherches enregistrées
 */
function getNombreTotalVisiteurs(): int {
    $fichierCsv = dirname(__DIR__) . '/données/historique_recherches.csv';
    
    if (!file_exists($fichierCsv)) {
        return 0;
    }
    
    $fichier = fopen($fichierCsv, 'r');
    if ($fichier === false) {
        return 0;
    }
    
    $count = 0;
    while (fgetcsv($fichier, separator: ',', escape: '\\') !== false) {
        $count++;
    }
    fclose($fichier);
    
    return $count > 0 ? $count - 1 : 0;
}

/**
 * @brief Retourne les villes les plus consultées triées par ordre décroissant
 * @param int $limite Nombre de villes à retourner (par défaut 10)
 * @return array Tableau associatif ['Ville' => nombre de consultations]
 */
function getTopVilles(int $limite = 10): array {
    $fichierCsv = dirname(__DIR__) . '/données/historique_recherches.csv';
    
    if (!file_exists($fichierCsv)) {
        return [];
    }
    
    $fichier = fopen($fichierCsv, 'r');
    if ($fichier === false) {
        return [];
    }
    
    $villes = [];
    fgetcsv($fichier, separator: ',', escape: '\\');
    
    while (($ligne = fgetcsv($fichier, separator: ',', escape: '\\')) !== false) {
        if (isset($ligne[2]) && !empty($ligne[2])) {
            $ville = trim($ligne[2]);
            if (!isset($villes[$ville])) {
                $villes[$ville] = 0;
            }
            $villes[$ville]++;
        }
    }
    fclose($fichier);
    
    arsort($villes);
    return array_slice($villes, 0, $limite, true);
}
?>