<?php
    // Gestion du thème via cookie et GET
    $style = $_GET['style'] ?? $_COOKIE['theme'] ?? 'clair';
    if (!in_array($style, ['sombre', 'clair'])) {
        $style = 'clair';
    }
    setcookie('theme', $style, time() + 3600*24*30);
    $pageTitle = $pageTitle ;
    $pageDescription = $pageDescription ;
    $pageAuthor = $pageAuthor;
    $currentPage = $currentPage ;
    $cssFile = $style . '.css';

    // choix de la lang mais par défaut c'est le français
    $lang = (!empty($_GET['lang']) && $_GET['lang'] === 'en') ? 'en' : 'fr';
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
    <link rel="stylesheet" href="styles/sombre.css" />
</head>
<body>
    <header id="top">
        <div class="en-tete-logo">
            <a href="index.php?style=<?= $style ?>&amp;lang=<?= $lang ?>"><img src="images/icon.png" alt="logo du site" class="logo-site"></a>
            <div class="titres-en-tete">
                <h1><?= htmlspecialchars($pageTitle) ?></h1>
            </div>
            <div class="header-settings">
                <div class="group-choix">
                    <span>lang :</span>
                    <a href="?lang=fr&amp;style=<?= $style ?>" class="btn-drapeau <?= ($lang == 'fr') ? 'active' : '' ?>">
                        <img src="images/fr.svg" alt="Français" />
                    </a>
                    <a href="?lang=en&amp;style=<?= $style ?>" class="btn-drapeau <?= ($lang == 'en') ? 'active' : '' ?>">
                        <img src="images/en.svg" alt="Anglais" />
                    </a>
                </div>
                <div class="group-choix">
                    <span>Style :</span>
                    <a href="?style=sombre&amp;lang=<?= $lang ?>" class="btn-choix <?= ($style == 'sombre') ? 'active' : '' ?>">Sombre</a>
                    <a href="?style=clair&amp;lang=<?= $lang ?>" class="btn-choix <?= ($style == 'clair') ? 'active' : '' ?>">Clair</a>
                </div>
            </div>
        </div>
            <nav class="navigation-principale">
        <ul>
            <li><a href="index.php?style=<?= $style ?>&amp;lang=<?= $lang ?>">Accueil</a></li>
        </ul>
    </nav>
    
    </header>
    <main>
