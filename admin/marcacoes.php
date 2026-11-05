<?php
require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/inc/db.php';
require_once ROOT_PATH . '/inc/admin_auth.php';
require_admin();

$stmt = $pdo->query("SELECT * FROM appointments ORDER BY created_at DESC");
$marcacoes = $stmt->fetchAll();

$title = "Admin - Marcações";
require_once ROOT_PATH . '/inc/header.php';
?>

<h1>Marcações (Pedidos)</h1>

<div class="card">
  <p>
    <a href="<?= BASE_URL ?>/admin/index.php">Voltar</a> |
    <a href="<?= BASE_URL ?>/admin/logout.php">Sair</a>
  </p>
</div>

<?php foreach ($marcacoes as $m): ?>
  <div class="card" style="margin-top:12px;">
    <p><strong>#<?= (int)$m['id'] ?></strong> — <?= htmlspecialchars($m['estado']) ?></p>
    <p><strong>Nome:</strong> <?= htmlspecialchars($m['nome']) ?></p>
    <p><strong>Telemóvel:</strong> <?= htmlspecialchars($m['telemovel']) ?></p>
    <p><strong>Serviço:</strong> <?= htmlspecialchars($m['servico']) ?></p>
    <p><strong>Data/Hora:</strong> <?= htmlspecialchars($m['data']) ?> <?= htmlspecialchars($m['hora']) ?></p>
    <?php if (!empty($m['obs'])): ?>
      <p><strong>Obs:</strong> <?= nl2br(htmlspecialchars($m['obs'])) ?></p>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>/admin/marcacao_estado.php" style="display:flex; gap:10px; flex-wrap:wrap;">
      <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
      <button type="submit" name="estado" value="aceite">Aceitar</button>
      <button type="submit" name="estado" value="recusado">Recusar</button>
      <button type="submit" name="estado" value="pendente">Pendente</button>
    </form>
  </div>
<?php endforeach; ?>

<?php require_once ROOT_PATH . '/inc/footer.php'; ?>