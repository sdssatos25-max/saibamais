<?php
$db = new PDO('sqlite:' . __DIR__ . '/cloaker.db');
$id = $_GET['id'] ?? 0;
$stmt = $db->prepare("SELECT * FROM campaigns WHERE id = :id");
$stmt->execute(['id' => $id]);
$campaign = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$campaign) {
    echo "Campanha não encontrada.";
    exit;
}

$metricsStmt = $db->prepare("SELECT * FROM metrics WHERE campaign_id = :id");
$metricsStmt->execute(['id' => $id]);
$metrics = $metricsStmt->fetch(PDO::FETCH_ASSOC);
$sources = json_decode($metrics['sources'], true) ?? [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Métricas - <?php echo $campaign['name']; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3"></script>
</head>
<body>
  <nav class="navbar navbar-expand-lg bg-light">
    <div class="container-fluid">
      <a class="navbar-brand" href="admin.php">Cloaker White Rabbit Educacional</a>
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="admin.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="campaigns.php">Campanhas</a></li>
      </ul>
    </div>
  </nav>
  <div class="container mt-4">
    <h1>Métricas da Campanha: <?php echo $campaign['name']; ?></h1>
    <canvas id="trafficChart" width="400" height="200"></canvas>
    <pre id="metrics"></pre>
  </div>
  <script>
    const ctx = document.getElementById('trafficChart').getContext('2d');
    const chart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode(array_keys($sources)); ?>,
        datasets: [{
          label: 'Tráfego por Fonte',
          data: <?php echo json_encode(array_values($sources)); ?>,
          backgroundColor: ['#4CAF50', '#2196F3', '#FF9800', '#F44336', '#9C27B0']
        }]
      },
      options: { scales: { y: { beginAtZero: true } } }
    });

    setInterval(() => {
      fetch(`get_metrics.php?id=<?php echo $id; ?>`)
        .then(response => response.json())
        .then(data => {
          document.getElementById('metrics').innerHTML = JSON.stringify(data, null, 2);
          chart.data.labels = Object.keys(data.sources || {});
          chart.data.datasets[0].data = Object.values(data.sources || {});
          chart.update();
        });
    }, 5000);
  </script>
</body>
</html>