<?php
require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/inc/db.php';
require_once ROOT_PATH . '/inc/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: " . BASE_URL . "/index.php");
  exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$redirect = $_POST['redirect'] ?? (BASE_URL . '/index.php');

if (!str_starts_with($redirect, BASE_URL)) $redirect = BASE_URL . '/index.php';

$stmt = $pdo->prepare("SELECT id, email, nome, password_hash FROM clients WHERE email = :e LIMIT 1");
$stmt->execute([':e' => $email]);
$client = $stmt->fetch();

if (!$client) {
  header("Location: " . $redirect . (str_contains($redirect,'?') ? '&' : '?') . "auth=no_account");
  exit;
}

if (!password_verify($password, $client['password_hash'])) {
  header("Location: " . $redirect . (str_contains($redirect,'?') ? '&' : '?') . "auth=invalid_password");
  exit;
}

$_SESSION['client_id'] = (int)$client['id'];
$_SESSION['client_email'] = $client['email'];
$_SESSION['client_nome'] = $client['nome'] ?? '';

header("Location: " . $redirect);
exit;