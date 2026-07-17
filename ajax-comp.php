<?php
/**
 * ajax-comp.php — handler do formulário StudyWing em comparar.php
 *
 * Recebe POST multi-step, valida, envia email para info@davinci.com.pt e
 * lafora@studywing.org (mesmo padrão do EstudarNoEstrangeiro/ajax.php).
 *
 * Retorna JSON: {ok:bool, message:string, errors?:array}.
 *
 * Sem Composer/PHPMailer para não obrigar a dependências externas: usa
 * mail() nativo do PHP. Em ambiente de produção com SMTP configurado,
 * recomenda-se trocar por PHPMailer (ver EstudarNoEstrangeiro/ajax.php).
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';

// Sessão para CSRF token (best-effort — não quebra se sessão não arrancar)
if (session_status() === PHP_SESSION_NONE) {
    @ini_set('session.use_strict_mode', '1');
    @ini_set('session.cookie_httponly', '1');
    @ini_set('session.cookie_samesite', 'Lax');
    @session_name('enp_sc');
    @session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Método não permitido.']);
    exit;
}

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
$perfil     = trim((string)($_POST['perfil']     ?? ''));
$email      = trim((string)($_POST['email']      ?? ''));
$tel        = trim((string)($_POST['tel']        ?? ''));
$local      = trim((string)($_POST['localidade'] ?? ''));
$nacionalidade      = trim((string)($_POST['nacionalidade']      ?? ''));
$ano        = trim((string)($_POST['ano']        ?? ''));
$enem       = trim((string)($_POST['enem']       ?? ''));
$ielts      = trim((string)($_POST['ielts']      ?? ''));
$media      = trim((string)($_POST['media']      ?? ''));
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
if ($ano === '')                                        $errors[] = 'ano escolar é obrigatório';
if ($enem === '')                                       $errors[] = 'ENEM é obrigatório';
if ($ielts === '')                                      $errors[] = 'IELTS/TOEFL é obrigatório';
if ($media === '')                                      $errors[] = 'média é obrigatória';
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
    return ['3-ano-em'=>'3.º ano EM','cursinho'=>'(Pré-)vestibular','formado'=>'Ensino Médio completo',
            'graduacao'=>'Já cursei faculdade'][$a] ?? $a;
}
function _bn_sim_nao(string $v): string {
    return $v === 'sim' ? 'Sim' : ($v === 'vou-fazer' ? 'Vou fazer este ano' : ($v === 'nao' ? 'Não' : $v));
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

$corpoTexto =
    "FOI EFETUADA UMA PRÉ-INSCRIÇÃO NO SITE estudar-em-portugal/comparar.php\n".
    "PARCERIA Da Vinci × StudyWing — PROGRAMA LÁ FORA\n\n".
    "===========================================\n".
    "DADOS PESSOAIS\n".
    "===========================================\n".
    "Nome:                {$nome}\n".
    "Sou:                 {$perfil}\n".
    "Email:               {$email}\n".
    "Telefone/WhatsApp:   {$tel}\n".
    "Onde mora:           {$local}\n".
    "Nacionalidade:       "._bn_nacionalidade_label($nacionalidade)."\n\n".
    "===========================================\n".
    "PERFIL ACADÉMICO\n".
    "===========================================\n".
    "Onde está no percurso: "._bn_ano_label($ano)."\n".
    "ENEM:                "._bn_sim_nao($enem)."\n".
    "IELTS/TOEFL:         "._bn_sim_nao($ielts)."\n".
    "Média do último ano: {$media}\n".
    "Áreas de interesse:  {$areas}\n".
    "Tipo de formação:    "._bn_tipo_curso_label($tipoCurso)."\n\n".
    "===========================================\n".
    "OBJETIVO E ORÇAMENTO\n".
    "===========================================\n".
    "Objetivo principal:  "._bn_objetivo_label($objetivo)."\n".
    "Situação financeira: "._bn_situacao_financeira_label($situacaoFinanceira)."\n".
    "Como vai financiar:  "._bn_financiamento_label($financiamento)."\n\n".
    "===========================================\n".
    "PREFERÊNCIAS\n".
    "===========================================\n".
    "Destino preferido:   "._bn_destino_pais($destino)."\n".
    "Quando quer começar: "._bn_quando_label($quando)."\n".
    "Fase atual:          "._bn_momento_label($momento)."\n".
    "Observações:         {$obs}\n\n".
    "===========================================\n".
    "Meta\n".
    "===========================================\n".
    "IP origem:           ".(string)($_SERVER['REMOTE_ADDR'] ?? '')."\n".
    "User-agent:          ".(string)($_SERVER['HTTP_USER_AGENT'] ?? '')."\n".
    "Data/hora servidor:  ".date('Y-m-d H:i:s')."\n".
    "Source:              comparar.php (estudar-em-portugal)\n";
$corpoHtml = nl2br(e($corpoTexto));

// Headers (formato RFC 5322)
$subject   = '=?UTF-8?B?' . base64_encode('Nova pré-inscrição — comparar (estudar-em-portugal)') . '?=';
$headers   = [];
$headers[] = 'From: no-reply@ginasiosdavinci.com';
$headers[] = 'Reply-To: ' . $email;
$headers[] = 'X-Mailer: estudar-em-portugal/ajax-comp';
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/plain; charset=UTF-8';
$headers[] = 'Content-Transfer-Encoding: 8bit';

// Envia para dois destinatários (CC) — padrão do EstudarNoEstrangeiro
$toPrimary = CONTACT_EMAIL;           // info@davinci.com.pt
$toStudywing = 'lafora@studywing.org'; // Parceiro internacional

$okPrimary  = @mail($toPrimary,     $subject, $corpoTexto, implode("\r\n", $headers));
$okStudywing = @mail($toStudywing,  $subject, $corpoTexto, implode("\r\n", $headers));

if (!$okPrimary && !$okStudywing) {
    // Em produção: escrever na fila + avisar admin. Aqui: responde com erro.
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Não foi possível enviar o formulário. Tenta novamente ou escreve-nos diretamente para ' . CONTACT_EMAIL . '.']);
    exit;
}

echo json_encode([
    'ok' => true,
    'message' => 'Recebemos! A equipa StudyWing contacta-te em ≤ 24h úteis por email ou WhatsApp.',
]);

// Log de auditoria (best-effort, silencioso)
$logDir = __DIR__ . '/storage';
if (!is_dir($logDir)) @mkdir($logDir, 0775, true);
$logLine = sprintf("[%s] nome=%s email=%s tel=%s destino=%s ip=%s\n",
    date('Y-m-d H:i:s'),
    preg_replace('/[^\w\sáàâãéêíóôõúçÀÁÉÍÓÚÑ-]/u', '_', $nome),
    $email, $tel, _bn_destino_pais($destino),
    (string)($_SERVER['REMOTE_ADDR'] ?? '')
);
@file_put_contents($logDir . '/lead-comparar.log', $logLine, FILE_APPEND);
