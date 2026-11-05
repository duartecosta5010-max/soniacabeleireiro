<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

require_once ROOT_PATH . '/inc/db.php';
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

function fmtDatePt(string $value): string
{
    try {
        return (new DateTimeImmutable($value))->format('d/m/Y');
    } catch (Throwable $exception) {
        return $value;
    }
}

function fmtTime(string $value): string
{
    return substr($value, 0, 5);
}

$actionValue = $_GET['action'] ?? '';
$action = is_string($actionValue) ? $actionValue : '';

$idValue = $_GET['id'] ?? '';
$id = filter_var($idValue, FILTER_VALIDATE_INT);
$id = $id === false ? 0 : (int) $id;

$tokenValue = $_GET['token'] ?? '';
$token = is_string($tokenValue) ? trim($tokenValue) : '';

$allowedActions = ['accept', 'decline', 'reschedule'];

if (
    $id < 1 ||
    $token === '' ||
    !in_array($action, $allowedActions, true)
) {
    $title = 'Link inválido - Sónia Cabeleireiro';
    require_once ROOT_PATH . '/inc/header.php';
    ?>
    <section class="card">
        <h1>Link inválido</h1>
        <p>Este link de marcação não é válido.</p>
    </section>
    <?php
    require_once ROOT_PATH . '/inc/footer.php';
    exit;
}

$baseUrl = rtrim((string) BASE_URL, '/');

$actionUrl = $baseUrl
    . '/pages/marcacao_acao.php?'
    . http_build_query(
        [
            'action' => $action,
            'id' => $id,
            'token' => $token,
        ],
        '',
        '&',
        PHP_QUERY_RFC3986
    );

$rescheduleUrl = $baseUrl
    . '/pages/marcacao_acao.php?'
    . http_build_query(
        [
            'action' => 'reschedule',
            'id' => $id,
            'token' => $token,
        ],
        '',
        '&',
        PHP_QUERY_RFC3986
    );

$todayMin = (new DateTimeImmutable('today'))->format('Y-m-d');

$postedDateValue = $_POST['data_nova'] ?? '';
$postedDate = is_string($postedDateValue)
    ? trim($postedDateValue)
    : '';

$postedTimeValue = $_POST['hora_nova'] ?? '';
$postedTime = is_string($postedTimeValue)
    ? trim($postedTimeValue)
    : '';

$title = 'Gerir marcação - Sónia Cabeleireiro';
require_once ROOT_PATH . '/inc/header.php';

$req = null;
$actionError = null;
$notice = null;
$noticeType = 'ok';

try {
    $pdo = db();

    $stmt = $pdo->prepare(
        'SELECT *
         FROM booking_requests
         WHERE id = :id
           AND action_token = :token
         LIMIT 1'
    );

    $stmt->execute([
        ':id' => $id,
        ':token' => $token,
    ]);

    $foundRequest = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$foundRequest) {
        throw new RuntimeException(
            'Pedido não encontrado ou token inválido.'
        );
    }

    $req = $foundRequest;

    $expiresAt = new DateTimeImmutable(
        (string) $req['token_expires_at']
    );

    if (new DateTimeImmutable('now') > $expiresAt) {
        throw new RuntimeException('Este link expirou.');
    }

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $postActionValue = $_POST['action'] ?? '';
        $postAction = is_string($postActionValue)
            ? $postActionValue
            : '';

        if ($postAction !== $action) {
            throw new RuntimeException('Ação inválida.');
        }

        if ((string) $req['estado'] !== 'pendente') {
            throw new RuntimeException(
                'Este pedido já foi tratado.'
            );
        }

        $targetDate = '';
        $targetTime = '';

        if ($action === 'accept') {
            $targetDate = (string) $req['data_pretendida'];
            $targetTime = substr(
                (string) $req['hora_pretendida'],
                0,
                5
            );

            if (!booking_slot_is_valid_date($targetDate)) {
                throw new RuntimeException(
                    'A data pedida não é válida.'
                );
            }

            if (!booking_slot_is_valid_time($targetTime)) {
                throw new RuntimeException(
                    'A hora deste pedido não está num intervalo de 15 minutos.'
                );
            }
        } elseif ($action === 'reschedule') {
            $targetDate = $postedDate;
            $targetTime = $postedTime;

            if (!booking_slot_is_valid_date($targetDate)) {
                throw new RuntimeException(
                    'Indica uma data válida para a remarcação.'
                );
            }

            if (!booking_slot_is_valid_time($targetTime)) {
                throw new RuntimeException(
                    'A nova hora tem de ser escolhida de 15 em 15 minutos.'
                );
            }
        }

        if ($action === 'accept' || $action === 'reschedule') {
            $targetDateTime = new DateTimeImmutable(
                $targetDate . ' ' . $targetTime
            );

            if ($targetDateTime < new DateTimeImmutable('now')) {
                throw new RuntimeException(
                    'A data e a hora da marcação não podem ser anteriores ao momento atual.'
                );
            }
        }

        $slotLocked = false;

        try {
            if ($action === 'accept' || $action === 'reschedule') {
                $slotLocked = booking_slot_lock_acquire(
                    $pdo,
                    $targetDate,
                    $targetTime,
                    10
                );

                if (!$slotLocked) {
                    throw new RuntimeException(
                        'A agenda está a ser atualizada. Tenta novamente dentro de alguns segundos.'
                    );
                }
            }

            /*
             * A transação começa depois de obter o bloqueio da hora.
             * Assim, a verificação seguinte vê a marcação confirmada
             * mais recente para esse horário.
             */
            $pdo->beginTransaction();

            try {
                $stmt = $pdo->prepare(
                    'SELECT *
                     FROM booking_requests
                     WHERE id = :id
                       AND action_token = :token
                     LIMIT 1
                     FOR UPDATE'
                );

                $stmt->execute([
                    ':id' => $id,
                    ':token' => $token,
                ]);

                $currentRequest = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$currentRequest) {
                    throw new RuntimeException(
                        'Pedido não encontrado ou token inválido.'
                    );
                }

                $currentExpiresAt = new DateTimeImmutable(
                    (string) $currentRequest['token_expires_at']
                );

                if (new DateTimeImmutable('now') > $currentExpiresAt) {
                    throw new RuntimeException('Este link expirou.');
                }

                if ((string) $currentRequest['estado'] !== 'pendente') {
                    throw new RuntimeException(
                        'Este pedido já foi tratado.'
                    );
                }

                if ($action === 'accept') {
                    $currentDate = (string) $currentRequest['data_pretendida'];
                    $currentTime = substr(
                        (string) $currentRequest['hora_pretendida'],
                        0,
                        5
                    );

                    /*
                     * Confirma que o pedido continua a referir-se
                     * ao mesmo horário para o qual obtivemos o bloqueio.
                     */
                    if (
                        $currentDate !== $targetDate ||
                        $currentTime !== $targetTime
                    ) {
                        throw new RuntimeException(
                            'Os dados deste pedido foram alterados. Atualiza a página e tenta novamente.'
                        );
                    }
                }

                if (
                    $action === 'accept' ||
                    $action === 'reschedule'
                ) {
                    if (
                        booking_slot_has_confirmed_booking(
                            $pdo,
                            $targetDate,
                            $targetTime,
                            $id
                        )
                    ) {
                        throw new RuntimeException(
                            'Este horário já tem outra marcação confirmada. O pedido continua pendente; escolhe outra hora na opção Remarcar.'
                        );
                    }
                }

                if ($action === 'accept') {
                    $update = $pdo->prepare(
                        "UPDATE booking_requests
                         SET estado = 'aceite',
                             data_final = data_pretendida,
                             hora_final = hora_pretendida,
                             token_expires_at = NOW()
                         WHERE id = :id
                           AND action_token = :token
                           AND estado = 'pendente'"
                    );

                    $update->execute([
                        ':id' => $id,
                        ':token' => $token,
                    ]);
                } elseif ($action === 'decline') {
                    $update = $pdo->prepare(
                        "UPDATE booking_requests
                         SET estado = 'recusado',
                             token_expires_at = NOW()
                         WHERE id = :id
                           AND action_token = :token
                           AND estado = 'pendente'"
                    );

                    $update->execute([
                        ':id' => $id,
                        ':token' => $token,
                    ]);
                } else {
                    $update = $pdo->prepare(
                        "UPDATE booking_requests
                         SET estado = 'remarcado',
                             data_final = :data_final,
                             hora_final = :hora_final,
                             token_expires_at = NOW()
                         WHERE id = :id
                           AND action_token = :token
                           AND estado = 'pendente'"
                    );

                    $update->execute([
                        ':data_final' => $targetDate,
                        ':hora_final' => $targetTime,
                        ':id' => $id,
                        ':token' => $token,
                    ]);
                }

                if ($update->rowCount() !== 1) {
                    throw new RuntimeException(
                        'O pedido já foi alterado. Atualiza a página e verifica o estado atual.'
                    );
                }

                $pdo->commit();
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                throw $exception;
            }
        } finally {
            if ($slotLocked) {
                booking_slot_lock_release(
                    $pdo,
                    $targetDate,
                    $targetTime
                );
            }
        }

        $stmt = $pdo->prepare(
            'SELECT *
             FROM booking_requests
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            ':id' => $id,
        ]);

        $updatedRequest = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$updatedRequest) {
            throw new RuntimeException(
                'O pedido foi atualizado, mas não foi possível voltar a carregá-lo.'
            );
        }

        $req = $updatedRequest;

        $newState = (string) $req['estado'];
        $clientEmail = trim((string) ($req['email'] ?? ''));
        $clientName = (string) ($req['nome'] ?? '');
        $service = (string) ($req['servico'] ?? '');

        $emailDate = !empty($req['data_final'])
            ? (string) $req['data_final']
            : (string) $req['data_pretendida'];

        $emailTime = !empty($req['hora_final'])
            ? (string) $req['hora_final']
            : (string) $req['hora_pretendida'];

        $dateFormatted = fmtDatePt($emailDate);
        $timeFormatted = fmtTime($emailTime);
        $orderNumber = str_pad(
            (string) $req['id'],
            6,
            '0',
            STR_PAD_LEFT
        );

        if ($newState === 'aceite') {
            $subject = "Marcação confirmada #{$orderNumber} — {$dateFormatted} às {$timeFormatted}";
            $headline = 'Marcação confirmada';
            $messageHtml = 'A tua marcação foi <strong>confirmada</strong>.';
            $badgeBackground = '#16a34a';
        } elseif ($newState === 'recusado') {
            $subject = "Marcação recusada #{$orderNumber}";
            $headline = 'Marcação recusada';
            $messageHtml = 'Infelizmente, não foi possível confirmar a tua marcação para esta data e hora.';
            $badgeBackground = '#dc2626';
        } else {
            $subject = "Marcação remarcada #{$orderNumber} — {$dateFormatted} às {$timeFormatted}";
            $headline = 'Marcação remarcada';
            $messageHtml = 'A tua marcação foi <strong>remarcada</strong>. Verifica os novos detalhes abaixo.';
            $badgeBackground = '#111827';
        }

        if (
            $clientEmail !== '' &&
            filter_var($clientEmail, FILTER_VALIDATE_EMAIL)
        ) {
            $html = '
            <div style="font-family:Arial,sans-serif;background:#f6f6f6;padding:22px;">
                <div style="max-width:720px;margin:0 auto;background:#fff;border:1px solid #eee;border-radius:12px;overflow:hidden;">
                    <div style="padding:18px 20px;background:#111;color:#fff;">
                        <div style="font-size:14px;opacity:.9;">Sónia Cabeleireiro</div>
                        <div style="font-size:20px;font-weight:700;margin-top:6px;">
                            ' . e($headline) . ' #' . e($orderNumber) . '
                        </div>
                    </div>

                    <div style="padding:18px 20px;color:#111;">
                        <div style="display:inline-block;background:' . e($badgeBackground) . ';color:#fff;padding:6px 10px;border-radius:999px;font-weight:700;font-size:12px;">
                            ' . e($newState) . '
                        </div>

                        <p style="margin:12px 0 0;line-height:1.6;">
                            Olá, <strong>' . e($clientName) . '</strong>,<br>
                            ' . $messageHtml . '
                        </p>

                        <div style="margin-top:14px;">
                            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                                <tr>
                                    <td style="padding:8px 0;color:#666;width:170px;">Serviço</td>
                                    <td style="padding:8px 0;font-weight:700;">' . e($service) . '</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0;color:#666;">Data/Hora</td>
                                    <td style="padding:8px 0;font-weight:700;">
                                        ' . e($dateFormatted) . ' às ' . e($timeFormatted) . '
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div style="margin-top:14px;padding-top:14px;border-top:1px solid #eee;font-size:13px;color:#555;line-height:1.6;">
                            Se precisares de alterar ou cancelar, contacta-nos:<br>
                            <strong>+351 933 148 593</strong>
                            (chamada para a rede móvel nacional)
                        </div>
                    </div>
                </div>
            </div>';

            try {
                send_mail_html($clientEmail, $subject, $html);
                $notice = 'O cliente foi notificado por email: ' . $clientEmail . '.';
                $noticeType = 'ok';
            } catch (Throwable $mailException) {
                error_log(
                    'A marcação #' . $orderNumber .
                    ' foi atualizada, mas falhou o email ao cliente: ' .
                    $mailException->getMessage()
                );

                $notice = 'A marcação foi atualizada, mas não foi possível enviar o email ao cliente.';
                $noticeType = 'warn';
            }
        } else {
            $notice = 'A marcação foi atualizada, mas não existe um email válido para notificar o cliente.';
            $noticeType = 'warn';
        }
    }
} catch (Throwable $exception) {
    error_log(
        'Erro na ação da marcação #' . $id . ': ' .
        $exception->getMessage()
    );

    if ($exception instanceof PDOException) {
        $actionError = 'Ocorreu um erro ao consultar ou atualizar o pedido. Tenta novamente.';
    } else {
        $actionError = $exception->getMessage();
    }
}
?>

<?php if ($req === null): ?>
    <section class="card">
        <h1>Não foi possível abrir o pedido</h1>
        <p><?= e($actionError ?? 'O pedido não foi encontrado.') ?></p>
    </section>
<?php else: ?>
    <?php
    $state = (string) $req['estado'];

    $stateLabels = [
        'pendente' => 'Pendente',
        'aceite' => 'Aceite',
        'recusado' => 'Recusado',
        'remarcado' => 'Remarcado',
    ];

    $stateLabel = $stateLabels[$state] ?? $state;
    $orderNumber = str_pad(
        (string) $req['id'],
        6,
        '0',
        STR_PAD_LEFT
    );

    $requestedDate = (string) $req['data_pretendida'];
    $requestedTime = (string) $req['hora_pretendida'];
    ?>

    <?php if ($actionError !== null): ?>
        <div
            class="card"
            role="alert"
            style="margin-bottom:18px;border-left:6px solid #dc2626;"
        >
            <strong>Não foi possível concluir a ação:</strong>
            <p style="margin:6px 0 0;"><?= e($actionError) ?></p>

            <?php if ($state === 'pendente' && $action !== 'reschedule'): ?>
                <p style="margin:12px 0 0;">
                    <a class="btn btn-primary" href="<?= e($rescheduleUrl) ?>">
                        Escolher outra hora
                    </a>
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($notice !== null): ?>
        <div
            class="card"
            role="status"
            style="
                margin-bottom:18px;
                border-left:6px solid <?= $noticeType === 'ok' ? '#16a34a' : '#dc2626' ?>;
            "
        >
            <strong><?= $noticeType === 'ok' ? 'Sucesso:' : 'Aviso:' ?></strong>
            <div style="margin-top:6px;color:var(--muted);line-height:1.6;">
                <?= e($notice) ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <h1>Pedido #<?= e($orderNumber) ?></h1>

        <p style="color:var(--muted);margin-top:6px;">
            Estado atual:
            <strong><?= e($stateLabel) ?></strong>
        </p>

        <div style="margin-top:12px;line-height:1.8;">
            <div>
                <strong>Nome:</strong>
                <?= e((string) ($req['nome'] ?? '')) ?>
            </div>

            <div>
                <strong>Telemóvel:</strong>
                <a href="tel:<?= e((string) ($req['telemovel'] ?? '')) ?>">
                    <?= e((string) ($req['telemovel'] ?? '')) ?>
                </a>
            </div>

            <div>
                <strong>Email:</strong>

                <?php if (!empty($req['email'])): ?>
                    <?= e((string) $req['email']) ?>
                <?php else: ?>
                    <span style="color:var(--muted);">Não indicado</span>
                <?php endif; ?>
            </div>

            <div>
                <strong>Serviço:</strong>
                <?= e((string) ($req['servico'] ?? '')) ?>
            </div>

            <div>
                <strong>Data/hora pretendida:</strong>
                <?= e(fmtDatePt($requestedDate)) ?>
                às
                <?= e(fmtTime($requestedTime)) ?>
            </div>

            <?php if (!empty($req['data_final']) && !empty($req['hora_final'])): ?>
                <div>
                    <strong>Data/hora final:</strong>
                    <?= e(fmtDatePt((string) $req['data_final'])) ?>
                    às
                    <?= e(fmtTime((string) $req['hora_final'])) ?>
                </div>
            <?php endif; ?>

            <div>
                <strong>Observações:</strong>

                <?php if (!empty($req['obs'])): ?>
                    <div><?= nl2br(e((string) $req['obs'])) ?></div>
                <?php else: ?>
                    <span style="color:var(--muted);">Sem observações</span>
                <?php endif; ?>
            </div>
        </div>

        <hr style="border:0;border-top:1px solid var(--border);margin:16px 0;">

        <?php if ($state !== 'pendente'): ?>
            <p>Este pedido já foi tratado.</p>
        <?php elseif ($action === 'accept'): ?>
            <form method="post" action="<?= e($actionUrl) ?>">
                <input type="hidden" name="action" value="accept">
                <button class="btn btn-primary" type="submit">
                    Confirmar ACEITAR
                </button>
            </form>

            <p style="margin-top:12px;">
                <a href="<?= e($rescheduleUrl) ?>">Em vez disso, escolher outra hora</a>
            </p>
        <?php elseif ($action === 'decline'): ?>
            <form method="post" action="<?= e($actionUrl) ?>">
                <input type="hidden" name="action" value="decline">
                <button class="btn btn-dark" type="submit">
                    Confirmar RECUSAR
                </button>
            </form>
        <?php else: ?>
            <form method="post" action="<?= e($actionUrl) ?>">
                <input type="hidden" name="action" value="reschedule">

                <div class="form-grid" style="margin-top:10px;">
                    <div>
                        <label for="data_nova">Nova data</label>
                        <input
                            id="data_nova"
                            type="date"
                            name="data_nova"
                            min="<?= e($todayMin) ?>"
                            value="<?= e($postedDate) ?>"
                            required
                        >
                    </div>

                    <div>
                        <label for="hora_nova">Nova hora</label>
                        <input
                            id="hora_nova"
                            type="time"
                            name="hora_nova"
                            min="00:00"
                            max="23:45"
                            step="900"
                            value="<?= e($postedTime) ?>"
                            required
                        >
                        <div style="margin-top:6px;color:var(--muted);font-size:13px;">
                            Escolhe uma hora de 15 em 15 minutos.
                        </div>
                    </div>

                    <div class="full">
                        <button class="btn btn-primary" type="submit">
                            Confirmar REMARCAR
                        </button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once ROOT_PATH . '/inc/footer.php'; ?>