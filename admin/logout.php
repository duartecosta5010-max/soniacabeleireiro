<?php
require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/inc/session.php';

session_destroy();
header("Location: " . BASE_URL . "/admin/login.php");
exit;