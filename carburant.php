<?php
// Partie 1 : Système de cache (téléchargement et mise en cache du flux XML carburant)
declare(strict_types=1);
// Configuration
$remoteZipUrl = 'https://donnees.roulez-eco.fr/opendata/instantane';
$xmlLocalPath = __DIR__ . '/carburants.xml';
$zipTempPath = __DIR__ . '/temp_carburants.zip';
$cacheValidity = 10 * 60; // 10 minutes en secondes (60s * 10)

// Vérification de la validité du cache existant
$needsRefresh = true;
if (file_exists($xmlLocalPath)) {
    $xmlLastMod = filemtime($xmlLocalPath); // Timestamp de dernière modification du XML local
    $currentTime = time();
    
    // Mécanisme de temps : si le fichier a été modifié il y a moins de 10 minutes,
    // le cache est encore valide (on compare le temps actuel moins la durée de validité
    // au temps de modification du fichier)
    if ($currentTime - $xmlLastMod < $cacheValidity) {
        $needsRefresh = false; // Cache valide, aucune action nécessaire
    }
}

// Mise à jour du cache si nécessaire (fichier absent ou trop vieux)
if ($needsRefresh) {
    // 1. Téléchargement du ZIP depuis l'Open Data
    $zipContent = file_get_contents($remoteZipUrl);
    if ($zipContent === false) {
        die('Erreur : Échec du téléchargement du ZIP');
    }
    file_put_contents($zipTempPath, $zipContent);

    // 2. Extraction du XML depuis le ZIP
    $zip = new ZipArchive;
    if ($zip->open($zipTempPath) === true) {
        $xmlExtracted = false;
        // Recherche du premier fichier XML dans le ZIP
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
            die('Erreur : Aucun fichier XML trouvé dans le ZIP');
        }
    } else {
        die('Erreur : Impossible d\'ouvrir le fichier ZIP téléchargé');
    }

    // 3. Suppression du fichier ZIP temporaire (ménage)
    if (file_exists($zipTempPath)) {
        unlink($zipTempPath);
    }
}
?>
