<?php
declare(strict_types=1);

$pageTitle = 'Carte de la France - Choix de votre ville';
$pageDescription = 'Bienvenue sur le site du projet de développement web - CY Cergy Paris Université';
$currentPage = 'carte';
$pageAuthor = 'ANURAJAN Thenuxshan, FERAOUN Mohamed Amine';

require_once 'include/header.inc.php';

// Initialize $lang from GET parameter or default to 'fr'
$lang = $_GET['lang'] ?? 'fr';
?>

<img src="images/carte_France.svg" alt="Carte de la France" usemap="#map_regions" style="max-width: 100%; height: auto;">

<map name="map_regions">
    <area target="" alt="Bretagne" title="Bretagne" href="" coords="44,294,95,356,208,403,285,357,297,288,266,280,246,270,165,256" shape="poly">
    <area target="" alt="Normandie" title="Normandie" href="" coords="242,175,268,276,412,318,455,250,470,223,472,176,450,143" shape="poly">
    <area target="" alt="Haut de france" title="Haut de france" href="" coords="452,141,472,175,471,222,556,232,579,261,587,214,613,209,626,155,616,117,520,46,462,61" shape="poly">
    <area target="" alt="Grand-Est" title="Grand-Est" href="" coords="573,302,577,262,588,242,588,217,617,210,627,156,661,135,868,246,822,386,807,385,777,346,742,338,715,366,692,376,673,364,657,337,608,343,587,320" shape="poly">
    <area target="" alt="Ile-de-France" title="Ile-de-France" href="" coords="460,253,463,277,486,309,503,307,519,317,520,327,537,326,551,305,572,301,575,259,558,236,492,226,472,228" shape="poly">
    <area target="" alt="Bourgogne" title="Bourgogne" href="" coords="802,385,775,348,747,342,711,371,682,376,662,358,656,339,604,347,572,304,552,307,548,322,550,344,552,449,609,475,605,499,660,502,671,477,689,480,715,489,739,483" shape="poly">
    <area target="" alt="Val-de-Loire" title="Val-de-Loire" href="" coords="369,414,377,377,422,325,457,257,483,309,508,311,515,324,545,329,549,444,504,480,437,484,403,430,385,431" shape="poly">
    <area target="" alt="Pays-de-Loire" title="Pays-de-Loire" href="" coords="211,406,285,496,329,486,305,427,367,414,372,376,412,324,300,291,290,361" shape="poly">
    <area target="" alt="Nouvelle-Aquitaine" title="Nouvelle-Aquitaine" href="" coords="252,749,346,796,362,735,351,703,416,682,456,605,495,609,524,525,504,485,437,489,404,434,382,431,370,419,311,429,335,484,293,498" shape="poly">
    <area target="" alt="Occitanie" title="Occitanie" href="" coords="351,795,367,739,356,703,419,687,458,612,499,635,535,613,547,639,573,611,616,663,648,667,661,696,645,719,625,737,560,827,483,832" shape="poly">
    <area target="" alt="Côte-d'Azur " title="Côte-d'Azur " href="" coords="628,740,667,694,657,672,715,680,698,656,768,598,798,633,838,685,748,779" shape="poly">
    <area target="" alt="Auvergne" title="Auvergne" href="" coords="762,597,697,650,705,671,658,665,620,660,573,604,549,627,538,606,502,624,499,607,529,523,509,481,547,447,600,473,602,505,661,503,677,484,714,491,784,480,804,569,802,589" shape="poly">
    <area target="" alt="Corse" title="Corse" href="" coords="889,825,944,771,959,851,933,923,904,899" shape="poly">
</map>

<?php require_once 'include/footer.inc.php'; ?>