<?php

/**
 * @file fonction.inc.php
 * @brief Fonctions utilitaires pour la gestion des régions, départements et villes de France.
 * 
 * Ce fichier contient les fonctions permettant de gérer l'affichage des régions et départements,
 * ainsi que la récupération des villes à partir d'un fichier CSV optimisé par indexation.
 * 
 * @author ANURAJAN Thenuxshan, FERAOUN Mohamed Amine
 * @version 1.0
 * @date 2026
 */

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
        echo '<a href="?dep=' . $dept['id'] . '&lang=' . $lang . '&style=' . $style . '&index=' . $index . '#form-villes" class="bouton-departement">Voir les villes</a>';
        echo '</article>';
    }
    
    echo '</div>';
}

/**
 * @brief Récupère la liste des villes d'un département à partir du fichier CSV.
 * 
 * Cette fonction lit séquentiellement le fichier CSV des communes de France
 * et retourne toutes les villes du département spécifié.
 * 
 * @warning Cette fonction est lente pour les fichiers volumineux.
 *          Utiliser getVillesByDepartementFast() pour de meilleures performances.
 * 
 * @param string $depCode Code du département (ex: "28", "02", "59").
 *                         Peut être au format string ou int.
 * 
 * @return array<string> Tableau trié alphabétiquement des noms de villes.
 *                       Retourne un tableau vide si le département n'existe pas
 *                       ou si le fichier CSV n'est pas trouvé.
 * 
 * @since Version 1.0
 * @see getVillesByDepartementFast() Pour une version optimisée avec indexation.
 * 
 * @note Le code département est normalisé (ajout du zéro initial si nécessaire).
 *       Par exemple, "2" devient "02" pour l'Aisne.
 * 
 * @example
 * @code
 * $villes = getVillesByDepartement("28"); // Retourne les villes de l'Eure-et-Loir
 * @endcode
 */
function getVillesByDepartement(string $depCode): array {
    $villes = [];
    $baseDir = dirname(__DIR__);
    $csvPath = $baseDir . '/données/communes-france-2025.csv';
    
    if (!file_exists($csvPath)) {
        return $villes;
    }
    
    if (strlen($depCode) == 1 && is_numeric($depCode)) {
        $depCode = str_pad($depCode, 2, '0', STR_PAD_LEFT);
    }
    
    if (($handle = fopen($csvPath, "r")) !== FALSE) {
        $header = fgetcsv($handle, 1000, ",", '"', "\\");
        $headerIndices = array_flip($header);
        $depIndex = $headerIndices['dep_code'] ?? 12;
        $nomIndex = $headerIndices['nom_standard'] ?? 2;
        
        while (($data = fgetcsv($handle, 1000, ",", '"', "\\")) !== FALSE) {
            $csvDepCode = isset($data[$depIndex]) ? trim($data[$depIndex]) : '';
            if ($csvDepCode == $depCode) {
                $villes[] = $data[$nomIndex];
            }
        }
        fclose($handle);
    }
    sort($villes);
    return $villes;
}

/**
 * @brief Génère le fichier d'index des positions de départ de chaque département.
 * 
 * Cette fonction parcourt le fichier CSV une seule fois et crée un fichier JSON
 * contenant la position de départ de chaque département dans le fichier.
 * Ce fichier d'index permet ensuite un accès direct aux données avec fseek().
 * 
 * @return bool true si l'index a été généré avec succès, false en cas d'erreur.
 * 
 * @note Le fichier d'index est enregistré dans données/index_villes.json
 * @note Cette fonction n'est normalement pas appelée directement.
 *       Elle est invoked automatiquement par getVillesByDepartementFast().
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
    $csvPath = $baseDir . '/données/communes-france-2025.csv';
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
    
    while (($data = fgetcsv($handle, 1000, ",", '"', "\\")) !== FALSE) {
        if (isset($data[12])) {
            $depCode = trim($data[12]);
            $position = ftell($handle);
            
            if (!isset($index[$depCode])) {
                $index[$depCode] = $position;
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
 * directement à la position du département dans le fichier CSV, sans lire
 * toutes les lignes précédentes. C'est beaucoup plus rapide que la version
 * séquentielle pour les fichiers volumineux.
 * 
 * Si le fichier d'index n'existe pas, il est généré automatiquement lors
 * du premier appel.
 * 
 * @param string $depCode Code du département (ex: "28", "02", "59").
 *                         Peut être au format string ou int.
 * 
 * @return array<string> Tableau trié alphabétiquement des noms de villes.
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
 * $villes = getVillesByDepartementFast("28"); // Retourne les villes de l'Eure-et-Loir
 * @endcode
 */
function getVillesByDepartementFast(string $depCode): array {
    $villes = [];
    $baseDir = dirname(__DIR__);
    $csvPath = $baseDir . '/données/communes-france-2025.csv';
    $indexPath = $baseDir . '/données/index_villes.json';
    
    if (!file_exists($csvPath)) {
        return $villes;
    }
    
    if (!file_exists($indexPath)) {
        genererIndexVilles();
    }
    
    $index = json_decode(file_get_contents($indexPath), true);
    
    if ($index === null || !isset($index[$depCode])) {
        return $villes;
    }
    
    if (strlen($depCode) == 1 && is_numeric($depCode)) {
        $depCode = str_pad($depCode, 2, '0', STR_PAD_LEFT);
    }
    
    $handle = fopen($csvPath, 'r');
    fseek($handle, $index[$depCode]);
    
    while (($data = fgetcsv($handle, 1000, ",", '"', "\\")) !== FALSE) {
        if (!isset($data[12])) {
            break;
        }
        
        $currentDep = trim($data[12]);
        
        if ($currentDep !== $depCode) {
            break;
        }
        
        if (isset($data[2])) {
            $villes[] = $data[2];
        }
    }
    
    fclose($handle);
    sort($villes);
    return $villes;
}
?>