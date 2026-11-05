<?php
require_once ROOT_PATH . '/inc/session.php';

$isLogged = !empty($_SESSION['client_id']);

$redirect = $_SERVER['REQUEST_URI'] ?? (BASE_URL . '/index.php');
if (!str_starts_with($redirect, BASE_URL)) {
  $redirect = BASE_URL . '/index.php';
}

$auth = $_GET['auth'] ?? '';

$loginError = null;
$registerError = null;

if ($auth === 'no_account') $loginError = "Esta conta não existe.";
if ($auth === 'invalid_password') $loginError = "Email ou palavra-passe inválidos.";
if ($auth === 'email_exists') $registerError = "Já existe uma conta com esse email.";
if ($auth === 'register_error') $registerError = "Dados inválidos. Confirma o email e a password (mín. 6 caracteres).";
?>

<div class="auth-overlay" data-auth-close></div>

<aside class="auth-drawer" data-auth-drawer aria-hidden="true">
  <div class="auth-head">
    <h2 class="auth-title"><?= $isLogged ? 'A minha conta' : 'Iniciar sessão' ?></h2>
    <button class="auth-x" type="button" data-auth-close aria-label="Fechar">×</button>
  </div>

  <div class="auth-body">
    <?php if ($isLogged): ?>
      <p>Olá, <strong><?= htmlspecialchars($_SESSION['client_nome'] ?: $_SESSION['client_email']) ?></strong></p>
      <p><a href="<?= BASE_URL ?>/auth/logout.php">Terminar sessão</a></p>

    <?php else: ?>

      <!-- LOGIN -->
      <section data-auth-view="login">
        <form method="post" action="<?= BASE_URL ?>/auth/login.php" class="auth-form">
          <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

          <?php if ($loginError): ?>
            <div class="auth-error"><?= htmlspecialchars($loginError) ?></div>
          <?php endif; ?>

          <label>Email*</label>
          <input type="email" name="email" placeholder="Insira o seu email" required>

          <label>Palavra-passe*</label>
          <input type="password" name="password" placeholder="Insira a sua palavra-passe" required>

          <button class="auth-btn" type="submit">INICIAR SESSÃO</button>

          <div class="auth-links">
            <button type="button" class="linklike" data-auth-switch="register">
              Não tem conta? Registe-se aqui.
            </button>
          </div>
        </form>
      </section>

      <!-- REGISTO -->
      <section data-auth-view="register" hidden>
        <form method="post" action="<?= BASE_URL ?>/auth/register.php" class="auth-form">
          <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

          <?php if ($registerError): ?>
            <div class="auth-error"><?= htmlspecialchars($registerError) ?></div>
          <?php endif; ?>

          <label>Nome</label>
          <input type="text" name="nome" placeholder="O seu nome">

          <label>Email*</label>
          <input type="email" name="email" placeholder="Insira o seu email" required>

          <label>Palavra-passe*</label>
          <input type="password" name="password" placeholder="Mínimo 6 caracteres" required>

          <button class="auth-btn" type="submit">CRIAR CONTA</button>

          <div class="auth-links">
            <button type="button" class="linklike" data-auth-switch="login">
              Já tem conta? Inicie sessão.
            </button>
          </div>
        </form>
      </section>

    <?php endif; ?>
  </div>
</aside>