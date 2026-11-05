<?php
require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/inc/db.php';
require_once ROOT_PATH . '/inc/session.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = :u LIMIT 1");
  $stmt->execute([':u' => $username]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['username'] = $user['username'];

    header("Location: " . BASE_URL . "/admin/index.php");
    exit;
  } else {
    $erro = "Login inválido.";
  }
}

$title = "Admin - Login";
require_once ROOT_PATH . '/inc/header.php';
?>

<h1>Admin - Login</h1>

<form class="card" method="post">
  <?php if ($erro): ?>
    <p style="color:red;"><strong><?= htmlspecialchars($erro) ?></strong></p>
  <?php endif; ?>

  <label>Utilizador<br>
    <input type="text" name="username" required>
  </label>
  <br><br>

  <label>Password<br>
    <input type="password" name="password" required>
  </label>
  <br><br>

  <button type="submit">Entrar</button>
</form>

<?php require_once ROOT_PATH . '/inc/footer.php'; ?>