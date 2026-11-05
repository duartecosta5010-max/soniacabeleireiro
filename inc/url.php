<?php
require_once __DIR__ . '/../config.php';

function site_base_url(): string {
  $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
  $scheme = $https ? 'https' : 'http';
  $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
  return $scheme . '://' . $host . rtrim(BASE_URL, '/');
}

function site_url(string $path = ''): string {
  $base = site_base_url();
  if ($path === '') return $base . '/';
  return $base . '/' . ltrim($path, '/');
}