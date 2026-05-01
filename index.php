<?php
declare(strict_types=1);
$pageTitle = 'Accueil - Projet de développement web';
$pageDescription = 'Bienvenue sur le site du projet de développement web - CY Cergy Paris Université';
$currentPage = 'index';
$pageAuthor = 'ANURAJAN Thenuxshan, FERAOUN Mohamed Amine';

require_once 'include/header.inc.php';

// Initialize $lang from GET parameter or default to 'fr'
$lang = $_GET['lang'] ?? 'fr';
?>

<div class="cartes-td">
    <article class="carte-td">
        <h3>Selection régionale</h3>
        <p>Evaluez le prix du carburant en sélectionnant votre région, département et ville.</p>
        <a href="carte.php?style=<?= $style ?>&lang=<?= $lang ?>" class="bouton-td">Voir la carte</a>
    </article>
</div>

<?php require_once 'include/footer.inc.php'; ?>
