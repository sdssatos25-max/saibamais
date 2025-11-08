<?php
session_start();
$users = require 'users.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    if (isset($users[$user]) && password_verify($pass, $users[$user])) {
        $_SESSION['auth'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Usuário ou senha inválidos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <style>
    body { background: #111; color: #fff; font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; }
    form { background: #222; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px #000; width: 300px; }
    input { display: block; margin: 10px 0; padding: 10px; border: none; border-radius: 5px; width: 100%; background: #333; color: #fff; }
    button { background: #00c853; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; width: 100%; }
  </style>
</head>
<body>
  <form method="post">
    <h2>Login</h2>
    <?php if (!empty($error)) echo "<p style='color: red;'>$error</p>"; ?>
    <input type="text" name="user" placeholder="Usuário" required>
    <input type="password" name="pass" placeholder="Senha" required>
    <button type="submit">Entrar</button>
  </form>
</body>
</html>