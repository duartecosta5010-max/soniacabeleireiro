<?php
require_once __DIR__ . '/../config.php';
$title = "Tipos de Cabelo - Sónia Cabeleireiro";
require_once ROOT_PATH . '/inc/header.php';
?>

<h1 class="section-title reveal">Tipos de Cabelo</h1>

<section class="card reveal">
  <p style="margin:0;color:var(--muted);line-height:1.7;">
    Cada tipo de cabelo precisa de cuidados e técnicas específicas. Escolhe o teu tipo para veres
    sugestões de cortes, rotinas e recomendações.
  </p>
</section>

<section class="home-section" style="padding-top:28px;">
  <div class="tiles">
    <a class="tile reveal" href="<?= BASE_URL ?>/pages/cabelo_liso.php">
      <img src="<?= BASE_URL ?>/assets/img/tiles/liso.jpg" alt="Cabelo liso" />
      <div class="tile-body">
        <h3>Liso</h3>
        <p>Sugestões de cortes e cuidados.</p>
      </div>
    </a>

    <a class="tile reveal" href="<?= BASE_URL ?>/pages/cabelo_ondulado.php">
      <img src="<?= BASE_URL ?>/assets/img/tiles/ondulado.jpg" alt="Cabelo ondulado" />
      <div class="tile-body">
        <h3>Ondulado</h3>
        <p>Definição e manutenção do movimento.</p>
      </div>
    </a>

    <a class="tile reveal" href="<?= BASE_URL ?>/pages/cabelo_encaracolado.php">
      <img src="<?= BASE_URL ?>/assets/img/tiles/encaracolado.jpg" alt="Cabelo encaracolado" />
      <div class="tile-body">
        <h3>Encaracolado</h3>
        <p>Hidratação, rotina e finalização.</p>
      </div>
    </a>
  </div>

  <div class="center reveal">
    <a class="btn btn-primary" href="<?= BASE_URL ?>/pages/contactos.php#marcacao">Pedir aconselhamento</a>
  </div>
</section>

<?php require_once ROOT_PATH . '/inc/footer.php'; ?>