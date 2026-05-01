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
 * Filtre les stations par code postal, périmètre et type de carburant.
 */
function rechercherStations(string $code_postal, string $perimetre = 'ville', string $carburant_choisi = 'Tous'): array {
    global $xmlLocalPath;
    $stations_trouvees = [];

    if (!file_exists($xmlLocalPath)) return $stations_trouvees;
    $xml = simplexml_load_file($xmlLocalPath);
    if ($xml === false) return $stations_trouvees;

    // 1. Logique du périmètre
    if ($perimetre === 'departement') {
        $prefixe_recherche = substr($code_postal, 0, 2);
    } elseif ($perimetre === 'environs') {
        $prefixe_recherche = substr($code_postal, 0, 3);
    } else { // 'ville' par défaut
        $prefixe_recherche = $code_postal;
    }

    foreach ($xml->pdv as $pdv) {
        $cp = (string) $pdv['cp'];
        
        // Si la station est dans notre zone géographique...
        if (strpos($cp, $prefixe_recherche) === 0) {
            $station = [
                'adresse' => (string) $pdv['adresse'],
                'ville' => (string) $pdv['ville'],
                'cp' => $cp,
                'automate_24' => (string) $pdv['automate-24-24'] === '1',
                'maj' => null,
                'services' => [],
                'ruptures' => [],
                'carburants' => []
            ];

            $possede_le_carburant = false;
            $maj = null;

            // 2. Logique du filtre de carburant
            foreach ($pdv->prix as $prix) {
                $nom_carb = (string) $prix['nom'];
                $station['carburants'][] = [
                    'nom' => $nom_carb,
                    'valeur' => (float) $prix['valeur']
                ];
                
                // On vérifie si la station vend le carburant demandé
                if ($carburant_choisi === 'Tous' || $nom_carb === $carburant_choisi) {
                    $possede_le_carburant = true;
                }

                if ($maj === null && isset($prix['maj'])) {
                    $maj = (string) $prix['maj'];
                }
            }

            // Si elle n'a pas le carburant demandé, on l'ignore (Le Videur !)
            if (!$possede_le_carburant) {
                continue; 
            }

            $station['maj'] = $maj;

            if (isset($pdv->services)) {
                foreach ($pdv->services->service as $service) {
                    $station['services'][] = (string) $service;
                }
            }

            foreach ($pdv->rupture as $rupture) {
                $station['ruptures'][] = [
                    'nom' => (string) $rupture['fuel'],
                    'type' => (string) $rupture['type']
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

    foreach ($stations as $index => $station) {
        // Séparer les carburants principaux des secondaires
        $carburants_principaux = [];
        $carburants_secondaires = [];

        foreach ($station['carburants'] as $carburant) {
            $nom = $carburant['nom'];
            if (in_array($nom, ['Gazole', 'E10', 'SP95'])) {
                $carburants_principaux[] = $carburant;
            } else {
                $carburants_secondaires[] = $carburant;
            }
        }



        $html .= '<article class="station-card" id="station-' . $index . '">';
        $html .= '<header>';
        
        // --- NOUVEAU : LE BADGE OR ---
        if ($index === 0) {
            $html .= '<div class="badge-or" style="background: linear-gradient(to right, #FFD700, #FDB931); color: #000; padding: 5px 15px; border-radius: 20px; font-weight: bold; display: inline-block; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">🥇 Station la moins chère !</div>';
        }
        // -----------------------------

        $html .= '<h3>' . htmlspecialchars($station['ville']) . ' (' . htmlspecialchars($station['cp']) . ')</h3>';
        $html .= '<p class="adresse">' . htmlspecialchars($station['adresse']) . '</p>';
        
        // Carburants principaux (Gazole, E10, SP95)
        if (!empty($carburants_principaux)) {
            $html .= '<div class="carburants-principaux">';
            foreach ($carburants_principaux as $carburant) {
                $html .= '<span class="prix-carburant">' . htmlspecialchars($carburant['nom']) . ' : ' . number_format($carburant['valeur'], 3, ',', ' ') . ' €</span>';
            }
            $html .= '</div>';
        }
        
        // Badge 24h/24
        if ($station['automate_24']) {
            $html .= '<span class="badge-24h">Ouvert 24h/24</span>';
        }
        
        // Bouton détails
        $html .= '<button class="btn-details" data-target="details-' . $index . '" onclick="toggleDetails(' . $index . ')">Voir tous les détails</button>';
        $html .= '</header>';
        
        // Section détails (cachée)
        $html .= '<div class="station-details" id="details-' . $index . '" hidden>';
        
        // Tous les carburants
        $html .= '<h4>Tous les carburants</h4><ul>';
        foreach ($station['carburants'] as $carburant) {
            $html .= '<li>' . htmlspecialchars($carburant['nom']) . ' : ' . number_format($carburant['valeur'], 3, ',', ' ') . ' €</li>';
        }
        $html .= '</ul>';
        
        // Date mise à jour
        if ($station['maj']) {
            $maj_timestamp = strtotime($station['maj']);
            if ($maj_timestamp !== false) {
                $html .= '<p class="maj">Prix mis à jour le ' . date('d/m/Y à H\hi', $maj_timestamp) . '</p>';
            }
        }
        
        // Services
        if (!empty($station['services'])) {
            $html .= '<h4>Services</h4><ul class="services-list">';
            foreach ($station['services'] as $service) {
                $html .= '<li>' . htmlspecialchars($service) . '</li>';
            }
            $html .= '</ul>';
        }
        
        // Ruptures de stock
        if (!empty($station['ruptures'])) {
            $html .= '<h4>Ruptures de stock</h4>';
            foreach ($station['ruptures'] as $rupture) {
                if ($rupture['type'] === 'temporaire') {
                    $html .= '<p class="rupture-alerte">' . htmlspecialchars($rupture['nom']) . ' : Rupture temporaire</p>';
                }
            }
        }
        
        $html .= '</div>'; // ferme station-details
        $html .= '</article>';
    }

    $html .= '</div>';
    return $html;
}

/**
 * Fonction 1 : Orchestration (Le chef d'orchestre)
 */
function genererHtmlStations(string $code_postal = '95000', string $perimetre = 'ville', string $carburant_choisi = 'Tous'): string {
    refreshCacheSiNecessaire();
    
    // On passe les nouveaux paramètres au détective
    $stations = rechercherStations($code_postal, $perimetre, $carburant_choisi);
    
    // === TRI POUR TROUVER LA MOINS CHÈRE (Analyse de données) ===
    if (!empty($stations)) {
        usort($stations, function($a, $b) use ($carburant_choisi) {
            // Si on cherche "Tous", on trie par Gazole par défaut. Sinon, par le carburant choisi.
            $carburant_ref = ($carburant_choisi === 'Tous') ? 'Gazole' : $carburant_choisi;
            
            // On attribue 999€ si la station n'a pas le carburant pour qu'elle finisse en bas de liste
            $prixA = 999.0;
            foreach ($a['carburants'] as $c) { if ($c['nom'] === $carburant_ref) $prixA = $c['valeur']; }
            
            $prixB = 999.0;
            foreach ($b['carburants'] as $c) { if ($c['nom'] === $carburant_ref) $prixB = $c['valeur']; }
            
            return $prixA <=> $prixB;
        });
    }
    // ======================================================================

    // Récupération IP et enregistrement CSV (Ton code existant)
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!empty($stations)) {
        $ville_trouvee = $stations[0]['ville'];
        enregistrerConsultationCsv($ville_trouvee, $user_ip);
    } else {
        enregistrerConsultationCsv("Inconnue (CP: " . $code_postal . ")", $user_ip);
    }
    
    $html = construireCartesHtml($stations);
    
    // Ajouter le JavaScript pour le toggle des détails
    $html .= '<script>
    function toggleDetails(id) {
        const detailsDiv = document.getElementById("details-" + id);
        const button = document.querySelector("button[data-target=\\"details-" + id + "\\"]");
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

// =====================================================================
// 5. Sous-fonction : Historique CSV (Les Logs)
// =====================================================================
/**
 * Enregistre chaque recherche dans un fichier CSV avec la date, l'heure et l'IP.
 */
function enregistrerConsultationCsv(string $ville, string $ip = ''): void {
    $fichierCsv = __DIR__ . '/historique_recherches.csv';
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

// =====================================================================
// AFFICHAGE DE LA PAGE
// =====================================================================
require_once 'include/header.inc.php';

$cp = $_GET['code_postal'] ?? '95000';
$perimetre = $_GET['perimetre'] ?? 'ville';
$carburant = $_GET['carburant'] ?? 'Tous';

echo genererHtmlStations($cp, $perimetre, $carburant);

require_once 'include/footer.inc.php';
?>
