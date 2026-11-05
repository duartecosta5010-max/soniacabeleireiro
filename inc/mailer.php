<?php
require_once __DIR__ . '/../config.php';

function send_mail_html(string $to, string $subject, string $html, string $text = ''): bool {
  $cfg = require __DIR__ . '/mail_config.php';

  $base = ROOT_PATH . '/lib/PHPMailer/src/';
  $phpmailerFile  = $base . 'PHPMailer.php';
  $smtpFile       = $base . 'SMTP.php';
  $exceptionFile  = $base . 'Exception.php';

  if (!file_exists($phpmailerFile) || !file_exists($smtpFile) || !file_exists($exceptionFile)) {
    throw new RuntimeException(
      "PHPMailer não encontrado. Confirma se existe:\n" .
      $phpmailerFile . "\n" . $smtpFile . "\n" . $exceptionFile
    );
  }

  require_once $exceptionFile;
  require_once $phpmailerFile;
  require_once $smtpFile;

  $mail = new PHPMailer\PHPMailer\PHPMailer(true);

  $mail->CharSet = 'UTF-8';
  $mail->isSMTP();

  $mail->Host = $cfg['smtp_host'];
  $mail->SMTPAuth = true;
  $mail->Username = $cfg['smtp_user'];
  $mail->Password = $cfg['smtp_pass'];

  $mail->SMTPSecure = $cfg['smtp_secure'];
  $mail->Port = (int)$cfg['smtp_port'];

  $mail->setFrom($cfg['from_email'], $cfg['from_name']);

  $mail->addAddress($to);

  $mail->isHTML(true);
  $mail->Subject = $subject;
  $mail->Body = $html;

  if ($text === '') {
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($html)));
  }
  $mail->AltBody = $text;

  return $mail->send();
}