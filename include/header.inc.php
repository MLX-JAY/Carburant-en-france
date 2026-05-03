<?php
    // 1. Récupération de la langue (GET en priorité, puis COOKIE, puis 'fr')
    $lang = (!empty($_GET['lang']) && $_GET['lang'] === 'en') ? 'en' : ($_COOKIE['lang'] ?? 'fr');

    // 2. Récupération du thème
    $style = $_GET['style'] ?? $_COOKIE['theme'] ?? 'clair';
    if (!in_array($style, ['sombre', 'clair'])) {
        $style = 'clair';
    }

    // 3. Enregistrement des cookies SEULEMENT si nécessaire (Optimisation)
    $optionsCookie = [
        'expires' => time() + 3600*24*30,
        'path' => '/',
        'samesite' => 'Lax'
    ];

    if (!isset($_COOKIE['theme']) || $_COOKIE['theme'] !== $style) {
        setcookie('theme', $style, $optionsCookie);
    }
    if (!isset($_COOKIE['lang']) || $_COOKIE['lang'] !== $lang) {
        setcookie('lang', $lang, $optionsCookie);
    }

    // 4. Fusion des paramètres pour les boutons "Interrupteurs"
    $paramsActuels = $_GET;

    $paramsFr = $paramsActuels; $paramsFr['lang'] = 'fr';
    $lienFr = '?' . http_build_query($paramsFr);

    $paramsEn = $paramsActuels; $paramsEn['lang'] = 'en';
    $lienEn = '?' . http_build_query($paramsEn);

    $paramsSombre = $paramsActuels; $paramsSombre['style'] = 'sombre';
    $lienSombre = '?' . http_build_query($paramsSombre);

    $paramsClair = $paramsActuels; $paramsClair['style'] = 'clair';
    $lienClair = '?' . http_build_query($paramsClair);

    // Titres par défaut (peuvent être surchargés par les pages avant l'inclusion du header)
    $pageTitle = $pageTitle ?? 'Carte de la France - Choix de votre ville';
    $pageDescription = $pageDescription ?? 'Bienvenue sur le site du projet de développement web - CY Cergy Paris Université';
    $pageAuthor = 'ANURAJAN Thenuxshan, FERAOUN Mohamed Amine';
    $cssFile = $style . '.css';
    $currentPage = $currentPage ?? '';
    
    // Récupérer tous les paramètres pour les préserver dans les liens
    // Utiliser les variables existantes si déjà définies (ex: stations.php)
    $index = $index ?? $_GET['index'] ?? null;
    $codePostal = $codePostal ?? $_GET['code_postal'] ?? '';
    $perimetre = $perimetre ?? $_GET['perimetre'] ?? 'ville';
    $carburant = $carburant ?? $_GET['carburant'] ?? 'Tous';
    $tri = $tri ?? $_GET['tri'] ?? 'prix_asc';
    $page = $page ?? $_GET['page'] ?? 1;
    $afficher = $afficher ?? $_GET['afficher'] ?? '';
?>
    <!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="author" content="ANURAJAN Thenuxshan, FERAOUN Mohamed Amine" />
    <meta name="description" content="Bienvenue sur le site du projet de développement web - CY Cergy Paris Université" />
    <link rel="icon" type="image/png" href="images/icon.png" />
    <title>Carte de la France - Choix de votre ville</title>
    <link rel="stylesheet" href="styles/<?= $cssFile ?>" />
</head>
<body>
    <header id="top">
        <div class="en-tete-logo">
            <a href="index.php"><img src="images/icon.png" alt="logo du site" class="logo-site"></a>
            <div class="titres-en-tete">
                <h1><?= htmlspecialchars($pageTitle) ?></h1>
            </div>
            <div class="header-settings">
                <div class="group-choix">
                    <span>lang :</span>
                    <a href="<?= htmlspecialchars($lienFr) ?>" class="btn-drapeau <?= ($lang == 'fr') ? 'active' : '' ?>">
                        <img src="images/fr.svg" alt="Français" />
                    </a>
                    <a href="<?= htmlspecialchars($lienEn) ?>" class="btn-drapeau <?= ($lang == 'en') ? 'active' : '' ?>">
                        <img src="images/en.svg" alt="Anglais" />
                    </a>
                </div>
                <div class="group-choix">
                    <?php if ($style == 'clair'): ?>
                        <a href="<?= htmlspecialchars($lienSombre) ?>" class="btn-icone" title="Passer en mode sombre">
                            <img src="images/moon.png" alt="Mode Sombre" />
                        </a>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($lienClair) ?>" class="btn-icone" title="Passer en mode clair">
                            <img src="images/soleil.png" alt="Mode Clair" />
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
<nav class="navigation-principale">
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="carte.php">Carte</a></li>
            <li><a href="stations.php">Stations</a></li>
            <li><a href="statistiques.php">Statistiques</a></li>
        </ul>
    </nav>
    
    </header>
    <main>
