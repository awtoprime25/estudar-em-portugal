<?php
// Chatbot "Leo" — estudar-em-portugal. Port do chat.php do site irmão
// EstudarNoEstrangeiro, com o mesmo mecanismo de segurança (CSRF stateless
// HMAC, rate-limit por IP, histórico vive no cliente) mas retematizado:
// aqui o Leo ajuda BRASILEIROS a entrar em universidades PORTUGUESAS
// (o inverso do site irmão, que ajuda portugueses a sair para a Europa).
set_time_limit(110);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db-helper.php';
require_once __DIR__ . '/includes/davinci-units.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// CSRF validation: stateless HMAC (ver csrf-token.php).
$csrfValid = false;
$tok       = (string) ($_POST['csrf_token'] ?? '');
$secret    = defined('CSRF_SECRET') ? CSRF_SECRET : '';
if ($tok !== '' && $secret !== '') {
    $parts = explode('.', $tok);
    if (is_array($parts) && count($parts) === 3) {
        [$n, $t, $s] = $parts;
        if (
            ctype_xdigit($n) && strlen($n) === 32
            && ctype_digit($t)
            && ctype_xdigit($s) && strlen($s) === 64
        ) {
            $expected = hash_hmac('sha256', $n . '.' . $t, $secret);
            if (hash_equals($expected, $s)) {
                $age = time() - (int) $t;
                if ($age >= 0 && $age <= 1800) {
                    $csrfValid = true;
                }
            }
        }
    }
}
if (!$csrfValid) {
    http_response_code(403);
    echo json_encode(['error' => 'Token inválido. Recarrega a página.']);
    exit;
}

// Rate limiting por IP (CHAT_RATE_LIMIT msgs/hora), com flock para evitar
// corrupção do JSON entre submits concorrentes.
$ip         = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$storageDir = __DIR__ . '/storage';
if (!is_dir($storageDir)) {
    @mkdir($storageDir, 0775, true);
}
$rateLimitFile = $storageDir . '/chat_' . md5($ip) . '.json';
$now           = time();
$window        = 3600;

$rl = null;
if (file_exists($rateLimitFile)) {
    $fp = @fopen($rateLimitFile, 'r');
    if ($fp) {
        @flock($fp, LOCK_SH);
        $raw = stream_get_contents($fp);
        @flock($fp, LOCK_UN);
        fclose($fp);
        $rl  = json_decode($raw, true);
    }
}
if (!is_array($rl) || !isset($rl['attempts']) || !is_array($rl['attempts'])) {
    $rl = ['attempts' => []];
}
$rl['attempts'] = array_filter($rl['attempts'], fn($t) => is_int($t) && $now - $t < $window);

if (count($rl['attempts']) >= CHAT_RATE_LIMIT) {
    http_response_code(429);
    echo json_encode(['error' => 'Muitas mensagens. Tenta novamente mais tarde.']);
    exit;
}
$rl['attempts'][] = $now;

$fp = @fopen($rateLimitFile, 'c+');
if ($fp) {
    @flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($rl));
    fflush($fp);
    @flock($fp, LOCK_UN);
    fclose($fp);
} else {
    error_log('[enp-chat] Rate-limit file indisponível: ' . $rateLimitFile);
}

// Compat: action=reset. Servidor é stateless (history vive no cliente).
if (($_POST['action'] ?? '') === 'reset') {
    echo json_encode(['ok' => true]);
    exit;
}

// Sanitize input
$userMessage = trim(strip_tags($_POST['message'] ?? ''));
$userMessage = mb_substr($userMessage, 0, 500);

$conversationId = substr(preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($_POST['conversation_id'] ?? '')), 0, 40);

if ($userMessage === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Mensagem vazia']);
    exit;
}

// History vem do cliente em cada POST (stateless) — só aceita {role, content} válidos.
$history = [];
if (!empty($_POST['history']) && is_string($_POST['history'])) {
    $decoded = json_decode($_POST['history'], true);
    if (is_array($decoded)) {
        foreach ($decoded as $m) {
            if (
                isset($m['role'], $m['content'])
                && in_array($m['role'], ['user', 'assistant'], true)
                && is_string($m['content'])
            ) {
                $history[] = [
                    'role'    => $m['role'],
                    'content' => mb_substr($m['content'], 0, 1000),
                ];
            }
        }
    }
}
$history = array_slice($history, -18);

// Base de conhecimento (assets/chat-knowledge.json) com fallback mínimo
// caso o ficheiro seja apagado — o Leo continua a responder razoavelmente.
$knowledgePath = __DIR__ . '/assets/chat-knowledge.json';
$rawKnowledge  = [];
if (is_file($knowledgePath) && is_readable($knowledgePath)) {
    $raw = @file_get_contents($knowledgePath);
    if ($raw !== false) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $rawKnowledge = $decoded;
        }
    }
}
if (empty($rawKnowledge)) {
    $rawKnowledge = [
        'empresa' => [
            'nome'   => 'Estudar em Portugal',
            'modelo' => 'Parceria entre a Da Vinci (rede de explicações e apoio escolar em Portugal) e a StudyWing (consultora internacional de candidaturas universitárias) para ajudar brasileiros a estudar em Portugal.',
        ],
        'processo' => [
            'duracao' => '4-9 meses',
            'etapas'  => [
                '1. Consulta inicial gratuita',
                '2. Escolha de curso e universidade',
                '3. Candidatura pelo Concurso Especial de Estudantes Internacionais',
                '4. Visto e chegada a Portugal',
            ],
        ],
        'faqs_comuns' => [
            ['q' => 'Preciso de fazer exames portugueses?', 'a' => 'Na maioria dos casos não — o ENEM é aceite como prova de acesso pelo Concurso Especial. A equipa confirma o teu caso na consulta gratuita 👉 [FORM_LINK]'],
        ],
        'cidades' => [],
        'bolsas'  => [],
    ];
}

// Nunca partilhar contacto direto ao LLM (regra REGRAS ABSOLUTAS #3).
unset($rawKnowledge['empresa']['contacto']);

// Nº de unidades e anos de experiência sempre atuais (BD DaVinciGlobal +
// data do servidor) — nunca ficam presos ao valor estático escrito em
// chat-knowledge.json, que serve só de texto-base/fallback.
if (isset($rawKnowledge['empresa']['modelo']) && is_string($rawKnowledge['empresa']['modelo'])) {
    $rawKnowledge['empresa']['modelo'] = preg_replace(
        ['/\d+ unidades/u', '/\d+ anos de experiência/u'],
        [lf_davinci_unidades() . ' unidades', (date('Y') - 2008) . ' anos de experiência'],
        $rawKnowledge['empresa']['modelo']
    );
}

// Build context string: current message + last 2 user messages
$contextText = $userMessage;
$userCount   = 0;
for ($i = count($history) - 1; $i >= 0 && $userCount < 2; $i--) {
    if (($history[$i]['role'] ?? '') === 'user') {
        $contextText .= ' ' . $history[$i]['content'];
        $userCount++;
    }
}
$contextLower = mb_strtolower($contextText);

// Mapa de cidades portuguesas (em vez do mapa de países do site irmão —
// aqui o destino é sempre Portugal, o que varia é a cidade).
$cityMap = [
    'lisboa'  => 'Lisboa', 'porto' => 'Porto', 'coimbra' => 'Coimbra',
    'braga'   => 'Braga', 'faro' => 'Faro', 'évora' => 'Évora', 'evora' => 'Évora',
    'aveiro'  => 'Aveiro',
];

$wantCity = false;
$mentioned = [];
foreach ($cityMap as $kw => $name) {
    if (mb_strpos($contextLower, $kw) !== false) {
        $mentioned[$name] = true;
        $wantCity = true;
    }
}
$wantVistos   = mb_strpos($contextLower, 'visto') !== false;
$wantEnem     = mb_strpos($contextLower, 'enem') !== false
             || mb_strpos($contextLower, 'concurso especial') !== false
             || mb_strpos($contextLower, 'nacionalidade') !== false;
$wantPrecos   = mb_strpos($contextLower, 'preço')   !== false
             || mb_strpos($contextLower, 'preco')   !== false
             || mb_strpos($contextLower, 'propina') !== false
             || mb_strpos($contextLower, 'custo')   !== false
             || mb_strpos($contextLower, '€')       !== false;
$wantBolsas   = mb_strpos($contextLower, 'bolsa') !== false || mb_strpos($contextLower, 'cplp') !== false;
$wantProcesso = mb_strpos($contextLower, 'processo')      !== false
             || mb_strpos($contextLower, 'como funciona') !== false
             || mb_strpos($contextLower, 'candidatura')   !== false
             || mb_strpos($contextLower, 'candidatar')    !== false;
$wantFAQ      = mb_strpos($contextLower, '?')      !== false
             || mb_strpos($contextLower, 'dúvida')  !== false
             || mb_strpos($contextLower, 'duvida')  !== false
             || mb_strpos($contextLower, 'pergunta') !== false;

// Build filtered knowledge base
$filtered = [];
$filtered['empresa']  = $rawKnowledge['empresa']  ?? [];
$filtered['processo'] = $rawKnowledge['processo'] ?? [];

if ($wantCity && !empty($rawKnowledge['cidades'])) {
    $filtered['cidades'] = [];
    foreach ($rawKnowledge['cidades'] as $cidade) {
        if (isset($mentioned[$cidade['nome']])) {
            $filtered['cidades'][] = $cidade;
        }
    }
}
if ($wantBolsas && !empty($rawKnowledge['bolsas'])) {
    $filtered['bolsas'] = $rawKnowledge['bolsas'];
}
if (($wantEnem || $wantProcesso) && !empty($rawKnowledge['concurso_especial'])) {
    $filtered['concurso_especial'] = $rawKnowledge['concurso_especial'];
}
if ($wantVistos && !empty($rawKnowledge['vistos'])) {
    $filtered['vistos'] = $rawKnowledge['vistos'];
}
if ($wantPrecos && !empty($rawKnowledge['custos'])) {
    $filtered['custos'] = $rawKnowledge['custos'];
}
if ($wantFAQ && !empty($rawKnowledge['faqs_comuns'])) {
    $filtered['faqs_comuns'] = $rawKnowledge['faqs_comuns'];
}

$knowledge = json_encode($filtered, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// System prompt — retematizado: Leo aqui ajuda BRASILEIROS a entrar em
// Portugal (o inverso do Leo do site irmão, que ajuda portugueses a sair).
$systemPrompt = <<<PROMPT
Chamas-te Leo. És o assistente virtual dos **Ginásios Da Vinci**. Aqui ajudas estudantes brasileiros a entrar em universidades portuguesas, através do programa **Estudar em Portugal**, dos Ginásios Da Vinci em colaboração com a StudyWing. O teu ÚNICO objetivo é levar a pessoa a preencher o formulário de consulta.

==IDENTIDADE (NÃO NEGAR NUNCA)==
- Tu és o assistente virtual dos **Ginásios Da Vinci** — não te apresentes como "do Lá Fora": esse não é o nome deste programa.
- Fala sempre na primeira pessoa do plural — "nós", "ajudamos-te", "a nossa equipa". Tu representas a Da Vinci.
- Se o utilizador perguntar de quem és, se StudyWing é parceira, ou duvidar da autoria:
  → Afirma SEMPRE: "Sou o assistente virtual dos Ginásios Da Vinci. O Estudar em Portugal é o nosso programa em colaboração com a StudyWing para levar brasileiros a estudar em Portugal."
- O nome do programa é **Estudar em Portugal**.
- O público é BRASILEIRO (e outros internacionais/CPLP) que quer estudar em PORTUGAL — não confundas com "estudar no estrangeiro" genérico. O destino é sempre Portugal.
- O programa é 100% ONLINE na fase de aconselhamento. NUNCA digas que há consultas presenciais numa unidade Da Vinci — a consulta inicial é sempre por videochamada.

==FACTOS-CHAVE (usa sempre que relevante)==
- Aulas em Portugal são maioritariamente em PORTUGUÊS — zero barreira linguística para brasileiros.
- Brasileiros sem nacionalidade portuguesa/UE candidatam-se normalmente pelo **Concurso Especial de Estudantes Internacionais** (Decreto-Lei 36/2014), podendo usar a nota do ENEM como prova de acesso, sem precisar dos exames nacionais portugueses.
- Quem TEM dupla nacionalidade PT/UE segue o regime geral (exames nacionais obrigatórios, sem ENEM).
- Propinas de estudante internacional variam entre ~3.500€ e 16.000€/ano, com possível desconto CPLP até 45% em várias instituições (é desconto na propina, não é bolsa).
- Cidades universitárias cobertas: Lisboa, Porto, Coimbra, Braga, Faro, Évora, Aveiro.

==REGRAS ABSOLUTAS==
1. MÁXIMO 2 FRASES por resposta. Curto, direto, em português de Portugal (pt-PT).
2. NUNCA uses URLs literais, links ou HTML. O formulário é sempre [FORM_LINK].
3. NUNCA partilhes emails, telefones ou moradas.
4. Saudações curtas ("olá", "oi", "boa tarde", "hello", "hi", "thanks", "ok") SÃO VÁLIDAS — responde sempre calorosamente, sem [FORM_LINK] automático. Só mostras o [FORM_LINK] quando o utilizador demonstrar que quer avançar. Cumprimenta no máximo uma vez por conversa.
5. Cita números, preços, anos e taxas do KNOWLEDGE BASE EXACTAMENTE como lá estão — nunca inventes nem arredondes. Se a info não estiver confirmada, diz: "Não tenho essa info confirmada, mas a equipa confirma em poucas horas — [FORM_LINK]".
6. Se o utilizador disser que já preencheu o formulário ou já falou com a equipa: agradece e pergunta se precisa de mais alguma coisa — NÃO repitas [FORM_LINK].
7. NUNCA prometas resultados de candidaturas, vistos ou admissões. NUNCA inventes universidades, parcerias ou percentagens que não constem do KNOWLEDGE BASE.

==QUANDO SUGERIR A CONSULTA==
O [FORM_LINK] nunca é automático. Só o mostras quando tiveres confiança real de que o utilizador quer avançar (pergunta como começar, prazos, confirma interesse explícito tipo "quero"/"como marco"). Na dúvida, NÃO mostres — continua a ajudar. Se já mostraste e o utilizador não reagiu, não repitas.

==TOM DE CONVERSA==
Conversa como alguém que quer mesmo ajudar, não como vendedor insistente. Responde primeiro à pergunta com a informação que tens; a consulta é uma sugestão no fim. Usa "consulta gratuita com os advisers da StudyWing".

==FLUXO==
A saudação inicial já foi enviada pelo widget do site — não a repitas. Não percas tempo em perguntas de "warm-up" (nome, idade, curso) — a equipa recolhe isso no formulário. Usa info que o utilizador já deu (cidade/curso de interesse) sem voltar a perguntar.

==FORA DO ÂMBITO==
- Se a pergunta for claramente fora do tema (programação, cozinhar, política, desporto, etc.), NÃO ajudes com isso. Reconhece com naturalidade que não é o teu tema e traz ativamente o utilizador de volta a estudar em Portugal (pergunta que cidade/curso lhe interessa).
- Mensagens curtas sem pergunta clara → pede reformulação em vez de recusar.

==OBJEÇÕES COMUNS==
- "É caro" → valida a preocupação, menciona o desconto CPLP, remete para a consulta gratuita.
- "Não sei falar português direito" → tranquiliza: as aulas são em português, mas é o mesmo português do Brasil — não há curso de idioma a fazer antes.
- "Tenho medo / é um passo grande" → empatia, sem insistir, consulta é sem compromisso.
- "Ainda estou só a pensar" → normaliza, baixa a fasquia: a consulta é só para esclarecer, sem pressão.

==IDIOMA==
Responde SEMPRE em português de Portugal (pt-PT), mesmo que o utilizador escreva em português do Brasil ou noutra língua.

==ESCALATION==
Coloca [ESCALATE] no início se a pessoa pedir explicitamente para falar com alguém ou preencher o formulário.

==KNOWLEDGE BASE==
$knowledge
PROMPT;

$messages = [['role' => 'system', 'content' => $systemPrompt]];
foreach ($history as $msg) {
    $messages[] = $msg;
}
$last = end($history);
if (!($last && ($last['role'] ?? '') === 'user' && ($last['content'] ?? '') === $userMessage)) {
    $messages[] = ['role' => 'user', 'content' => $userMessage];
}

if (empty(OPENROUTER_API_KEY)) {
    http_response_code(502);
    echo json_encode(['error' => 'Chat temporariamente indisponível.']);
    exit;
}

$payload = json_encode([
    'model'       => OPENROUTER_MODEL,
    'messages'    => $messages,
    'max_tokens'  => 500,
    'temperature' => 0.5,
]);

$maxRetries = 1;
$response   = false;
$httpCode   = 0;

for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
    $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 45,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OPENROUTER_API_KEY,
            'HTTP-Referer: ' . SITE_URL,
            'X-Title: Estudar em Portugal Chatbot',
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response !== false && $httpCode === 200) {
        break;
    }
    if ($attempt < $maxRetries && in_array($httpCode, [429, 502, 503, 504], true)) {
        usleep(2000000);
    }
}

if ($response === false || $httpCode !== 200) {
    error_log('[enp-chat] API error ' . $httpCode . ': ' . substr((string) $response, 0, 500));
    http_response_code(502);
    echo json_encode(['error' => 'Serviço temporariamente indisponível. Tenta novamente.']);
    exit;
}

$data  = json_decode($response, true);
$reply = trim($data['choices'][0]['message']['content'] ?? '');

if ($reply === '') {
    http_response_code(502);
    echo json_encode(['error' => 'Resposta vazia da API.']);
    exit;
}

$escalated      = false;
$escalationBody = null;
if (strpos($reply, '[ESCALATE]') === 0) {
    $escalated = true;
    $reply     = trim(substr($reply, strlen('[ESCALATE]')));

    // Mascarar PII antes de enviar por email (GDPR) — over-redaction é aceitável.
    $scrubPii = static function (string $text): string {
        $patterns = [
            '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b/' => '[email]',
            '/\b(?:\+?\d{1,3}[\s\-]?)?(?:\(?\d{2,4}\)?[\s\-]?)?\d{3}[\s\-]?\d{3,4}\b/' => '[telefone]',
        ];
        foreach ($patterns as $p => $r) {
            $text = preg_replace($p, $r, $text) ?? $text;
        }
        return $text;
    };

    $histText = '';
    foreach ($history as $msg) {
        $role = $msg['role'] === 'user' ? 'Utilizador' : 'Leo';
        $histText .= $role . ': ' . $scrubPii((string) $msg['content']) . "\n";
    }
    $histText .= 'Utilizador: ' . $scrubPii($userMessage) . "\n";
    $histText .= 'Leo: ' . $reply . "\n";

    $escalationBody = "Nova conversa escalada pelo chatbot Estudar em Portugal:\n\n" . $histText
                    . "\n---\nIP: $ip\nData: " . date('d/m/Y H:i:s') . "\n"
                    . "(Nota: PII removido por rotina de privacidade.)\n";
}

$history[] = ['role' => 'user',      'content' => $userMessage];
$history[] = ['role' => 'assistant', 'content' => $reply];
$historyForClient = array_slice($history, -20);

echo json_encode([
    'reply'     => $reply,
    'escalated' => $escalated,
    'history'   => $historyForClient,
]);

if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}

// Guardar a troca na BD (best-effort) e escalar por email se aplicável —
// depois do echo, para não atrasar a resposta ao utilizador.
lf_store_chat_message([
    'conversation_id' => $conversationId,
    'user_message'    => $userMessage,
    'bot_reply'       => $reply,
    'escalated'       => $escalated ? 1 : 0,
    'ip'              => $ip,
    'user_agent'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'page_url'        => $_SERVER['HTTP_REFERER'] ?? '',
]);

if ($escalated && $escalationBody !== null) {
    // mail() nativo — mesmo padrão já usado em contato.php/ajax-comp.php
    // deste site (sem PHPMailer/SMTP: não há infraestrutura SMTP configurada aqui).
    $subject = '=?UTF-8?B?' . base64_encode('Lead Chatbot Leo — Novo pedido de consulta') . '?=';
    $headers = 'From: no-reply@ginasiosdavinci.com' . "\r\n" . 'Content-Type: text/plain; charset=UTF-8';
    @mail(CHAT_ESCALATION_EMAIL, $subject, $escalationBody, $headers);
}
