<?php
    // Gestion du thème via cookie et GET
    $style = $_GET['style'] ?? $_COOKIE['theme'] ?? 'clair';
    if (!in_array($style, ['sombre', 'clair'])) {
        $style = 'clair';
    }
    setcookie('theme', $style, time() + 3600*24*30);

    // Titres par défaut (peuvent être surchargés par les pages avant l'inclusion du header)
    $pageTitle = $pageTitle ?? 'Carte de la France - Choix de votre ville';
    $pageDescription = $pageDescription ?? 'Bienvenue sur le site du projet de développement web - CY Cergy Paris Université';
    $pageAuthor = 'ANURAJAN Thenuxshan, FERAOUN Mohamed Amine';
    $cssFile = $style . '.css';
    $currentPage = $currentPage ?? '';
    
    // Récupérer tous les paramètres pour les préserver dans les liens
    $index = $_GET['index'] ?? null;
    $codePostal = $_GET['code_postal'] ?? '';
    $perimetre = $_GET['perimetre'] ?? 'ville';
    $carburant = $_GET['carburant'] ?? 'Tous';
    $tri = $_GET['tri'] ?? 'prix_asc';
    $page = $_GET['page'] ?? 1;
    $afficher = $_GET['afficher'] ?? '';

    // choix de la lang mais par défaut c'est le français
    $lang = (!empty($_GET['lang']) && $_GET['lang'] === 'en') ? 'en' : 'fr';
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8" >
    <meta name="viewport" content="width=device-width, initial-scale=1.0" >
    <meta name="author" content="<?= htmlspecialchars($pageAuthor) ?>" >
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>" >
    <link rel="icon" type="image/png" href="images/icon.png" >
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="styles/<?= $cssFile ?>" >
</head>
<body>
    <header id="top">
        <div class="en-tete-logo">
            <a href="index.php?index=<?= $index ?>&amp;code_postal=<?= $codePostal ?>&amp;perimetre=<?= $perimetre ?>&amp;carburant=<?= $carburant ?>&amp;tri=<?= $tri ?>&amp;page=<?= $page ?>&amp;afficher=<?= $afficher ?>&amp;style=<?= $style ?>&amp;lang=<?= $lang ?>"><img src="images/icon.png" alt="logo du site" class="logo-site"></a>
            <div class="titres-en-tete">
                <h1><?= htmlspecialchars($pageTitle) ?></h1>
            </div>
            <div class="header-settings">
                <div class="group-choix">
                    <span>lang :</span>
                    <a href="?index=<?= $index ?>&amp;code_postal=<?= $codePostal ?>&amp;perimetre=<?= $perimetre ?>&amp;carburant=<?= $carburant ?>&amp;tri=<?= $tri ?>&amp;page=<?= $page ?>&amp;afficher=<?= $afficher ?>&amp;lang=fr&amp;style=<?= $style ?>" class="btn-drapeau <?= ($lang == 'fr') ? 'active' : '' ?>">
                        <img src="images/fr.svg" alt="Français" />
                    </a>
                    <a href="?index=<?= $index ?>&amp;code_postal=<?= $codePostal ?>&amp;perimetre=<?= $perimetre ?>&amp;carburant=<?= $carburant ?>&amp;tri=<?= $tri ?>&amp;page=<?= $page ?>&amp;afficher=<?= $afficher ?>&amp;lang=en&amp;style=<?= $style ?>" class="btn-drapeau <?= ($lang == 'en') ? 'active' : '' ?>">
                        <img src="images/en.svg" alt="Anglais" />
                    </a>
                </div>
                <div class="group-choix">
                    <span>Style :</span>
                    <a href="?index=<?= $index ?>&amp;code_postal=<?= $codePostal ?>&amp;perimetre=<?= $perimetre ?>&amp;carburant=<?= $carburant ?>&amp;tri=<?= $tri ?>&amp;page=<?= $page ?>&amp;afficher=<?= $afficher ?>&amp;style=sombre&amp;lang=<?= $lang ?>" class="btn-choix <?= ($style == 'sombre') ? 'active' : '' ?>">Sombre</a>
                    <a href="?index=<?= $index ?>&amp;code_postal=<?= $codePostal ?>&amp;perimetre=<?= $perimetre ?>&amp;carburant=<?= $carburant ?>&amp;tri=<?= $tri ?>&amp;page=<?= $page ?>&amp;afficher=<?= $afficher ?>&amp;style=clair&amp;lang=<?= $lang ?>" class="btn-choix <?= ($style == 'clair') ? 'active' : '' ?>">Clair</a>
                </div>
            </div>
        </div>
<nav class="navigation-principale">
        <ul>
            <li><a href="index.php?index=<?= $index ?>&amp;code_postal=<?= $codePostal ?>&amp;perimetre=<?= $perimetre ?>&amp;carburant=<?= $carburant ?>&amp;tri=<?= $tri ?>&amp;page=<?= $page ?>&amp;afficher=<?= $afficher ?>&amp;style=<?= $style ?>&amp;lang=<?= $lang ?>">Accueil</a></li>
            <li><a href="carte.php?index=<?= $index ?>&amp;code_postal=<?= $codePostal ?>&amp;perimetre=<?= $perimetre ?>&amp;carburant=<?= $carburant ?>&amp;tri=<?= $tri ?>&amp;page=<?= $page ?>&amp;afficher=<?= $afficher ?>&amp;style=<?= $style ?>&amp;lang=<?= $lang ?>">Carte</a></li>
            <li><a href="stations.php?index=<?= $index ?>&amp;code_postal=<?= $codePostal ?>&amp;perimetre=<?= $perimetre ?>&amp;carburant=<?= $carburant ?>&amp;tri=<?= $tri ?>&amp;page=<?= $page ?>&amp;afficher=<?= $afficher ?>&amp;style=<?= $style ?>&amp;lang=<?= $lang ?>">Stations</a></li>
            <li><a href="statistiques.php?index=<?= $index ?>&amp;code_postal=<?= $codePostal ?>&amp;perimetre=<?= $perimetre ?>&amp;carburant=<?= $carburant ?>&amp;tri=<?= $tri ?>&amp;page=<?= $page ?>&amp;afficher=<?= $afficher ?>&amp;style=<?= $style ?>&amp;lang=<?= $lang ?>">Statistiques</a></li>
        </ul>
    </nav>
    
    </header>
    <main>
