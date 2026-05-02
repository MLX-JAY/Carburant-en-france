<?php
declare(strict_types=1);
require_once 'include/header.inc.php';
require_once 'include/fonction.inc.php';

$currentPage = 'carte';


ini_set('memory_limit', '512M'); // Donne 512 Mo de RAM au serveur au lieu de 128


// Gestion de la dernière recherche...

// Gestion de la dernière recherche (Ville + Code Postal)
if (isset($_GET['ville']) && isset($_GET['code_postal'])) {
    $derniere_ville = $_GET['ville'];
    $dernier_cp = $_GET['code_postal'];
    
    setcookie('derniere_ville', $derniere_ville, time() + 3600*24*30);
    setcookie('dernier_cp', $dernier_cp, time() + 3600*24*30);
} else {
    $derniere_ville = $_COOKIE['derniere_ville'] ?? '';
    $dernier_cp = $_COOKIE['dernier_cp'] ?? '';
}
$lang = $_GET['lang'] ?? 'fr';
$index = $_GET['index'] ?? null;
$codePostal = $_GET['code_postal'] ?? '';
$afficher = $_GET['afficher'] ?? null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$tri = $_GET['tri'] ?? $_COOKIE['tri'] ?? 'prix_asc';
$perimetre = $_GET['perimetre'] ?? $_COOKIE['perimetre'] ?? 'ville';
$carburant = $_GET['carburant'] ?? $_COOKIE['carburant'] ?? 'Tous';

if ($afficher === 'prix' && !empty($codePostal)) {
    setcookie('tri', $tri, time() + 3600*24*30);
    setcookie('perimetre', $perimetre, time() + 3600*24*30);
    setcookie('carburant', $carburant, time() + 3600*24*30);
}

// Extraire le préfixe du département (2 premiers chiffres du code postal)
$dep = !empty($codePostal) ? substr($codePostal, 0, 2) : null;


// Bloc de rappel de la dernière recherche
if (!empty($derniere_ville) && !empty($dernier_cp)): ?>
    <div class="rappel-recherche" style="margin-bottom:20px; padding:15px; border-radius:8px;">
        <p>Votre dernière recherche : <strong><?= htmlspecialchars($derniere_ville) ?></strong></p>
        <a href="carte.php?afficher=prix&code_postal=<?= urlencode($dernier_cp) ?>&style=<?= $style ?>&lang=<?= $lang ?>#form-villes" class="bouton-rapide">
            Voir directement les prix à <?= htmlspecialchars($derniere_ville) ?>
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

// Si on a un code_postal mais pas de ville dans l'URL, trouver la ville correspondante au code postal
if (empty($derniere_ville) && !empty($codePostal) && $dep !== null) {
    $villesTemp = getVillesByDepartementFast($dep);
    foreach ($villesTemp as $nom => $code) {
        if ($code === $codePostal) {
            $derniere_ville = $nom;
            break;
        }
    }
}

if ($dep !== null) {
    $villes = getVillesByDepartementFast($dep); //array contenant les villes du département
    
    if (empty($villes)) {
        $villesHTML = '<p class="message-erreur">Aucune ville trouvée pour ce département.</p>';
    } else {
        ob_start();
        ?>
        <form method="get" class="form-villes" id="form-villes" action="stations.php">
            <input type="hidden" name="code_postal" value="" id="code_postal">
            <input type="hidden" name="afficher" value="prix">
            <input type="hidden" name="index" value="<?= htmlspecialchars($index ?? '') ?>">
            <input type="hidden" name="lang" value="<?= $lang ?>">
            <input type="hidden" name="style" value="<?= $style ?>">
            <label for="ville">Sélectionnez une ville :</label>
            <?php $villeNormalisee = normaliserChaine($derniere_ville ?? ''); ?>
            <select name="ville" id="ville">
                <?php foreach ($villes as $nom => $code): ?>
                    <option value="<?= htmlspecialchars($nom) ?>" data-code-postal="<?= htmlspecialchars($code) ?>" <?= (normaliserChaine($nom) === $villeNormalisee) ? 'selected' : '' ?>><?= htmlspecialchars($nom) ?> (<?= htmlspecialchars($code) ?>)</option>
                <?php endforeach; ?>
            </select>
            
            <div class="champ-formulaire" style="margin-top: 15px;">
                <label>Périmètre de recherche :</label>
                <div class="radio-options">
                    <label><input type="radio" name="perimetre" value="ville" <?= ($perimetre === 'ville') ? 'checked' : '' ?>> Uniquement cette ville</label>
                    <label><input type="radio" name="perimetre" value="environs" <?= ($perimetre === 'environs') ? 'checked' : '' ?>> Dans les environs</label>
                    <label><input type="radio" name="perimetre" value="departement" <?= ($perimetre === 'departement') ? 'checked' : '' ?>> Tout le département</label>
                </div>
            </div>
            
            <div class="champ-formulaire" style="margin-top: 15px; margin-bottom: 20px;">
                <label for="carburant">Filtrer par carburant :</label>
                <select name="carburant" id="carburant">
                    <option value="Tous" <?= ($carburant === 'Tous') ? 'selected' : '' ?>>Tous les carburants</option>
                    <option value="Gazole" <?= ($carburant === 'Gazole') ? 'selected' : '' ?>>Gazole</option>
                    <option value="E10" <?= ($carburant === 'E10') ? 'selected' : '' ?>>SP95-E10</option>
                    <option value="SP95" <?= ($carburant === 'SP95') ? 'selected' : '' ?>>SP95</option>
                    <option value="SP98" <?= ($carburant === 'SP98') ? 'selected' : '' ?>>SP98</option>
                    <option value="E85" <?= ($carburant === 'E85') ? 'selected' : '' ?>>Superéthanol (E85)</option>
                    <option value="GPLc" <?= ($carburant === 'GPLc') ? 'selected' : '' ?>>GPLc</option>
                </select>
            </div>
            
            <button type="submit" class="bouton-valider">Afficher les prix</button>
        </form>
        <script>
            document.getElementById('ville').addEventListener('change', function() { // Quand l'utilisateur change de ville, on met à jour le champ code_postal caché avec le code postal de la ville sélectionnée
                var selectedOption = this.options[this.selectedindex]; // Récupère l'option sélectionnée
                var codePostal = selectedOption.getAttribute('data-code-postal'); // Récupère le code postal depuis l'attribut data-code-postal de l'option
                document.getElementById('code_postal').value = codePostal; // Met à jour le champ caché code_postal avec la valeur du code postal de la ville sélectionnée
            });
            // quand la page charge, on pré-remplit le code postal correspondant à la ville sélectionnée
            var villeSelect = document.getElementById('ville');
            var selectedOption = villeSelect.options[villeSelect.selectedIndex];
            if (selectedOption) {
                document.getElementById('code_postal').value = selectedOption.getAttribute('data-code-postal');
            }
        </script>
        <?php
        $villesHTML = ob_get_clean();
    }
}

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
        <form method="get" class="geo-form" action="carte.php#exo-2">
            <input type="hidden" name="ville" value="<?= htmlspecialchars($geoData['ville'] ?? $derniere_ville) ?>">
            <input type="hidden" name="index" value="<?= $regionindex === false ? $regionindex='' : $regionindex ?>">
            <input type="hidden" name="code_postal" value="<?= htmlspecialchars(!empty($geoData['zip_code']) ? $geoData['zip_code'] : $dernier_cp) ?>">
            <input type="hidden" name="afficher" value="villes">
            <input type="hidden" name="lang" value="<?= $lang ?>">
            <input type="hidden" name="style" value="<?= $style ?>">
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