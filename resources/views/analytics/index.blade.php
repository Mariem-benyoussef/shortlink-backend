<!-- resources/views/analytics/index.blade.php -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Analytics Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>Google Analytics Dashboard</h1>

    <!-- Graphique : Stats par clic -->
    <h2>Stats par clic</h2>
    <canvas id="clickStatsChart" width="400" height="200"></canvas>

    <!-- Graphique : Stats par jour -->
    <h2>Stats par jour</h2>
    <canvas id="dailyStatsChart" width="400" height="200"></canvas>

    <!-- Graphique : Stats par source -->
    <h2>Stats par source</h2>
    <canvas id="sourceStatsChart" width="400" height="200"></canvas>

    <!-- Graphique : Stats par type d'appareil -->
    <h2>Stats par type d'appareil</h2>
    <canvas id="deviceStatsChart" width="400" height="200"></canvas>

    <!-- Graphique : Liens les plus performants -->
    <h2>Liens les plus performants</h2>
    <canvas id="topLinksChart" width="400" height="200"></canvas>

    <script>
        // Données pour les graphiques
        const clickStatsData = {
            labels: @json(array_column($clickStats, 0)), // Noms des événements
            datasets: [{
                label: 'Clics',
                data: @json(array_column($clickStats, 1)), // Nombre de clics
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        };

        const dailyStatsData = {
            labels: @json(array_column($dailyStats, 0)), // Dates
            datasets: [{
                label: 'Utilisateurs actifs',
                data: @json(array_column($dailyStats, 1)), // Utilisateurs actifs
                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        };

        const sourceStatsData = {
            labels: @json(array_column($sourceStats, 0)), // Sources
            datasets: [{
                label: 'Sessions',
                data: @json(array_column($sourceStats, 1)), // Sessions
                backgroundColor: 'rgba(255, 159, 64, 0.2)',
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1
            }]
        };

        const deviceStatsData = {
            labels: @json(array_column($deviceStats, 0)), // Types d'appareils
            datasets: [{
                label: 'Sessions',
                data: @json(array_column($deviceStats, 1)), // Sessions
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        };

        const topLinksData = {
            labels: @json(array_column($topLinks, 0)), // Liens
            datasets: [{
                label: 'Clics',
                data: @json(array_column($topLinks, 1)), // Clics
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        };

        // Créer les graphiques
        new Chart(document.getElementById('clickStatsChart'), { type: 'bar', data: clickStatsData });
        new Chart(document.getElementById('dailyStatsChart'), { type: 'line', data: dailyStatsData });
        new Chart(document.getElementById('sourceStatsChart'), { type: 'bar', data: sourceStatsData });
        new Chart(document.getElementById('deviceStatsChart'), { type: 'pie', data: deviceStatsData });
        new Chart(document.getElementById('topLinksChart'), { type: 'bar', data: topLinksData });
    </script>
</body>
</html>