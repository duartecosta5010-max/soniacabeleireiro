<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

require_once ROOT_PATH . '/inc/auth.php';
require_once ROOT_PATH . '/inc/db.php';
require_once ROOT_PATH . '/inc/url.php';
require_once ROOT_PATH . '/inc/mailer.php';
require_once ROOT_PATH . '/inc/booking_slots.php';

date_default_timezone_set('Europe/Lisbon');

function e(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

$user = auth_user();

if (!$user) {
    header('Location: ' . BASE_URL . '/pages/marcacoes.php');
    exit;
}

$userEmail = trim((string) user_email($user));

$title = 'Enviar marcação - Sónia Cabeleireiro';

if (
    $userEmail === '' ||
    !filter_var($userEmail, FILTER_VALIDATE_EMAIL)
) {
    require_once ROOT_PATH . '/inc/header.php';
    ?>
    <section class="card">
        <h1>Não foi possível enviar o pedido</h1>

        <p>
            Para fazer uma marcação é necessário ter um email válido associado
            à tua conta.
        </p>

        <p style="color:var(--muted);">
            Inicia sessão com uma conta que tenha email ou atualiza os dados
            da tua conta.
        </p>

        <a class="btn btn-primary" href="<?= e(BASE_URL . '/pages/marcacoes.php') ?>">
            Voltar às marcações
        </a>
    </section>
    <?php
    require_once ROOT_PATH . '/inc/footer.php';
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/marcacoes.php');
    exit;
}

$nome = trim((string) ($_POST['nome'] ?? ''));
$telemovel = trim((string) ($_POST['telemovel'] ?? ''));
$servico = trim((string) ($_POST['servico'] ?? ''));
$data = trim((string) ($_POST['data'] ?? ''));
$hora = trim((string) ($_POST['hora'] ?? ''));
$obs = trim((string) ($_POST['obs'] ?? ''));

/*
 * O email vem sempre da conta iniciada.
 * Não é aceite um email diferente enviado pelo formulário.
 */
$email = $userEmail;

$servicosValidos = [
    'Corte',
    'Brushing / Finalização',
    'Coloração',
    'Madeixas / Balayage',
    'Tratamentos',
    'Penteados / Eventos',
];

$errors = [];

if ($nome === '') {
    $errors[] = 'Indica o nome.';
}

if ($telemovel === '') {
    $errors[] = 'Indica o telemóvel.';
}

if (!in_array($servico, $servicosValidos, true)) {
    $errors[] = 'Escolhe um serviço válido.';
}

if (!booking_slot_is_valid_date($data)) {
    $errors[] = 'A data indicada não é válida.';
}

if (!booking_slot_is_valid_time($hora)) {
    $errors[] = 'A hora tem de ser escolhida de 15 em 15 minutos, por exemplo 10:00, 10:15, 10:30 ou 10:45.';
}

if (!$errors) {
    try {
        $dataPretendida = new DateTimeImmutable($data);
        $hoje = new DateTimeImmutable('today');

        if ($dataPretendida < $hoje) {
            $errors[] = 'A data pretendida não pode ser anterior a hoje.';
        } else {
            $dataHoraPretendida = new DateTimeImmutable($data . ' ' . $hora);
            $agora = new DateTimeImmutable('now');

            if ($dataHoraPretendida < $agora) {
                $errors[] = 'A hora pretendida não pode ser anterior à hora atual.';
            }
        }
    } catch (Throwable $exception) {
        $errors[] = 'A data ou a hora indicada não é válida.';
    }
}

if ($errors) {
    require_once ROOT_PATH . '/inc/header.php';
    ?>
    <section class="card">
        <h1>Não foi possível enviar o pedido</h1>

        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>

        <a class="btn btn-primary" href="<?= e(BASE_URL . '/pages/marcacoes.php') ?>">
            Voltar às marcações
        </a>
    </section>
    <?php
    require_once ROOT_PATH . '/inc/footer.php';
    exit;
}

$pdo = null;
$slotLocked = false;
$inserted = false;
$pedidoId = 0;
$failure = null;

try {
    $pdo = db();

    $slotLocked = booking_slot_lock_acquire(
        $pdo,
        $data,
        $hora,
        10
    );

    if (!$slotLocked) {
        throw new RuntimeException(
            'A agenda está a ser atualizada. Tenta novamente dentro de alguns segundos.'
        );
    }

    $token = bin2hex(random_bytes(32));
    $expiresAt = (new DateTimeImmutable('+7 days'))->format('Y-m-d H:i:s');

    $pdo->beginTransaction();

    try {
        if (booking_slot_has_confirmed_booking($pdo, $data, $hora)) {
            throw new RuntimeException(
                'Este horário já tem uma marcação confirmada. Escolhe outra hora e tenta novamente.'
            );
        }

        $stmt = $pdo->prepare(
            'INSERT INTO booking_requests
                (
                    nome,
                    telemovel,
                    email,
                    servico,
                    data_pretendida,
                    hora_pretendida,
                    obs,
                    action_token,
                    token_expires_at
                )
             VALUES
                (
                    :nome,
                    :telemovel,
                    :email,
                    :servico,
                    :data_pretendida,
                    :hora_pretendida,
                    :obs,
                    :token,
                    :expires
                )'
        );

        $stmt->execute([
            ':nome' => $nome,
            ':telemovel' => $telemovel,
            ':email' => $email,
            ':servico' => $servico,
            ':data_pretendida' => $data,
            ':hora_pretendida' => $hora,
            ':obs' => $obs !== '' ? $obs : null,
            ':token' => $token,
            ':expires' => $expiresAt,
        ]);

        $pedidoId = (int) $pdo->lastInsertId();

        if ($pedidoId < 1) {
            throw new RuntimeException(
                'Não foi possível obter o número do pedido criado.'
            );
        }

        $pdo->commit();
        $inserted = true;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
} catch (Throwable $exception) {
    $failure = $exception;

    error_log(
        'Erro ao registar pedido de marcação: ' .
        $exception->getMessage()
    );
} finally {
    if ($slotLocked && $pdo instanceof PDO) {
        booking_slot_lock_release($pdo, $data, $hora);
    }
}

if ($failure !== null || !$inserted) {
    if ($failure instanceof RuntimeException && !$failure instanceof PDOException) {
        $message = $failure->getMessage();
    } else {
        $message = 'Ocorreu um erro ao registar o pedido. Tenta novamente.';
    }

    require_once ROOT_PATH . '/inc/header.php';
    ?>
    <section class="card">
        <h1>Não foi possível registar o pedido</h1>
        <p><?= e($message) ?></p>

        <a class="btn btn-primary" href="<?= e(BASE_URL . '/pages/marcacoes.php') ?>">
            Voltar às marcações
        </a>
    </section>
    <?php
    require_once ROOT_PATH . '/inc/footer.php';
    exit;
}

$pedidoNum = str_pad((string) $pedidoId, 6, '0', STR_PAD_LEFT);

$acceptUrl = site_url(
    "pages/marcacao_acao.php?action=accept&id={$pedidoId}&token={$token}"
);

$declineUrl = site_url(
    "pages/marcacao_acao.php?action=decline&id={$pedidoId}&token={$token}"
);

$rescheduleUrl = site_url(
    "pages/marcacao_acao.php?action=reschedule&id={$pedidoId}&token={$token}"
);

$safeObs = $obs !== ''
    ? nl2br(e($obs))
    : '<span style="color:#777;">(sem observações)</span>';

$subject = "Novo pedido de marcação #{$pedidoNum}";

$html = '
<div style="font-family:Arial,sans-serif;background:#f6f6f6;padding:22px;">
    <div style="max-width:720px;margin:0 auto;background:#fff;border:1px solid #eee;border-radius:12px;overflow:hidden;">
        <div style="padding:18px 20px;background:#111;color:#fff;">
            <div style="font-size:14px;opacity:.9;">Sónia Cabeleireiro</div>
            <div style="font-size:20px;font-weight:700;margin-top:6px;">
                Pedido de marcação #' . e($pedidoNum) . '
            </div>
        </div>

        <div style="padding:18px 20px;">
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <tr>
                    <td style="padding:8px 0;color:#666;width:170px;">Nome</td>
                    <td style="padding:8px 0;font-weight:700;">' . e($nome) . '</td>
                </tr>
                <tr>
                    <td style="padding:8px 0;color:#666;">Telemóvel</td>
                    <td style="padding:8px 0;font-weight:700;">
                        <a href="tel:' . e($telemovel) . '" style="color:#111;text-decoration:none;">' .
                            e($telemovel) .
                        '</a>
                    </td>
                </tr>
                <tr>
                    <td style="padding:8px 0;color:#666;">Email</td>
                    <td style="padding:8px 0;">' . e($email) . '</td>
                </tr>
                <tr>
                    <td style="padding:8px 0;color:#666;">Serviço</td>
                    <td style="padding:8px 0;">' . e($servico) . '</td>
                </tr>
                <tr>
                    <td style="padding:8px 0;color:#666;">Data/Hora</td>
                    <td style="padding:8px 0;">' . e($data) . ' às ' . e($hora) . '</td>
                </tr>
                <tr>
                    <td style="padding:8px 0;color:#666;vertical-align:top;">Observações</td>
                    <td style="padding:8px 0;line-height:1.5;">' . $safeObs . '</td>
                </tr>
            </table>

            <div style="margin-top:18px;padding-top:16px;border-top:1px solid #eee;">
                <div style="font-size:13px;color:#666;margin-bottom:10px;">
                    Escolhe uma opção. O link expira em 7 dias:
                </div>

                <a href="' . e($acceptUrl) . '" style="display:inline-block;margin:6px 10px 6px 0;background:#16a34a;color:#fff;text-decoration:none;padding:12px 14px;border-radius:8px;font-weight:700;">
                    Aceitar
                </a>

                <a href="' . e($declineUrl) . '" style="display:inline-block;margin:6px 10px 6px 0;background:#dc2626;color:#fff;text-decoration:none;padding:12px 14px;border-radius:8px;font-weight:700;">
                    Recusar
                </a>

                <a href="' . e($rescheduleUrl) . '" style="display:inline-block;margin:6px 10px 6px 0;background:#111827;color:#fff;text-decoration:none;padding:12px 14px;border-radius:8px;font-weight:700;">
                    Remarcar
                </a>
            </div>
        </div>
    </div>
</div>';

$mailFailure = null;

try {
    $mailConfig = require ROOT_PATH . '/inc/mail_config.php';
    $to = (string) ($mailConfig['notify_to'] ?? '');

    send_mail_html($to, $subject, $html);
} catch (Throwable $exception) {
    $mailFailure = $exception;

    error_log(
        'Pedido de marcação #' . $pedidoNum .
        ' registado, mas falhou o email de notificação: ' .
        $exception->getMessage()
    );
}

if ($mailFailure !== null) {
    require_once ROOT_PATH . '/inc/header.php';
    ?>
    <section class="card">
        <h1>Pedido registado</h1>

        <p>
            O pedido #<?= e($pedidoNum) ?> foi registado, mas não foi possível
            enviar a notificação por email ao salão. Não voltes a enviar o
            mesmo pedido; contacta o salão para confirmar que foi recebido.
        </p>

        <a class="btn btn-primary" href="<?= e(BASE_URL . '/pages/marcacoes.php') ?>">
            Voltar às marcações
        </a>
    </section>
    <?php
    require_once ROOT_PATH . '/inc/footer.php';
    exit;
}

header(
    'Location: ' .
    BASE_URL .
    '/pages/marcacao_obrigado.php?id=' .
    rawurlencode((string) $pedidoId)
);
exit;