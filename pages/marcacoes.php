<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/inc/auth.php';

date_default_timezone_set('Europe/Lisbon');

$user = auth_user();

$servicoQuery = $_GET['servico'] ?? '';
$servicoPre = is_string($servicoQuery)
    ? trim($servicoQuery)
    : '';

$servicos = [
    'Corte',
    'Brushing / Finalização',
    'Coloração',
    'Madeixas / Balayage',
    'Tratamentos',
    'Penteados / Eventos',
];

$minDate = (new DateTimeImmutable('today'))->format('Y-m-d');

$prefName = $user ? user_name($user) : '';
$prefPhone = $user ? user_phone($user) : '';
$prefEmail = $user ? user_email($user) : '';

/*
 * Horários disponíveis, de 15 em 15 minutos:
 * 09:00–13:00 e 15:00–18:00.
 */
$bookingRanges = [
    ['inicio' => 9 * 60, 'fim' => 13 * 60],
    ['inicio' => 15 * 60, 'fim' => 18 * 60],
];

$bookingMinutesByHour = [];

foreach ($bookingRanges as $range) {
    for (
        $minutesOfDay = $range['inicio'];
        $minutesOfDay <= $range['fim'];
        $minutesOfDay += 15
    ) {
        $hour = sprintf('%02d', intdiv($minutesOfDay, 60));
        $minute = sprintf('%02d', $minutesOfDay % 60);

        if (!isset($bookingMinutesByHour[$hour])) {
            $bookingMinutesByHour[$hour] = [];
        }

        $bookingMinutesByHour[$hour][] = $minute;
    }
}

$escape = static function ($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
};

$title = 'Marcações - Sónia Cabeleireiro';
require_once ROOT_PATH . '/inc/header.php';
?>

<style>
    .booking-page {
        --booking-ink: #292320;
        --booking-muted: #77706b;
        --booking-accent: #a66853;
        --booking-accent-dark: #82503f;
        --booking-line: #ebe4df;
        --booking-soft: #faf7f5;

        width: calc(100% - 32px);
        max-width: 1040px;
        margin: 38px auto 70px;
        color: var(--booking-ink);
    }

    .booking-page *,
    .booking-page *::before,
    .booking-page *::after {
        box-sizing: border-box;
    }

    .booking-header {
        margin-bottom: 25px;
        text-align: center;
    }

    .booking-kicker {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 13px;
        color: var(--booking-accent-dark);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .booking-kicker::before {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--booking-accent);
        content: "";
    }

    .booking-title {
        margin: 0;
        color: var(--booking-ink);
        font-family: Georgia, "Times New Roman", serif;
        font-size: clamp(42px, 6vw, 66px);
        font-weight: 500;
        letter-spacing: -.045em;
        line-height: 1.05;
    }

    .booking-title span {
        color: var(--booking-accent);
    }

    .booking-subtitle {
        max-width: 620px;
        margin: 15px auto 0;
        color: var(--booking-muted);
        font-size: 16px;
        line-height: 1.7;
    }

    .booking-info {
        display: flex;
        align-items: flex-start;
        gap: 13px;
        margin-bottom: 20px;
        padding: 17px 20px;
        border: 1px solid #eee5df;
        border-radius: 16px;
        background: linear-gradient(135deg, #fff 0%, #fcf8f5 100%);
    }

    .booking-info-icon {
        display: grid;
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        place-items: center;
        border-radius: 50%;
        background: #f5e9e3;
        color: var(--booking-accent-dark);
    }

    .booking-info p {
        margin: 3px 0 0;
        color: #655d58;
        font-size: 14px;
        line-height: 1.65;
    }

    .booking-card {
        position: relative;
        overflow: visible;
        padding: clamp(22px, 4vw, 40px);
        border: 1px solid var(--booking-line);
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 18px 48px rgba(44, 31, 25, .07);
    }

    .booking-card-heading {
        margin-bottom: 27px;
    }

    .booking-card-eyebrow {
        margin: 0 0 8px;
        color: var(--booking-accent-dark);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .booking-card-heading h2 {
        margin: 0;
        color: var(--booking-ink);
        font-family: Georgia, "Times New Roman", serif;
        font-size: clamp(27px, 3vw, 34px);
        font-weight: 500;
        letter-spacing: -.025em;
    }

    .booking-card-heading p {
        margin: 9px 0 0;
        color: var(--booking-muted);
        font-size: 14px;
        line-height: 1.65;
    }

    .booking-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 21px 20px;
    }

    .booking-field {
        min-width: 0;
    }

    .booking-field--full {
        grid-column: 1 / -1;
    }

    .booking-field label {
        display: block;
        margin: 0 0 8px;
        color: #39322e;
        font-size: 14px;
        font-weight: 600;
    }

    .booking-field input,
    .booking-field select,
    .booking-field textarea {
        display: block;
        width: 100%;
        min-height: 52px;
        padding: 12px 15px;
        border: 1px solid #e5ded9;
        border-radius: 12px;
        outline: none;
        background: #fff;
        color: var(--booking-ink);
        font: inherit;
        font-size: 15px;
        transition:
            border-color .18s ease,
            box-shadow .18s ease,
            background .18s ease;
    }

    .booking-field input:focus,
    .booking-field select:focus,
    .booking-field textarea:focus {
        border-color: var(--booking-accent);
        box-shadow: 0 0 0 4px rgba(166, 104, 83, .12);
    }

    .booking-field input[readonly] {
        background: #f8f5f3;
        color: #655d58;
    }

    .booking-field textarea {
        min-height: 118px;
        resize: vertical;
    }

    .booking-field select {
        cursor: pointer;
    }

    .booking-help {
        margin: 7px 0 0;
        color: var(--booking-muted);
        font-size: 12px;
        line-height: 1.55;
    }

    .booking-time-picker {
        position: relative;
    }

    .booking-time-native[hidden],
    .booking-time-custom[hidden],
    .booking-time-panel[hidden] {
        display: none !important;
    }

    .booking-time-trigger {
        display: flex;
        width: 100%;
        min-height: 52px;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 15px;
        border: 1px solid #e5ded9;
        border-radius: 12px;
        background: #fff;
        color: var(--booking-ink);
        cursor: pointer;
        font: inherit;
        text-align: left;
        transition:
            border-color .18s ease,
            box-shadow .18s ease,
            background .18s ease;
    }

    .booking-time-trigger:hover {
        border-color: #caa697;
        background: #fffdfc;
    }

    .booking-time-trigger:focus-visible {
        outline: none;
        border-color: var(--booking-accent);
        box-shadow: 0 0 0 4px rgba(166, 104, 83, .12);
    }

    .booking-time-trigger[aria-invalid="true"] {
        border-color: #bd5145;
        box-shadow: 0 0 0 3px rgba(189, 81, 69, .12);
    }

    .booking-time-trigger.is-selected {
        border-color: #d7b4a6;
        background: #fffdfc;
    }

    .booking-time-value {
        color: #6f6863;
        font-size: 15px;
    }

    .booking-time-trigger.is-selected .booking-time-value {
        color: var(--booking-ink);
        font-weight: 700;
    }

    .booking-time-panel {
        position: absolute;
        z-index: 50;
        top: calc(100% + 10px);
        left: 0;
        width: min(420px, calc(100vw - 48px));
        max-height: min(560px, 76vh);
        overflow-y: auto;
        padding: 20px;
        border: 1px solid #e8ddd7;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 22px 55px rgba(38, 27, 22, .18);
    }

    .booking-time-panel-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 17px;
    }

    .booking-time-panel-header h3 {
        margin: 0;
        color: var(--booking-ink);
        font-size: 17px;
        font-weight: 700;
    }

    .booking-time-panel-header p {
        margin: 5px 0 0;
        color: var(--booking-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    .booking-time-close {
        display: grid;
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        place-items: center;
        border: 1px solid #eee6e1;
        border-radius: 10px;
        background: #fff;
        color: #625a55;
        cursor: pointer;
        font-size: 20px;
        line-height: 1;
    }

    .booking-time-close:hover {
        background: var(--booking-soft);
    }

    .booking-time-preview {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 17px;
        padding: 12px 14px;
        border: 1px solid #f0e3dc;
        border-radius: 12px;
        background: #fcf7f4;
    }

    .booking-time-preview span {
        color: var(--booking-muted);
        font-size: 12px;
    }

    .booking-time-preview strong {
        color: var(--booking-accent-dark);
        font-size: 19px;
        letter-spacing: .04em;
    }

    .booking-time-section-title {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 16px 0 10px;
        color: #3f3834;
        font-size: 13px;
        font-weight: 700;
    }

    .booking-time-section-number {
        display: grid;
        width: 22px;
        height: 22px;
        place-items: center;
        border-radius: 50%;
        background: #f4e8e2;
        color: var(--booking-accent-dark);
        font-size: 11px;
    }

    .booking-hour-grid,
    .booking-minute-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
    }

    .booking-hour-option,
    .booking-minute-option {
        min-height: 39px;
        border: 1px solid #ece5e0;
        border-radius: 10px;
        background: #fff;
        color: #403934;
        cursor: pointer;
        font: inherit;
        font-size: 13px;
        transition:
            border-color .16s ease,
            background .16s ease,
            color .16s ease,
            transform .16s ease;
    }

    .booking-hour-option:hover,
    .booking-minute-option:not(:disabled):hover {
        transform: translateY(-1px);
        border-color: #cda99a;
        background: #fcf7f4;
    }

    .booking-hour-option.is-selected,
    .booking-minute-option.is-selected {
        border-color: var(--booking-accent);
        background: var(--booking-accent);
        color: #fff;
        font-weight: 700;
    }

    .booking-minute-option {
        min-height: 43px;
        font-weight: 600;
    }

    .booking-minute-option:disabled {
        border-color: #f0ece9;
        background: #f8f6f5;
        color: #b8b1ad;
        cursor: not-allowed;
    }

    .booking-time-feedback {
        min-height: 18px;
        margin: 11px 0 0;
        color: var(--booking-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    .booking-actions {
        display: flex;
        justify-content: flex-end;
        padding-top: 3px;
    }

    .booking-submit,
    .booking-login-button {
        display: inline-flex;
        min-height: 50px;
        align-items: center;
        justify-content: center;
        gap: 11px;
        padding: 12px 21px;
        border: 1px solid var(--booking-accent);
        border-radius: 12px;
        background: var(--booking-accent);
        color: #fff;
        cursor: pointer;
        font: inherit;
        font-size: 14px;
        font-weight: 700;
        transition:
            background .18s ease,
            border-color .18s ease,
            transform .18s ease,
            box-shadow .18s ease;
    }

    .booking-submit:hover,
    .booking-login-button:hover {
        transform: translateY(-1px);
        border-color: var(--booking-accent-dark);
        background: var(--booking-accent-dark);
        box-shadow: 0 8px 20px rgba(130, 80, 63, .19);
    }

    .booking-login-card {
        text-align: center;
    }

    .booking-login-icon {
        display: grid;
        width: 54px;
        height: 54px;
        margin: 0 auto 16px;
        place-items: center;
        border-radius: 50%;
        background: #f5e9e3;
        color: var(--booking-accent-dark);
    }

    .booking-login-card h2 {
        margin: 0;
        color: var(--booking-ink);
        font-family: Georgia, "Times New Roman", serif;
        font-size: 30px;
        font-weight: 500;
    }

    .booking-login-card p {
        max-width: 560px;
        margin: 11px auto 20px;
        color: var(--booking-muted);
        font-size: 14px;
        line-height: 1.7;
    }

    .booking-login-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    .booking-contact-link {
        display: inline-flex;
        min-height: 50px;
        align-items: center;
        justify-content: center;
        padding: 12px 18px;
        border: 1px solid #e5ded9;
        border-radius: 12px;
        background: #fff;
        color: #514944;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: background .18s ease, border-color .18s ease;
    }

    .booking-contact-link:hover {
        border-color: #d3b6a9;
        background: #fcf8f6;
    }

    @media (max-width: 650px) {
        .booking-page {
            width: calc(100% - 24px);
            margin-top: 25px;
        }

        .booking-grid {
            grid-template-columns: 1fr;
            gap: 17px;
        }

        .booking-field--full {
            grid-column: auto;
        }

        .booking-card {
            border-radius: 18px;
        }

        .booking-actions {
            justify-content: stretch;
        }

        .booking-submit {
            width: 100%;
        }

        .booking-time-panel {
            width: min(420px, calc(100vw - 40px));
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .booking-page *,
        .booking-page *::before,
        .booking-page *::after {
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
        }
    }
</style>

<div class="booking-page">
    <header class="booking-header">
        <div class="booking-kicker">
            Sónia Cabeleireiro · Marcações online
        </div>

        <h1 class="booking-title">
            Marcações <span>online</span>
        </h1>

        <p class="booking-subtitle">
            Escolhe o serviço, o dia e a hora que te dão jeito.
            Entraremos em contacto para confirmar a disponibilidade.
        </p>
    </header>

    <div class="booking-info">
        <div class="booking-info-icon" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path
                    d="M12 8v4l2.5 1.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </div>

        <p>
            O pedido fica sujeito à confirmação da disponibilidade.
            A resposta será enviada para o email associado à tua conta.
        </p>
    </div>

    <?php if (!$user): ?>

        <section class="booking-card booking-login-card">
            <div class="booking-login-icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path
                        d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4.5 20a7.5 7.5 0 0 1 15 0"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                    />
                </svg>
            </div>

            <h2>Inicia sessão para marcar</h2>

            <p>
                Para fazeres uma marcação é necessário ter conta e iniciar sessão.
                Assim, a confirmação ou recusa será enviada para o email associado à tua conta.
            </p>

            <div class="booking-login-actions">
                <button
                    class="booking-login-button"
                    type="button"
                    onclick="document.body.classList.add('auth-open');"
                >
                    Iniciar sessão / Criar conta
                </button>

                <a
                    class="booking-contact-link"
                    href="<?= $escape(BASE_URL . '/pages/contactos.php') ?>"
                >
                    Ver contactos
                </a>
            </div>
        </section>

    <?php else: ?>

        <section class="booking-card">
            <div class="booking-card-heading">
                <p class="booking-card-eyebrow">Novo pedido</p>
                <h2>Pedido de marcação</h2>
                <p>
                    Preenche os teus dados e indica quando preferes vir ao salão.
                </p>
            </div>

            <form
                id="booking-form"
                method="post"
                action="<?= $escape(BASE_URL . '/pages/enviar_marcacao.php') ?>"
            >
                <div class="booking-grid">
                    <div class="booking-field">
                        <label for="nome">Nome</label>
                        <input
                            id="nome"
                            type="text"
                            name="nome"
                            autocomplete="name"
                            placeholder="O teu nome"
                            required
                            value="<?= $escape($prefName) ?>"
                        >
                    </div>

                    <div class="booking-field">
                        <label for="telemovel">Telemóvel</label>
                        <input
                            id="telemovel"
                            type="text"
                            name="telemovel"
                            autocomplete="tel"
                            placeholder="O teu número de telemóvel"
                            required
                            value="<?= $escape($prefPhone) ?>"
                        >
                    </div>

                    <div class="booking-field booking-field--full">
                        <label for="email">Email da conta</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            autocomplete="email"
                            value="<?= $escape($prefEmail) ?>"
                            readonly
                        >
                        <p class="booking-help">
                            A confirmação ou recusa será enviada para este email.
                        </p>
                    </div>

                    <div class="booking-field">
                        <label for="servico">Serviço</label>
                        <select id="servico" name="servico" required>
                            <option value="">Seleciona um serviço</option>

                            <?php foreach ($servicos as $servico): ?>
                                <option
                                    value="<?= $escape($servico) ?>"
                                    <?= $servicoPre === $servico ? 'selected' : '' ?>
                                >
                                    <?= $escape($servico) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="booking-field">
                        <label for="data">Data pretendida</label>
                        <input
                            id="data"
                            type="date"
                            name="data"
                            required
                            min="<?= $escape($minDate) ?>"
                        >
                    </div>

                    <div class="booking-field">
                        <label id="hora-label" for="hora-native">
                            Hora pretendida
                        </label>

                        <div class="booking-time-picker" id="booking-time-picker">
                            <select
                                class="booking-time-native"
                                id="hora-native"
                                name="hora"
                                required
                            >
                                <option value="">Escolhe uma hora</option>

                                <?php foreach ($bookingMinutesByHour as $hour => $minutes): ?>
                                    <?php $hourValue = sprintf('%02d', (int) $hour); ?>

                                    <?php foreach ($minutes as $minute): ?>
                                        <?php $timeValue = $hourValue . ':' . $minute; ?>
                                        <option value="<?= $escape($timeValue) ?>">
                                            <?= $escape($timeValue) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </select>

                            <div
                                class="booking-time-custom"
                                id="booking-time-custom"
                                hidden
                            >
                                <button
                                    class="booking-time-trigger"
                                    id="hora-trigger"
                                    type="button"
                                    aria-haspopup="dialog"
                                    aria-expanded="false"
                                    aria-controls="hora-panel"
                                    aria-labelledby="hora-label hora-display"
                                    aria-describedby="hora-ajuda"
                                >
                                    <span
                                        class="booking-time-value"
                                        id="hora-display"
                                    >
                                        Escolher hora
                                    </span>

                                    <svg
                                        width="19"
                                        height="19"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        aria-hidden="true"
                                    >
                                        <circle
                                            cx="12"
                                            cy="12"
                                            r="8.5"
                                            stroke="currentColor"
                                            stroke-width="1.7"
                                        />
                                        <path
                                            d="M12 7v5l3 2"
                                            stroke="currentColor"
                                            stroke-width="1.7"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </button>

                                <div
                                    class="booking-time-panel"
                                    id="hora-panel"
                                    role="dialog"
                                    aria-labelledby="hora-panel-title"
                                    hidden
                                >
                                    <div class="booking-time-panel-header">
                                        <div>
                                            <h3 id="hora-panel-title">
                                                Escolhe uma hora
                                            </h3>
                                            <p>
                                                Disponível das 09:00 às 13:00
                                                e das 15:00 às 18:00.
                                            </p>
                                        </div>

                                        <button
                                            class="booking-time-close"
                                            id="hora-close"
                                            type="button"
                                            aria-label="Fechar seletor de hora"
                                        >
                                            &times;
                                        </button>
                                    </div>

                                    <div class="booking-time-preview">
                                        <span>Hora escolhida</span>
                                        <strong id="hora-preview">--:--</strong>
                                    </div>

                                    <div class="booking-time-section-title">
                                        <span class="booking-time-section-number">
                                            1
                                        </span>
                                        <span>Escolhe a hora</span>
                                    </div>

                                    <div
                                        class="booking-hour-grid"
                                        role="group"
                                        aria-label="Escolhe a hora"
                                    >
                                        <?php foreach ($bookingMinutesByHour as $hour => $minutes): ?>
                                            <?php
                                            $hourValue = sprintf('%02d', (int) $hour);
                                            $allowedMinutes = implode(',', $minutes);
                                            ?>
                                            <button
                                                class="booking-hour-option"
                                                type="button"
                                                data-hour="<?= $escape($hourValue) ?>"
                                                data-minutes="<?= $escape($allowedMinutes) ?>"
                                                aria-pressed="false"
                                            >
                                                <?= $escape($hourValue) ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="booking-time-section-title">
                                        <span class="booking-time-section-number">
                                            2
                                        </span>
                                        <span>Escolhe os minutos</span>
                                    </div>

                                    <div
                                        class="booking-minute-grid"
                                        role="group"
                                        aria-label="Escolhe os minutos"
                                    >
                                        <?php foreach (['00', '15', '30', '45'] as $minute): ?>
                                            <button
                                                class="booking-minute-option"
                                                type="button"
                                                data-minute="<?= $escape($minute) ?>"
                                                aria-pressed="false"
                                                disabled
                                            >
                                                <?= $escape(':' . $minute) ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>

                                    <p
                                        class="booking-time-feedback"
                                        id="hora-feedback"
                                        aria-live="polite"
                                    >
                                        Escolhe primeiro a hora e depois os minutos.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <p class="booking-help" id="hora-ajuda">
                            Horários de 15 em 15 minutos: 09:00–13:00 e 15:00–18:00.
                        </p>
                    </div>

                    <div class="booking-field booking-field--full">
                        <label for="obs">Observações</label>
                        <textarea
                            id="obs"
                            name="obs"
                            rows="4"
                            placeholder="Se quiser, deixe aqui alguma informação adicional."
                        ></textarea>
                    </div>

                    <div class="booking-field booking-field--full booking-actions">
                        <button class="booking-submit" type="submit">
                            <span>Enviar pedido</span>
                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M5 12h14M13 6l6 6-6 6"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </section>

    <?php endif; ?>
</div>

<script>
(() => {
    const picker = document.getElementById('booking-time-picker');
    const nativeSelect = document.getElementById('hora-native');
    const customPicker = document.getElementById('booking-time-custom');
    const trigger = document.getElementById('hora-trigger');
    const panel = document.getElementById('hora-panel');
    const closeButton = document.getElementById('hora-close');
    const label = document.getElementById('hora-label');
    const display = document.getElementById('hora-display');
    const preview = document.getElementById('hora-preview');
    const feedback = document.getElementById('hora-feedback');
    const form = document.getElementById('booking-form');

    if (
        !picker ||
        !nativeSelect ||
        !customPicker ||
        !trigger ||
        !panel ||
        !display ||
        !preview ||
        !feedback
    ) {
        return;
    }

    const hourButtons = Array.from(
        picker.querySelectorAll('.booking-hour-option')
    );

    const minuteButtons = Array.from(
        picker.querySelectorAll('.booking-minute-option')
    );

    const allowedTimes = new Set(
        Array.from(nativeSelect.options)
            .map((option) => option.value)
            .filter((value) => value !== '')
    );

    let selectedHour = null;
    let selectedMinute = null;

    const readNativeValue = () => {
        const match = nativeSelect.value.match(
            /^([01]\d|2[0-3]):(00|15|30|45)$/
        );

        if (match && allowedTimes.has(nativeSelect.value)) {
            selectedHour = match[1];
            selectedMinute = match[2];
        }
    };

    const getAllowedMinutes = () => {
        const hourButton = hourButtons.find(
            (button) => button.dataset.hour === selectedHour
        );

        if (!hourButton) {
            return [];
        }

        return (hourButton.dataset.minutes || '')
            .split(',')
            .filter((minute) => minute !== '');
    };

    const updatePicker = () => {
        const hasCompleteSelection = allowedTimes.has(nativeSelect.value);
        const allowedMinutes = getAllowedMinutes();

        hourButtons.forEach((button) => {
            const isSelected = button.dataset.hour === selectedHour;

            button.classList.toggle('is-selected', isSelected);
            button.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
        });

        minuteButtons.forEach((button) => {
            const isAllowed = allowedMinutes.includes(button.dataset.minute);
            const isSelected =
                isAllowed && button.dataset.minute === selectedMinute;

            button.disabled = !isAllowed;
            button.classList.toggle('is-selected', isSelected);
            button.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
        });

        display.textContent = hasCompleteSelection
            ? nativeSelect.value
            : 'Escolher hora';

        trigger.classList.toggle('is-selected', hasCompleteSelection);

        if (hasCompleteSelection) {
            trigger.removeAttribute('aria-invalid');
            preview.textContent = nativeSelect.value;
            feedback.textContent = 'Hora selecionada.';
        } else if (selectedHour !== null) {
            preview.textContent =
                selectedHour + ':' +
                (selectedMinute === null ? '__' : selectedMinute);

            feedback.textContent = selectedMinute === null
                ? 'Agora escolhe os minutos disponíveis para esta hora.'
                : 'Escolhe os minutos para completar a hora.';
        } else {
            preview.textContent = '--:--';
            feedback.textContent =
                'Escolhe primeiro a hora e depois os minutos.';
        }
    };

    const openPanel = () => {
        readNativeValue();
        panel.hidden = false;
        trigger.setAttribute('aria-expanded', 'true');
        updatePicker();
    };

    const closePanel = (restoreFocus = false) => {
        panel.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');

        if (restoreFocus) {
            trigger.focus();
        }
    };

    nativeSelect.hidden = true;
    nativeSelect.removeAttribute('required');
    customPicker.hidden = false;

    if (label) {
        label.htmlFor = trigger.id;
    }

    readNativeValue();
    updatePicker();

    trigger.addEventListener('click', () => {
        if (panel.hidden) {
            openPanel();
        } else {
            closePanel();
        }
    });

    if (closeButton) {
        closeButton.addEventListener('click', () => {
            closePanel(true);
        });
    }

    hourButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const newHour = button.dataset.hour;

            if (!newHour) {
                return;
            }

            if (newHour !== selectedHour) {
                selectedHour = newHour;
                selectedMinute = null;
                nativeSelect.value = '';
            }

            updatePicker();
        });
    });

    minuteButtons.forEach((button) => {
        button.addEventListener('click', () => {
            if (selectedHour === null) {
                return;
            }

            const newMinute = button.dataset.minute;
            const allowedMinutes = getAllowedMinutes();

            if (!newMinute || !allowedMinutes.includes(newMinute)) {
                return;
            }

            selectedMinute = newMinute;
            nativeSelect.value = selectedHour + ':' + selectedMinute;

            nativeSelect.dispatchEvent(
                new Event('change', { bubbles: true })
            );

            updatePicker();
            closePanel(true);
        });
    });

    document.addEventListener('pointerdown', (event) => {
        if (!panel.hidden && !picker.contains(event.target)) {
            closePanel();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !panel.hidden) {
            event.preventDefault();
            closePanel(true);
        }
    });

    if (form) {
        form.addEventListener('submit', (event) => {
            if (!allowedTimes.has(nativeSelect.value)) {
                event.preventDefault();
                trigger.setAttribute('aria-invalid', 'true');
                openPanel();
                feedback.textContent =
                    'Escolhe uma hora disponível para continuar.';
                trigger.focus();
            }
        });
    }
})();
</script>

<?php require_once ROOT_PATH . '/inc/footer.php'; ?>