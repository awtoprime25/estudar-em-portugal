<?php
/**
 * ajax-comp.php — handler do formulário StudyWing (includes/studywing-form.php,
 * incluído em todas as páginas via footer.php)
 *
 * Recebe POST multi-step, valida, grava em BD (leads), depois envia email
 * SMTP best-effort com PHPMailer. Se SMTP falhar, o lead já ficou gravado.
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

// Rate limit: 5 submissões por IP por hora (mesmo padrão do site irmão
// EstudarNoEstrangeiro/send.php). Prefixo próprio (enp_) para não colidir
// com os ficheiros de rate-limit desse site irmão no mesmo servidor/tmp.
$enpRlIp   = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$enpRlFile = sys_get_temp_dir() . '/enp_ratelimit_' . md5($enpRlIp) . '.json';
$enpRlNow  = time();
$enpRlLimit  = 5;
$enpRlWindow = 3600;

$enpRlData = file_exists($enpRlFile) ? json_decode((string) file_get_contents($enpRlFile), true) : null;
if (!is_array($enpRlData) || !isset($enpRlData['attempts'])) {
    $enpRlData = ['attempts' => []];
}
$enpRlData['attempts'] = array_values(array_filter(
    $enpRlData['attempts'],
    fn($t) => $enpRlNow - (int) $t < $enpRlWindow
));
if (count($enpRlData['attempts']) >= $enpRlLimit) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'message' => 'Muitas tentativas. Tenta novamente daqui a algum tempo.']);
    exit;
}
$enpRlData['attempts'][] = $enpRlNow;
@file_put_contents($enpRlFile, json_encode($enpRlData), LOCK_EX);
unset($enpRlIp, $enpRlFile, $enpRlNow, $enpRlLimit, $enpRlWindow, $enpRlData);

// CSRF (Low-friction): comparar.php gera um token em cookie+session, ajax-comp.php verifica.
// Honeypot anti-bot (campo invisível) — defesa secundária.
if (!empty($_POST['website'] ?? '')) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Pedido inválido.']);
    exit;
}

$enpTokenPosted = (string) ($_POST['csrf'] ?? '');
$enpTokenSess   = (string) ($_SESSION['enp_csrf']    ?? '');
$enpTokenCookie = (string) ($_COOKIE['enp_csrf']     ?? '');
$enpTokens      = array_filter([$enpTokenSess, $enpTokenCookie], 'strlen');
if ($enpTokenPosted === '' || empty($enpTokens)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Sessão expirada. Recarrega a página e tenta de novo.']);
    exit;
}
$enpTokenOk = false;
foreach ($enpTokens as $t) {
    if (hash_equals((string) $t, $enpTokenPosted)) { $enpTokenOk = true; break; }
}
if (!$enpTokenOk) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Token inválido. Recarrega a página e tenta de novo.']);
    exit;
}
// Regenera após uso (defesa contra replay).
unset($_SESSION['enp_csrf']);

$nome       = trim((string)($_POST['nome']       ?? ''));
$email      = trim((string)($_POST['email']      ?? ''));
$tel        = trim((string)($_POST['tel']        ?? ''));
$local      = trim((string)($_POST['localidade'] ?? ''));
$nacionalidade      = trim((string)($_POST['nacionalidade']      ?? ''));
$ano        = trim((string)($_POST['ano']        ?? ''));
$areas      = trim((string)($_POST['areas']      ?? ''));
$tipoCurso  = trim((string)($_POST['tipo_curso'] ?? ''));
$objetivo   = trim((string)($_POST['objetivo']   ?? ''));
$situacaoFinanceira = trim((string)($_POST['situacao_financeira'] ?? ''));
$financiamento      = trim((string)($_POST['financiamento']       ?? ''));
$destino    = trim((string)($_POST['destino']    ?? ''));
$quando     = trim((string)($_POST['quando']     ?? ''));
$momento    = trim((string)($_POST['momento']    ?? ''));
$obs        = trim((string)($_POST['obs']        ?? ''));
$termos     = !empty($_POST['termos']) ? '1' : '';

// Defesa contra header injection (CRLF smuggling) em mail() headers:
// filter_var filtrou o formato RFC, mas quoted-local-part admite control chars.
// Strip CR/LF de TODOS os campos que entram em headers (From/Reply-To/Subject).
foreach (['nome','email','tel','local','areas'] as $enpF) {
    if (preg_match('/[\r\n]/', (string) ($$enpF))) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Pedido inválido.']);
        exit;
    }
}

$errors = [];
if ($nome === '' || mb_strlen($nome) < 2)              $errors[] = 'nome é obrigatório';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))         $errors[] = 'email inválido';
if ($tel === '' || mb_strlen($tel) < 6)                 $errors[] = 'telefone é obrigatório';
if ($local === '')                                      $errors[] = 'localidade é obrigatória';
if ($nacionalidade === '')                              $errors[] = 'nacionalidade é obrigatória';
if ($ano === '')                                        $errors[] = 'grau de escolaridade é obrigatório';
if ($areas === '')                                      $errors[] = 'áreas de interesse são obrigatórias';
if ($tipoCurso === '')                                  $errors[] = 'tipo de formação é obrigatório';
if ($objetivo === '')                                   $errors[] = 'objetivo é obrigatório';
if ($situacaoFinanceira === '')                         $errors[] = 'situação financeira é obrigatória';
if ($financiamento === '')                              $errors[] = 'plano de financiamento é obrigatório';
if ($destino === '')                                    $errors[] = 'destino é obrigatório';
if ($quando === '')                                     $errors[] = 'quando é obrigatório';
if ($momento === '')                                    $errors[] = 'fase atual é obrigatória';
if ($termos !== '1')                                    $errors[] = 'termos de privacidade não aceites';

if ($errors) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Verifica os campos.', 'errors' => $errors]);
    exit;
}

// Enrich: país origem (heurística simples por código de tel)
function _bn_destino_pais(string $dest): string {
    return ['portugal'=>'Portugal','holanda'=>'Holanda','alemanha'=>'Alemanha','reino-unido'=>'Reino Unido',
            'espanha'=>'Espanha','italia'=>'Itália','franca'=>'França','republica-checa'=>'República Checa',
            'irlanda'=>'Irlanda','aberto'=>'A definir com consultor'][$dest] ?? $dest;
}
function _bn_ano_label(string $a): string {
    return ['3-ano-em'=>'A cursar o Ensino Médio','cursinho'=>'Ensino Médio completo, a fazer cursinho/pré-vestibular',
            'formado'=>'Ensino Médio completo','graduacao'=>'Já concluiu uma graduação'][$a] ?? $a;
}
function _bn_quando_label(string $q): string {
    return ['2026-set'=>'Setembro 2026','2027-jan'=>'Janeiro 2027','2027-set'=>'Setembro 2027',
            'ainda-decidi'=>'A decidir'][$q] ?? $q;
}
function _bn_nacionalidade_label(string $n): string {
    return ['brasileira'=>'Brasileira','portuguesa'=>'Portuguesa','dupla-br-pt'=>'Dupla — brasileira e portuguesa/UE',
            'outra-cplp'=>'Outra nacionalidade CPLP','outra'=>'Outra'][$n] ?? $n;
}
function _bn_tipo_curso_label(string $t): string {
    return ['licenciatura'=>'Licenciatura','mestrado'=>'Mestrado','mestrado-integrado'=>'Mestrado Integrado',
            'doutoramento'=>'Doutoramento','ctesp'=>'Curso técnico superior (CTeSP)',
            'pos-graduacao'=>'Pós-graduação','nao-sei'=>'Ainda não sei'][$t] ?? $t;
}
function _bn_objetivo_label(string $o): string {
    return ['diploma-ue'=>'Um diploma reconhecido na UE','mudar-vida'=>'Mudar de vida / viver na Europa',
            'raizes-familia'=>'Já tem família ou raízes em Portugal','explorando'=>'Ainda está a explorar as opções'][$o] ?? $o;
}
function _bn_situacao_financeira_label(string $s): string {
    return ['garantido'=>'Já tem o valor garantido','a-juntar'=>'Está a juntar, ainda não tem tudo',
            'preciso-ajuda'=>'Precisa de ajuda para perceber como chegar lá',
            'nao-pensei'=>'Ainda não pensou nisso'][$s] ?? $s;
}
function _bn_financiamento_label(string $f): string {
    return ['recursos-proprios'=>'Recursos próprios / da família','bolsa'=>'Bolsa ou financiamento estudantil',
            'trabalho'=>'Vai trabalhar part-time enquanto estuda','nao-decidi'=>'Ainda não decidiu'][$f] ?? $f;
}
function _bn_momento_label(string $m): string {
    return ['pesquisando'=>'Só começou a pesquisar agora',
            'duvidas-sozinho'=>'Quer esclarecer dúvidas, mas pensava tratar sozinho',
            'quero-assessoria'=>'Quer contratar já o acompanhamento completo'][$m] ?? $m;
}

$origemPagina = (string) ($_SERVER['HTTP_REFERER'] ?? 'estudar-em-portugal (página não identificada)');

// Email HTML com o mesmo template do site-irmão EstudarNoEstrangeiro/send.php
// (marca + aviso + cartão "NOVA INSCRIÇÃO" + tabela de campos), com as cores
// e os campos próprios deste formulário (StudyWing, 4 passos).
$campos = [
    'Nome:'                   => $nome,
    'Email:'                  => $email,
    'Telefone/WhatsApp:'      => $tel,
    'Onde mora:'              => $local,
    'Nacionalidade:'          => _bn_nacionalidade_label($nacionalidade),
    'Objetivo principal:'     => _bn_objetivo_label($objetivo),
    'Tipo de formação:'       => _bn_tipo_curso_label($tipoCurso),
    'Curso pretendido:'       => $areas,
    'Quando quer começar:'    => _bn_quando_label($quando),
    'Grau de escolaridade:'   => _bn_ano_label($ano),
    'Situação financeira:'    => _bn_situacao_financeira_label($situacaoFinanceira),
    'Como vai financiar:'     => _bn_financiamento_label($financiamento),
    'Destino preferido:'      => _bn_destino_pais($destino),
    'Fase atual:'             => _bn_momento_label($momento),
    'Observações:'            => $obs,
];
$dataHoraStr = date('Y-m-d H:i:s');
$h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
$headNotice = "Foi submetida uma nova pré-inscrição através do site estudar-em-portugal.com\n\n"
            . "O interessado solicita contacto por parte da equipa StudyWing, com o objetivo de obter mais informações.\n\n"
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
           .     "<small>Da Vinci × StudyWing</small></div>"
           .   "<div class='notice'>$headNoticeHtml</div>"
           .   "<div class='title'><h2>NOVA PRÉ-INSCRIÇÃO</h2></div>"
           .   "<div class='meta'>Data e Hora: <b>$dataHoraStr</b></div>"
           .   "<table class='fields' role='presentation' cellpadding='0' cellspacing='0' border='0'>";
foreach ($campos as $rotulo => $valor) {
    $corpoHtml .= "<tr><td class='k'>$rotulo</td><td class='v'>" . $h((string) $valor) . "</td></tr>";
}
$corpoHtml .= "</table>"
           .   "<div class='footer'>© $year Estudar em Portugal · Da Vinci × StudyWing · "
           .     "<a href='https://www.estudar-em-portugal.com'>www.estudar-em-portugal.com</a></div>"
           . "</div></body></html>";

$corpoTexto = "$headNotice\n\nDados da pré-inscrição:\n\nData e Hora: $dataHoraStr\n\n";
foreach ($campos as $rotulo => $valor) {
    $corpoTexto .= $rotulo . ' ' . $valor . "\n";
}
$corpoTexto .= "\nIP origem: " . (string) ($_SERVER['REMOTE_ADDR'] ?? '') . "\n"
             . "User-agent: " . (string) ($_SERVER['HTTP_USER_AGENT'] ?? '') . "\n"
             . "Página de origem: {$origemPagina}\n";

// ---- PASSO 1: GRAVAR NA BASE DE DADOS ----
$leadId = lf_store_lead([
    'nome'                   => $nome,
    'email'                  => $email,
    'tel'                    => $tel,
    'localidade'             => $local,
    'nacionalidade'          => $nacionalidade,
    'ano'                    => $ano,
    'tipo_curso'             => $tipoCurso,
    'objetivo'               => $objetivo,
    'situacao_financeira'    => $situacaoFinanceira,
    'financiamento'          => $financiamento,
    'destino'                => $destino,
    'quando'                 => $quando,
    'momento'                => $momento,
    'origem'                 => $origemPagina,
    'ip'                     => $_SERVER['REMOTE_ADDR'] ?? '',
    'user_agent'             => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'areas'                  => $areas,
    'obs'                    => $obs,
]);
if ($leadId === null) {
    error_log('[estudar-em-portugal/ajax-comp] Falha ao gravar lead na BD');
}

// ---- PASSO 2: RESPONDER AO UTILIZADOR (JÁ COM SUCESSO) ----
http_response_code(200);
echo json_encode([
    'ok' => true,
    'message' => 'Recebemos! A equipa StudyWing contacta-te em ≤ 24h úteis por email ou WhatsApp.',
]);

// ---- PASSO 3: TERMINAR A LIGAÇÃO AO CLIENTE ----
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}

// ---- LOG DE AUDITORIA (best-effort — corre SEMPRE, mesmo sem SMTP/BD) ----
// Rede de segurança: garante rasto do lead em ficheiro mesmo que a BD esteja
// em baixo e o SMTP não esteja configurado (nunca perder um lead sem rasto).
$logDir = __DIR__ . '/storage';
if (!is_dir($logDir)) @mkdir($logDir, 0775, true);
$logLine = sprintf("[%s] nome=%s email=%s tel=%s destino=%s ip=%s\n",
    date('Y-m-d H:i:s'),
    preg_replace('/[^\w\sáàâãéêíóôõúçÀÁÉÍÓÚÑ-]/u', '_', $nome),
    $email, $tel, _bn_destino_pais($destino),
    (string)($_SERVER['REMOTE_ADDR'] ?? '')
);
@file_put_contents($logDir . '/lead-comparar.log', $logLine, FILE_APPEND);

// ---- PASSO 4: ENVIO SMTP (BEST-EFFORT, NUNCA QUEBRA A RESPOSTA JÁ ENVIADA) ----
// Se SMTP_HOST vazio, skip silencioso (não há servidor configurado)
if (SMTP_HOST === '') {
    exit;
}

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
    $mail->addAddress(MAIL_TO, 'Da Vinci');
    if (MAIL_CC !== '') {
        $mail->addCC(MAIL_CC, 'StudyWing');
    }
    if (MAIL_CC2 !== '') {
        $mail->addCC(MAIL_CC2);
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mail->addReplyTo($email, $nome);
    }
    $mail->isHTML(true);
    $mail->Subject = '=?UTF-8?B?' . base64_encode('Nova pré-inscrição — Estudar em Portugal - ' . $nome) . '?=';
    $mail->Body    = $corpoHtml;
    $mail->AltBody = $corpoTexto;
    $mail->send();
} catch (\Throwable $e) {
    // Best-effort: o lead já ficou gravado na BD. Apenas registamos o erro.
    error_log('[estudar-em-portugal/ajax-comp] Falha envio SMTP: ' . $e->getMessage());
}
