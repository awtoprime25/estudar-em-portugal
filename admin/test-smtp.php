<?php
/**
 * admin/test-smtp.php — Diagnóstico de envio SMTP (mesma configuração/lógica
 * do ajax-comp.php), com debug verboso do PHPMailer impresso no ecrã.
 *
 * Protegido por token VIEWS_STATS_TOKEN (mesmo padrão de admin/leads.php).
 * Existe só para diagnosticar falhas de SMTP em produção sem precisar de
 * acesso ao log de erros do servidor — apaga depois de resolvido.
 *
 * URL: admin/test-smtp.php?key=<TOKEN>&to=<email opcional, default MAIL_CC2>
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

header('Content-Type: text/plain; charset=utf-8');

$token = (string) ($_GET['key'] ?? '');
if (VIEWS_STATS_TOKEN === '' || !hash_equals(VIEWS_STATS_TOKEN, $token)) {
    header('HTTP/1.0 401 Unauthorized');
    echo "401 Unauthorized\n\nUso: " . htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? '/admin/test-smtp.php') . "?key=<TOKEN>\n";
    exit;
}

$to = (string) ($_GET['to'] ?? MAIL_CC2);
if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
    echo "Sem destinatário válido. Usa ?to=email@exemplo.com ou define MAIL_CC2 no .env.\n";
    exit;
}

echo "=== Configuração lida do .env ===\n";
echo "SMTP_HOST             = " . SMTP_HOST . "\n";
echo "SMTP_PORT             = " . SMTP_PORT . "\n";
echo "SMTP_USER             = " . (SMTP_USER !== '' ? SMTP_USER : '(vazio)') . "\n";
echo "SMTP_PASS             = " . (SMTP_PASS !== '' ? '(definida, ' . strlen(SMTP_PASS) . ' caracteres)' : '(vazio)') . "\n";
echo "SMTP_SECURE           = " . (SMTP_SECURE !== '' ? SMTP_SECURE : '(vazio — sem TLS/SSL)') . "\n";
echo "SMTP_ALLOW_SELF_SIGNED = " . (SMTP_ALLOW_SELF_SIGNED ? 'true' : 'false') . "\n";
echo "SMTP_FROM              = " . SMTP_FROM . "\n";
echo "MAIL_TO                = " . MAIL_TO . "\n";
echo "MAIL_CC                = " . (MAIL_CC !== '' ? MAIL_CC : '(vazio)') . "\n";
echo "MAIL_CC2               = " . (MAIL_CC2 !== '' ? MAIL_CC2 : '(vazio)') . "\n";
echo "A enviar teste para     = " . $to . "\n";
echo "\n=== Debug SMTP (PHPMailer, nível 2) ===\n";

if (SMTP_HOST === '') {
    echo "\nSMTP_HOST está vazio — o envio real seria sempre ignorado (skip silencioso).\n";
    exit;
}

require_once __DIR__ . '/../lib/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/src/SMTP.php';

$mail = new PHPMailer\PHPMailer\PHPMailer(true);
$mail->CharSet = 'UTF-8';
$mail->isSMTP();
$mail->Host       = SMTP_HOST;
$mail->Port       = SMTP_PORT;
$mail->Timeout    = 12;
$mail->SMTPDebug  = 2; // 0=off, 1=client, 2=client+server
$mail->Debugoutput = function ($str, $level) {
    echo $str . "\n";
};
$mail->SMTPSecure = SMTP_SECURE ?: null;
if (SMTP_ALLOW_SELF_SIGNED) {
    $mail->SMTPOptions = ['ssl' => [
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'allow_self_signed' => true,
    ]];
}
if (SMTP_USER !== '') {
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
}
$mail->setFrom(SMTP_FROM, SMTP_FROMNAME);
$mail->addAddress($to);
$mail->isHTML(false);
$mail->Subject = 'Teste SMTP — Estudar em Portugal (' . date('Y-m-d H:i:s') . ')';
$mail->Body    = 'Email de diagnóstico enviado por admin/test-smtp.php em ' . date('Y-m-d H:i:s') . '.';

echo "\n=== Resultado ===\n";
try {
    $mail->send();
    echo "OK — email enviado com sucesso para {$to}.\n";
} catch (\Throwable $e) {
    echo "FALHOU: " . $e->getMessage() . "\n";
}
