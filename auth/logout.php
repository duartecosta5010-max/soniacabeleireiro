<?php
require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/inc/session.php';

unset($_SESSION['client_id'], $_SESSION['client_email'], $_SESSION['client_nome']);

header("Location: " . BASE_URL . "/index.php");
exit;