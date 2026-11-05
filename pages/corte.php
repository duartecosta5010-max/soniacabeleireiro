<?php
require_once __DIR__ . '/../config.php';
$title = "Corte - Sónia Cabeleireiro";
require_once ROOT_PATH . '/inc/header.php';
?>

<h1 class="section-title reveal">Corte</h1>

<section class="card reveal">
  <p style="margin:0;color:var(--muted);line-height:1.7;">
    Corte adaptado ao formato do rosto e ao teu estilo, com acabamento profissional.
    Se quiseres, incluímos lavagem e finalização (brushing/ondas).
  </p>

  <ul class="clean-list" style="margin-top:14px;">
    <li><strong>Opções:</strong> senhora, homem, criança</li>
    <li><strong>Inclui:</strong> aconselhamento + acabamento</li>
    <li><strong>Extra:</strong> franja / ajuste entre cortes</li>
  </ul>

  <div style="margin-top:16px; display:flex; gap:10px; flex-wrap:wrap;">
    <a class="btn btn-primary"
       href="<?= BASE_URL ?>/pages/contactos.php?servico=<?= urlencode('Corte') ?>#marcacao">
      Pedir marcação
    </a>
    <a class="btn btn-dark" href="<?= BASE_URL ?>/pages/servicos.php">Ver todos os serviços</a>
  </div>
</section>

<section class="reveal" style="margin-top:34px; padding: 10px 0 34px;">
  <div class="ba" data-ba>
    <div class="ba__layer ba__before">
      <img src="<?= BASE_URL ?>/assets/img/before-after/corte-antes.jpg" alt="Antes do corte" loading="lazy">
    </div>

    <div class="ba__layer ba__after">
      <img src="<?= BASE_URL ?>/assets/img/before-after/corte-depois.jpg" alt="Depois do corte" loading="lazy">
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