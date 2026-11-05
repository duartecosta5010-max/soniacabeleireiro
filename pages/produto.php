<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$baseUrl = rtrim((string) BASE_URL, '/');

$escape = static function ($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
};

$slugValue = $_GET['slug'] ?? '';
$slug = is_string($slugValue) ? trim($slugValue) : '';

$produtos = [
    'jorge' => [
        'nome' => 'Jorge',
        'img' => 'jorge.jpg',
        'resumo' => 'O Jorge é um produto de brincadeira para hidratação capilar, conhecido pela sua “sugadinha”, que ajuda a deixar o cabelo mais macio e com brilho.',
        'descricao' => [
            'O Jorge é um produto capilar de brincadeira pensado para dar mais humor à coleção.',
            'A famosa “sugadinha” é o toque divertido do conceito. Para escolher produtos reais adequados ao teu cabelo, pede aconselhamento no salão.',
            'Nota: este é um produto fictício criado para o website; não representa um cosmético real.',
        ],
        'beneficios_titulo' => 'Benefícios do conceito',
        'beneficios' => [
            'Uma dose extra de humor para a coleção.',
            'Um nome que dá que falar.',
            'Uma forma divertida de apresentar os produtos do salão.',
            'Não substitui cosméticos reais nem aconselhamento profissional.',
        ],
        'como_usar_titulo' => 'Como aproveitar a experiência',
        'como_usar' => [
            'Conhece o conceito do Jorge e a sua famosa “sugadinha”.',
            'Diverte-te com o produto de brincadeira.',
            'Para cuidar do cabelo, usa cosméticos próprios e segue as instruções do respetivo rótulo.',
        ],
    ],

    'jose' => [
        'nome' => 'José',
        'img' => 'jose.jpg',
        'resumo' => 'O José é um produto fictício de brincadeira com uma dose lendária de confiança, atitude e boa disposição.',
        'descricao' => [
            'Este é o José, um produto de brincadeira com uma presença lendária na coleção. A sua imagem dá logo personalidade à página.',
            'A fórmula secreta? Muita atitude, confiança e uma boa dose de humor. É a escolha perfeita para dar à coleção um toque divertido.',
            'Nota: o José é um produto fictício criado para o website; a imagem não representa um cosmético real.',
        ],
        'beneficios_titulo' => 'Benefícios do conceito',
        'beneficios' => [
            'Uma dose extra de confiança para a coleção.',
            'Atitude lendária nos dias de cabelo rebelde — pelo menos na brincadeira.',
            'Boa disposição incluída em cada visita a esta página.',
            'Não substitui champô, máscara ou aconselhamento profissional.',
        ],
        'como_usar_titulo' => 'Como aproveitar a experiência José',
        'como_usar' => [
            'Admira a imagem e diverte-te com o conceito do produto.',
            'Lembra-te: é um produto fictício e não deve ser aplicado no cabelo.',
            'Para cuidados reais, escolhe cosméticos próprios e segue as instruções do rótulo.',
        ],
    ],

    'antonio' => [
        'nome' => 'Antonio',
        'img' => 'antonio.jpg',
        'resumo' => 'O Antonio é um produto capilar fictício de edição executiva, criado para trazer estilo e humor à coleção.',
        'descricao' => [
            'O Antonio é o produto mais formal da coleção: um conceito de brincadeira com apresentação executiva, pronto para uma reunião muito séria com o teu cabelo.',
            'Com um visual elegante e cheio de atitude, o produto Antonio dá personalidade à coleção e transforma a prateleira digital num lugar mais divertido.',
            'Nota: o Antonio é um produto fictício criado para humor no website. Não tem fórmula, não está à venda e não deve ser aplicado no cabelo.',
        ],
        'beneficios_titulo' => 'Benefícios do conceito Antonio',
        'beneficios' => [
            'Estilo executivo para a coleção de produtos.',
            'Uma dose de humor para a rotina capilar.',
            'Um nome memorável para a prateleira digital.',
            'Não oferece benefícios capilares reais nem substitui cosméticos.',
        ],
        'como_usar_titulo' => 'Como aproveitar o produto Antonio',
        'como_usar' => [
            'Admira a apresentação executiva do Antonio na página.',
            'Desfruta do conceito como uma brincadeira — não o apliques no cabelo.',
            'Para cuidados reais, escolhe cosméticos adequados e segue as instruções do rótulo.',
        ],
    ],
];

$produto = $produtos[$slug] ?? null;

$page_title = $produto
    ? $produto['nome'] . ' - Produto'
    : 'Produto não encontrado';

$title = $page_title;

require_once ROOT_PATH . '/inc/header.php';
?>

<style>
    .product-detail-page,
    .product-detail-page * {
        box-sizing: border-box;
    }

    .product-detail-page {
        width: 100%;
        max-width: 1250px;
        margin: 0 auto;
        padding: 38px 24px 64px;
    }

    .product-detail-page__back {
        display: inline-block;
        margin-bottom: 18px;
        color: #555;
        font-weight: 600;
        text-decoration: none;
    }

    .product-detail-page__back:hover {
        color: #ff4b40;
        text-decoration: underline;
    }

    .product-detail-layout {
        display: grid;
        grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr);
        gap: 28px;
        align-items: start;
    }

    .product-detail-image-panel,
    .product-detail-content {
        overflow: hidden;
        border: 1px solid #e9e9e9;
        border-radius: 18px;
        background: #fff;
    }

    .product-detail-image-panel {
        padding: 12px;
    }

    .product-detail-image {
        display: block;
        width: 100%;
        height: min(44vw, 520px);
        min-height: 320px;
        border-radius: 12px;
        background: #f4f4f4;
        object-fit: contain;
    }

    .product-detail-content {
        padding: 28px;
    }

    .product-detail-kicker {
        margin: 0 0 8px;
        color: #ff4b40;
        font-size: 0.9rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .product-detail-title {
        margin: 0 0 12px;
        color: #171717;
        font-size: clamp(2rem, 4vw, 2.8rem);
        line-height: 1.15;
    }

    .product-detail-summary {
        margin: 0 0 22px;
        color: #666;
        font-size: 1.1rem;
        line-height: 1.7;
    }

    .product-detail-description p {
        margin: 0 0 14px;
        color: #333;
        line-height: 1.75;
    }

    .product-detail-content h2 {
        margin: 26px 0 10px;
        color: #171717;
        font-size: 1.2rem;
        line-height: 1.4;
    }

    .product-detail-list {
        margin: 0;
        padding-left: 22px;
        color: #444;
        line-height: 1.75;
    }

    .product-detail-list li + li {
        margin-top: 5px;
    }

    .product-detail-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 28px;
    }

    .product-detail-action {
        display: inline-flex;
        min-height: 44px;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        border: 1px solid #dedede;
        border-radius: 8px;
        color: #333;
        font-weight: 700;
        text-decoration: none;
        transition: background 160ms ease, transform 160ms ease;
    }

    .product-detail-action:hover {
        transform: translateY(-1px);
        background: #f5f5f5;
    }

    .product-detail-action--primary {
        border-color: #ff4b40;
        background: #ff4b40;
        color: #fff;
    }

    .product-detail-action--primary:hover {
        border-color: #e83c32;
        background: #e83c32;
        color: #fff;
    }

    .product-detail-not-found {
        max-width: 800px;
        margin: 0 auto;
        padding: 24px;
        border: 1px solid #eadba7;
        border-radius: 14px;
        background: #fff9e8;
        color: #55451b;
    }

    .product-detail-not-found h1 {
        margin: 0 0 10px;
    }

    .product-detail-not-found p {
        margin: 0;
    }

    @media (max-width: 800px) {
        .product-detail-layout {
            grid-template-columns: minmax(0, 1fr);
        }

        .product-detail-image {
            height: min(80vw, 480px);
            min-height: 260px;
        }
    }

    @media (max-width: 600px) {
        .product-detail-page {
            padding: 28px 16px 48px;
        }

        .product-detail-content {
            padding: 21px;
        }

        .product-detail-actions {
            flex-direction: column;
        }

        .product-detail-action {
            width: 100%;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .product-detail-action {
            transition: none;
        }
    }
</style>

<section class="product-detail-page">
    <?php if (!$produto): ?>
        <div class="product-detail-not-found" role="status">
            <h1>Produto não encontrado</h1>
            <p>Não encontrámos esse produto. Podes voltar à lista de produtos.</p>

            <div class="product-detail-actions">
                <a
                    class="product-detail-action product-detail-action--primary"
                    href="<?= $escape($baseUrl . '/pages/produtos.php') ?>"
                >
                    Voltar a Produtos
                </a>
            </div>
        </div>
    <?php else: ?>
        <?php
        $imagemUrl = $baseUrl
            . '/assets/img/produtos/'
            . rawurlencode($produto['img']);
        ?>

        <a
            class="product-detail-page__back"
            href="<?= $escape($baseUrl . '/pages/produtos.php') ?>"
        >
            ← Voltar a Produtos
        </a>

        <div class="product-detail-layout">
            <div class="product-detail-image-panel">
                <img
                    class="product-detail-image"
                    src="<?= $escape($imagemUrl) ?>"
                    alt="<?= $escape($produto['nome']) ?>"
                    decoding="async"
                >
            </div>

            <article class="product-detail-content">
                <p class="product-detail-kicker">Produtos</p>

                <h1 class="product-detail-title">
                    <?= $escape($produto['nome']) ?>
                </h1>

                <p class="product-detail-summary">
                    <?= $escape($produto['resumo']) ?>
                </p>

                <div class="product-detail-description">
                    <?php foreach ($produto['descricao'] as $paragrafo): ?>
                        <p><?= $escape($paragrafo) ?></p>
                    <?php endforeach; ?>
                </div>

                <h2><?= $escape($produto['beneficios_titulo']) ?></h2>

                <ul class="product-detail-list">
                    <?php foreach ($produto['beneficios'] as $beneficio): ?>
                        <li><?= $escape($beneficio) ?></li>
                    <?php endforeach; ?>
                </ul>

                <h2><?= $escape($produto['como_usar_titulo']) ?></h2>

                <ol class="product-detail-list">
                    <?php foreach ($produto['como_usar'] as $passo): ?>
                        <li><?= $escape($passo) ?></li>
                    <?php endforeach; ?>
                </ol>

                <div class="product-detail-actions">
                    <a
                        class="product-detail-action"
                        href="<?= $escape($baseUrl . '/pages/produtos.php') ?>"
                    >
                        ← Todos os produtos
                    </a>

                    <a
                        class="product-detail-action product-detail-action--primary"
                        href="<?= $escape($baseUrl . '/pages/contactos.php') ?>"
                    >
                        Perguntar no salão
                    </a>
                </div>
            </article>
        </div>
    <?php endif; ?>
</section>

<?php require_once ROOT_PATH . '/inc/footer.php'; ?>