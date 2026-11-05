<?php
if (!defined('ROOT_PATH') || !defined('BASE_URL')) {
  require_once __DIR__ . '/../config.php';
}

require_once ROOT_PATH . '/inc/session.php';

if (!isset($title)) { $title = "Sónia Cabeleireiro"; }
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($title) ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<div class="announce">
  <div class="container">
    Sónia Cabeleireiro — a escolha dos profissionais
  </div>
</div>

<header class="site-header">
  <div class="container header-main">
    <a class="brand" href="<?= BASE_URL ?>/index.php">Sónia Cabeleireiro</a>

    <form class="search" method="get" action="<?= BASE_URL ?>/pages/produtos.php">
      <input type="text" name="q" placeholder="Pesquise por produtos, marca..." />
      <button type="submit">Pesquisar</button>
    </form>

    <div class="header-actions">
      <a class="btn btn-primary" href="<?= BASE_URL ?>/pages/marcacoes.php">
        Marcações Online
      </a>

      <button class="icon-btn" type="button" data-auth-open aria-label="Iniciar sessão">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M20 21a8 8 0 0 0-16 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          <path d="M12 13a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2"/>
        </svg>
      </button>
    </div>
  </div>

  <nav class="header-nav">
    <div class="container nav-links">
      <a href="<?= BASE_URL ?>/pages/servicos.php">Serviços</a>
      <a href="<?= BASE_URL ?>/pages/produtos.php">Produtos</a>
      <a href="<?= BASE_URL ?>/pages/sobre.php">Sobre nós</a>
      <a href="<?= BASE_URL ?>/pages/blog.php">Blog</a>
      <a href="<?= BASE_URL ?>/pages/contactos.php">Contactos / Marcações</a>
    </div>
  </nav>
</header>

<main class="container">