<?php
declare(strict_types=1);

function booking_slot_is_valid_date(string $date): bool
{
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date) !== 1) {
        return false;
    }

    $dateObject = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

    if ($dateObject === false) {
        return false;
    }

    $errors = DateTimeImmutable::getLastErrors();

    if (
        is_array($errors) &&
        ($errors['warning_count'] > 0 || $errors['error_count'] > 0)
    ) {
        return false;
    }

    return $dateObject->format('Y-m-d') === $date;
}

function booking_slot_is_valid_time(string $time): bool
{
    return preg_match(
        '/^(?:[01]\d|2[0-3]):(?:00|15|30|45)$/D',
        $time
    ) === 1;
}

function booking_slot_lock_name(string $date, string $time): string
{
    $normalizedTime = substr($time, 0, 5);

    return 'sonia_booking_' .
        substr(hash('sha256', $date . '|' . $normalizedTime), 0, 48);
}

function booking_slot_lock_acquire(
    PDO $pdo,
    string $date,
    string $time,
    int $timeoutSeconds = 10
): bool {
    $timeoutSeconds = max(0, min(30, $timeoutSeconds));
    $lockName = booking_slot_lock_name($date, $time);

    $stmt = $pdo->prepare(
        'SELECT GET_LOCK(:lock_name, ' . $timeoutSeconds . ')'
    );

    $stmt->execute([
        ':lock_name' => $lockName,
    ]);

    return (int) $stmt->fetchColumn() === 1;
}

function booking_slot_lock_release(
    PDO $pdo,
    string $date,
    string $time
): void {
    $lockName = booking_slot_lock_name($date, $time);

    try {
        $stmt = $pdo->prepare('SELECT RELEASE_LOCK(:lock_name)');
        $stmt->execute([
            ':lock_name' => $lockName,
        ]);
        $stmt->fetchColumn();
    } catch (Throwable $exception) {
        error_log(
            'Não foi possível libertar o bloqueio da marcação: ' .
            $exception->getMessage()
        );
    }
}

function booking_slot_has_confirmed_booking(
    PDO $pdo,
    string $date,
    string $time,
    ?int $excludeId = null
): bool {
    $normalizedTime = substr($time, 0, 5) . ':00';

    $sql = "
        SELECT id
        FROM booking_requests
        WHERE estado IN ('aceite', 'remarcado')
          AND COALESCE(data_final, data_pretendida) = :booking_date
          AND COALESCE(hora_final, hora_pretendida) = :booking_time
    ";

    if ($excludeId !== null) {
        $sql .= ' AND id <> :exclude_id';
    }

    $sql .= ' LIMIT 1';

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':booking_date', $date, PDO::PARAM_STR);
    $stmt->bindValue(':booking_time', $normalizedTime, PDO::PARAM_STR);

    if ($excludeId !== null) {
        $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
    }

    $stmt->execute();

    return $stmt->fetchColumn() !== false;
}