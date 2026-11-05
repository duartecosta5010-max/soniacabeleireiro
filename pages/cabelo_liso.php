<?php
require_once __DIR__ . '/../config.php';
$title = "Cabelo Liso - Sónia Cabeleireiro";
require_once ROOT_PATH . '/inc/header.php';
?>

<h1 class="section-title reveal">Cabelo Liso</h1>

<section class="card reveal">
  <p style="margin:0;color:var(--muted);line-height:1.7;">
    O cabelo liso tende a ter mais brilho e um aspeto uniforme. Pode ganhar facilmente oleosidade
    na raiz e “pesar” se não for cortado e finalizado com a técnica certa.
  </p>

  <ul class="clean-list" style="margin-top:14px;">
    <li><strong>Objetivo:</strong> leveza, movimento e brilho saudável</li>
    <li><strong>Cortes recomendados:</strong> camadas leves, long bob, reto bem definido</li>
    <li><strong>Cuidados:</strong> shampoo equilibrante + máscara leve nas pontas</li>
    <li><strong>Dica:</strong> evita excesso de produto na raiz para não perder volume</li>
  </ul>

  <div style="margin-top:16px; display:flex; gap:10px; flex-wrap:wrap;">
    <a class="btn btn-primary" href="<?= BASE_URL ?>/pages/contactos.php#marcacao">Pedir aconselhamento</a>
    <a class="btn btn-dark" href="<?= BASE_URL ?>/pages/tipos_cabelo.php">Ver todos os tipos</a>
  </div>
</section>

<section class="reveal" style="margin-top:34px; padding:10px 0 34px;">
  <div class="ba" data-ba>
    <div class="ba__layer ba__before">
      <img src="<?= BASE_URL ?>/assets/img/before-after/liso-antes.jpg" alt="Antes (cabelo liso)" loading="lazy">
    </div>

    <div class="ba__layer ba__after">
      <img src="<?= BASE_URL ?>/assets/img/before-after/liso-depois.jpg" alt="Depois (cabelo liso)" loading="lazy">
    </div>

    <div class="ba__line" aria-hidden="true"></div>

    <div class="ba__knob" aria-hidden="true">
      <span>‹ ›</span>
    </div>

    <input class="ba__range" type="range" min="0" max="100" value="50" aria-label="Arrastar para ver antes e depois">
  </div>

  <div class="ba-labels" aria-hidden="true">
    <span>Antes</span>
    <span>Depois</span>
  </div>
</section>

<?php require_once ROOT_PATH . '/inc/footer.php'; ?>