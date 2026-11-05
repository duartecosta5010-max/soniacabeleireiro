<?php
require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/inc/admin_auth.php';
require_admin();

$title = "Admin - Painel";
require_once ROOT_PATH . '/inc/header.php';
?>

<h1>Painel de Administração</h1>

<div class="card">
  <p>Bem-vindo, <?= htmlspecialchars($_SESSION['username'] ?? 'admin') ?>.</p>
  <p>
    <a href="<?= BASE_URL ?>/admin/marcacoes.php">Ver marcações</a> |
    <a href="<?= BASE_URL ?>/admin/logout.php">Sair</a>
  </p>
</div>

<?php require_once ROOT_PATH . '/inc/footer.php'; ?>