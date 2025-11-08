<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['username'] === ADMIN_USER && hash('sha256', $_POST['password']) === ADMIN_PASS_HASH) {
        $_SESSION['logged_in'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = "Credenciais inválidas.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Cloaker</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
      overflow: hidden;
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
    .login-container {
      background: var(--bg-secondary);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 28px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.4);
      padding: 3rem 2rem;
      width: 380px;
      text-align: center;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      position: relative;
      z-index: 1;
    }
    .login-container:hover {
      transform: scale(1.02);
      box-shadow: 0 12px 48px rgba(0,0,0,0.5);
    }
    .login-title {
      font-size: 1.75rem;
      font-weight: 700;
      margin-bottom: 2rem;
      text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }
    .form-control {
      background: var(--glass-bg);
      border: none;
      color: var(--text-primary);
      border-radius: 14px;
      padding: 0.9rem 1rem;
      margin-bottom: 1.5rem;
      font-size: 1rem;
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
      padding: 0.9rem;
      transition: background 0.2s, transform 0.2s;
    }
    .btn-primary:hover {
      background: #005bb5;
      transform: translateY(-2px);
    }
    .alert-danger {
      background: #ff453a;
      color: #fff;
      border-radius: 14px;
      padding: 0.75rem;
      margin-bottom: 1.5rem;
      font-size: 0.875rem;
      animation: shake 0.5s;
    }
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%, 60% { transform: translateX(-5px); }
      40%, 80% { transform: translateX(5px); }
    }
    @media (max-width: 576px) {
      .login-container {
        width: 90%;
        padding: 2rem 1.5rem;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2 class="login-title">Login</h2>
    <?php if (isset($error)): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="mb-3">
        <input type="text" class="form-control" name="username" placeholder="Usuário" required>
      </div>
      <div class="mb-3">
        <input type="password" class="form-control" name="password" placeholder="Senha" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>
  </div>
</body>
</html>