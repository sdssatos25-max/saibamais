<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

$db = new PDO('sqlite:' . __DIR__ . '/cloaker.db');

// Atualizar settings se POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("UPDATE settings SET white_url = :white_url, black_url = :black_url, facebook_pixel_id = :facebook_pixel_id WHERE id = 1");
    $stmt->execute([
        'white_url' => $_POST['white_url'],
        'black_url' => $_POST['black_url'],
        'facebook_pixel_id' => $_POST['facebook_pixel_id']
    ]);
    $_SESSION['toast'] = "Configurações salvas com sucesso!";
}

// Buscar settings
$stmt = $db->prepare("SELECT * FROM settings WHERE id = 1");
$stmt->execute();
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar metrics
$metricsStmt = $db->query("SELECT * FROM metrics");
$metrics = $metricsStmt->fetch(PDO::FETCH_ASSOC);
$sources = json_decode($metrics['sources'], true) ?? [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Cloaker</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3"></script>
  <style>
    :root {
      --bg-primary: #0a0a0b;
      --bg-secondary: rgba(28,28,30,0.85);
      --text-primary: #f5f5f7;
      --text-secondary: #a2a2a7;
      --accent: #0071e3;
      --font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      --glass-bg: rgba(255,255,255,0.05);
      --glass-border: rgba(255,255,255,0.1);
    }
    body {
      background: var(--bg-primary);
      color: var(--text-primary);
      font-family: var(--font-family);
      margin: 0;
      padding: 0;
      overflow-x: hidden;
    }
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: radial-gradient(circle at 20% 20%, rgba(0,113,227,0.15), transparent 50%), radial-gradient(circle at 80% 80%, rgba(0,113,227,0.15), transparent 50%);
      animation: holographic 20s linear infinite;
      z-index: -1;
    }
    @keyframes holographic {
      0% { opacity: 0.4; }
      50% { opacity: 0.8; }
      100% { opacity: 0.4; }
    }
    .navbar {
      background: var(--bg-secondary) !important;
      backdrop-filter: blur(20px);
      border-bottom: 1px solid var(--glass-border);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .navbar-brand, .nav-link {
      color: var(--text-primary) !important;
      font-weight: 500;
      transition: color 0.2s;
    }
    .nav-link:hover {
      color: var(--accent) !important;
    }
    .container {
      max-width: 1200px;
      padding: 2rem;
    }
    h1 {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 2rem;
      text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }
    .card {
      background: var(--glass-bg);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    }
    .card-title {
      font-size: 1rem;
      font-weight: 600;
      color: var(--text-secondary);
    }
    .card-text {
      font-size: 2rem;
      font-weight: 700;
    }
    canvas {
      background: var(--glass-bg);
      backdrop-filter: blur(20px);
      border-radius: 20px;
      padding: 1rem;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    pre {
      background: var(--glass-bg) !important;
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      color: var(--text-primary);
      border-radius: 20px;
      padding: 1.5rem;
      font-size: 0.875rem;
      white-space: pre-wrap;
      overflow: auto;
    }
    .form-control {
      background: var(--glass-bg);
      border: none;
      color: var(--text-primary);
      border-radius: 14px;
      padding: 0.9rem 1rem;
      transition: box-shadow 0.3s, transform 0.2s;
    }
    .form-control:focus {
      box-shadow: 0 0 0 3px var(--accent);
      transform: translateY(-2px);
    }
    .btn-primary {
      background: var(--accent);
      border: none;
      border-radius: 14px;
      font-weight: 600;
      padding: 0.9rem 1.5rem;
      transition: background 0.2s, transform 0.2s;
    }
    .btn-primary:hover {
      background: #005bb5;
      transform: translateY(-2px);
    }
    .toast {
      background: var(--bg-secondary);
      border: 1px solid var(--glass-border);
      color: var(--text-primary);
      border-radius: 14px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    @media (max-width: 768px) {
      h1 { font-size: 2rem; }
      .card { padding: 1.5rem; }
      .card-text { font-size: 1.5rem; }
      .container { padding: 1.5rem; }
    }
    @media (max-width: 576px) {
      h1 { font-size: 1.75rem; }
      .card { padding: 1rem; }
      .card-text { font-size: 1.25rem; }
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand" href="admin.php">Cloaker</a>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="logout.php">Sair</a></li>
      </ul>
    </div>
  </nav>
  <div class="container mt-5">
    <h1>Dashboard</h1>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card p-4">
          <h5 class="card-title">Total de Visitantes</h5>
          <p class="card-text"><?php echo $metrics['totalVisitors'] ?? 0; ?></p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-4">
          <h5 class="card-title">Bots Bloqueados</h5>
          <p class="card-text"><?php echo $metrics['botsBlocked'] ?? 0; ?></p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-4">
          <h5 class="card-title">Pass-Through</h5>
          <p class="card-text"><?php echo $metrics['passThrough'] ?? 0; ?></p>
        </div>
      </div>
    </div>
    <h2 class="mt-5">Tráfego por Fonte</h2>
    <canvas id="trafficChart"></canvas>
    <pre id="metrics" class="mt-4"></pre>
    <h2 class="mt-5">Configurações da Campanha</h2>
    <form method="POST" id="settingsForm">
      <div class="mb-3">
        <label class="form-label">White URL</label>
        <input type="url" class="form-control" name="white_url" value="<?php echo $settings['white_url']; ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Black URL</label>
        <input type="url" class="form-control" name="black_url" value="<?php echo $settings['black_url']; ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Facebook Pixel ID</label>
        <input type="text" class="form-control" name="facebook_pixel_id" value="<?php echo $settings['facebook_pixel_id']; ?>">
      </div>
      <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </form>
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
      <?php if (isset($_SESSION['toast'])): ?>
        <div class="toast show" role="alert" data-bs-autohide="true" data-bs-delay="3000">
          <div class="toast-body"><?php echo $_SESSION['toast']; ?></div>
        </div>
        <?php unset($_SESSION['toast']); ?>
      <?php endif; ?>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
      options: {
        scales: {
          y: { beginAtZero: true, grid: { color: '#333' }, ticks: { color: '#e0e0e0' } },
          x: { grid: { color: '#333' }, ticks: { color: '#e0e0e0' } }
        },
        plugins: {
          legend: { labels: { color: '#e0e0e0' } }
        }
      }
    });

    setInterval(() => {
      fetch(`get_metrics.php`)
        .then(response => response.json())
        .then(data => {
          document.getElementById('metrics').innerHTML = JSON.stringify(data, null, 2);
          chart.data.labels = Object.keys(data.sources || {});
          chart.data.datasets[0].data = Object.values(data.sources || {});
          chart.update();
        });
    }, 5000);

    // Validação client-side
    document.getElementById('settingsForm').addEventListener('submit', function(e) {
      const whiteUrl = this.white_url.value;
      const blackUrl = this.black_url.value;
      const urlPattern = /^(https?:\/\/)/;
      if (!urlPattern.test(whiteUrl) || !urlPattern.test(blackUrl)) {
        e.preventDefault();
        alert('Por favor, insira URLs válidas começando com http:// ou https://');
      }
    });
  </script>
</body>
</html>