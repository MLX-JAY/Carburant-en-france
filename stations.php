<?php
declare(strict_types=1);

$pageTitle = 'Stations de carburant';
$currentPage = 'stations';

require_once 'include/header.inc.php';
require_once 'include/fonction.inc.php';

ini_set('memory_limit', '512M');

$codePostal = $_GET['code_postal'] ?? '';
$perimetre = $_GET['perimetre'] ?? $_COOKIE['perimetre'] ?? 'ville';
$carburant = $_GET['carburant'] ?? $_COOKIE['carburant'] ?? 'Tous';
$tri = $_GET['tri'] ?? $_COOKIE['tri'] ?? 'prix_asc';
$index = $_GET['index'] ?? null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

setcookie('tri', $tri, time() + 3600*24*30);
setcookie('perimetre', $perimetre, time() + 3600*24*30);
setcookie('carburant', $carburant, time() + 3600*24*30);

$villeNom = '';
if (!empty($codePostal)) {
    $dep = substr($codePostal, 0, 2);
    $villes = getVillesByDepartementFast($dep);
    foreach ($villes as $nom => $code) {
        if ($code === $codePostal) {
            $villeNom = $nom;
            break;
        }
    }
    if (!empty($villeNom)) {
        $pageTitle = 'Stations à ' . $villeNom . ' (' . $codePostal . ')';
    } else {
        $pageTitle = 'Stations du ' . $codePostal;
    }
}

$retourParams = [
    'code_postal' => $codePostal,
    'afficher' => 'prix',
    'perimetre' => $perimetre,
    'carburant' => $carburant,
    'tri' => $tri,
    'lang' => $lang,
    'style' => $style,
];
if ($index !== null) {
    $retourParams['index'] = $index;
}
$retourUrl = 'carte.php?' . http_build_query($retourParams) . '#form-villes';

$stationsHTML = genererHtmlStations($codePostal, $perimetre, $carburant, $tri, $index, $page);
?>

<article id="stations-page">
    <div class="retour-carte" style="margin-bottom: 20px;">
        <a href="<?= htmlspecialchars($retourUrl) ?>" class="bouton-retour" style="display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; font-size: 0.9em;">
            ← Retour à la carte
        </a>
    </div>

    <?= $stationsHTML ?>
</article>

<?php require_once 'include/footer.inc.php'; ?>