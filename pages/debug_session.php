<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/inc/session.php';

/**
 * Página temporária de debug de sessão.
 * Proteção: só funciona quando APP_DEBUG = true.
 */
if (!defined('APP_DEBUG') || APP_DEBUG !== true) {
    // Em produção (ou debug desligado), escondemos a existência da página
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "404 Not Found";
    exit;
}

// Evitar cache
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

echo "APP_ENV: " . (defined('APP_ENV') ? APP_ENV : '(undefined)') . PHP_EOL;
echo "BASE_URL: " . (defined('BASE_URL') ? BASE_URL : '(undefined)') . PHP_EOL;
echo PHP_EOL;

echo "SESSION NAME: " . session_name() . PHP_EOL;
echo "SESSION ID: " . session_id() . PHP_EOL;
echo PHP_EOL;

print_r($_SESSION);