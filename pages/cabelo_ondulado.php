<?php
require_once __DIR__ . '/../config.php';
$title = "Cabelo Ondulado - Sónia Cabeleireiro";
require_once ROOT_PATH . '/inc/header.php';
?>

<h1 class="section-title reveal">Cabelo Ondulado</h1>

<section class="card reveal">
  <p style="margin:0;color:var(--muted);line-height:1.7;">
    O ondulado precisa de equilíbrio: hidratação suficiente para reduzir frizz, mas sem pesar
    para manter a onda definida e com movimento.
  </p>

  <ul class="clean-list" style="margin-top:14px;">
    <li><strong>Objetivo:</strong> definição + controlo de frizz + leveza</li>
    <li><strong>Cortes recomendados:</strong> camadas médias, corte em U, long bob</li>
    <li><strong>Cuidados:</strong> hidratação regular e finalização com creme/gel leve</li>
    <li><strong>Dica:</strong> seca com difusor ou ao natural e amassa a onda (“scrunch”)</li>
  </ul>

  <div style="margin-top:16px; display:flex; gap:10px; flex-wrap:wrap;">
    <a class="btn btn-primary" href="<?= BASE_URL ?>/pages/contactos.php#marcacao">Pedir aconselhamento</a>
    <a class="btn btn-dark" href="<?= BASE_URL ?>/pages/tipos_cabelo.php">Ver todos os tipos</a>
  </div>
</section>

<section class="reveal" style="margin-top:34px; padding:10px 0 34px;">
  <div class="ba" data-ba>
    <div class="ba__layer ba__before">
      <img src="<?= BASE_URL ?>/assets/img/before-after/ondulado-antes.jpg" alt="Antes (cabelo ondulado)" loading="lazy">
    </div>

    <div class="ba__layer ba__after">
      <img src="<?= BASE_URL ?>/assets/img/before-after/ondulado-depois.jpg" alt="Depois (cabelo ondulado)" loading="lazy">
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