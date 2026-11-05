<?php
require_once ROOT_PATH . '/inc/session.php';
$auth = $_GET['auth'] ?? '';

$loginError = null;
$registerError = null;

if ($auth === 'no_account') $loginError = "Esta conta não existe.";
if ($auth === 'invalid_password') $loginError = "Palavra-passe incorreta.";
if ($auth === 'register_error') $registerError = "Dados inválidos. Confirma o email e a password (mín. 6 caracteres).";
if ($auth === 'email_exists') $registerError = "Já existe uma conta com esse email.";

function require_admin() {
  if (empty($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/admin/login.php");
    exit;
  }
}