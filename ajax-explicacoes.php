<?php
/**
 * ajax-explicacoes.php — handler do formulário próprio de Cursos Preparatórios
 * (cursos-preparacao-exames.php). FORMULÁRIO DISTINTO do StudyWing
 * (includes/studywing-form.php + ajax-comp.php): este é para marcar aulas
 * de explicações/exames nacionais, não para a assessoria de admissão.
 * Mesmo padrão de segurança e mesmo SMTP_FROM do ajax-comp.php, mas
 * destinatários e campos próprios (ver EXPLICACOES_MAIL_TO/CC/CC2 em config.php).
 *
 * Recebe POST, valida, grava em BD (leads, form_tipo='explicacoes'), depois
 * envia email SMTP best-effort com PHPMailer.
 *
 * Retorna JSON: {ok:bool, message:string, errors?:array}.
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db-helper.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Método não permitido.']);
    exit;
}

// Rate limit: 5 submissões por IP por hora. Prefixo próprio (exp_) para não
// partilhar quota com o formulário StudyWing (ajax-comp.php usa enp_).
$expRlIp   = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$expRlFile = sys_get_temp_dir() . '/exp_ratelimit_' . md5($expRlIp) . '.json';
$expRlNow  = time();
$expRlLimit  = 5;
$expRlWindow = 3600;

$expRlData = file_exists($expRlFile) ? json_decode((string) file_get_contents($expRlFile), true) : null;
if (!is_array($expRlData) || !isset($expRlData['attempts'])) {
    $expRlData = ['attempts' => []];
}
$expRlData['attempts'] = array_values(array_filter(
    $expRlData['attempts'],
    fn($t) => $expRlNow - (int) $t < $expRlWindow
));
if (count($expRlData['attempts']) >= $expRlLimit) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'message' => 'Muitas tentativas. Tenta novamente daqui a algum tempo.']);
    exit;
}
$expRlData['attempts'][] = $expRlNow;
@file_put_contents($expRlFile, json_encode($expRlData), LOCK_EX);
unset($expRlIp, $expRlFile, $expRlNow, $expRlLimit, $expRlWindow, $expRlData);

// CSRF (mesmo token de sessão global do site, ver config.php) + honeypot anti-bot.
if (!empty($_POST['website'] ?? '')) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Pedido inválido.']);
    exit;
}

$expTokenPosted = (string) ($_POST['csrf'] ?? '');
$expTokenSess   = (string) ($_SESSION['enp_csrf']    ?? '');
$expTokenCookie = (string) ($_COOKIE['enp_csrf']     ?? '');
$expTokens      = array_filter([$expTokenSess, $expTokenCookie], 'strlen');
if ($expTokenPosted === '' || empty($expTokens)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Sessão expirada. Recarrega a página e tenta de novo.']);
    exit;
}
$expTokenOk = false;
foreach ($expTokens as $t) {
    if (hash_equals((string) $t, $expTokenPosted)) { $expTokenOk = true; break; }
}
if (!$expTokenOk) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Token inválido. Recarrega a página e tenta de novo.']);
    exit;
}
unset($_SESSION['enp_csrf']);

$nome                = trim((string) ($_POST['nome']                 ?? ''));
$email               = trim((string) ($_POST['email']                ?? ''));
$tel                 = trim((string) ($_POST['tel']                  ?? ''));
$responsavelEducacao = trim((string) ($_POST['responsavel_educacao'] ?? ''));
$local               = trim((string) ($_POST['localidade']           ?? ''));
$nacionalidade       = trim((string) ($_POST['nacionalidade']        ?? ''));
$disciplinaAno       = trim((string) ($_POST['disciplina_ano']       ?? ''));
$obs                 = trim((string) ($_POST['obs']                  ?? ''));
$termos              = !empty($_POST['termos']) ? '1' : '';

// Defesa contra header injection (CRLF smuggling) em mail() headers.
foreach (['nome', 'email', 'tel', 'responsavelEducacao', 'local'] as $expF) {
    if (preg_match('/[\r\n]/', (string) ($$expF))) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Pedido inválido.']);
        exit;
    }
}

$errors = [];
if ($nome === '' || mb_strlen($nome) < 2)       $errors[] = 'nome é obrigatório';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'email inválido';
if ($tel === '' || mb_strlen($tel) < 6)         $errors[] = 'telefone é obrigatório';
if ($local === '')                              $errors[] = 'localidade é obrigatória';
if ($nacionalidade === '')                      $errors[] = 'nacionalidade é obrigatória';
if ($disciplinaAno === '')                      $errors[] = 'disciplina e ano são obrigatórios';
if ($termos !== '1')                            $errors[] = 'termos de privacidade não aceites';

if ($errors) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Verifica os campos.', 'errors' => $errors]);
    exit;
}

function _exp_nacionalidade_label(string $n): string {
    return ['brasileira' => 'Brasileira', 'portuguesa' => 'Portuguesa', 'dupla-br-pt' => 'Dupla — brasileira e portuguesa/UE',
            'outra-cplp' => 'Outra nacionalidade CPLP', 'outra' => 'Outra'][$n] ?? $n;
}

function _exp_disciplina_ano_label(string $v): string {
    $map = [
        'alemao-11' => 'Alemão — 11.º ano',
        'biologia-geologia-11' => 'Biologia e Geologia — 11.º ano',
        'economia-a-11' => 'Economia A — 11.º ano',
        'espanhol-iniciacao-11' => 'Espanhol – Iniciação — 11.º ano',
        'espanhol-continuacao-11' => 'Espanhol – Continuação — 11.º ano',
        'filosofia-11' => 'Filosofia — 11.º ano',
        'fisica-quimica-a-11' => 'Física e Química A — 11.º ano',
        'frances-11' => 'Francês — 11.º ano',
        'geografia-a-11' => 'Geografia A — 11.º ano',
        'geometria-descritiva-a-11' => 'Geometria Descritiva A — 11.º ano',
        'historia-b-11' => 'História B — 11.º ano',
        'historia-cultura-artes-11' => 'História da Cultura e das Artes — 11.º ano',
        'ingles-11' => 'Inglês — 11.º ano',
        'italiano-11' => 'Italiano — 11.º ano',
        'latim-a-11' => 'Latim A — 11.º ano',
        'literatura-portuguesa-11' => 'Literatura Portuguesa — 11.º ano',
        'mandarim-11' => 'Mandarim — 11.º ano',
        'matematica-aplicada-css-11' => 'Matemática Aplicada às Ciências Sociais — 11.º ano',
        'matematica-b-11' => 'Matemática B — 11.º ano',
        'desenho-a-12' => 'Desenho A — 12.º ano',
        'historia-a-12' => 'História A — 12.º ano',
        'matematica-a-12' => 'Matemática A — 12.º ano',
        'portugues-12' => 'Português — 12.º ano',
        'portugues-lingua-nao-materna-12' => 'Português Língua Não Materna — 12.º ano',
        'portugues-lingua-segunda-12' => 'Português Língua Segunda — 12.º ano',
    ];
    return $map[$v] ?? $v;
}

$origemPagina = (string) ($_SERVER['HTTP_REFERER'] ?? 'estudar-em-portugal (página não identificada)');

$campos = [
    'Nome:'                  => $nome,
    'Email:'                 => $email,
    'Telefone/WhatsApp:'     => $tel,
    'Responsável de Educação:' => $responsavelEducacao !== '' ? $responsavelEducacao : '—',
    'Onde mora:'             => $local,
    'Nacionalidade do estudante:' => _exp_nacionalidade_label($nacionalidade),
    'Disciplina e Ano:'      => _exp_disciplina_ano_label($disciplinaAno),
    'Observações:'           => $obs,
];
$dataHoraStr = date('Y-m-d H:i:s');
$h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
$headNotice = "Foi submetida uma nova marcação de aula através do site estudar-em-portugal.com (Cursos Preparatórios).\n\n"
            . "O interessado pretende ser contactado pela equipa de explicações para agendar a aula.\n\n"
            . "Segue abaixo a informação preenchida no formulário:";
$headNoticeHtml = nl2br($headNotice);
$year = date('Y');

$css = "body{margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#1a1a1a;}"
     . ".wrap{max-width:600px;margin:24px auto;background:#fff;border:none;border-top:4px solid #0a1628;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.08);overflow:hidden;}"
     . ".brand{background:#0a1628;color:#fff;padding:22px 28px;}"
     . ".brand h1{margin:0;font-size:18px;font-weight:700;letter-spacing:.4px;}"
     . ".brand small{display:block;font-size:12px;opacity:.85;margin-top:4px;letter-spacing:.4px;}"
     . ".notice{background:#eef6f8;color:#0f4a57;padding:14px 28px;border-bottom:1px solid #d3e9ed;font-size:13px;line-height:1.5;}"
     . ".title{padding:22px 28px 4px;}"
     . ".title h2{margin:0;font-size:22px;color:#0f8ba6;font-weight:700;letter-spacing:.4px;}"
     . ".meta{padding:6px 28px 22px;color:#6b7280;font-size:13px;border-bottom:1px solid #e5e7eb;}"
     . "table.fields{width:100%;border-collapse:collapse;font-size:14px;}"
     . "table.fields td{padding:12px 28px;border-bottom:1px solid #e5e7eb;vertical-align:top;}"
     . "table.fields td.k{color:#6b7280;width:42%;font-weight:bold;}"
     . "table.fields tr:nth-child(even) td{background:#f8fafc;}"
     . "table.fields tr:last-child td{border-bottom:none;}"
     . ".footer{background:#f4f6f8;padding:16px 28px;color:#6b7280;font-size:12px;text-align:center;}"
     . ".footer a{color:#0f8ba6;text-decoration:none;}"
     . "@media only screen and (max-width:600px){.wrap{margin:0;border-radius:0;border-left:none;border-right:none;}"
     . "table.fields td{padding:10px 16px;}"
     . ".brand,.notice,.title,.meta,.footer{padding-left:16px;padding-right:16px;}}";

$corpoHtml = "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'>"
           . "<style>$css</style></head><body>"
           . "<div class='wrap'>"
           .   "<div class='brand'><h1>Estudar em Portugal</h1>"
           .     "<small>Cursos Preparatórios — Ginásios Da Vinci</small></div>"
           .   "<div class='notice'>$headNoticeHtml</div>"
           .   "<div class='title'><h2>NOVA MARCAÇÃO DE AULA</h2></div>"
           .   "<div class='meta'>Data e Hora: <b>$dataHoraStr</b></div>"
           .   "<table class='fields' role='presentation' cellpadding='0' cellspacing='0' border='0'>";
foreach ($campos as $rotulo => $valor) {
    $corpoHtml .= "<tr><td class='k'>$rotulo</td><td class='v'>" . $h((string) $valor) . "</td></tr>";
}
$corpoHtml .= "</table>"
           .   "<div class='footer'>© $year Estudar em Portugal · Ginásios Da Vinci · "
           .     "<a href='https://www.estudar-em-portugal.com'>www.estudar-em-portugal.com</a></div>"
           . "</div></body></html>";

$corpoTexto = "$headNotice\n\nDados da marcação:\n\nData e Hora: $dataHoraStr\n\n";
foreach ($campos as $rotulo => $valor) {
    $corpoTexto .= $rotulo . ' ' . $valor . "\n";
}
$corpoTexto .= "\nIP origem: " . (string) ($_SERVER['REMOTE_ADDR'] ?? '') . "\n"
             . "User-agent: " . (string) ($_SERVER['HTTP_USER_AGENT'] ?? '') . "\n"
             . "Página de origem: {$origemPagina}\n";

// ---- PASSO 1: GRAVAR NA BD (tabela própria `leads_explicacoes`) ----
$leadId = lf_store_lead_explicacoes([
    'nome'                 => $nome,
    'email'                => $email,
    'tel'                  => $tel,
    'responsavel_educacao' => $responsavelEducacao,
    'localidade'           => $local,
    'nacionalidade'        => $nacionalidade,
    'disciplina_ano'       => $disciplinaAno,
    'origem'               => $origemPagina,
    'ip'                   => $_SERVER['REMOTE_ADDR'] ?? '',
    'user_agent'           => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'obs'                  => $obs,
]);
if ($leadId === null) {
    error_log('[estudar-em-portugal/ajax-explicacoes] Falha ao gravar lead na BD');
}

// ---- LOG DE AUDITORIA (best-effort, ficheiro próprio — não partilha o log do StudyWing) ----
$logDir = __DIR__ . '/storage';
if (!is_dir($logDir)) @mkdir($logDir, 0775, true);
$logLine = sprintf("[%s] nome=%s email=%s tel=%s disciplina_ano=%s ip=%s\n",
    date('Y-m-d H:i:s'),
    preg_replace('/[^\w\sáàâãéêíóôõúçÀÁÉÍÓÚÑ-]/u', '_', $nome),
    $email, $tel, $disciplinaAno,
    (string) ($_SERVER['REMOTE_ADDR'] ?? '')
);
@file_put_contents($logDir . '/lead-explicacoes.log', $logLine, FILE_APPEND);

// ---- PASSO 2: ENVIO SMTP (BEST-EFFORT) — antes de responder: em vez de correr
// depois de fastcgi_finish_request(), porque neste host (LiteSpeed/Exim) o
// processo é encerrado assim que a resposta é fechada e o envio nunca chegava
// a correr (confirmado: admin/test-smtp.php enviava OK, o form não).
if (SMTP_HOST !== '') {
    try {
        require_once __DIR__ . '/lib/PHPMailer/src/Exception.php';
        require_once __DIR__ . '/lib/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/lib/PHPMailer/src/SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->Port       = SMTP_PORT;
        $mail->Timeout    = 12;
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
        $mail->addAddress(EXPLICACOES_MAIL_TO, 'Da Vinci');
        if (EXPLICACOES_MAIL_CC !== '') {
            $mail->addCC(EXPLICACOES_MAIL_CC);
        }
        if (EXPLICACOES_MAIL_CC2 !== '') {
            $mail->addCC(EXPLICACOES_MAIL_CC2);
        }
        if (EXPLICACOES_MAIL_BCC !== '') {
            $mail->addBCC(EXPLICACOES_MAIL_BCC);
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($email, $nome);
        }
        $mail->isHTML(true);
        $mail->Subject = '=?UTF-8?B?' . base64_encode('Nova marcação de aula — Cursos Preparatórios - ' . $nome) . '?=';
        $mail->Body    = $corpoHtml;
        $mail->AltBody = $corpoTexto;
        $mail->send();
    } catch (\Throwable $e) {
        error_log('[estudar-em-portugal/ajax-explicacoes] Falha envio SMTP: ' . $e->getMessage());
    }
}

// ---- PASSO 3: RESPONDER AO UTILIZADOR ----
http_response_code(200);
echo json_encode([
    'ok' => true,
    'message' => 'Recebemos! A equipa Da Vinci contacta-te em ≤ 24h úteis por email ou WhatsApp para marcar a aula.',
]);
