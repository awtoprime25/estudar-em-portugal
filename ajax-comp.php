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

$corpoTexto =
    "FOI EFETUADA UMA PRÉ-INSCRIÇÃO NO SITE ESTUDAR EM PORTUGAL\n".
    "PARCERIA Da Vinci × StudyWing\n\n".
    "===========================================\n".
    "DADOS PESSOAIS\n".
    "===========================================\n".
    "Nome:                {$nome}\n".
    "Email:               {$email}\n".
    "Telefone/WhatsApp:   {$tel}\n".
    "Onde mora:           {$local}\n".
    "Nacionalidade:       "._bn_nacionalidade_label($nacionalidade)."\n\n".
    "===========================================\n".
    "CURSO E OBJETIVO\n".
    "===========================================\n".
    "Objetivo principal:  "._bn_objetivo_label($objetivo)."\n".
    "Tipo de formação:    "._bn_tipo_curso_label($tipoCurso)."\n".
    "Curso pretendido:    {$areas}\n".
    "Quando quer começar: "._bn_quando_label($quando)."\n\n".
    "===========================================\n".
    "PERFIL E ORÇAMENTO\n".
    "===========================================\n".
    "Grau de escolaridade: "._bn_ano_label($ano)."\n".
    "Situação financeira: "._bn_situacao_financeira_label($situacaoFinanceira)."\n".
    "Como vai financiar:  "._bn_financiamento_label($financiamento)."\n\n".
    "===========================================\n".
    "PREFERÊNCIAS FINAIS\n".
    "===========================================\n".
    "Destino preferido:   "._bn_destino_pais($destino)."\n".
    "Fase atual:          "._bn_momento_label($momento)."\n".
    "Observações:         {$obs}\n\n".
    "===========================================\n".
    "Meta\n".
    "===========================================\n".
    "IP origem:           ".(string)($_SERVER['REMOTE_ADDR'] ?? '')."\n".
    "User-agent:          ".(string)($_SERVER['HTTP_USER_AGENT'] ?? '')."\n".
    "Data/hora servidor:  ".date('Y-m-d H:i:s')."\n".
    "Página de origem:    {$origemPagina}\n";
$corpoHtml = nl2br(e($corpoTexto));

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
    $mail->addAddress(MAIL_TO);
    if (MAIL_CC !== '') {
        $mail->addCC(MAIL_CC);
    }
    if (MAIL_CC2 !== '') {
        $mail->addCC(MAIL_CC2);
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mail->addReplyTo($email, $nome);
    }
    $mail->isHTML(true);
    $mail->Subject = '=?UTF-8?B?' . base64_encode('Nova pré-inscrição — Estudar em Portugal') . '?=';
    $mail->Body    = $corpoHtml;
    $mail->AltBody = $corpoTexto;
    $mail->send();
} catch (\Throwable $e) {
    // Best-effort: o lead já ficou gravado na BD. Apenas registamos o erro.
    error_log('[estudar-em-portugal/ajax-comp] Falha envio SMTP: ' . $e->getMessage());
}
