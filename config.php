<?php
declare(strict_types=1);


date_default_timezone_set('Europe/Lisbon');

if (!defined('ROOT_PATH')) {
  define('ROOT_PATH', __DIR__);
}

$host = $_SERVER['HTTP_HOST'] ?? '';

$isLocalHost =
  $host === 'localhost' ||
  str_starts_with($host, 'localhost:') ||
  $host === '127.0.0.1' ||
  str_starts_with($host, '127.0.0.1:');

$forcedEnv = getenv('APP_ENV') ?: '';
$forcedEnv = is_string($forcedEnv) ? strtolower(trim($forcedEnv)) : '';

if ($forcedEnv === 'local' || $forcedEnv === 'development') {
  $env = 'local';
} elseif ($forcedEnv === 'production' || $forcedEnv === 'prod') {
  $env = 'production';
} else {
  $env = $isLocalHost ? 'local' : 'production';
}

if (!defined('APP_ENV')) {
  define('APP_ENV', $env);
}

$debug = (APP_ENV === 'local');

if (!defined('APP_DEBUG')) {
  define('APP_DEBUG', $debug);
}

if (APP_DEBUG) {
  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
  error_reporting(E_ALL);
} else {
  ini_set('display_errors', '0');
  error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
}

$envFile = ROOT_PATH . '/config.' . APP_ENV . '.php';
if (file_exists($envFile)) {
  require_once $envFile;
}

if (!defined('BASE_URL')) {
  define('BASE_URL', APP_ENV === 'local' ? '/soniacabeleireiro' : '');
}

if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_NAME')) define('DB_NAME', 'sonia_cabeleireiro');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');