<?php
/**
 * @file statistiques.php
 * @brief Page de statistiques affichant les consultations du site.
 * 
 * Cette page présente deux types d'informations :
 * 1. Des indicateurs clés (KPI) sous forme de cartes
 * 2. Un histogramme interactif via Chart.js
 * 
 * @details
 * - Les données proviennent du fichier CSV historique des consultations
 * - Le thème (clair/sombre) est automatiquement détecté pour adapter les couleurs du graphique
 * - Les données PHP sont passées au JavaScript via json_encode() pour alimenter Chart.js
 * 
 * @dependencies
 * - Chart.js (CDN) pour l'histogramme
 * - fonction.inc.php pour les fonctions getNombreTotalVisiteurs() et getTopVilles()
 * 
 * @version 1.0
 * @date 2026
 */

declare(strict_types=1);

// =============================================================================
// CONFIGURATION DE LA PAGE
// =============================================================================

// Titre de la page (surcharge le défaut du header)
$pageTitle = 'Statistiques - Consultations';

// Identifiant pour le menu de navigation
$currentPage = 'statistiques';

// Inclusion des fichiers requis
require_once 'include/header.inc.php';
require_once 'include/fonction.inc.php';

// =============================================================================
// RÉCUPÉRATION DES DONNÉES
// =============================================================================

/**
 * Récupération du nombre total de consultations depuis le fichier CSV
 * @var int $totalVisiteurs Nombre total de recherches enregistrées
 */
$totalVisiteurs = getNombreTotalVisiteurs();

/**
 * Récupération du top 10 des villes les plus consultées
 * @var array $topVilles Tableau associatif ['Ville' => nombre de consultations]
 */
$topVilles = getTopVilles(10);

/**
 * Extraction de la ville la plus consultée (première clé du tableau trié)
 * @var string $topVilleNom Nom de la ville la plus consultée
 */
$topVilleNom = !empty($topVilles) ? array_key_first($topVilles) : 'Aucune ville';

/**
 * Nombre de consultations pour la ville la plus populaire
 * @var int $topVilleCount Nombre de consultations
 */
$topVilleCount = !empty($topVilles) ? $topVilles[$topVilleNom] : 0;
?>

<article id="statistiques">
    <h2>Statistiques de consultation</h2>
    
    <!-- ==========================================================================
         SECTION 1 : CARTES DE STATISTIQUES (KPI)
         ========================================================================== -->
    <div class="stats-cles" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin: 30px 0;">
        
        <!-- Carte 1 : Nombre total de visiteurs -->
        <div class="carte-stat" style="background: <?= $style === 'sombre' ? '#2a2a2a' : '#ffffff' ?>; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; border-left: 4px solid #cf5e26;">
            <!-- Icône SVG : utilisateurs (couleur orange thème) -->
            <div style="margin-bottom: 15px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cf5e26" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <p style="font-size: 0.9em; color: <?= $style === 'sombre' ? '#aaa' : '#666' ?>; margin-bottom: 10px;">Nombre total de consultations</p>
            <!-- Formatage du nombre avec séparateurs de milliers -->
            <p style="font-size: 2.5em; font-weight: bold; color: #cf5e26;"><?= number_format($totalVisiteurs, 0, ',', ' ') ?></p>
        </div>
        
        <!-- Carte 2 : Ville la plus consultée -->
        <div class="carte-stat" style="background: <?= $style === 'sombre' ? '#2a2a2a' : '#ffffff' ?>; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; border-left: 4px solid #0055A4;">
            <!-- Icône SVG : localisation (couleur bleu) -->
            <div style="margin-bottom: 15px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#0055A4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
            </div>
            <p style="font-size: 0.9em; color: <?= $style === 'sombre' ? '#aaa' : '#666' ?>; margin-bottom: 10px;">Ville la plus consultée</p>
            <p style="font-size: 1.8em; font-weight: bold; color: #0055A4;"><?= htmlspecialchars($topVilleNom) ?></p>
            <p style="font-size: 1em; color: <?= $style === 'sombre' ? '#888' : '#888' ?>;"><?= $topVilleCount ?> consultations</p>
        </div>
    </div>
    
    <!-- ==========================================================================
         SECTION 2 : GRAPHIQUE HISTOGRAMME (Chart.js)
         ========================================================================== -->
    <?php if (!empty($topVilles)): ?>
    <div class="stats-graphique" style="background: <?= $style === 'sombre' ? '#2a2a2a' : '#ffffff' ?>; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-top: 30px;">
        <h3 style="margin-bottom: 25px; color: <?= $style === 'sombre' ? '#eee' : '#333' ?>;">Top 10 des villes les plus consultées</h3>
        
        <!--
            Élément canvas : conteneur pour le graphique Chart.js
            L'attribut id permet au JavaScript de trouver cet élément
            La hauteur fixe (400px) sert de base pour le responsive
        -->
        <div style="position: relative; height: 400px; width: 100%;">
            <canvas id="villesChart"></canvas>
        </div>
    </div>
    
    <!--
        ========================================================================
        CHARGEMENT DE CHART.JS
        ========================================================================
        Chart.js est chargé depuis un CDN (jsDelivr) pour optimiser le chargement.
        Le fichier umd (Universal Module Definition) fonctionne aussi bien dans
        un navigateur que dans un module CommonJS.
        
        Version utilisée : 4.4.1 (stable et éprouvée)
    -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    <!--
        ========================================================================
        CRÉATION DU GRAPHIQUE
        ========================================================================
        
        FLUX DE DONNÉES PHP → JAVASCRIPT :
        
        1. PHP lit les données depuis le fichier CSV (getTopVilles())
        2. Les données sont converties en JSON via json_encode()
        3. Le JavaScript réceptionne ces données et les affiche dans Chart.js
        
        Détailledu processus :
        - array_keys($topVilles) → liste des noms de villes → labels du graphique
        - array_values($topVilles) → liste des nombres → données du graphique
        
        PROTOCOLE DE SÉCURITÉ :
        - Les noms de villes sont échappés par htmlspecialchars() en PHP
        - json_encode() encode automatiquement les caractères spéciaux
        - Les couleurs sont définies côté PHP pour s'adapter au thème
    -->
    <script>
    /**
     * Initialisation du graphique Chart.js
     * 
     * Ce script IIFE (Immediately Invoked Function Expression) s'exécute
     * automatiquement lors du chargement de la page. Il :
     * 1. Récupère les données PHP transmises via json_encode()
     * 2. Configure les couleurs selon le thème (clair/sombre)
     * 3. Crée et affiche l'histogramme
     */
    (function() {
        // ========================================================================
        // ÉTAPE 1 : RÉCUPÉRATION DES DONNÉES DEPUIS PHP
        // ========================================================================
        // json_encode() convertit les tableaux PHP en objets JavaScript
        // array_keys() extrait les noms de villes pour les étiquettes (axe X)
        // array_values() extrait les nombres de consultations pour les données (axe Y)
        
        const labels = <?php echo json_encode(array_keys($topVilles)); ?>;
        const data = <?php echo json_encode(array_values($topVilles)); ?>;
        
        // ========================================================================
        // ÉTAPE 2 : CONFIGURATION DES COULEURS SELON LE THÈME
        // ========================================================================
        // Le thème est passé depuis PHP via la variable $style
        // - Mode clair : texte sombre (#333333), grille claire (#dddddd)
        // - Mode sombre : texte clair (#cccccc), grille foncée (#444444)
        // Cette adaptation assure une lisibilité optimale dans les deux thèmes
        
        const textColor = '<?php echo $style === 'sombre' ? '#cccccc' : '#333333'; ?>';
        const gridColor = '<?php echo $style === 'sombre' ? '#444444' : '#dddddd'; ?>';
        
        // ========================================================================
        // ÉTAPE 3 : CRÉATION DU CONTEXTE DE CANVAS
        // ========================================================================
        // getContext('2d') crée un contexte de dessin 2D sur le canvas
        // Ce contexte est nécessaire pour initialiser Chart.js
        
        const ctx = document.getElementById('villesChart').getContext('2d');
        
        // ========================================================================
        // ÉTAPE 4 : CONFIGURATION ET CRÉATION DU GRAPHIQUE
        // ========================================================================
        new Chart(ctx, {
            // ====================================================================
            // type: 'bar' configure un histogramme (diagramme en barres)
            // ====================================================================
            type: 'bar',
            
            // ====================================================================
            // data : données à afficher dans le graphique
            // ====================================================================
            data: {
                // labels : noms des villes (axe horizontal)
                labels: labels,
                
                // datasets : tableau des séries de données (ici une seule)
                datasets: [{
                    // Étiquette de la série (affichée dans les tooltips)
                    label: 'Nombre de consultations',
                    
                    // data : valeurs numériques (nombre de consultations par ville)
                    data: data,
                    
                    // -------------------------------------------------------
                    // STYLING DES BARRES
                    // -------------------------------------------------------
                    // Couleur de remplissage : orange thème (#cf5e26)
                    backgroundColor: '#cf5e26',
                    
                    // Couleur de la bordure : orange plus foncé (#a84d1e)
                    borderColor: '#a84d1e',
                    
                    // Épaisseur de la bordure : 2px
                    borderWidth: 2,
                    
                    // borderRadius : arrondi des coins des barres (6px)
                    borderRadius: 6,
                    
                    // borderSkipped: false → applique le borderRadius sur tous les coins
                    // (par défaut, le bas des barres n'a pas de bordure arrondie)
                    borderSkipped: false,
                }]
            },
            
            // ====================================================================
            // options : options de configuration du graphique
            // ====================================================================
            options: {
                // ----------------------------------------------------------------
                // responsive: true → le graphique s'adapte à la largeur du conteneur
                // ----------------------------------------------------------------
                responsive: true,
                
                // ----------------------------------------------------------------
                // maintainAspectRatio: false → permet de contrôler la hauteur
                // indépendamment du ratio par défaut (évite les distorsions)
                // ----------------------------------------------------------------
                maintainAspectRatio: false,
                
                // ----------------------------------------------------------------
                // plugins : configuration des plugins (légende, tooltips, etc.)
                // ----------------------------------------------------------------
                plugins: {
                    // La légende n'est pas affichée car il n'y a qu'une série
                    legend: {
                        display: false
                    }
                },
                
                // ----------------------------------------------------------------
                // scales : configuration des axes
                // ----------------------------------------------------------------
                scales: {
                    // -----------------------------------------------
                    // AXE Y (ordonnées) - nombre de consultations
                    // -----------------------------------------------
                    y: {
                        // beginAtZero: true → l'axe commence à 0
                        beginAtZero: true,
                        
                        // ticks : configuration des graduations
                        ticks: {
                            // Couleur du texte des graduations (adaptée au thème)
                            color: textColor,
                            
                            // stepSize: 1 → graduation uniquement sur les entiers
                            stepSize: 1
                        },
                        
                        // grid : lignes de grille horizontales
                        grid: {
                            // Couleur des lignes (adaptée au thème)
                            color: gridColor
                        }
                    },
                    
                    // -----------------------------------------------
                    // AXE X (abscisses) - noms des villes
                    // -----------------------------------------------
                    x: {
                        ticks: {
                            color: textColor
                        },
                        grid: {
                            // display: false → pas de lignes de grille verticales
                            display: false
                        }
                    }
                }
            }
        });
        
        // ========================================================================
        // FIN DE L'INITIALISATION DU GRAPHIQUE
        // ========================================================================
    })();
    </script>
    
    <?php else: ?>
    <!-- Message affiché si aucune donnée n'est disponible -->
    <p style="text-align: center; color: <?= $style === 'sombre' ? '#aaa' : '#666' ?>; margin-top: 30px;">Aucune donnée de consultation disponible.</p>
    <?php endif; ?>
</article>

<?php require_once 'include/footer.inc.php'; ?>