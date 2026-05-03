<?php
declare(strict_types=1);
require_once 'include/header.inc.php';
require_once 'include/fonction.inc.php';

$currentPage = 'carte';


// Gestion de la dernière recherche (lecture des cookies définis par stations.php)
$derniere_ville = $_COOKIE['derniere_ville'] ?? '';
$dernier_cp = $_COOKIE['dernier_cp'] ?? '';
$lang = $_GET['lang'] ?? 'fr';
$index = $_GET['index'] ?? null;
$codePostal = $_GET['code_postal'] ?? $_COOKIE['dernier_cp'] ?? '';
$afficher = $_GET['afficher'] ?? null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$tri = $_GET['tri'] ?? $_COOKIE['tri'] ?? 'prix_asc';
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

// Extraire le préfixe du département (2 premiers chiffres du code postal)
$dep = !empty($codePostal) ? substr($codePostal, 0, 2) : null;


// Bloc de rappel de la dernière recherche
if (!empty($derniere_ville) && !empty($dernier_cp)): ?>
    <div class="rappel-recherche">
        <div class="rappel-texte">
            <svg class="icone" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            <p>Dernière recherche : <strong><?= htmlspecialchars($derniere_ville, ENT_QUOTES, 'UTF-8') ?></strong></p>
        </div>
        
        <?php 
        $url = 'stations.php?code_postal=' . urlencode($dernier_cp) . '&perimetre=' . urlencode($perimetre);
        foreach ($carburants as $carb) {
            $url .= '&carburants[]=' . urlencode($carb);
        }
        if ($index !== null && $index !== '') {
            $url .= '&index=' . urlencode($index);
        }
        ?>
        
        <a href="<?= $url ?>" class="bouton-rapide">
            Voir les prix
            <svg class="icone-droite" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </a>
    </div>
<?php endif;

$departementsHTML = '';
if ($index != null && isset($regionsDepartements[$index])) {
    ob_start();
    echo '<h2>Départements de ' . $regionsNoms[$index] . '</h2>';
    afficherDepartements($regionsDepartements[$index]);
    $departementsHTML = ob_get_clean();
}

$villesHTML = '';


$codePostal = $_GET['code_postal'] ?? '';
?>

<article id=exo-1>
    <h2>Géolocalisation IP</h2>
    <p>
        Nous avons utilisé une API de géolocalisation IP pour détecter votre emplacement approximatif. Si les informations sont correctes, 
        vous pouvez pré-remplir les champs de sélection pour accéder rapidement aux prix du carburant de votre région.
    </p>
    <?php 
// Géolocalisation IP
$geoData = getGeolocationIP();

$regionindex = null;
$dernier_cp = '';
$dep = null;
$derniere_ville = '';

// Vérifier si le zip_code est valide (5 chiffres)
if ($geoData !== null && !empty($geoData['zip_code']) && strlen($geoData['zip_code']) === 5) {
    $dernier_cp = $geoData['zip_code'];
    $dep = substr($dernier_cp, 0, 2);
    $derniere_ville = $geoData['ville'];
    
    // Trouver l'index de la région à partir du département
    if (!empty($dep)) {
        foreach ($regionsDepartements as $index => $departements) {
            foreach ($departements as $dept) {
                if ($dept['id'] === $dep) {
                    $regionindex = $index;
                    break 2;
                }
            }
        }
    }
}

// Fallback: si pas de zip_code valide, utiliser Paris
if (empty($dernier_cp)) {
    $dernier_cp = '75001';
    $dep = '75';
    $regionindex = 4; // Île-de-France
    $derniere_ville = 'Paris';
}
?>
    <div class="geo-detected" role="alert">
        <p>Nous avons détecté que vous êtes à <b><?= htmlspecialchars($geoData['ville']) ?></b>, dans la région 
           <b><?= htmlspecialchars($geoData['region']) ?></b>. Est-ce correct ?</p>
        <form method="get" class="geo-form" action="stations.php">
            <input type="hidden" name="dep" value="<?= htmlspecialchars($dep) ?>">
            <input type="hidden" name="code_postal" value="<?= htmlspecialchars($dernier_cp) ?>">
            <input type="hidden" name="perimetre" value="<?= htmlspecialchars($perimetre) ?>">
            <?php foreach ($carburants as $carb): ?>
                <input type="hidden" name="carburants[]" value="<?= htmlspecialchars($carb) ?>">
            <?php endforeach; ?>
            <button type="submit" class="bouton-geo">Oui, pré-remplir</button>
        </form>
    </div>
</article>
<article id="exo-2">
    <h2>Carte de la France - Sélectionnez votre région</h2>

    <p>
        Cliquez sur une région de la carte pour voir les départements correspondants, puis sélectionnez votre ville pour afficher les prix du carburant.
    </p>


    <img src="images/carte_France.svg" alt="Carte de la France" usemap="#map_regions" style="max-width: 100%; height: auto;">

    <div id="tooltip-region" class="tooltip-region"></div>

    <map name="map_regions">
        <area target="" alt="Bretagne" title="Bretagne" href="carte.php?index=<?= $index = '0' ?>&lang=<?= $lang ?>&style=<?= $style ?>#departements" coords="44,294,95,356,208,403,285,357,297,288,266,280,246,270,165,256" shape="poly">
        <area target="" alt="Normandie" title="Normandie" href="carte.php?index=<?= $index = '1' ?>&lang=<?= $lang ?>&style=<?= $style ?>#departements" coords="242,175,268,276,412,318,455,250,470,223,472,176,450,143" shape="poly">
        <area target="" alt="Haut de france" title="Haut de france" href="carte.php?index=<?= $index = '2' ?>&lang=<?= $lang ?>&style=<?= $style ?>#departements" coords="452,141,472,175,471,222,556,232,579,261,587,214,613,209,626,155,616,117,520,46,462,61" shape="poly">
        <area target="" alt="Grand-Est" title="Grand-Est" href="carte.php?index=<?= $index = '3' ?>&lang=<?= $lang ?>&style=<?= $style ?>#departements" coords="573,302,577,262,588,242,588,217,617,210,627,156,661,135,868,246,822,386,807,385,777,346,742,338,715,366,692,376,673,364,657,337,608,343,587,320" shape="poly">
        <area target="" alt="Ile-de-France" title="Ile-de-France" href="carte.php?index=<?= $index = '4' ?>&lang=<?= $lang ?>&style=<?= $style ?>#departements" coords="460,253,463,277,486,309,503,307,519,317,520,327,537,326,551,305,572,301,575,259,558,236,492,226,472,228" shape="poly">
        <area target="" alt="Bourgogne" title="Bourgogne" href="carte.php?index=<?= $index = '5' ?>&lang=<?= $lang ?>&style=<?= $style ?>#departements" coords="802,385,775,348,747,342,711,371,682,376,662,358,656,339,604,347,572,304,552,307,548,322,550,344,552,449,609,475,605,499,660,502,671,477,689,480,715,489,739,483" shape="poly">
        <area target="" alt="Val-de-Loire" title="Val-de-Loire" href="carte.php?index=<?= $index = '6' ?>&lang=<?= $lang ?>&style=<?= $style ?>#departements" coords="369,414,377,377,422,325,457,257,483,309,508,311,515,324,545,329,549,444,504,480,437,484,403,430,385,431" shape="poly">
        <area target="" alt="Pays-de-Loire" title="Pays-de-Loire" href="carte.php?index=<?= $index = '7' ?>&lang=<?= $lang ?>&style=<?= $style ?>#departements" coords="211,406,285,496,329,486,305,427,367,414,372,376,412,324,300,291,290,361" shape="poly">
        <area target="" alt="Nouvelle-Aquitaine" title="Nouvelle-Aquitaine" href="carte.php?index=<?= $index = '8' ?>&lang=<?= $lang ?>&style=<?= $style ?>#departements" coords="252,749,346,796,362,735,351,703,416,682,456,605,495,609,524,525,504,485,437,489,404,434,382,431,370,419,311,429,335,484,293,498" shape="poly">
        <area target="" alt="Occitanie" title="Occitanie" href="carte.php?index=<?= $index = '9' ?>&lang=<?= $lang ?>&style=<?= $style ?>#departements" coords="351,795,367,739,356,703,419,687,458,612,499,635,535,613,547,639,573,611,616,663,648,667,661,696,645,719,625,737,560,827,483,832" shape="poly">
        <area target="" alt="Côte-d'Azur " title="Côte-d'Azur " href="carte.php?index=<?= $index = '10' ?>&lang=<?= $lang ?>&style=<?= $style ?>#departements" coords="628,740,667,694,657,672,715,680,698,656,768,598,798,633,838,685,748,779" shape="poly">
        <area target="" alt="Auvergne" title="Auvergne" href="carte.php?index=<?= $index = '11' ?>&lang=<?= $lang ?>&style=<?= $style ?>#departements" coords="762,597,697,650,705,671,658,665,620,660,573,604,549,627,538,606,502,624,499,607,529,523,509,481,547,447,600,473,602,505,661,503,677,484,714,491,784,480,804,569,802,589" shape="poly">
        <area target="" alt="Corse" title="Corse" href="carte.php?index=<?= $index = '12' ?>&lang=<?= $lang ?>&style=<?= $style ?>#departements" coords="889,825,944,771,959,851,933,923,904,899" shape="poly">
    </map>

    <?= $departementsHTML ?>
    <?= $villesHTML ?>
</article>

<script>
    document.querySelectorAll('map[name="map_regions"] area').forEach(area => {
        area.addEventListener('mouseenter', (e) => {
            const tooltip = document.getElementById('tooltip-region');
            tooltip.textContent = area.getAttribute('title');
            tooltip.style.display = 'block';
        });
        
        area.addEventListener('mousemove', (e) => {
            const tooltip = document.getElementById('tooltip-region');
            tooltip.style.left = e.pageX + 15 + 'px';
            tooltip.style.top = e.pageY + 15 + 'px';
        });
        
        area.addEventListener('mouseleave', () => {
            document.getElementById('tooltip-region').style.display = 'none';
        });
    });
</script>

<?php require_once 'include/footer.inc.php'; ?>