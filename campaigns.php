<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

$db = new PDO('sqlite:' . __DIR__ . '/cloaker.db');

// Criar campanha
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("INSERT INTO campaigns (name, domain, white_url, black_url) VALUES (:name, :domain, :white_url, :black_url)");
    $stmt->execute([
        'name' => $_POST['name'],
        'domain' => $_POST['domain'],
        'white_url' => $_POST['white_url'],
        'black_url' => $_POST['black_url']
    ]);
    $id = $db->lastInsertId();
    $db->exec("INSERT INTO metrics (campaign_id) VALUES ($id)");
    header("Location: campaigns.php");
    exit;
}

// Listar
$stmt = $db->query("SELECT * FROM campaigns");
$campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Campanhas - Cloaker Educacional</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --bg-primary: #000000;
      --bg-secondary: #1c1c1e;
      --text-primary: #f2f2f7;
      --text-secondary: #a2a2a7;
      --accent: #007aff;
      --font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    body {
      background: var(--bg-primary);
      color: var(--text-primary);
      font-family: var(--font-family);
    }
    .navbar {
      background: var(--bg-secondary) !important;
      box-shadow: 0 1px 0 rgba(255,255,255,0.05);
    }
    .navbar-brand, .nav-link {
      color: var(--text-primary) !important;
      font-weight: 500;
    }
    .nav-link:hover {
      color: var(--accent) !important;
    }
    .container {
      max-width: 1200px;
    }
    h1 {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 2rem;
    }
    .btn-success {
      background: #34c759;
      border: none;
      border-radius: 14px;
      font-weight: 600;
      transition: background 0.2s;
    }
    .btn-success:hover {
      background: #2db74f;
    }
    .table {
      background: var(--bg-secondary);
      border-radius: 20px;
      overflow: hidden;
    }
    .table th {
      background: #2c2c2e;
      color: var(--text-secondary);
      font-weight: 600;
      border: none;
    }
    .table td {
      color: var(--text-primary);
      border-top: 1px solid #333;
    }
    .btn-primary {
      background: var(--accent);
      border: none;
      border-radius: 12px;
      font-weight: 600;
      transition: background 0.2s;
    }
    .btn-primary:hover {
      background: #0066d6;
    }
    .modal-content {
      background: var(--bg-secondary);
      border-radius: 28px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    .modal-header {
      border: none;
      color: var(--text-primary);
    }
    .form-control {
      background: #2c2c2e;
      border: none;
      color: var(--text-primary);
      border-radius: 14px;
      padding: 0.75rem 1rem;
    }
    .form-control:focus {
      box-shadow: 0 0 0 2px var(--accent);
    }
    .btn-close {
      filter: invert(1);
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand" href="admin.php">Cloaker White Rabbit Educacional</a>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="admin.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="campaigns.php">Campanhas</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Sair</a></li>
      </ul>
    </div>
  </nav>
  <div class="container mt-5">
    <h1>Campanhas</h1>
    <button class="btn btn-success mb-4" data-bs-toggle="modal" data-bs-target="#createModal">Criar Nova</button>
    <table class="table">
      <thead>
        <tr>
          <th>Nome</th>
          <th>Domínio</th>
          <th>White URL</th>
          <th>Black URL</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($campaigns as $camp): ?>
          <tr>
            <td><?php echo $camp['name']; ?></td>
            <td><?php echo $camp['domain']; ?></td>
            <td><?php echo $camp['white_url']; ?></td>
            <td><?php echo $camp['black_url']; ?></td>
            <td><a href="metrics.php?id=<?php echo $camp['id']; ?>" class="btn btn-primary btn-sm">Ver Métricas</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Modal -->
    <div class="modal fade" id="createModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Criar Campanha</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form method="POST">
              <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" class="form-control" name="name" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Domínio</label>
                <input type="text" class="form-control" name="domain" required>
              </div>
              <div class="mb-3">
                <label class="form-label">White URL</label>
                <input type="url" class="form-control" name="white_url" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Black URL</label>
                <input type="url" class="form-control" name="black_url" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">Criar</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>