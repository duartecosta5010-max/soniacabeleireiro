<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$page_title = 'Produtos';
$title = $page_title;

$baseUrl = rtrim((string) BASE_URL, '/');

$escape = static function ($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
};

$qValue = $_GET['q'] ?? '';
$q = is_string($qValue) ? trim($qValue) : '';

$produtos = [
    [
        'slug' => 'jorge',
        'nome' => 'Jorge',
        'img' => 'jorge.jpg',
        'desc' => 'Um produto de brincadeira para hidratação do cabelo, com uma “sugadinha” que o deixa mais macio e com um aspeto saudável.',
    ],
    [
        'slug' => 'jose',
        'nome' => 'José',
        'img' => 'jose.jpg',
        'desc' => 'O José é simplesmente incrível para o cabelo: traz uma dose lendária de confiança e atitude. Dizem que até os fios mais rebeldes se alinham quando ele entra em cena! (Produto de brincadeira.)',
    ],
    [
        'slug' => 'antonio',
        'nome' => 'Antonio',
        'img' => 'antonio.jpg',
        'desc' => 'O Antonio é um produto capilar fictício de edição executiva: traz estilo e humor à coleção. Não é um cosmético real.',
    ],
];

$contains = static function (string $text, string $search): bool {
    if (function_exists('mb_stripos')) {
        return mb_stripos($text, $search, 0, 'UTF-8') !== false;
    }

    return stripos($text, $search) !== false;
};

$produtosFiltrados = $produtos;

if ($q !== '') {
    $produtosFiltrados = array_values(array_filter(
        $produtos,
        static function (array $produto) use ($q, $contains): bool {
            $textoPesquisa = $produto['nome'] . ' '
                . $produto['slug'] . ' '
                . $produto['desc'];

            return $contains($textoPesquisa, $q);
        }
    ));
}

require_once ROOT_PATH . '/inc/header.php';
?>

<style>
    .products-page,
    .products-page * {
        box-sizing: border-box;
    }

    .products-page {
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 24px 64px;
    }

    .products-page__title {
        margin: 0 0 10px;
        color: #171717;
        font-size: clamp(2rem, 4vw, 2.5rem);
        line-height: 1.15;
    }

    .products-page__intro {
        margin: 0 0 26px;
        color: #666;
        font-size: 1.1rem;
        line-height: 1.6;
    }

    .products-page__search {
        margin: -8px 0 22px;
        color: #666;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(
            auto-fill,
            minmax(min(100%, 300px), 1fr)
        );
        gap: 28px;
        align-items: stretch;
    }

    .product-card {
        display: flex;
        min-width: 0;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #e9e9e9;
        border-radius: 18px;
        background: #fff;
        transition: transform 180ms ease, box-shadow 180ms ease;
    }

    .product-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 32px rgba(0, 0, 0, 0.09);
    }

    .product-card__image-link {
        display: block;
        overflow: hidden;
        aspect-ratio: 1 / 1;
        background: #f5f5f5;
    }

    .product-card__image {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .product-card__body {
        flex: 1;
        padding: 20px 21px 12px;
    }

    .product-card__title {
        margin: 0 0 10px;
        color: #171717;
        font-size: 1.35rem;
        line-height: 1.3;
    }

    .product-card__description {
        margin: 0;
        color: #686868;
        font-size: 1rem;
        line-height: 1.65;
    }

    .product-card__footer {
        padding: 0 21px 21px;
    }

    .product-card__link {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #ff4b40;
        font-size: 1.05rem;
        font-weight: 700;
        text-decoration: none;
    }

    .product-card__link:hover {
        text-decoration: underline;
    }

    .product-card__link:focus-visible {
        outline: 3px solid rgba(255, 75, 64, 0.35);
        outline-offset: 4px;
        border-radius: 3px;
    }

    .products-empty {
        grid-column: 1 / -1;
        padding: 24px;
        border: 1px solid #eadba7;
        border-radius: 14px;
        background: #fff9e8;
        color: #55451b;
    }

    .products-empty p {
        margin: 0 0 12px;
    }

    .products-empty a {
        color: #b3362e;
        font-weight: 700;
    }

    @media (max-width: 600px) {
        .products-page {
            padding: 30px 16px 48px;
        }

        .products-grid {
            grid-template-columns: minmax(0, 1fr);
            gap: 20px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .product-card {
            transition: none;
        }
    }
</style>

<section class="products-page" aria-labelledby="products-page-title">
    <h1 class="products-page__title" id="products-page-title">Produtos</h1>

    <p class="products-page__intro">
        Descobre os produtos e as novidades da Sónia Cabeleireiro.
    </p>

    <?php if ($q !== ''): ?>
        <p class="products-page__search">
            Pesquisa: <strong><?= $escape($q) ?></strong>
        </p>
    <?php endif; ?>

    <div class="products-grid">
        <?php if (empty($produtosFiltrados)): ?>
            <div class="products-empty" role="status">
                <p>
                    Não foi encontrado nenhum produto para a pesquisa
                    “<?= $escape($q) ?>”.
                </p>

                <a href="<?= $escape($baseUrl . '/pages/produtos.php') ?>">
                    Ver todos os produtos
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($produtosFiltrados as $produto): ?>
                <?php
                $imagemUrl = $baseUrl
                    . '/assets/img/produtos/'
                    . rawurlencode($produto['img']);

                $produtoUrl = $baseUrl
                    . '/pages/produto.php?slug='
                    . rawurlencode($produto['slug']);
                ?>

                <article class="product-card">
                    <a
                        class="product-card__image-link"
                        href="<?= $escape($produtoUrl) ?>"
                        aria-label="Saber mais sobre <?= $escape($produto['nome']) ?>"
                    >
                        <img
                            class="product-card__image"
                            src="<?= $escape($imagemUrl) ?>"
                            alt="<?= $escape($produto['nome']) ?>"
                            loading="lazy"
                            decoding="async"
                        >
                    </a>

                    <div class="product-card__body">
                        <h2 class="product-card__title">
                            <?= $escape($produto['nome']) ?>
                        </h2>

                        <p class="product-card__description">
                            <?= $escape($produto['desc']) ?>
                        </p>
                    </div>

                    <div class="product-card__footer">
                        <a
                            class="product-card__link"
                            href="<?= $escape($produtoUrl) ?>"
                        >
                            Saber mais <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once ROOT_PATH . '/inc/footer.php'; ?>