<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/inc/auth.php';

date_default_timezone_set('Europe/Lisbon');

$user = auth_user();

$servicoPreRaw = $_GET['servico'] ?? '';
$servicoPre = is_string($servicoPreRaw)
    ? trim($servicoPreRaw)
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

$title = 'Contactos - Sónia Cabeleireiro';
require_once ROOT_PATH . '/inc/header.php';
?>

<style>
    .contact-booking {
        --contact-booking-ink: #292320;
        --contact-booking-muted: #77706b;
        --contact-booking-accent: #a66853;
        --contact-booking-accent-dark: #82503f;
        --contact-booking-line: #ebe4df;

        margin-top: 18px;
        padding: clamp(22px, 4vw, 38px);
        border: 1px solid var(--contact-booking-line);
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 16px 42px rgba(44, 31, 25, .06);
    }

    .contact-booking *,
    .contact-booking *::before,
    .contact-booking *::after {
        box-sizing: border-box;
    }

    .contact-booking > h2 {
        margin: 0 0 24px;
        color: var(--contact-booking-ink);
        font-family: Georgia, "Times New Roman", serif;
        font-size: clamp(27px, 3vw, 34px);
        font-weight: 500;
        letter-spacing: -.025em;
    }

    .contact-booking .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px 18px;
    }

    .contact-booking .form-grid > div {
        min-width: 0;
    }

    .contact-booking .form-grid > .full {
        grid-column: 1 / -1;
    }

    .contact-booking label {
        display: block;
        margin: 0 0 8px;
        color: #39322e;
        font-size: 14px;
        font-weight: 600;
    }

    .contact-booking input,
    .contact-booking select,
    .contact-booking textarea {
        display: block;
        width: 100%;
        min-height: 50px;
        padding: 12px 14px;
        border: 1px solid #e5ded9;
        border-radius: 11px;
        outline: none;
        background: #fff;
        color: var(--contact-booking-ink);
        font: inherit;
        font-size: 15px;
        transition:
            border-color .18s ease,
            box-shadow .18s ease,
            background .18s ease;
    }

    .contact-booking input:focus,
    .contact-booking select:focus,
    .contact-booking textarea:focus {
        border-color: var(--contact-booking-accent);
        box-shadow: 0 0 0 4px rgba(166, 104, 83, .12);
    }

    .contact-booking input[readonly] {
        background: #f8f5f3;
        color: #655d58;
    }

    .contact-booking select {
        cursor: pointer;
    }

    .contact-booking textarea {
        min-height: 110px;
        resize: vertical;
    }

    .contact-booking-help {
        margin: 7px 0 0;
        color: var(--contact-booking-muted);
        font-size: 12px;
        line-height: 1.55;
    }

    .contact-time-picker {
        position: relative;
    }

    .contact-time-native[hidden],
    .contact-time-custom[hidden],
    .contact-time-panel[hidden] {
        display: none !important;
    }

    .contact-time-trigger {
        display: flex;
        width: 100%;
        min-height: 50px;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        border: 1px solid #e5ded9;
        border-radius: 11px;
        background: #fff;
        color: var(--contact-booking-ink);
        cursor: pointer;
        font: inherit;
        text-align: left;
        transition:
            border-color .18s ease,
            box-shadow .18s ease,
            background .18s ease;
    }

    .contact-time-trigger:hover {
        border-color: #caa697;
        background: #fffdfc;
    }

    .contact-time-trigger:focus-visible {
        outline: none;
        border-color: var(--contact-booking-accent);
        box-shadow: 0 0 0 4px rgba(166, 104, 83, .12);
    }

    .contact-time-trigger[aria-invalid="true"] {
        border-color: #bd5145;
        box-shadow: 0 0 0 3px rgba(189, 81, 69, .12);
    }

    .contact-time-trigger.is-selected {
        border-color: #d7b4a6;
        background: #fffdfc;
    }

    .contact-time-value {
        color: #6f6863;
        font-size: 15px;
    }

    .contact-time-trigger.is-selected .contact-time-value {
        color: var(--contact-booking-ink);
        font-weight: 700;
    }

    .contact-time-panel {
        position: absolute;
        z-index: 50;
        top: calc(100% + 9px);
        left: 0;
        width: min(410px, calc(100vw - 40px));
        max-height: min(540px, 72vh);
        overflow-y: auto;
        padding: 19px;
        border: 1px solid #e8ddd7;
        border-radius: 17px;
        background: #fff;
        box-shadow: 0 20px 50px rgba(38, 27, 22, .18);
    }

    .contact-time-panel-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 16px;
    }

    .contact-time-panel-header h3 {
        margin: 0;
        color: var(--contact-booking-ink);
        font-size: 17px;
        font-weight: 700;
    }

    .contact-time-panel-header p {
        margin: 5px 0 0;
        color: var(--contact-booking-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    .contact-time-close {
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

    .contact-time-close:hover {
        background: #faf7f5;
    }

    .contact-time-preview {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        padding: 11px 13px;
        border: 1px solid #f0e3dc;
        border-radius: 11px;
        background: #fcf7f4;
    }

    .contact-time-preview span {
        color: var(--contact-booking-muted);
        font-size: 12px;
    }

    .contact-time-preview strong {
        color: var(--contact-booking-accent-dark);
        font-size: 19px;
        letter-spacing: .04em;
    }

    .contact-time-section-title {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 15px 0 9px;
        color: #3f3834;
        font-size: 13px;
        font-weight: 700;
    }

    .contact-time-section-number {
        display: grid;
        width: 22px;
        height: 22px;
        place-items: center;
        border-radius: 50%;
        background: #f4e8e2;
        color: var(--contact-booking-accent-dark);
        font-size: 11px;
    }

    .contact-hour-grid,
    .contact-minute-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
    }

    .contact-hour-option,
    .contact-minute-option {
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

    .contact-hour-option:hover,
    .contact-minute-option:not(:disabled):hover {
        transform: translateY(-1px);
        border-color: #cda99a;
        background: #fcf7f4;
    }

    .contact-hour-option.is-selected,
    .contact-minute-option.is-selected {
        border-color: var(--contact-booking-accent);
        background: var(--contact-booking-accent);
        color: #fff;
        font-weight: 700;
    }

    .contact-minute-option {
        min-height: 42px;
        font-weight: 600;
    }

    .contact-minute-option:disabled {
        border-color: #f0ece9;
        background: #f8f6f5;
        color: #b8b1ad;
        cursor: not-allowed;
    }

    .contact-time-feedback {
        min-height: 18px;
        margin: 10px 0 0;
        color: var(--contact-booking-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    .contact-booking-actions {
        display: flex;
        justify-content: flex-end;
    }

    .contact-booking-submit {
        display: inline-flex;
        min-height: 49px;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 12px 20px;
        border: 1px solid var(--contact-booking-accent);
        border-radius: 11px;
        background: var(--contact-booking-accent);
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

    .contact-booking-submit:hover {
        transform: translateY(-1px);
        border-color: var(--contact-booking-accent-dark);
        background: var(--contact-booking-accent-dark);
        box-shadow: 0 8px 20px rgba(130, 80, 63, .19);
    }

    @media (max-width: 650px) {
        .contact-booking .form-grid {
            grid-template-columns: 1fr;
            gap: 17px;
        }

        .contact-booking .form-grid > .full {
            grid-column: auto;
        }

        .contact-booking-actions,
        .contact-booking-submit {
            width: 100%;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .contact-booking *,
        .contact-booking *::before,
        .contact-booking *::after {
            transition-duration: .01ms !important;
        }
    }
</style>

<h1 class="section-title reveal">Contactos / Marcações</h1>

<section class="contact-grid">
    <div class="card reveal">
        <h2>Contactos &amp; Horário</h2>

        <h3>Morada</h3>
        <p>
            Rua D. Manuel Correia Ramalho, Lote 6, Loja N<br>
            2070-095 Cartaxo
        </p>

        <h3>Contacto</h3>
        <p>
            <a href="tel:+351933148593">+351 933 148 593</a><br>
            <a href="mailto:soniarego78@hotmail.com">soniarego78@hotmail.com</a>
        </p>

        <h3>Horário</h3>
        <ul class="clean-list">
            <li><strong>Ter–Sex:</strong> 09:00–13:00 e 15:00–18:00</li>
            <li><strong>Sábado:</strong> 09:00–13:00</li>
            <li><strong>Domingo:</strong> Encerrado</li>
            <li><strong>Segunda:</strong> Encerrado</li>
        </ul>
    </div>

    <div class="card reveal">
        <h2>Localização</h2>

        <iframe
            class="map-embed"
            src="https://www.google.com/maps/embed?pb=!3m2!1spt-PT!2spt!4v1791487772362!5m2!1spt-PT!2spt!6m8!1m7!1svnHjkTjgJCFxwB1yYEv0OQ!2m2!1d39.15791861924367!2d-8.791702111921085!3f256.9679806459595!4f-17.638796860629!5f0.7820865974627469"
            allowfullscreen
            loading="lazy"
            referrerpolicy="strict-origin-when-cross-origin"
        ></iframe>
    </div>
</section>

<section class="card reveal contact-booking" id="marcacao">
    <h2>Pedido de marcação</h2>

    <?php if (!$user): ?>

        <p style="margin:0;color:var(--muted);line-height:1.7;">
            Para fazeres uma marcação é necessário ter conta e iniciar sessão.
        </p>

        <div style="margin-top:14px;">
            <button
                class="btn btn-primary"
                type="button"
                onclick="document.body.classList.add('auth-open');"
            >
                Iniciar sessão / Criar conta
            </button>
        </div>

    <?php else: ?>

        <form
            id="contact-booking-form"
            method="post"
            action="<?= $escape(BASE_URL . '/pages/enviar_marcacao.php') ?>"
        >
            <div class="form-grid">
                <div>
                    <label for="contact-booking-name">Nome</label>
                    <input
                        id="contact-booking-name"
                        type="text"
                        name="nome"
                        autocomplete="name"
                        required
                        value="<?= $escape($prefName) ?>"
                    >
                </div>

                <div>
                    <label for="contact-booking-phone">Telemóvel</label>
                    <input
                        id="contact-booking-phone"
                        type="text"
                        name="telemovel"
                        autocomplete="tel"
                        required
                        value="<?= $escape($prefPhone) ?>"
                    >
                </div>

                <div class="full">
                    <label for="contact-booking-email">Email da conta</label>
                    <input
                        id="contact-booking-email"
                        type="email"
                        autocomplete="email"
                        value="<?= $escape($prefEmail) ?>"
                        readonly
                    >
                    <p class="contact-booking-help">
                        A confirmação ou recusa será enviada para este email.
                    </p>
                </div>

                <div>
                    <label for="contact-booking-service">Serviço</label>
                    <select
                        id="contact-booking-service"
                        name="servico"
                        required
                    >
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

                <div>
                    <label for="contact-booking-date">Data pretendida</label>
                    <input
                        id="contact-booking-date"
                        type="date"
                        name="data"
                        required
                        min="<?= $escape($minDate) ?>"
                    >
                </div>

                <div>
                    <label id="contact-booking-time-label" for="contact-booking-time-native">
                        Hora pretendida
                    </label>

                    <div class="contact-time-picker" id="contact-time-picker">
                        <select
                            class="contact-time-native"
                            id="contact-booking-time-native"
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
                            class="contact-time-custom"
                            id="contact-booking-time-custom"
                            hidden
                        >
                            <button
                                class="contact-time-trigger"
                                id="contact-booking-time-trigger"
                                type="button"
                                aria-haspopup="dialog"
                                aria-expanded="false"
                                aria-controls="contact-booking-time-panel"
                                aria-labelledby="contact-booking-time-label contact-booking-time-display"
                                aria-describedby="contact-booking-time-help"
                            >
                                <span
                                    class="contact-time-value"
                                    id="contact-booking-time-display"
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
                                class="contact-time-panel"
                                id="contact-booking-time-panel"
                                role="dialog"
                                aria-labelledby="contact-booking-time-panel-title"
                                hidden
                            >
                                <div class="contact-time-panel-header">
                                    <div>
                                        <h3 id="contact-booking-time-panel-title">
                                            Escolhe uma hora
                                        </h3>
                                        <p>
                                            Horários: 09:00–13:00 e 15:00–18:00.
                                        </p>
                                    </div>

                                    <button
                                        class="contact-time-close"
                                        id="contact-booking-time-close"
                                        type="button"
                                        aria-label="Fechar seletor de hora"
                                    >
                                        &times;
                                    </button>
                                </div>

                                <div class="contact-time-preview">
                                    <span>Hora escolhida</span>
                                    <strong id="contact-booking-time-preview">
                                        --:--
                                    </strong>
                                </div>

                                <div class="contact-time-section-title">
                                    <span class="contact-time-section-number">1</span>
                                    <span>Escolhe a hora</span>
                                </div>

                                <div
                                    class="contact-hour-grid"
                                    role="group"
                                    aria-label="Escolhe a hora"
                                >
                                    <?php foreach ($bookingMinutesByHour as $hour => $minutes): ?>
                                        <?php
                                        $hourValue = sprintf('%02d', (int) $hour);
                                        $allowedMinutes = implode(',', $minutes);
                                        ?>
                                        <button
                                            class="contact-hour-option"
                                            type="button"
                                            data-hour="<?= $escape($hourValue) ?>"
                                            data-minutes="<?= $escape($allowedMinutes) ?>"
                                            aria-pressed="false"
                                        >
                                            <?= $escape($hourValue) ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>

                                <div class="contact-time-section-title">
                                    <span class="contact-time-section-number">2</span>
                                    <span>Escolhe os minutos</span>
                                </div>

                                <div
                                    class="contact-minute-grid"
                                    role="group"
                                    aria-label="Escolhe os minutos"
                                >
                                    <?php foreach (['00', '15', '30', '45'] as $minute): ?>
                                        <button
                                            class="contact-minute-option"
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
                                    class="contact-time-feedback"
                                    id="contact-booking-time-feedback"
                                    aria-live="polite"
                                >
                                    Escolhe primeiro a hora e depois os minutos.
                                </p>
                            </div>
                        </div>
                    </div>

                    <p
                        class="contact-booking-help"
                        id="contact-booking-time-help"
                    >
                        De 15 em 15 minutos, dentro do horário do salão.
                    </p>

                    <div class="full">
                        <label for="contact-booking-observations">
                            Observações
                        </label>
                        <textarea
                            id="contact-booking-observations"
                            name="obs"
                            rows="4"
                            placeholder="Se quiseres, deixa aqui alguma informação adicional."
                        ></textarea>
                    </div>

                    <div class="full contact-booking-actions">
                        <button
                            class="contact-booking-submit"
                            type="submit"
                        >
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

    <?php endif; ?>
</section>

<script>
(() => {
    const picker = document.getElementById('contact-time-picker');
    const nativeSelect = document.getElementById('contact-booking-time-native');
    const customPicker = document.getElementById('contact-booking-time-custom');
    const trigger = document.getElementById('contact-booking-time-trigger');
    const panel = document.getElementById('contact-booking-time-panel');
    const closeButton = document.getElementById('contact-booking-time-close');
    const label = document.getElementById('contact-booking-time-label');
    const display = document.getElementById('contact-booking-time-display');
    const preview = document.getElementById('contact-booking-time-preview');
    const feedback = document.getElementById('contact-booking-time-feedback');
    const form = document.getElementById('contact-booking-form');

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
        picker.querySelectorAll('.contact-hour-option')
    );

    const minuteButtons = Array.from(
        picker.querySelectorAll('.contact-minute-option')
    );

    const allowedTimes = new Set(
        Array.from(nativeSelect.options)
            .map((option) => option.value)
            .filter((value) => value !== '')
    );

    let selectedHour = null;
    let selectedMinute = null;

    const readNativeValue = () => {
        const match = nativeSelect.value.match(/^(\d{2}):(\d{2})$/);

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

            feedback.textContent =
                'Escolhe os minutos disponíveis para esta hora.';
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

            if (!newMinute || !getAllowedMinutes().includes(newMinute)) {
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