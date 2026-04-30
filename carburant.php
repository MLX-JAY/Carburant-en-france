<?php
declare(strict_types=1);

// Configuration
$remoteZipUrl = 'https://donnees.roulez-eco.fr/opendata/instantane';
$xmlLocalPath = __DIR__ . '/carburants.xml';
$zipTempPath = __DIR__ . '/temp_carburants.zip';
$cacheValidity = 10 * 60;

/**
 * Fonction 2 : Cache (L'intendant)
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
 * Fonction 3 : Recherche (Le détective)
 * Filtre les stations par code postal et extrait les données
 * @return array Tableau des stations trouvées
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
                'adresse' => (string) $pdv['adresse'],
                'ville' => (string) $pdv['ville'],
                'cp' => $cp,
                'carburants' => []
            ];

            foreach ($pdv->prix as $prix) {
                $station['carburants'][] = [
                    'nom' => (string) $prix['nom'],
                    'valeur' => (float) $prix['valeur']
                ];
            }

            if (!empty($station['carburants'])) {
                $stations_trouvees[] = $station;
            }
        }
    }

    return $stations_trouvees;
}

/**
 * Fonction 4 : Affichage (Le designer)
 * Génère le HTML des cartes à partir du tableau de stations
 * @return string Code HTML des stations
 */
function construireCartesHtml(array $stations): string {
    if (empty($stations)) {
        return '<p>Aucune station trouvée pour cette zone.</p>';
    }

    $html = '<div class="stations-grid">';

    foreach ($stations as $station) {
        $html .= '<article class="station-card">';
        $html .= '<h3>' . htmlspecialchars($station['ville']) . ' (' . htmlspecialchars($station['cp']) . ')</h3>';
        $html .= '<p class="adresse">' . htmlspecialchars($station['adresse']) . '</p>';
        $html .= '<ul class="carburants-list">';

        foreach ($station['carburants'] as $carburant) {
            $html .= '<li>';
            $html .= '<span class="nom-carburant">' . htmlspecialchars($carburant['nom']) . '</span>';
            $html .= '<span class="prix-carburant">' . number_format($carburant['valeur'], 3, ',', ' ') . ' €</span>';
            $html .= '</li>';
        }

        $html .= '</ul></article>';
    }

    $html .= '</div>';
    return $html;
}

/**
 * Fonction 1 : Orchestration (Le chef d'orchestre)
 * Appelle les autres fonctions dans l'ordre et retourne le HTML final
 * @return string Code HTML prêt à être affiché
 */
function genererHtmlStations(string $code_postal = '95000'): string {
    refreshCacheSiNecessaire();
    $stations = rechercherStations($code_postal);
    return construireCartesHtml($stations);
}
?>
