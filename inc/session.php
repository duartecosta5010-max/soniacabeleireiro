<?php

if (!defined('ROOT_PATH')) {
  require_once __DIR__ . '/../config.php';
}

$days = 30;
$lifetime = 60 * 60 * 24 * $days;

session_name('SONIASESSID');

ini_set('session.use_strict_mode', '1');
ini_set('session.gc_maxlifetime', (string)$lifetime);
ini_set('session.cookie_lifetime', (string)$lifetime);

$cookieParams = session_get_cookie_params();

$path = '/';
if (defined('BASE_URL') && is_string(BASE_URL) && BASE_URL !== '') {
  $path = rtrim(BASE_URL, '/') . '/';
}

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

session_set_cookie_params([
  'lifetime' => $lifetime,
  'path'     => $path,
  'domain'   => $cookieParams['domain'] ?? '',
  'secure'   => $secure,
  'httponly' => true,
  'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

if (!isset($_SESSION['__initiated'])) {
  session_regenerate_id(true);
  $_SESSION['__initiated'] = time();
}