<?php
require_once __DIR__ . '/config.php';
$title = "Início - Sónia Cabeleireiro";
require_once ROOT_PATH . '/inc/header.php';
?>

<section class="hero reveal" style="background-image:url('<?= BASE_URL ?>/assets/img/hero.jpg');">
  <div class="hero-overlay">
    <div class="hero-content">
      <p class="hero-kicker">Profissionais</p>
      <h1>Realça o teu estilo</h1>
      <p>Descobre serviços, inspirações e faz o teu pedido de marcação online.</p>
      <a class="btn btn-primary" href="<?= BASE_URL ?>/pages/contactos.php#marcacao">Marcar agora</a>
    </div>
  </div>
</section>

<section class="home-section">
  <h2 class="section-title reveal">Serviços e Tratamentos</h2>

  <div class="tiles">
    <a class="tile reveal" href="<?= BASE_URL ?>/pages/corte.php">
      <img src="<?= BASE_URL ?>/assets/img/tiles/corte.jpg" alt="Cortes" />
      <div class="tile-body">
        <h3>Cortes</h3>
        <p>Clássicos, modernos e personalizados.</p>
      </div>
    </a>

    <a class="tile reveal" href="<?= BASE_URL ?>/pages/coloracao.php">
      <img src="<?= BASE_URL ?>/assets/img/tiles/coloracao.jpg" alt="Coloração" />
      <div class="tile-body">
        <h3>Coloração</h3>
        <p>Madeixas, balayage e cor completa.</p>
      </div>
    </a>

    <a class="tile reveal" href="<?= BASE_URL ?>/pages/tratamentos.php">
      <img src="<?= BASE_URL ?>/assets/img/tiles/tratamentos.jpg" alt="Tratamentos" />
      <div class="tile-body">
        <h3>Tratamentos</h3>
        <p>Hidratação, nutrição e reconstrução.</p>
      </div>
    </a>
  </div>

  <div class="center reveal">
    <a class="btn btn-dark" href="<?= BASE_URL ?>/pages/servicos.php">Conheça os nossos serviços</a>
  </div>
</section>

<section class="home-section">
  <h2 class="section-title reveal">Tipos de Cabelo</h2>

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
    <a class="btn btn-dark" href="<?= BASE_URL ?>/pages/tipos_cabelo.php">Ver todos os tipos</a>
  </div>
</section>

<?php require_once ROOT_PATH . '/inc/footer.php'; ?>