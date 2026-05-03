<?php
declare(strict_types=1);

$pageTitle = 'Stations de carburant';
$currentPage = 'stations';

require_once 'include/fonction.inc.php';

ini_set('memory_limit', '512M');

// Récupération des paramètres EN PREMIER
$codePostal = $_GET['code_postal'] ?? $_COOKIE['dernier_cp'] ?? '';
$dep = $_GET['dep'] ?? null;
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

// --- SETUP DEPARTEMENT ET VILLE ---
// Initialisation défensive de la variable $villes
$villes = [];
if (!empty($dep)) {
    $villesTrouvees = getVillesByDepartementFast($dep);
    $villes = is_array($villesTrouvees) ? $villesTrouvees : [];
}

// --- VALIDATION ET SANITIZATION ---
$erreurFiltre = null;

// Si le périmètre est le département, on purge le code postal
if ($perimetre === 'departement') {
    $codePostal = '';
}

// Si on arrive via un département sans code postal, on force le département
if (!empty($dep) && empty($codePostal)) {
    $perimetre = 'departement';
}

// Si périmètre restreint mais aucun code postal n'est fourni : on bloque la requête SQL
if (($perimetre === 'ville' || $perimetre === 'environs') && empty($codePostal)) {
    $erreurFiltre = "Erreur : Vous devez sélectionner une ville pour ce périmètre de recherche.";
}

// Maintenant on peut inclure le header (HTML commence ici)
require_once 'include/header.inc.php';

// Paramètres pour le lien de retour
$retourParams = [
    'code_postal' => $codePostal,
    'perimetre' => $perimetre,
    'tri' => $tri,
];
$retourUrl = 'carte.php?' . http_build_query($retourParams);
foreach ($carburants as $carb) {
    $retourUrl .= '&carburants[]=' . urlencode($carb);
}

// Préparer le code postal pour la recherche
$codePostalRecherche = $codePostal;
if ($perimetre === 'departement' && !empty($dep)) {
    $codePostalRecherche = $dep . '000';
}
?>

<article id="stations-page">
    <div class="retour-carte">
        <a href="<?= htmlspecialchars($retourUrl) ?>" class="bouton-retour">
            ← Retour à la carte
        </a>
    </div>

    <form method="get" action="stations.php" class="form-stations">
        <?php if (!empty($dep)): ?>
            <input type="hidden" name="dep" value="<?= htmlspecialchars($dep) ?>">
        <?php endif; ?>

        <div style="margin-bottom: 15px;">
            <strong>Périmètre :</strong>
            <label><input type="radio" name="perimetre" value="ville" <?= $perimetre === 'ville' ? 'checked' : '' ?>> Ville</label>
            <label><input type="radio" name="perimetre" value="environs" <?= $perimetre === 'environs' ? 'checked' : '' ?>> Environs</label>
            <label><input type="radio" name="perimetre" value="departement" <?= $perimetre === 'departement' ? 'checked' : '' ?>> Département</label>
        </div>

        <div id="bloc-ville" style="margin-bottom: 15px;">
            <strong>Sélectionnez une ville :</strong>
            <select name="code_postal" id="select-ville" class="select-ville">
                <?php if (empty($villes)): ?>
                    <option value="">Aucune ville disponible</option>
                <?php else: ?>
                    <?php foreach ($villes as $nom => $code): ?>
                        <option value="<?= htmlspecialchars($code) ?>" <?= $code === $codePostal ? 'selected' : '' ?>>
                            <?= htmlspecialchars($nom) ?> (<?= htmlspecialchars($code) ?>)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <strong>Carburants :</strong>
            <label><input type="checkbox" name="carburants[]" value="Tous" <?= in_array('Tous', $carburants) ? 'checked' : '' ?>> Tous</label>
            <label><input type="checkbox" name="carburants[]" value="Gazole" <?= in_array('Gazole', $carburants) ? 'checked' : '' ?>> Gazole</label>
            <label><input type="checkbox" name="carburants[]" value="E10" <?= in_array('E10', $carburants) ? 'checked' : '' ?>> E10</label>
            <label><input type="checkbox" name="carburants[]" value="SP95" <?= in_array('SP95', $carburants) ? 'checked' : '' ?>> SP95</label>
            <label><input type="checkbox" name="carburants[]" value="SP98" <?= in_array('SP98', $carburants) ? 'checked' : '' ?>> SP98</label>
            <label><input type="checkbox" name="carburants[]" value="E85" <?= in_array('E85', $carburants) ? 'checked' : '' ?>> E85</label>
            <label><input type="checkbox" name="carburants[]" value="GPLc" <?= in_array('GPLc', $carburants) ? 'checked' : '' ?>> GPLc</label>
        </div>

        <button type="submit">Appliquer les filtres</button>
    </form>

    <?php if ($erreurFiltre): ?>
        <p class="message-erreur"><?= htmlspecialchars($erreurFiltre) ?></p>
    <?php elseif (!empty($codePostalRecherche)): ?>
        <?= genererHtmlStations($codePostalRecherche, $perimetre, $carburants, $tri, $page) ?>
    <?php endif; ?>
</article>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('input[name="perimetre"]');
    const blocVille = document.getElementById('bloc-ville');
    const selectVille = document.getElementById('select-ville');

    function gererAffichageVille() {
        const perimetreActuel = document.querySelector('input[name="perimetre"]:checked')?.value;
        if (perimetreActuel === 'departement') {
            blocVille.style.display = 'none';
            selectVille.disabled = true;
        } else {
            blocVille.style.display = 'block';
            selectVille.disabled = false;
        }
    }

    radios.forEach(radio => radio.addEventListener('change', gererAffichageVille));
    gererAffichageVille();
});
</script>

<?php require_once 'include/footer.inc.php'; ?>
