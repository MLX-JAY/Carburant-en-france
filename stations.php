<?php
declare(strict_types=1);

$pageTitle = 'Stations de carburant';
$currentPage = 'stations';

require_once 'include/fonction.inc.php';

ini_set('memory_limit', '512M');

// Récupération des paramètres EN PREMIER
$codePostal = $_GET['code_postal'] ?? $_COOKIE['dernier_cp'] ?? '';
$perimetre = $_GET['perimetre'] ?? $_COOKIE['perimetre'] ?? 'ville';
$carburants = $_GET['carburants'] ?? null;
if ($carburants === null) {
    if (isset($_COOKIE['carburants'])) {
        $carburants = json_decode($_COOKIE['carburants'], true) ?? ['Tous'];
    } else {
        $carburants = ['Tous'];
    }
} elseif (is_string($carburants)) {
    $carburants = [$carburants];
}

// Nettoyage : si "Tous" est coché, on ignore le reste
if (in_array('Tous', $carburants)) {
    $carburants = ['Tous'];
}

$tri = $_GET['tri'] ?? $_COOKIE['tri'] ?? 'prix_asc';
$index = $_GET['index'] ?? null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Définir les cookies pour les paramètres de recherche (AVANT tout HTML)
if (!isset($_COOKIE['tri']) || $_COOKIE['tri'] !== $tri) {
    setcookie('tri', $tri, time() + 3600*24*30, '/');
}
if (!isset($_COOKIE['perimetre']) || $_COOKIE['perimetre'] !== $perimetre) {
    setcookie('perimetre', $perimetre, time() + 3600*24*30, '/');
}
if (!isset($_COOKIE['carburants']) || $_COOKIE['carburants'] !== json_encode($carburants)) {
    setcookie('carburants', json_encode($carburants), time() + 3600*24*30, '/');
}

// Récupérer le nom de la ville
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

// Enregistrer la dernière recherche dans les cookies
if (!empty($villeNom) && !empty($codePostal)) {
    setcookie('derniere_ville', $villeNom, time() + 3600*24*30, '/');
    setcookie('dernier_cp', $codePostal, time() + 3600*24*30, '/');
} elseif (!empty($codePostal)) {
    setcookie('dernier_cp', $codePostal, time() + 3600*24*30, '/');
    setcookie('derniere_ville', '', time() - 3600, '/');
}

// Maintenant on peut inclure le header (HTML commence ici)
require_once 'include/header.inc.php';

// Paramètres pour le lien de retour
$retourParams = [
    'code_postal' => $codePostal,
    'perimetre' => $perimetre,
    'carburants' => $carburants,
    'tri' => $tri,
];
if ($index !== null) {
    $retourParams['index'] = $index;
}
$retourUrl = 'carte.php?' . http_build_query($retourParams) . '#form-villes';

$stationsHTML = genererHtmlStations($codePostal, $perimetre, $carburants, $tri, $index, $page);
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
