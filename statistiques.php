<?php
declare(strict_types=1);

$pageTitle = 'Statistiques - Consultations';
$currentPage = 'statistiques';

require_once 'include/header.inc.php';
require_once 'include/fonction.inc.php';

$totalVisiteurs = getNombreTotalVisiteurs();
$topVilles = getTopVilles(10);
$topVilleNom = !empty($topVilles) ? array_key_first($topVilles) : 'Aucune ville';
$topVilleCount = !empty($topVilles) ? $topVilles[$topVilleNom] : 0;
?>

<article id="statistiques">
    <h2>Statistiques de consultation</h2>
    
    <div class="stats-cles" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin: 30px 0;">
        <div class="carte-stat" style="background: <?= $style === 'sombre' ? '#2a2a2a' : '#ffffff' ?>; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; border-left: 4px solid #cf5e26;">
            <div style="margin-bottom: 15px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cf5e26" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <p style="font-size: 0.9em; color: <?= $style === 'sombre' ? '#aaa' : '#666' ?>; margin-bottom: 10px;">Nombre total de consultations</p>
            <p style="font-size: 2.5em; font-weight: bold; color: #cf5e26;"><?= number_format($totalVisiteurs, 0, ',', ' ') ?></p>
        </div>
        
        <div class="carte-stat" style="background: <?= $style === 'sombre' ? '#2a2a2a' : '#ffffff' ?>; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; border-left: 4px solid #0055A4;">
            <div style="margin-bottom: 15px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#0055A4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
            </div>
            <p style="font-size: 0.9em; color: <?= $style === 'sombre' ? '#aaa' : '#666' ?>; margin-bottom: 10px;">Ville la plus consultée</p>
            <p style="font-size: 1.8em; font-weight: bold; color: #0055A4;"><?= htmlspecialchars($topVilleNom) ?></p>
            <p style="font-size: 1em; color: <?= $style === 'sombre' ? '#888' : '#888' ?>;"><?= $topVilleCount ?> consultations</p>
        </div>
    </div>
    
    <?php if (!empty($topVilles)): ?>
    <div class="stats-graphique" style="background: <?= $style === 'sombre' ? '#2a2a2a' : '#ffffff' ?>; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-top: 30px;">
        <h3 style="margin-bottom: 25px; color: <?= $style === 'sombre' ? '#eee' : '#333' ?>;">Top 10 des villes les plus consultées</h3>
        <div style="position: relative; height: 400px; width: 100%;">
            <canvas id="villesChart"></canvas>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
    (function() {
        const labels = <?php echo json_encode(array_keys($topVilles)); ?>;
        const data = <?php echo json_encode(array_values($topVilles)); ?>;
        
        const textColor = '<?php echo $style === 'sombre' ? '#cccccc' : '#333333'; ?>';
        const gridColor = '<?php echo $style === 'sombre' ? '#444444' : '#dddddd'; ?>';
        
        const ctx = document.getElementById('villesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Nombre de consultations',
                    data: data,
                    backgroundColor: '#cf5e26',
                    borderColor: '#a84d1e',
                    borderWidth: 2,
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: textColor,
                            stepSize: 1
                        },
                        grid: {
                            color: gridColor
                        }
                    },
                    x: {
                        ticks: {
                            color: textColor
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    })();
    </script>
    <?php else: ?>
    <p style="text-align: center; color: <?= $style === 'sombre' ? '#aaa' : '#666' ?>; margin-top: 30px;">Aucune donnée de consultation disponible.</p>
    <?php endif; ?>
</article>

<?php require_once 'include/footer.inc.php'; ?>