<?php
require_once __DIR__ . '/../config.php';
$title = "Pedido enviado - Sónia Cabeleireiro";
require_once ROOT_PATH . '/inc/header.php';

$id = (int)($_GET['id'] ?? 0);
$pedidoNum = $id > 0 ? str_pad((string)$id, 6, '0', STR_PAD_LEFT) : '';
?>

<main class="container">
  <div class="card">
    <h1>Pedido enviado</h1>
    <?php if ($pedidoNum): ?>
      <p>O teu pedido foi registado com o número <strong>#<?= htmlspecialchars($pedidoNum) ?></strong>.</p>
    <?php else: ?>
      <p>O teu pedido foi registado.</p>
    <?php endif; ?>
    <p style="color: var(--muted);">Vamos analisar a disponibilidade e entraremos em contacto para confirmar.</p>
    <p><a class="btn btn-primary" href="<?= BASE_URL ?>/">Voltar ao início</a></p>
  </div>
</main>

<?php require_once ROOT_PATH . '/inc/footer.php'; ?>