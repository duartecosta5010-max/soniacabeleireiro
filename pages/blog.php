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

$posts = $BLOG_POSTS;
uasort($posts, function($a, $b){
  return strcmp($b['date'], $a['date']);
});

require_once ROOT_PATH . '/inc/header.php';
?>

<main class="container">
  <h1>Blog</h1>
  <p style="color: var(--muted); margin-top: 6px;">Dicas, inspirações e cuidados com o cabelo.</p>

  <div class="blog-grid" style="margin-top:18px;">
    <?php foreach ($posts as $slug => $post): ?>
      <?php
        $url = BASE_URL . '/pages/blog_post.php?slug=' . urlencode($slug);
        $imgRel = $post['image'] ?? '';
        $imgUrl = $imgRel ? (BASE_URL . '/' . ltrim($imgRel, '/')) : '';
        $imgAlt = $post['image_alt'] ?? $post['title'];

        // se o ficheiro existir, mostramos a imagem; se não existir, fica na mesma o bloco (com fundo cinza)
        $imgOk = false;
        if ($imgRel) {
          $imgPath = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($imgRel, '/\\'));
          $imgOk = file_exists($imgPath);
        }
      ?>
      <article class="blog-card reveal">
        <a class="blog-thumb" href="<?= e($url) ?>" aria-label="<?= e($post['title']) ?>">
          <?php if ($imgUrl && $imgOk): ?>
            <img src="<?= e($imgUrl) ?>" alt="<?= e($imgAlt) ?>" loading="lazy">
          <?php endif; ?>
        </a>

        <div class="blog-body">
          <div class="blog-meta"><?= e(format_date_pt($post['date'])) ?></div>

          <h2 class="blog-title">
            <a href="<?= e($url) ?>"><?= e($post['title']) ?></a>
          </h2>

          <p class="blog-excerpt"><?= e($post['excerpt']) ?></p>

          <div class="blog-actions">
            <a class="blog-readmore" href="<?= e($url) ?>">Ler mais →</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</main>

<?php require_once ROOT_PATH . '/inc/footer.php'; ?>