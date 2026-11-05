<?php
declare(strict_types=1);


require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/inc/session.php';

function auth_user(): ?array
{

    if (!empty($_SESSION['client_id'])) {
        return [
            'id'       => (int) $_SESSION['client_id'],
            'nome'     => (string) ($_SESSION['client_nome'] ?? ''),
            'email'    => (string) ($_SESSION['client_email'] ?? ''),
            'telemovel'=> (string) ($_SESSION['client_telemovel'] ?? $_SESSION['client_phone'] ?? ''),
            'type'     => 'client',
        ];
    }

    if (!empty($_SESSION['user_id'])) {
        return [
            'id'       => (int) $_SESSION['user_id'],
            'nome'     => (string) ($_SESSION['user_name'] ?? $_SESSION['nome'] ?? ''),
            'email'    => (string) ($_SESSION['user_email'] ?? $_SESSION['email'] ?? ''),
            'telemovel'=> (string) ($_SESSION['user_phone'] ?? $_SESSION['telemovel'] ?? ''),
            'type'     => 'user',
        ];
    }

    return null;
}

function is_logged_in(): bool
{
    return auth_user() !== null;
}

function user_name(): string
{
    $u = auth_user();
    return $u ? (string)($u['nome'] ?? '') : '';
}

function user_email(): string
{
    $u = auth_user();
    return $u ? (string)($u['email'] ?? '') : '';
}

function user_phone(): string
{
    $u = auth_user();
    return $u ? (string)($u['telemovel'] ?? '') : '';
}