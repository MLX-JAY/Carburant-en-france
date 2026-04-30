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
        echo '<a href="?dep=' . $dept['id'] . '&afficher=villes&lang=' . $lang . '&style=' . $style . '&index=' . $index . '#form-villes" class="bouton-departement">Voir les villes</a>';
        echo '<a href="?dep=' . $dept['id'] . '&afficher=stations&lang=' . $lang . '&style=' . $style . '&index=' . $index . '#stations-section" class="bouton-departement">Voir les stations</a>';
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
 * @brief Normalise une chaîne pour supprimer les accents
 * @param string $chaine La chaîne à normaliser
 * @return string Chaîne sans accents
 */
function normaliserChaine(string $chaine): string {
    $chaine = strtolower($chaine);
    $chaine = str_replace(['é', 'è', 'ê', 'ë'], 'e', $chaine);
    $chaine = str_replace(['à', 'â', 'ä'], 'a', $chaine);
    $chaine = str_replace(['î', 'ï'], 'i', $chaine);
    $chaine = str_replace(['ô', 'ö'], 'o', $chaine);
    $chaine = str_replace(['ù', 'û', 'ü'], 'u', $chaine);
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
        
        return [
            'ville' => $data['city_name'] ?? '',
            'region' => $data['region_name'] ?? '',
            'pays' => $data['country_name'] ?? '',
            'ip' => $user_ip
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
 * @brief Recherche (Le détective)
 * Filtre les stations par code postal et extrait les données complètes
 * @param string $code_postal Code postal pour la recherche (ex: "28000")
 * @return array Tableau des stations trouvées avec toutes les informations
 */
function rechercherStations(string $code_postal): array {
    global $xmlLocalPath;

    $prefixe_recherche = substr($code_postal, 0, 3);
    $stations_trouvees = [];

    if (!file_exists($xmlLocalPath)) {
        return $stations_trouvees;
    }

    $xml = simplexml_load_file($xmlLocalPath);
    if ($xml === false) {
        return $stations_trouvees;
    }

    foreach ($xml->pdv as $pdv) {
        $cp = (string) $pdv['cp'];
        if (strpos($cp, $prefixe_recherche) === 0) {
            $station = [
                'adresse' => (string) $pdv->adresse,
                'ville' => (string) $pdv->ville,
                'cp' => $cp,
                'automate_24' => (string)$pdv['automate-24-24'] === '1',
                'maj' => null,
                'services' => [],
                'ruptures' => [],
                'carburants' => []
            ];

            $maj = null;
            foreach ($pdv->prix as $prix) {
                $station['carburants'][] = [
                    'nom' => (string) $prix['nom'],
                    'valeur' => (float) $prix['valeur']
                ];
                if ($maj === null && isset($prix['maj'])) {
                    $maj = (string) $prix['maj'];
                }
            }
            $station['maj'] = $maj;

            if (isset($pdv->services)) {
                foreach ($pdv->services->service as $service) {
                    $station['services'][] = (string) $service;
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
 * @brief Génère le HTML des cartes de stations de carburant avec détails interactifs
 * @param array $stations Tableau des stations à afficher
 * @return string Code HTML des cartes avec toggle détails
 */
function construireCartesHtml(array $stations): string {
    if (empty($stations)) {
        return '<p class="message-erreur">Aucune station trouvée pour cette zone.</p>';
    }

    $carburants_principaux = ['Gazole', 'E10', 'SP95', 'SP98'];

    $html = '<div class="stations-grid">';
    $index = 0;

    foreach ($stations as $station) {
        $principaux = array_filter($station['carburants'], function($c) use ($carburants_principaux) {
            return in_array($c['nom'], $carburants_principaux);
        });

        $html .= '<article class="station-card" id="station-' . $index . '">';
        $html .= '<header>';
        $html .= '<h3>' . htmlspecialchars($station['ville']) . ' (' . htmlspecialchars($station['cp']) . ')</h3>';
        $html .= '<p class="adresse">' . htmlspecialchars($station['adresse']) . '</p>';
        
        if (!empty($principaux)) {
            $html .= '<div class="carburants-principaux">';
            foreach ($principaux as $carburant) {
                $html .= '<span class="prix-carburant">' . htmlspecialchars($carburant['nom']) . ' : ' . number_format($carburant['valeur'], 3, ',', ' ') . ' €</span>';
            }
            $html .= '</div>';
        }
        
        if ($station['automate_24']) {
            $html .= '<span class="badge-24h">Ouvert 24h/24</span>';
        }
        
        $html .= '<button class="btn-details" data-target="details-' . $index . '" onclick="toggleDetails(' . $index . ')">Voir tous les détails</button>';
        $html .= '</header>';
        
        $html .= '<div class="station-details" id="details-' . $index . '" hidden>';
        
        $html .= '<h4>Tous les carburants</h4><ul>';
        foreach ($station['carburants'] as $carburant) {
            $html .= '<li>' . htmlspecialchars($carburant['nom']) . ' : ' . number_format($carburant['valeur'], 3, ',', ' ') . ' €</li>';
        }
        $html .= '</ul>';
        
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

        $html .= '</div>';
        $html .= '</article>';
        $index++;
    }

    $html .= '</div>';
    return $html;
}

/**
 * @brief Orchestration (Le chef d'orchestre)
 * Appelle les autres fonctions dans l'ordre et retourne le HTML final avec JavaScript
 * @param string $code_postal Code postal pour la recherche
 * @return string Code HTML prêt à être affiché avec toggle détails
 */
function genererHtmlStations(string $code_postal = '95000'): string {
    refreshCacheSiNecessaire();
    $stations = rechercherStations($code_postal);
    $html = '<h2>Stations de carburant à proximité du ' . htmlspecialchars($code_postal) . '</h2>';
    $html .= construireCartesHtml($stations);
    
    $html .= '<script>
    function toggleDetails(id) {
        const detailsDiv = document.getElementById("details-" + id);
        const button = document.querySelector("button[data-target=\"details-" + id + "\"]");
        
        detailsDiv.hidden = !detailsDiv.hidden;
        
        if (detailsDiv.hidden) {
            button.textContent = "Voir tous les détails";
        } else {
            button.textContent = "Masquer les détails";
        }
    }
    </script>';
    
    return $html;
}

/**
 * @brief Affiche les stations de carburant d'un département avec pagination et tri.
 * 
 * Cette fonction orchestre l'affichage des stations pour un département donne :
 * - Recherche les stations par prefixe de code postal
 * - Applique le tri par prix si demande
 * - Gere la pagination (20 stations par page)
 * - Genere le HTML complet avec en-tete et pagination
 * 
 * @param string $depCode Code du departement (ex: "28")
 * @param int $page Numero de page (defaut: 1)
 * @param string $tri Type de tri ('prix' pour tri croissant par prix)
 * 
 * @return string Code HTML des stations avec pagination et outils de tri.
 * 
 * @note Affiche 20 stations par page
 * @note Le tri s'applique sur le premier carburant disponible (Gazole le plus souvent)
 * 
 * @example
 * @code
 * echo afficherStationsParDepartement("28", 1, "prix"); // Affiche les stations du 28 triees par prix
 * @endcode
 */
function afficherStationsParDepartement(string $depCode, int $page = 1, string $tri = '', ?int $index = null): string {
    // Normaliser le code departement
    if (strlen($depCode) == 1 && is_numeric($depCode)) {
        $depCode = str_pad($depCode, 2, '0', STR_PAD_LEFT);
    }
    
    // Rechercher les stations (utilise le prefixe 3 chiffres pour le departement)
    $prefixe = $depCode; // Ex: 280 pour le departement 28
    refreshCacheSiNecessaire();
    $stations = rechercherStations($prefixe);
    
    if (empty($stations)) {
        return '<p class="message-erreur">Aucune station trouvee pour ce departement.</p>';
    }
    
    // Tri par prix si demande
    if ($tri === 'prix') {
        usort($stations, function($a, $b) {
            $prixA = $a['carburants'][0]['valeur'] ?? 999;
            $prixB = $b['carburants'][0]['valeur'] ?? 999;
            return $prixA <=> $prixB;
        });
    }
    
    // Tri alphabetique si demande
    if ($tri === 'az') {
        usort($stations, function($a, $b) {
            return strcasecmp($a['ville'], $b['ville']);
        });
    }
    
    // Pagination
    $stationsParPage = 10;
    $totalStations = count($stations);
    $totalPages = max(1, ceil($totalStations / $stationsParPage));
    $page = max(1, min($page, $totalPages));
    $debut = ($page - 1) * $stationsParPage;
    $stationsPage = array_slice($stations, $debut, $stationsParPage);
    
    // Obtenir le nom du departement
    $nomDepartement = '';
    foreach ($GLOBALS['regionsDepartements'] as $region) {
        foreach ($region as $dept) {
            if ($dept['id'] === $depCode || $dept['id'] === (int)$depCode) {
                $nomDepartement = $dept['nom'];
                break 2;
            }
        }
    }
    
    // Generation du HTML
    $html = '<div class="stations-section" id="stations-section">';
    
    // En-tete
    $html .= '<div class="stations-header">';
    $html .= '<h2>Stations essence - ' . htmlspecialchars($nomDepartement) . ' (' . $depCode . ')</h2>';
    $html .= '<p class="stations-compteur">' . $totalStations . ' station(s) trouvee(s)</p>';
    $html .= '<div class="stations-toolbar">';
    $triActif = $tri === 'prix' ? 'active' : '';
    $triAzActif = $tri === 'az' ? 'active' : '';
    $indexParam = $index !== null ? '&index=' . $index : '';
    $html .= '<a href="?dep=' . $depCode . '&afficher=stations&tri=prix' . $indexParam . '#stations-section" class="btn-tri ' . $triActif . '">Trier par prix ↗</a>';
    $html .= '<a href="?dep=' . $depCode . '&afficher=stations&tri=az' . $indexParam . '#stations-section" class="btn-tri ' . $triAzActif . '">Trier A-Z ↗</a>';
    $html .= '</div>';
    $html .= '</div>';
    
    // Grille des stations
    $html .= '<div class="stations-grid">';
    foreach ($stationsPage as $station) {
        $html .= '<article class="station-card">';
        
        // Header avec ville et code postal
        $html .= '<div class="station-header">';
        $html .= '<h3>' . htmlspecialchars($station['ville']) . ' <span class="cp">(' . htmlspecialchars($station['cp']) . ')</span></h3>';
        $html .= '</div>';
        
        // Body avec adresse
        $html .= '<div class="station-body">';
        $html .= '<p class="station-adresse">' . htmlspecialchars($station['adresse']) . '</p>';
        $html .= '</div>';
        
        // Prix des carburants
        $html .= '<div class="station-prix">';
        foreach ($station['carburants'] as $carburant) {
            $html .= '<div class="carburant-prix">';
            $html .= '<span class="nom-carburant">' . htmlspecialchars($carburant['nom']) . '</span>';
            $html .= '<span class="valeur-prix">' . number_format($carburant['valeur'], 3, ',', ' ') . ' €</span>';
            $html .= '</div>';
        }
        $html .= '</div>';
        
        $html .= '</article>';
    }
    $html .= '</div>';
    
    // Pagination
    if ($totalPages > 1) {
        $html .= '<div class="stations-pagination">';
        
        $baseUrl = '?dep=' . $depCode . '&afficher=stations';
        if ($tri) {
            $baseUrl .= '&tri=' . $tri;
        }
        if ($index !== null) {
            $baseUrl .= '&index=' . $index;
        }
        
        if ($page > 1) {
            $html .= '<a href="' . $baseUrl . '&page=' . ($page - 1) . '#stations-section" class="btn-page">← Preccedent</a>';
        }
        
        $html .= '<span class="page-info">Page ' . $page . ' / ' . $totalPages . '</span>';
        
        if ($page < $totalPages) {
            $html .= '<a href="' . $baseUrl . '&page=' . ($page + 1) . '#stations-section" class="btn-page">Suivant →</a>';
        }
        
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * @brief Historique CSV (Les Logs)
 * Enregistre chaque recherche dans un fichier CSV avec la date, l'heure et l'IP
 * @param string $ville Ville recherchée
 * @param string $ip Adresse IP de l'utilisateur
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
        fputcsv($fichier, $ligne);
        
        fclose($fichier);
    }
}
?>