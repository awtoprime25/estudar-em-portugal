<?php
// ============================================================
// estudar-em-portugal — config.php
// Constantes globais do site (marca, contactos, SEO).
// Dados de contacto oficiais da unidade Faro da Ginásios da Vinci,
// reutilizados a partir do projeto irmão EstudarNoEstrangeiro.
// ============================================================

define('SITE_NAME',        'Estudar em Portugal');
define('SITE_SHORT_NAME',  'Estudar em Portugal');
define('SITE_URL',         'https://estudar-em-portugal.com/');
define('SITE_DESCRIPTION', 'Estude em Portugal com quem conhece o caminho. Candidatura, visto, bolsas e chegada — acompanhamento completo para estudantes brasileiros que querem a Europa como porta de entrada.');

define('CONTACT_PHONE',        '+351 289 108 105');
define('CONTACT_PHONE_TEL',    '+351289108105');
define('CONTACT_EMAIL',        'info@davinci.com.pt');
define('CONTACT_ADDRESS_LINE', 'Largo do Carmo nº51, 8000-148 Faro · Portugal');

define('SOCIAL_INSTAGRAM', 'https://www.instagram.com/ginasioseducacaodavinci/');
define('SOCIAL_FACEBOOK',  'https://www.facebook.com/ginasioseducacaodavinci');
define('SOCIAL_YOUTUBE',   'https://www.youtube.com/gedavinci');

define('BLOG_URL', 'https://www.ginasiosdavinci.com/estudar-no-estrangeiro/blog/');

// ============================================================
// .env loader + BD (analytics de visitas — ver includes/db-helper.php)
// Mesmo padrão nativo (sem Composer) usado por noticias-local e
// EstudarNoEstrangeiro: lê .env se existir, senão cai em defaults de
// desenvolvimento local (XAMPP: root sem password).
//
// IMPORTANTE: esta base de dados é PRÓPRIA do estudar-em-portugal — não
// é partilhada com noticias-local nem com EstudarNoEstrangeiro. Cada
// site irmão tem o seu próprio schema de analytics (mesma estrutura,
// dados isolados).
// ============================================================
$enaEnvFile = __DIR__ . '/.env';
if (is_readable($enaEnvFile)) {
    foreach (file($enaEnvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $enaLine) {
        $enaLine = trim($enaLine);
        if ($enaLine === '' || $enaLine[0] === '#' || strpos($enaLine, '=') === false) continue;
        [$enaK, $enaV] = explode('=', $enaLine, 2);
        $enaK = trim($enaK);
        $enaV = trim($enaV);
        $enaFirst = substr($enaV, 0, 1);
        $enaLast  = substr($enaV, -1);
        if (strlen($enaV) >= 2 && (($enaFirst === '"' && $enaLast === '"') || ($enaFirst === "'" && $enaLast === "'"))) {
            $enaV = substr($enaV, 1, -1);
        }
        if ($enaK !== '' && (getenv($enaK) === false || getenv($enaK) === '')) {
            putenv("$enaK=$enaV");
        }
    }
}
unset($enaEnvFile, $enaLine, $enaK, $enaV);

define('DB_HOST', (string) (getenv('DB_HOST') ?: 'localhost'));
define('DB_USER', (string) (getenv('DB_USER') ?: 'root'));
define('DB_PASS', (string) (getenv('DB_PASS') ?: ''));
define('DB_NAME', (string) (getenv('DB_NAME') ?: 'ginasiosdavinci_estudaremportugal'));

// BD DaVinciGlobal — só leitura, para o número de unidades em tempo real
// (ver includes/davinci-units.php). BD própria da Da Vinci, partilhada
// (read-only) com os sites irmãos — mesmo padrão do EstudarNoEstrangeiro.
define('DAVINCI_DB_HOST',  (string) (getenv('DAVINCI_DB_HOST')  ?: 'localhost'));
define('DAVINCI_DB_USER',  (string) (getenv('DAVINCI_DB_USER')  ?: ''));
define('DAVINCI_DB_PASS',  (string) (getenv('DAVINCI_DB_PASS')  ?: ''));
define('DAVINCI_DB_NAME',  (string) (getenv('DAVINCI_DB_NAME')  ?: ''));
define('DAVINCI_DB_TABLE', (string) (getenv('DAVINCI_DB_TABLE') ?: 'DaVinciGlobal'));

// Segredo usado para fazer hash(IP) antes de gravar (nunca guardamos o IP em
// claro — ver site_visit_track() em includes/db-helper.php). Preferível vir
// do .env em produção; fallback determinístico estável para dev local.
define('CSRF_SECRET', (string) (getenv('CSRF_SECRET') ?: hash('sha256', 'estudar-em-portugal-dev-secret')));

// HMAC-SHA256 usado pelo view-tracker para hash(IP). Preferência: .env
// (openssl rand -hex 32). Fallback: CSRF_SECRET. Dev fallback: literal dev.
define('VIEWS_HASH_SALT', (string) (getenv('VIEWS_HASH_SALT') ?: hash('sha256', 'estudar-em-portugal-views-dev-salt')));

// Retenção da auditoria de views (blog_view_hits) em dias. Reduzido a 30
// alinhado com o site irmão + princípio RGPD de minimização.
define('LF_VIEWS_RETENTION_DAYS', max(7, min(180, (int) (getenv('LF_VIEWS_RETENTION_DAYS') ?: 30))));

// Token de acesso ao dashboard /views-stats.php?key=<TOKEN>. Definido aqui
// com um valor gerado para já funcionar em dev local; sobrepõe-se com
// VIEWS_STATS_TOKEN no .env em produção.
define('VIEWS_STATS_TOKEN', (string) (getenv('VIEWS_STATS_TOKEN') ?: '88b4193f989f69dd43a5b48b36ff16a5d42872c6093f449febd75864e1f5393e'));

// ============================================================
// SMTP para envio de email (formulário StudyWing — ajax-comp.php)
// Lido do .env; vazio = skip (best-effort, sem erro se mail() falhar).
// ============================================================
define('SMTP_HOST',     (string) (getenv('SMTP_HOST')     ?: ''));
define('SMTP_PORT',     (int)    (getenv('SMTP_PORT')     ?: 587));
define('SMTP_USER',     (string) (getenv('SMTP_USER')     ?: ''));
define('SMTP_PASS',     (string) (getenv('SMTP_PASS')     ?: ''));
// SMTP_SECURE precisa de verificação explícita: o operador ?: trata string vazia
// como falsy, por isso `SMTP_SECURE=` no .env caía sempre para 'tls' e forçava
// STARTTLS contra um relay local em texto simples (porta 25). Mesmo bug já
// corrigido no site irmão EstudarNoEstrangeiro — replicado aqui.
$enpSmtpSecureEnv = getenv('SMTP_SECURE');
define('SMTP_SECURE', $enpSmtpSecureEnv === false ? 'tls' : $enpSmtpSecureEnv);   // 'tls', 'ssl' ou '' (sem encriptação)
unset($enpSmtpSecureEnv);
define('SMTP_ALLOW_SELF_SIGNED', (getenv('SMTP_ALLOW_SELF_SIGNED') ?: '0') === '1');
define('SMTP_FROM',     (string) (getenv('SMTP_FROM')     ?: 'no-reply@estudar-em-portugal.com'));
define('SMTP_FROMNAME', (string) (getenv('SMTP_FROMNAME') ?: 'Estudar em Portugal'));
define('MAIL_TO',       (string) (getenv('MAIL_TO')       ?: CONTACT_EMAIL));
define('MAIL_CC',       (string) (getenv('MAIL_CC')       ?: ''));
define('MAIL_CC2',      (string) (getenv('MAIL_CC2')      ?: ''));   // Cc opcional (cópia pessoal). Vazio = desativado.

// Destinatários do report por email do cron-gerar-blog.php (1 email por
// artigo gerado/falhado). Nomes das chaves tal como o utilizador as definiu
// no .env (minúsculas, sem prefixo EMAIL_/MAIL_).
define('EMAILREP1', (string) (getenv('emailrep1') ?: ''));
define('EMAILREP2', (string) (getenv('emailrep2') ?: ''));

// ============================================================
// Chatbot "Leo" (chat.php) — mesma integração OpenRouter do site irmão
// EstudarNoEstrangeiro. Sem chave: chat.php recusa pedidos com erro 502
// em vez de rebentar (fail-safe, não bloqueia o resto do site).
// ============================================================
define('OPENROUTER_API_KEY',    (string) (getenv('OPENROUTER_API_KEY') ?: ''));
define('OPENROUTER_MODEL',      (string) (getenv('OPENROUTER_MODEL')   ?: 'openai/gpt-4o-mini'));
define('CHAT_RATE_LIMIT',       max(1, (int) (getenv('CHAT_RATE_LIMIT') ?: 20)));
define('CHAT_ESCALATION_EMAIL', (string) (getenv('CHAT_ESCALATION_EMAIL') ?: CONTACT_EMAIL));

// ============================================================
// Gerador automático de blog (cron-gerar-blog.php) — texto + imagem via
// OpenRouter. Chave separada da do chat para permitir modelos/orçamentos
// distintos por funcionalidade (mesmo padrão do site irmão).
// ============================================================
define('BLOG_OPENROUTER_API_KEY', (string) (getenv('BLOG_OPENROUTER_API_KEY') ?: ''));
define('BLOG_MODEL',              (string) (getenv('BLOG_MODEL')       ?: 'openai/gpt-4o-mini'));
define('BLOG_IMAGE_MODEL',        (string) (getenv('BLOG_IMAGE_MODEL') ?: 'google/gemini-2.5-flash-image-preview'));

// ============================================================
// CSRF do formulário StudyWing (includes/studywing-form.php + ajax-comp.php).
// Gerado aqui — o PRIMEIRO require de todas as páginas — para que
// session_start()/setcookie() corram sempre antes de qualquer output,
// já que o formulário passou a aparecer no footer de todas as páginas.
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    @ini_set('session.use_strict_mode', '1');
    @ini_set('session.cookie_httponly', '1');
    @ini_set('session.cookie_samesite', 'Lax');
    @session_name('enp_sc');
    @session_start();
}
if (empty($_SESSION['enp_csrf']) || !is_string($_SESSION['enp_csrf'])) {
    $_SESSION['enp_csrf'] = bin2hex(random_bytes(16));
}
define('ENP_CSRF', (string) $_SESSION['enp_csrf']);
setcookie('enp_csrf', ENP_CSRF, [
    'expires'  => 0,
    'path'     => '/',
    'secure'   => (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off'),
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (!function_exists('e')) {
    function e(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * site_image($name)
 * Devolve o caminho relativo para assets/images/{$name}.png se esse ficheiro
 * já tiver sido gerado (ex.: correndo tools/gerar-imagens.js), caso contrário
 * volta para o ilustração .svg feita à mão que serve de placeholder.
 * Isto permite que basta gerar o PNG com o nome certo para o site "atualizar"
 * sozinho, sem tocar no HTML.
 */
if (!function_exists('site_image')) {
    function site_image(string $name): string {
        $pngPath = __DIR__ . '/assets/images/' . $name . '.png';
        if (is_file($pngPath) && filesize($pngPath) > 0) {
            return 'assets/images/' . $name . '.png';
        }
        return 'assets/images/' . $name . '.svg';
    }
}

/**
 * site_image_exists($name)
 * Devolve o caminho do PNG em assets/images/{$name}.png se ele existir (e não
 * estiver vazio), caso contrário null. Ao contrário de site_image(), NÃO cai
 * para .svg — serve para templates que só querem mostrar a imagem quando ela
 * já foi gerada, com fallback próprio (ex.: um ícone) quando ainda não existe.
 */
if (!function_exists('site_image_exists')) {
    function site_image_exists(string $name): ?string {
        $pngPath = __DIR__ . '/assets/images/' . $name . '.png';
        return (is_file($pngPath) && filesize($pngPath) > 0) ? 'assets/images/' . $name . '.png' : null;
    }
}

/**
 * asset_url($relPath)
 * Acrescenta ?v=<filemtime> a assets/css|js para cache-busting automático —
 * sem isto, browsers com cache antiga não veem atualizações de CSS/JS até
 * expirar (ver bug: nav novo só aplicava depois de forçar reload).
 */
if (!function_exists('asset_url')) {
    function asset_url(string $relPath): string {
        $full = __DIR__ . '/' . $relPath;
        $v = is_file($full) ? filemtime($full) : time();
        return $relPath . '?v=' . $v;
    }
}
