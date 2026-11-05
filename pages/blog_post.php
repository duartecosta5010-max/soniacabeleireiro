<?php
require_once __DIR__ . '/../config.php';

require_once ROOT_PATH . '/inc/blog_posts.php';

function e(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function format_date_pt(string $ymd): string {
  try{
    $dt = new DateTime($ymd);
    return $dt->format('d/m/Y');
  }catch(Throwable $e){
    return $ymd;
  }
}

$slug = $_GET['slug'] ?? '';
$slug = is_string($slug) ? trim($slug) : '';

$post = $BLOG_POSTS[$slug] ?? null;

require_once ROOT_PATH . '/inc/header.php';
?>

<main class="container">
  <?php if (!$post): ?>
    <div class="card">
      <h1>Post não encontrado</h1>
      <p>O artigo que procuras não existe (ou o link está incorreto).</p>
      <p><a class="btn btn-primary" href="<?= e(BASE_URL . '/pages/blog.php') ?>">Voltar ao Blog</a></p>
    </div>
  <?php else: ?>
    <?php
      $imgRel = $post['image'] ?? '';
      $imgUrl = $imgRel ? (BASE_URL . '/' . ltrim($imgRel, '/')) : '';
      $imgAlt = $post['image_alt'] ?? $post['title'];

      $imgOk = false;
      if ($imgRel) {
        $imgPath = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($imgRel, '/\\'));
        $imgOk = file_exists($imgPath);
      }
    ?>

    <article class="post-wrap">
      <?php if ($imgUrl && $imgOk): ?>
        <figure class="post-cover">
          <img src="<?= e($imgUrl) ?>" alt="<?= e($imgAlt) ?>" loading="lazy">
        </figure>
      <?php endif; ?>

      <header class="post-head">
        <p class="post-meta"><?= e(format_date_pt($post['date'])) ?></p>
        <h1><?= e($post['title']) ?></h1>
      </header>

      <div class="post-content">
        <?= $post['content'] ?>
      </div>

      <div style="margin-top:18px;">
        <a class="btn btn-primary" href="<?= e(BASE_URL . '/pages/blog.php') ?>">← Voltar ao Blog</a>
      </div>
    </article>
  <?php endif; ?>
</main>

<?php require_once ROOT_PATH . '/inc/footer.php'; ?>