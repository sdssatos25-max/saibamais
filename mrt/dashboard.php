<?php
session_start();
if (!isset($_SESSION['auth'])) {
    header('Location: login.php');
    exit;
}

// Logout handler
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Lê o config atual
$configFile = 'redirect_config.json';
$config = json_decode(file_get_contents($configFile), true);

// Atualiza config se enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config['offer_url'] = $_POST['offer_url'] ?? $config['offer_url'];
    $config['fake_url'] = $_POST['fake_url'] ?? $config['fake_url'];
    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
    header("Location: dashboard.php?success=1");
    exit;
}

// Lê os logs
$logs = file_exists('logs.json') ? json_decode(file_get_contents('logs.json'), true) : [];

// Organiza os dados para os gráficos
$paises = [];
$dispositivos = [];
$horas = [];
foreach ($logs as $log) {
    $paises[$log['country']] = ($paises[$log['country']] ?? 0) + 1;
    $dispositivos[$log['device']] = ($dispositivos[$log['device']] ?? 0) + 1;
    $hora = date('H', strtotime($log['timestamp']));
    $horas[$hora] = ($horas[$hora] ?? 0) + 1;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: url('https://www.transparenttextures.com/patterns/dark-mosaic.png'), #0d1117;
            font-family: 'Segoe UI', sans-serif;
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 900px;
        }
        .glass {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            margin-bottom: 30px;
        }
        input, button {
            padding: 12px;
            margin: 8px 0;
            border: none;
            border-radius: 8px;
            width: 100%;
            box-sizing: border-box;
        }
        input {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        button {
            background-color: #00c853;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        canvas {
            width: 100% !important;
            height: auto !important;
        }
        h1, h2 {
            text-align: center;
        }
        .logout {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }
        .logout a {
            color: #f44336;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="logout">
        <a href="?logout=1">Sair</a>
    </div>

    <h1>Dashboard</h1>

    <div class="glass">
        <h2>Editar URLs</h2>
        <?php if (isset($_GET['success'])) echo "<p style='color:#0f0'>Atualizado com sucesso!</p>"; ?>
        <form method="post">
            <label>URL da Oferta</label>
            <input type="url" name="offer_url" value="<?= htmlspecialchars($config['offer_url']) ?>" required>
            <label>URL Fake</label>
            <input type="url" name="fake_url" value="<?= htmlspecialchars($config['fake_url']) ?>" required>
            <button type="submit">Salvar</button>
        </form>
    </div>

    <div class="glass">
        <h2>Acessos por País</h2>
        <canvas id="chartPais"></canvas>
    </div>

    <div class="glass">
        <h2>Acessos por Dispositivo</h2>
        <canvas id="chartDispositivo"></canvas>
    </div>

    <div class="glass">
        <h2>Acessos por Hora</h2>
        <canvas id="chartHora"></canvas>
    </div>
</div>

<script>
const paises = <?= json_encode($paises) ?>;
const dispositivos = <?= json_encode($dispositivos) ?>;
const horas = <?= json_encode($horas) ?>;

new Chart(document.getElementById("chartPais"), {
    type: 'pie',
    data: {
        labels: Object.keys(paises),
        datasets: [{ data: Object.values(paises), backgroundColor: ['#f44336', '#2196f3', '#4caf50', '#ff9800'] }]
    }
});

new Chart(document.getElementById("chartDispositivo"), {
    type: 'bar',
    data: {
        labels: Object.keys(dispositivos),
        datasets: [{ label: 'Dispositivos', data: Object.values(dispositivos), backgroundColor: '#4caf50' }]
    }
});

new Chart(document.getElementById("chartHora"), {
    type: 'line',
    data: {
        labels: Object.keys(horas),
        datasets: [{ label: 'Acessos por Hora', data: Object.values(horas), borderColor: '#00c853', fill: false }]
    }
});
</script>

</body>
</html>