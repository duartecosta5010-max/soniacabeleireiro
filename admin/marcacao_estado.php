<?php
require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/inc/db.php';
require_once ROOT_PATH . '/inc/admin_auth.php';

require_admin();

if (empty($_SESSION['user_id'])) {
    exit;
}

$redirectUrl = rtrim(BASE_URL, '/') . '/admin/marcacoes.php';

$redirectWithFlash = static function ($type, $message) use ($redirectUrl) {
    $_SESSION['admin_booking_flash'] = [
        'type' => (string) $type,
        'message' => (string) $message,
    ];

    header('Location: ' . $redirectUrl);
    exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . $redirectUrl);
    exit;
}

$expectedToken = (
    isset($_SESSION['admin_booking_csrf']) &&
    is_string($_SESSION['admin_booking_csrf'])
)
    ? $_SESSION['admin_booking_csrf']
    : '';

$providedToken = (
    isset($_POST['csrf_token']) &&
    is_string($_POST['csrf_token'])
)
    ? $_POST['csrf_token']
    : '';

if (
    $expectedToken === '' ||
    $providedToken === '' ||
    !hash_equals($expectedToken, $providedToken)
) {
    $redirectWithFlash(
        'error',
        'A sessão do formulário expirou. Atualiza a página e tenta novamente.'
    );
}

$idRaw = $_POST['id'] ?? '';

if (!is_string($idRaw) && !is_int($idRaw)) {
    $redirectWithFlash('error', 'O pedido indicado não é válido.');
}

$id = filter_var((string) $idRaw, FILTER_VALIDATE_INT);

if ($id === false || $id < 1) {
    $redirectWithFlash('error', 'O pedido indicado não é válido.');
}

$estado = $_POST['estado'] ?? '';

if (!is_string($estado)) {
    $redirectWithFlash('error', 'A ação indicada não é válida.');
}

$acoes = [
    'aceite' => 'accept',
    'recusado' => 'decline',
    'remarcado' => 'reschedule',
];

if (!array_key_exists($estado, $acoes)) {
    $redirectWithFlash('error', 'A ação indicada não é válida.');
}

try {
    $pdo = db();

    $stmt = $pdo->prepare(
        'SELECT id, estado, action_token
         FROM booking_requests
         WHERE id = :id
         LIMIT 1'
    );

    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $marcacao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$marcacao) {
        $redirectWithFlash('error', 'A marcação indicada não foi encontrada.');
    }

    if ((string) $marcacao['estado'] !== 'pendente') {
        $redirectWithFlash(
            'warning',
            'Esta marcação já foi tratada. Atualiza a lista para ver o estado atual.'
        );
    }

    $token = trim((string) ($marcacao['action_token'] ?? ''));

    if ($token === '') {
        $redirectWithFlash(
            'error',
            'Não foi encontrado um token de ação válido para esta marcação.'
        );
    }

    $target = rtrim(BASE_URL, '/') . '/pages/marcacao_acao.php?' . http_build_query(
        [
            'action' => $acoes[$estado],
            'id' => (int) $marcacao['id'],
            'token' => $token,
        ],
        '',
        '&',
        PHP_QUERY_RFC3986
    );

    header('Location: ' . $target);
    exit;
} catch (Throwable $exception) {
    error_log(
        'Erro ao abrir a ação administrativa da marcação: ' .
        $exception->getMessage()
    );

    $redirectWithFlash(
        'error',
        'Não foi possível abrir a ação da marcação. Tenta novamente.'
    );
}