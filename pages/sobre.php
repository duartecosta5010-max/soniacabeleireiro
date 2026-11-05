<?php
require_once __DIR__ . '/../config.php';
$title = "Sobre nós - Sónia Cabeleireiro";
require_once ROOT_PATH . '/inc/header.php';
?>

<h1 class="section-title reveal">Sobre nós</h1>

<section class="card reveal">
  <h2>Sónia Cabeleireiro</h2>
  <p>
    (Escreve aqui uma apresentação curta do salão: há quantos anos existe, especialidades, etc.)
  </p>
</section>

<section class="contact-grid">
  <div class="card reveal">
    <h3>Morada</h3>
    <p>
      Rua D. Manuel Correia Ramalho, Lote 6, Loja N<br>
      2070-095 Cartaxo
    </p>

    <h3>Contacto</h3>
    <p>
      <a href="tel:+351933148593">+351 933 148 593</a><br>
      <a href="mailto:soniarego78@hotmail.com">soniarego78@hotmail.com</a>
    </p>

    <h3>Horário</h3>
    <ul class="clean-list">
      <li><strong>Ter–Sex:</strong> 09:00 – 18:00</li>
      <li><strong>Sábado:</strong> 09:00 – 13:00</li>
      <li><strong>Domingo:</strong> Encerrado</li>
      <li><strong>Segunda:</strong> Encerrado</li>
    </ul>

    <div style="margin-top:16px;">
      <a class="btn btn-primary" href="<?= BASE_URL ?>/pages/contactos.php">Pedir marcação</a>
    </div>
  </div>

  <div class="card reveal">
    <h3>Localização</h3>

    <iframe
      class="map-embed"
      src="https://www.google.com/maps/embed?pb=!3m2!1spt-PT!2spt!4v1791487772362!5m2!1spt-PT!2spt!6m8!1m7!1svnHjkTjgJCFxwB1yYEv0OQ!2m2!1d39.15791861924367!2d-8.791702111921085!3f256.9679806459595!4f-17.638796860629!5f0.7820865974627469"
      allowfullscreen=""
      loading="lazy"
      referrerpolicy="strict-origin-when-cross-origin">
    </iframe>
  </div>
</section>

<?php require_once ROOT_PATH . '/inc/footer.php'; ?>