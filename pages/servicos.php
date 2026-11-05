<?php
require_once __DIR__ . '/../config.php';
$title = "Serviços - Sónia Cabeleireiro";
require_once ROOT_PATH . '/inc/header.php';
?>

<h1 class="section-title reveal">Serviços</h1>

<section class="card reveal">
  <p style="margin:0;color:var(--muted);line-height:1.7;">
    No Sónia Cabeleireiro trabalhamos com diagnóstico personalizado para encontrares o corte,
    cor, tratamento e ideais para o teu tipo de cabelo. Para valores e tempos exatos, confirma
    connosco no pedido de marcação.
  </p>
</section>

<section style="margin-top:18px; display:grid; gap:16px;">
  <div class="card reveal">
    <h2 style="margin-top:0;">Corte</h2>
    <p style="margin:0 0 10px;color:var(--muted);line-height:1.7;">
      Corte adaptado ao formato do rosto e ao teu estilo, com acabamento profissional.
      Podemos incluir lavagem e finalização (brushing/ondas) conforme preferires.
    </p>
    <ul class="clean-list">
      <li><strong>Opções:</strong> senhora, homem, criança</li>
      <li><strong>Inclui:</strong> aconselhamento + acabamento</li>
      <li><strong>Extra:</strong> franja / ajuste entre cortes</li>
    </ul>
    <div style="margin-top:14px;">
      <a class="btn btn-primary"
         href="<?= BASE_URL ?>/pages/contactos.php?servico=<?= urlencode('Corte') ?>#marcacao">
        Pedir marcação
      </a>
    </div>
  </div>

  <div class="card reveal">
    <h2 style="margin-top:0;">Coloração</h2>
    <p style="margin:0 0 10px;color:var(--muted);line-height:1.7;">
      Cor uniforme, brilho e cobertura (incluindo brancos), com aconselhamento de tom e manutenção.
      Trabalhamos para preservar a saúde do fio e o resultado natural.
    </p>
    <ul class="clean-list">
      <li><strong>Serviços:</strong> raiz, cor total, tonalização/banho de cor</li>
      <li><strong>Indicado para:</strong> cobertura de brancos, mudança de visual</li>
      <li><strong>Manutenção:</strong> recomendamos máscara/tratamento adequado</li>
    </ul>
    <div style="margin-top:14px;">
      <a class="btn btn-primary"
         href="<?= BASE_URL ?>/pages/contactos.php?servico=<?= urlencode('Coloração') ?>#marcacao">
        Pedir marcação
      </a>
    </div>
  </div>

  <div class="card reveal">
    <h2 style="margin-top:0;">Madeixas / Balayage</h2>
    <p style="margin:0 0 10px;color:var(--muted);line-height:1.7;">
      Técnicas de iluminação para dar dimensão e movimento ao cabelo, com resultado mais natural
      (balayage) ou mais marcado (madeixas tradicionais), sempre ajustado ao teu tom de pele.
    </p>
    <ul class="clean-list">
      <li><strong>Técnicas:</strong> balayage, babylights, face framing</li>
      <li><strong>Inclui:</strong> escolha de tom + acabamento</li>
      <li><strong>Nota:</strong> pode exigir tonalização após descoloração</li>
    </ul>
    <div style="margin-top:14px;">
      <a class="btn btn-primary"
         href="<?= BASE_URL ?>/pages/contactos.php?servico=<?= urlencode('Madeixas / Balayage') ?>#marcacao">
        Pedir marcação
      </a>
    </div>
  </div>

  <div class="card reveal">
    <h2 style="margin-top:0;">Tratamentos</h2>
    <p style="margin:0 0 10px;color:var(--muted);line-height:1.7;">
      Tratamentos para recuperar brilho, maciez e força. Fazemos diagnóstico para escolher o cuidado
      certo (hidratação/nutrição/reconstrução) conforme o estado do teu cabelo.
    </p>
    <ul class="clean-list">
      <li><strong>Opções:</strong> hidratação, nutrição, reconstrução</li>
      <li><strong>Indicado para:</strong> cabelo seco, danificado, pintado/descolorado</li>
      <li><strong>Objetivo:</strong> reduzir frizz, melhorar toque e elasticidade</li>
    </ul>
    <div style="margin-top:14px;">
      <a class="btn btn-primary"
         href="<?= BASE_URL ?>/pages/contactos.php?servico=<?= urlencode('Tratamentos') ?>#marcacao">
        Pedir marcação
      </a>
    </div>
  </div>

  <div class="card reveal">
    <h2 style="margin-top:0;">Brushing / Finalização</h2>
    <p style="margin:0 0 10px;color:var(--muted);line-height:1.7;">
      Secagem e finalização para o dia a dia ou para um evento. Podes escolher liso, ondas,
      volume ou um look mais polido e duradouro.
    </p>
    <ul class="clean-list">
      <li><strong>Looks:</strong> liso, ondas, volume, efeito polido</li>
      <li><strong>Extra:</strong> proteção térmica e finalizadores</li>
    </ul>
    <div style="margin-top:14px;">
      <a class="btn btn-primary"
         href="<?= BASE_URL ?>/pages/contactos.php?servico=<?= urlencode('Brushing / Finalização') ?>#marcacao">
        Pedir marcação
      </a>
    </div>
  </div>

  <div class="card reveal">
    <h2 style="margin-top:0;">Penteados / Eventos</h2>
    <p style="margin:0 0 10px;color:var(--muted);line-height:1.7;">
      Penteados para ocasiões especiais (festas, cerimónias, casamentos), com opção de prova
      (recomendado para noivas).
    </p>
    <ul class="clean-list">
      <li><strong>Inclui:</strong> preparação + fixação + acabamento</li>
      <li><strong>Recomendação:</strong> trazer referências do estilo que gostas</li>
    </ul>
    <div style="margin-top:14px;">
      <a class="btn btn-primary"
         href="<?= BASE_URL ?>/pages/contactos.php?servico=<?= urlencode('Penteados / Eventos') ?>#marcacao">
        Pedir marcação
      </a>
    </div>
  </div>
</section>

<?php require_once ROOT_PATH . '/inc/footer.php'; ?>