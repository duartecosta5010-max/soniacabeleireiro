<?php
require_once __DIR__ . '/../config.php';

function db(): PDO {
  static $pdo = null;

  if ($pdo instanceof PDO) {
    return $pdo;
  }

  $host = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
  $name = defined('DB_NAME') ? DB_NAME : 'sonia_cabeleireiro';
  $user = defined('DB_USER') ? DB_USER : 'root';
  $pass = defined('DB_PASS') ? DB_PASS : '';

  $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

  $options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ];

  $pdo = new PDO($dsn, $user, $pass, $options);

  $GLOBALS['pdo'] = $pdo;

  return $pdo;
}

$pdo = db();