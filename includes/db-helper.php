<?php
// =========================================================================
// estudar-em-portugal — includes/db-helper.php
//
// Analytics de visitas (humanos vs bots, IPs únicos, país) — MESMO padrão
// usado em noticias-local (site_visits + bn_ensure_analytics_tables) e em
// EstudarNoEstrangeiro (blog_view_hits). Copiado de propósito de
// noticias-local/deploy/includes/db-helper.php para ficar consistente com
// o resto da "família" de sites Da Vinci, com o schema self-healing que
// corrigiu o dashboard de noticias-local a mostrar sempre zero.
//
// Base de dados PRÓPRIA (config.php define DB_NAME=ginasiosdavinci_estudaremportugal
// por defeito) — não é partilhada com os projetos irmãos.
//
// Preparado para artigos: as tabelas `artigos` e `artigo_views` já são
// criadas aqui (idempotente) mesmo sem nenhum artigo publicado ainda —
// para quando blog.php deixar de ser estático, basta usar artigo_insert()
// / artigo_regista_view() / artigo_increment_views() sem precisar de
// nenhuma migração de schema nova. views-stats.php já lê `artigos` e
// mostra "sem artigos ainda" enquanto a tabela estiver vazia.
// =========================================================================

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

// ---- Conexão MySQLi (cache por request) -----------------------
function db(): ?mysqli {
    static $link = null;
    if ($link instanceof mysqli) return $link;

    if (function_exists('mysqli_report')) mysqli_report(MYSQLI_REPORT_OFF);
    $hosts = [DB_HOST];
    $hl = strtolower((string) DB_HOST);
    if     ($hl === 'localhost') $hosts[] = '127.0.0.1';
    elseif ($hl === '127.0.0.1' || $hl === '::1') $hosts[] = 'localhost';

    $last = '';
    foreach ($hosts as $h) {
        $c = mysqli_init();
        if (!$c) { $last = 'mysqli_init falhou'; continue; }
        @mysqli_options($c, MYSQLI_OPT_CONNECT_TIMEOUT, 4);
        if (@mysqli_real_connect($c, $h, DB_USER, DB_PASS, DB_NAME)) {
            $c->set_charset('utf8mb4');
            $link = $c;
            bn_ensure_analytics_tables($link);
            return $link;
        }
        $last = mysqli_connect_error();
    }
    error_log('[estudar-em-portugal/db] ligação falhou: ' . $last);
    return null;
}

/**
 * Garante (idempotente, CREATE TABLE IF NOT EXISTS) que as tabelas de
 * analytics existem logo após ligar à BD — 1x por pedido (o $link fica
 * em cache estático em db()). Mesmo fix aplicado a noticias-local em
 * 2026-07-11 (bn_ensure_analytics_tables): sem isto, um schema.sql nunca
 * corrido manualmente = dashboard sempre a zero, sem qualquer erro visível.
 *
 * Inclui também `artigos` e `artigo_views`, mesmo sem nenhum artigo
 * publicado ainda — ver nota no topo do ficheiro.
 */
function bn_ensure_analytics_tables(mysqli $d): void {
    static $done = false;
    if ($done) return;
    $done = true;

    @$d->query("CREATE TABLE IF NOT EXISTS site_visits (
        id       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        ip_hash  CHAR(64) NOT NULL,
        pais     CHAR(2) NULL,
        is_bot   TINYINT(1) NOT NULL DEFAULT 0,
        dia      DATE NOT NULL,
        UNIQUE KEY uniq_visit (ip_hash, dia),
        KEY idx_dia (dia),
        KEY idx_dia_bot (dia, is_bot)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    @$d->query("CREATE TABLE IF NOT EXISTS ip_geo_cache (
        ip_hash     CHAR(64) NOT NULL PRIMARY KEY,
        pais        CHAR(2) NULL,
        created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    @$d->query("CREATE TABLE IF NOT EXISTS stats_pais_historico (
        pais            CHAR(2) NOT NULL PRIMARY KEY,
        leituras        INT NOT NULL DEFAULT 0,
        ultimo_aumento  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    @$d->query("CREATE TABLE IF NOT EXISTS ip_addresses (
        ip_hash              CHAR(64)  NOT NULL PRIMARY KEY,
        ip_address           VARCHAR(45) NOT NULL,
        user_agent           VARCHAR(255) NOT NULL DEFAULT '',
        first_seen           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        last_seen            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        hits                 INT NOT NULL DEFAULT 1,
        pais                 VARCHAR(2) NOT NULL DEFAULT '',
        pais_atualizado_em   TIMESTAMP NULL,
        KEY idx_last_seen (last_seen),
        KEY idx_pais (pais),
        KEY idx_hits (hits)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // ---- Preparado para o blog real (ainda sem nenhuma linha) -----------
    @$d->query("CREATE TABLE IF NOT EXISTS artigos (
        id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        slug            VARCHAR(191) NOT NULL UNIQUE,
        titulo          VARCHAR(255) NOT NULL,
        descricao_meta  VARCHAR(500) NULL,
        imagem_url      VARCHAR(255) NULL,
        tema            VARCHAR(60)  NULL,
        status          ENUM('rascunho','publicado') NOT NULL DEFAULT 'rascunho',
        contador_views  INT UNSIGNED NOT NULL DEFAULT 0,
        data_publicacao DATETIME NULL,
        created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_status_pub (status, data_publicacao),
        KEY idx_views (contador_views)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    @$d->query("CREATE TABLE IF NOT EXISTS artigo_views (
        artigo_id   BIGINT UNSIGNED NOT NULL,
        ip_hash     CHAR(64) NOT NULL,
        viewed_at   DATETIME NOT NULL,
        PRIMARY KEY (artigo_id, ip_hash),
        KEY idx_viewed_at (viewed_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // ---- Comparações (Portugal vs Estrangeiro) --------------------------
    // Tabela de cards pré-fabricados para a secção "Comparações do Blog" em
    // comparar.php. Permite ao gerador_artigo_comparar.php registar cada
    // artigo gerado sem ter de criar tabelas novas; e ao views-stats.php
    // contar leituras por destino. INSERT em `comparar_artigo_views` é
    // deduplicado por (artigo_id, ip_hash) com janela de 24h.
    $d && @$d->query("CREATE TABLE IF NOT EXISTS blog_views (
        slug       VARCHAR(191) NOT NULL PRIMARY KEY,
        views      INT UNSIGNED NOT NULL DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $d && @$d->query("CREATE TABLE IF NOT EXISTS blog_view_hits (
        id      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        slug    VARCHAR(191) NOT NULL,
        ip_hash CHAR(64) NOT NULL,
        country CHAR(2) NOT NULL DEFAULT 'XX',
        is_bot  TINYINT(1) NOT NULL DEFAULT 0,
        day     DATE NOT NULL,
        UNIQUE KEY uniq_hit (slug, ip_hash, day),
        KEY idx_day_country (day, country),
        KEY idx_day_bot     (day, is_bot)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $d && @$d->query("CREATE TABLE IF NOT EXISTS ip_country_ranges (
        range_start INT UNSIGNED NOT NULL,
        range_end   INT UNSIGNED NOT NULL,
        country     CHAR(2) NOT NULL,
        PRIMARY KEY (range_start)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    // Migração leve (idempotente) para BD legadas — 1060/1061 são swallowable.
    $d && @$d->query("ALTER TABLE blog_view_hits ADD INDEX idx_day_country (day, country)");
    $d && @$d->query("ALTER TABLE blog_view_hits ADD INDEX idx_day_bot (day, is_bot)");

    @$d->query("CREATE TABLE IF NOT EXISTS comparar_artigos (
        id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        slug            VARCHAR(191) NOT NULL UNIQUE,
        titulo          VARCHAR(255) NOT NULL,
        descricao_meta  VARCHAR(500) NULL,
        h1_html         VARCHAR(255) NULL,
        destino_a       VARCHAR(40) NOT NULL,
        destino_b       VARCHAR(40) NOT NULL,
        imagem_url      VARCHAR(255) NULL,
        categoria       VARCHAR(60) NULL,
        status          ENUM('rascunho','publicado') NOT NULL DEFAULT 'publicado',
        contador_views  INT UNSIGNED NOT NULL DEFAULT 0,
        data_publicacao DATETIME NULL,
        created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_pub (status, data_publicacao),
        KEY idx_views (contador_views)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    @$d->query("CREATE TABLE IF NOT EXISTS comparar_artigo_views (
        artigo_id   BIGINT UNSIGNED NOT NULL,
        ip_hash     CHAR(64) NOT NULL,
        viewed_at   DATETIME NOT NULL,
        PRIMARY KEY (artigo_id, ip_hash),
        KEY idx_viewed_at (viewed_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // ---- Chatbot Leo (histórico de conversas) --------------------------
    @$d->query("CREATE TABLE IF NOT EXISTS chat_messages (
        id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        conversation_id  VARCHAR(40)   NOT NULL DEFAULT '',
        user_message     TEXT          NOT NULL,
        bot_reply        TEXT          NOT NULL,
        escalated        TINYINT(1)    NOT NULL DEFAULT 0,
        ip               VARCHAR(45)   NOT NULL DEFAULT '',
        user_agent       VARCHAR(255)  NOT NULL DEFAULT '',
        page_url         VARCHAR(255)  NOT NULL DEFAULT '',
        created_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_conversation (conversation_id),
        KEY idx_created (created_at),
        KEY idx_escalated (escalated)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // ---- Blog gerado por IA (cron-gerar-blog.php) ----------------------
    @$d->query("CREATE TABLE IF NOT EXISTS blog_posts (
        id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
        slug             VARCHAR(200)  NOT NULL,
        title            VARCHAR(255)  NOT NULL,
        h1_html          VARCHAR(400)  NULL,
        excerpt          VARCHAR(500)  NULL,
        content_html     MEDIUMTEXT    NOT NULL,
        category         VARCHAR(50)   NOT NULL DEFAULT 'dica',
        category_label   VARCHAR(80)   NULL,
        category_icon    VARCHAR(80)   NULL,
        hero_image       VARCHAR(255)  NULL,
        meta_description VARCHAR(320)  NULL,
        meta_keywords    VARCHAR(500)  NULL,
        reading_minutes  TINYINT UNSIGNED NULL,
        author           VARCHAR(120)  NOT NULL DEFAULT 'Equipa Lá Fora',
        status           ENUM('published','draft') NOT NULL DEFAULT 'published',
        published_at     DATETIME      NULL,
        updated_at       DATETIME      NULL,
        created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_slug (slug),
        KEY idx_status_date (status, published_at),
        KEY idx_category (category)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

/**
 * Guarda 1 troca de mensagens do chatbot Leo (best-effort — nunca quebra o
 * chat). Reaproveita a ligação db() já existente em vez de uma BD separada
 * (ponytail: este site já centraliza tudo numa BD própria, não há motivo
 * para criar uma segunda ligação só para o chat).
 */
function lf_store_chat_message(array $row): bool {
    $d = db(); if (!$d) return false;
    $stmt = $d->prepare(
        'INSERT INTO chat_messages (conversation_id, user_message, bot_reply, escalated, ip, user_agent, page_url)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    if (!$stmt) return false;
    $conv = mb_substr((string) ($row['conversation_id'] ?? ''), 0, 40);
    $um   = (string) ($row['user_message'] ?? '');
    $br   = (string) ($row['bot_reply']    ?? '');
    $esc  = (int) ($row['escalated']       ?? 0);
    $ip   = mb_substr((string) ($row['ip']         ?? ''), 0, 45);
    $ua   = mb_substr((string) ($row['user_agent'] ?? ''), 0, 255);
    $url  = mb_substr((string) ($row['page_url']   ?? ''), 0, 255);
    $stmt->bind_param('sssisss', $conv, $um, $br, $esc, $ip, $ua, $url);
    $ok = $stmt->execute();
    $stmt->close();
    return (bool) $ok;
}

if (!function_exists('e')) {
    function e(?string $s): string {
        return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

/** Deteta bots/crawlers pelo User-Agent (partilhado por site_visit_track() e artigo_regista_view()). */
function bn_is_bot(): bool {
    $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    return $ua === ''
        || (bool) preg_match('/bot|crawl|spider|slurp|curl|wget|python|httpclient|libwww|headless|phantom|lighthouse|pingdom|facebookexternalhit|bingpreview|whatsapp|telegram|preview/i', $ua);
}

/**
 * Regista 1 visita ao site inteiro (qualquer página), dedupada por
 * (ip_hash, dia). Chamada em includes/footer.php (partilhado por todas
 * as páginas: index.php, contato.php, blog.php, ...). Bots ficam
 * registados (is_bot=1) para auditoria, mas nunca contam como visita
 * humana — mesma filosofia de noticias-local.
 */
function site_visit_track(): void {
    $d = db(); if (!$d) return;
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    if ($ip === '') return;
    $ipHash = hash('sha256', $ip . '|' . CSRF_SECRET);
    $isBot  = bn_is_bot() ? 1 : 0;
    $pais   = $isBot ? null : bn_geo_pais_resolve($ipHash, $ip);

    $stmt = $d->prepare('INSERT IGNORE INTO site_visits (ip_hash, pais, is_bot, dia) VALUES (?, ?, ?, CURDATE())');
    if (!$stmt) return;   // tabela pode não existir ainda — silencioso, nunca quebra a página
    $stmt->bind_param('ssi', $ipHash, $pais, $isBot);
    $stmt->execute();
    $isNew = $stmt->affected_rows > 0;
    $stmt->close();

    if (!$isBot && $ip !== '') {
        ip_addresses_upsert($ipHash, $ip, (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), (string) ($pais ?? ''));
    }

    if ($isNew && random_int(1, 100) === 1) bn_analytics_prune();
}

// =========================================================================
// GEO-ANALYTICS (anónimo, RGPD-friendly) — igual a noticias-local.
// Apenas o código ISO do país (PT, BR, US) é persistido. O IP em claro
// nunca chega à BD fora de ip_addresses (retenção 30 dias, ver prune).
// =========================================================================

/** Lookup do país de um IP via ip-api.com (keyless). Null em falha/IP privado. */
function bn_ip_country(string $ip): ?string {
    $ip = trim($ip);
    if ($ip === '') return null;
    if (stripos($ip, '::ffff:') === 0) $ip = substr($ip, 7);
    if (!filter_var($ip, FILTER_VALIDATE_IP)) return null;
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return null;
    }
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 1.2,
            'ignore_errors' => true,
            'header' => "User-Agent: estudar-em-portugal/1.0\r\n",
            'method' => 'GET',
        ],
    ]);
    $url = 'http://ip-api.com/json/' . rawurlencode($ip) . '?fields=status,countryCode';
    $res = @file_get_contents($url, false, $ctx);
    if (!$res) return null;
    $j = json_decode($res, true);
    if (!is_array($j) || ($j['status'] ?? '') !== 'success') return null;
    $cc = isset($j['countryCode']) ? strtoupper(trim((string) $j['countryCode'])) : '';
    return preg_match('/^[A-Z]{2}$/', $cc) ? $cc : null;
}

/** Resolve o país por ip_hash, com cache de 30 dias em ip_geo_cache. */
function bn_geo_pais_resolve(string $ipHash, string $ipRaw): ?string {
    if ($ipHash === '') return null;
    $d = db(); if (!$d) return null;

    if ($stmt = $d->prepare('SELECT pais, created_at FROM ip_geo_cache WHERE ip_hash = ? LIMIT 1')) {
        $stmt->bind_param('s', $ipHash);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($row && !empty($row['created_at']) && strtotime((string) $row['created_at']) > time() - 30 * 86400) {
            return ($row['pais'] !== null) ? (string) $row['pais'] : null;
        }
    }
    if ($ipRaw === '') return null;
    $pais = bn_ip_country($ipRaw);
    if ($stmt = $d->prepare('INSERT INTO ip_geo_cache (ip_hash, pais, created_at)
                             VALUES (?, ?, NOW())
                             ON DUPLICATE KEY UPDATE pais = VALUES(pais), created_at = NOW()')) {
        $stmt->bind_param('ss', $ipHash, $pais);
        @$stmt->execute();
        $stmt->close();
    }
    return $pais;
}

/** Incrementa o contador vitalício por país (só visitas humanas novas). */
function stats_pais_increment(?string $pais): void {
    if ($pais === null || !preg_match('/^[A-Z]{2}$/', $pais)) return;
    $d = db(); if (!$d) return;
    $stmt = $d->prepare('INSERT INTO stats_pais_historico (pais, leituras)
                         VALUES (?, 1)
                         ON DUPLICATE KEY UPDATE leituras = leituras + 1');
    if (!$stmt) return;
    $stmt->bind_param('s', $pais);
    @$stmt->execute();
    $stmt->close();
}

/** UPSERT do visitante em ip_addresses (auditoria agregada, retenção 30 dias). */
function ip_addresses_upsert(string $ipHash, string $ipAddress, string $userAgent = '', string $pais = ''): void {
    if ($ipHash === '' || $ipAddress === '') return;
    $d = db(); if (!$d) return;
    $paisStr = preg_match('/^[A-Z]{2}$/', $pais) ? $pais : '';
    $ua      = mb_substr($userAgent, 0, 255);

    $sql = "INSERT INTO ip_addresses (ip_hash, ip_address, user_agent, pais, hits)
            VALUES (?, ?, ?, ?, 1)
            ON DUPLICATE KEY UPDATE
                hits = hits + 1,
                user_agent = CASE WHEN ? <> '' THEN ? ELSE user_agent END,
                pais = IF(? <> '' AND (pais = '' OR pais IS NULL), ?, pais),
                pais_atualizado_em = IF(? <> '' AND (pais = '' OR pais IS NULL), NOW(), pais_atualizado_em)";
    $stmt = $d->prepare($sql);
    if (!$stmt) return;
    $stmt->bind_param('sssssssss', $ipHash, $ipAddress, $ua, $paisStr, $ua, $ua, $paisStr, $paisStr, $paisStr);
    @$stmt->execute();
    $stmt->close();
}

/** Purga registos com mais de 30 dias (chamado ~1% das visitas). */
function bn_analytics_prune(): void {
    $d = db(); if (!$d) return;
    @$d->query('DELETE FROM site_visits WHERE dia < CURDATE() - INTERVAL 30 DAY');
    @$d->query('DELETE FROM ip_geo_cache WHERE created_at < NOW() - INTERVAL 30 DAY');
    @$d->query('DELETE FROM ip_addresses WHERE last_seen < NOW() - INTERVAL 30 DAY');
    @$d->query('DELETE FROM artigo_views WHERE viewed_at < NOW() - INTERVAL 7 DAY');
    @$d->query('DELETE FROM comparar_artigo_views WHERE viewed_at < NOW() - INTERVAL 7 DAY');
}

// =========================================================================
// ARTIGOS — pronto para quando o blog deixar de ser estático.
// Mesma filosofia de noticias-local: contador_views só conta humanos,
// dedupado por (artigo_id, ip_hash) via artigo_regista_view(). Nenhuma
// destas funções é chamada por blog.php hoje (é estático) — mas o
// schema já existe, self-healing, para plugar sem migração.
// =========================================================================

/** Lista de artigos publicados (vazio até haver artigos reais). */
function artigos_publicados(int $limit = 20): array {
    $d = db(); if (!$d) return [];
    $stmt = $d->prepare("SELECT id, slug, titulo, descricao_meta, imagem_url, tema, contador_views, data_publicacao
                         FROM artigos WHERE status = 'publicado'
                         ORDER BY data_publicacao DESC LIMIT ?");
    if (!$stmt) return [];
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

/** Top artigos por leituras (para o dashboard). */
function artigos_top(int $limit = 15): array {
    $d = db(); if (!$d) return [];
    $stmt = $d->prepare("SELECT slug, titulo, contador_views FROM artigos
                         WHERE status = 'publicado' ORDER BY contador_views DESC LIMIT ?");
    if (!$stmt) return [];
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function artigo_increment_views(int $id): void {
    $d = db(); if (!$d) return;
    $stmt = $d->prepare('UPDATE artigos SET contador_views = contador_views + 1 WHERE id = ?');
    if (!$stmt) return;
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}

function artigo_regista_view(int $id, string $ipHash, string $ipRaw = '', string $userAgent = ''): bool {
    $d = db(); if (!$d) return false;
    $stmt = $d->prepare(
        'INSERT INTO artigo_views (artigo_id, ip_hash, viewed_at) VALUES (?, ?, NOW())
         ON DUPLICATE KEY UPDATE viewed_at =
             IF(viewed_at < NOW() - INTERVAL 24 HOUR, NOW(), viewed_at)');
    if (!$stmt) return false;
    $stmt->bind_param('is', $id, $ipHash);
    $ok = $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    if (!$ok) return false;
    $counts = ($affected === 1 || $affected === 2);
    if (!$counts) return false;

    if ($ipRaw !== '') {
        $pais = bn_geo_pais_resolve($ipHash, $ipRaw);
        stats_pais_increment($pais);
        ip_addresses_upsert($ipHash, $ipRaw, $userAgent, (string) $pais);
    }
    return true;
}

// =========================================================================
// COMPARAR — Portugal vs Estrangeiro (cards pré-fabricados em comparar.php).
//
// conversas:
// - comparar_links_publicados()  — lista os pares já prontos para mostrar
// - comparar_link_by_slug()      — usado pelo views-stats para contar leituras
// - comparar_link_increment_views()
// - comparar_link_regista_view()
// - comparar_link_insert()       — usado pelo gerador_artigo_comparar.php
// =========================================================================

function comparar_links_publicados(int $limit = 18): array {
    $d = db(); if (!$d) return [];
    $stmt = $d->prepare("SELECT id, slug, titulo, h1_html, destino_a, destino_b, imagem_url,
                                categoria, descricao_meta, contador_views, data_publicacao
                         FROM comparar_artigos
                         WHERE status='publicado'
                         ORDER BY data_publicacao DESC, contador_views DESC LIMIT ?");
    if (!$stmt) return [];
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function comparar_top(int $limit = 15): array {
    $d = db(); if (!$d) return [];
    $stmt = $d->prepare("SELECT slug, titulo, destino_a, destino_b, contador_views
                         FROM comparar_artigos
                         WHERE status='publicado' ORDER BY contador_views DESC LIMIT ?");
    if (!$stmt) return [];
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function comparar_link_by_slug(string $slug): ?array {
    $d = db(); if (!$d) return null;
    $stmt = $d->prepare("SELECT id, slug, titulo, h1_html, destino_a, destino_b, imagem_url,
                                categoria, descricao_meta, contador_views, data_publicacao
                         FROM comparar_artigos WHERE slug = ? LIMIT 1");
    if (!$stmt) return null;
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function comparar_link_insert(array $dados): ?int {
    $d = db(); if (!$d) return null;
    $slug      = (string) ($dados['slug']          ?? '');
    $titulo    = (string) ($dados['titulo']        ?? '');
    $h1        = (string) ($dados['h1_html']       ?? $titulo);
    $a         = (string) ($dados['destino_a']     ?? 'Portugal');
    $b         = (string) ($dados['destino_b']     ?? 'Europa');
    $img       = (string) ($dados['imagem_url']    ?? '');
    $cat       = (string) ($dados['categoria']     ?? 'comparação');
    $desc      = (string) ($dados['descricao_meta'] ?? '');
    $status    = (string) ($dados['status']        ?? 'publicado');
    $pub       = (string) ($dados['data_publicacao'] ?? date('Y-m-d H:i:s'));
    if ($slug === '' || $titulo === '') return null;

    $stmt = $d->prepare("INSERT INTO comparar_artigos
        (slug, titulo, h1_html, destino_a, destino_b, imagem_url, categoria, descricao_meta, status, data_publicacao, contador_views)
        VALUES (?,?,?,?,?,?,?,?,?,?,0)
        ON DUPLICATE KEY UPDATE
            id = LAST_INSERT_ID(id),
            titulo = VALUES(titulo),
            descricao_meta = VALUES(descricao_meta),
            imagem_url = VALUES(imagem_url),
            data_publicacao = VALUES(data_publicacao)");
    if (!$stmt) return null;
    $stmt->bind_param('ssssssssss', $slug, $titulo, $h1, $a, $b, $img, $cat, $desc, $status, $pub);
    $stmt->execute();
    $id = (int) $stmt->insert_id;
    $stmt->close();
    return $id > 0 ? $id : null;
}

function comparar_link_increment_views(int $id): void {
    $d = db(); if (!$d) return;
    $stmt = $d->prepare('UPDATE comparar_artigos SET contador_views = contador_views + 1 WHERE id = ?');
    if (!$stmt) return;
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}

function comparar_link_regista_view(int $id, string $ipHash, string $ipRaw = '', string $userAgent = ''): bool {
    $d = db(); if (!$d) return false;
    $stmt = $d->prepare(
        'INSERT INTO comparar_artigo_views (artigo_id, ip_hash, viewed_at) VALUES (?, ?, NOW())
         ON DUPLICATE KEY UPDATE viewed_at =
             IF(viewed_at < NOW() - INTERVAL 24 HOUR, NOW(), viewed_at)');
    if (!$stmt) return false;
    $stmt->bind_param('is', $id, $ipHash);
    $ok = $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    if (!$ok) return false;
    if ($affected !== 1 && $affected !== 2) return false;
    if ($ipRaw !== '') {
        $pais = bn_geo_pais_resolve($ipHash, $ipRaw);
        stats_pais_increment($pais);
        ip_addresses_upsert($ipHash, $ipRaw, $userAgent, (string) $pais);
    }
    return true;
}

// =========================================================================
// VIEW-TRACKER (L\u00e1 Fora studding) — re-aplicado do EstudarNoEstrangeiro/final.
// Sistema unificado de tracking por SLUG (incl. sentinel 'global-site-visit'
// para o site inteiro). Substitui o legado site_visits como fonte principal
// do dashboard admin/views-stats.php. site_visits/ip_addresses continuam a
// ser preenchidos em paralelo para retro-compat (costam ≈ zero).
//
// Funções com prefixo `lf_*` para isolar do namespace `bn_*` local.
// =========================================================================

/** HMAC-SHA256 com salt estável por-deployment. */
function lf_hash_salt(): string {
    if (defined('VIEWS_HASH_SALT') && VIEWS_HASH_SALT !== '') return (string) VIEWS_HASH_SALT;
    if (defined('CSRF_SECRET')     && CSRF_SECRET     !== '') return (string) CSRF_SECRET;
    $looksProd = (bool) preg_match('/\.([a-z]{2,3})\.?$/i', (string) DB_HOST);
    if ($looksProd) {
        error_log('[estudar-em-portugal/view-tracker] AVISO: VIEWS_HASH_SALT/CRSF_SECRET ausentes em produção — a usar literal dev NÃO RGPD-seguro. Define VIEWS_HASH_SALT no .env (openssl rand -hex 32).');
    }
    return (string) 'estudar-em-portugal-dev-salt';
}

/** IP do visitante, respeitando CDN/proxy. NUNCA persistido em claro. */
function lf_client_ip(): string {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) return (string) $_SERVER['HTTP_CF_CONNECTING_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $xff = (string) $_SERVER['HTTP_X_FORWARDED_FOR'];
        $first = trim(explode(',', $xff)[0] ?? '');
        if ($first !== '') return $first;
    }
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

/** Deteta de bots/crawlers pelo User-Agent. Lista AUMENTADA (incl. GPTBot, ClaudeBot, PerplexityBot, Anthropic-AI) para GEO/AEO. */
function lf_is_bot(): bool {
    $ua = strtolower((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    if ($ua === '') return true;
    $bots = [
        'googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider', 'yandexbot',
        'sogou', 'exabot', 'facebot', 'facebookexternalhit', 'ia_archiver', 'ahrefsbot',
        'semrushbot', 'mj12bot', 'dotbot', 'petalbot', 'bytespider', 'gptbot', 'claudebot',
        'anthropic-ai', 'perplexitybot', 'applebot', 'bot', 'crawler', 'spider',
    ];
    foreach ($bots as $b) {
        if (strpos($ua, $b) !== false) return true;
    }
    return false;
}

/** País via CF-IPCountry header (se houver CDN), senão fallback ip2long+ip_country_ranges. */
function lf_country(): string {
    $cc = strtoupper(trim((string) ($_SERVER['HTTP_CF_IPCOUNTRY'] ?? '')));
    if (preg_match('/^[A-Z]{2}$/', $cc)) {
        if ($cc === 'T1') return 'XX'; // Tor/anónimo = 'XX'
        return $cc;
    }
    return lf_country_from_db(lf_client_ip());
}

/** Lookup local IP→Country (IPv4 only; fallback offline). */
function lf_country_from_db(string $ip): string {
    if (strpos($ip, ':') !== false) return 'XX';
    $long = ip2long($ip);
    if ($long === false) return 'XX';
    $d = db(); if (!$d) return 'XX';
    $stmt = $d->prepare(
        "SELECT country, range_end FROM ip_country_ranges
         WHERE range_start <= ? ORDER BY range_start DESC LIMIT 1"
    );
    if (!$stmt) return 'XX';
    $stmt->bind_param('i', $long);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row || $long > (int) $row['range_end']) return 'XX';
    $cc = strtoupper((string) $row['country']);
    return preg_match('/^[A-Z]{2}$/', $cc) ? $cc : 'XX';
}

/**
 * Regista 1 hit em blog_view_hits, incrementando blog_views APENAS se for
 * humano único no dia (UNIQUE slug+ip_hash+day).
 *
 * GC probabilístico 1%: limpa rows > LF_VIEWS_RETENTION_DAYS dias.
 * Tudo best-effort, 0 erro para o utilizador.
 */
function lf_track_view(string $slug, ?string $ipRaw = null, ?string $userAgent = null): void {
    $slug = preg_replace('/[^a-z0-9\-\_\/]/', '', strtolower($slug));
    if ($slug === '') return;
    $d = db(); if (!$d) return;
    try {
        $ipHash  = hash_hmac('sha256', lf_client_ip(), lf_hash_salt());
        $country = lf_country();
        $isBot   = lf_is_bot() ? 1 : 0;
        $day     = date('Y-m-d');

        $stmt = $d->prepare(
            'INSERT IGNORE INTO blog_view_hits (slug, ip_hash, country, is_bot, day)
             VALUES (?, ?, ?, ?, ?)'
        );
        if (!$stmt) return;
        $stmt->bind_param('sssis', $slug, $ipHash, $country, $isBot, $day);
        $stmt->execute();
        $isNew = (int) ($d->affected_rows ?? 0) > 0;
        $stmt->close();

        if ($isNew && $isBot === 0) {
            $up = $d->prepare(
                "INSERT INTO blog_views (slug, views) VALUES (?, 1)
                 ON DUPLICATE KEY UPDATE views = views + 1"
            );
            if ($up) {
                $up->bind_param('s', $slug);
                @$up->execute();
                @$up->close();
            }
        }
        if (mt_rand(1, 100) === 1) {
            $ret = defined('LF_VIEWS_RETENTION_DAYS') ? (int) LF_VIEWS_RETENTION_DAYS : 30;
            $del = $d->prepare("DELETE FROM blog_view_hits WHERE day < DATE_SUB(CURDATE(), INTERVAL $ret DAY)");
            if ($del) { @$del->execute(); @$del->close(); }
        }
    } catch (\Throwable $e) {
        // silencioso
    }
}

/** Cacheado por request. Devolve mapa slug → views (só humanos). */
function lf_get_views_map(): array {
    static $map = null;
    if ($map !== null) return $map;
    $map = [];
    $d = db(); if (!$d) return $map;
    try {
        $res = $d->query('SELECT slug, views FROM blog_views');
        if ($res) {
            while ($row = $res->fetch_assoc()) $map[(string) $row['slug']] = (int) $row['views'];
            $res->free();
        }
    } catch (\Throwable $e) {}
    return $map;
}

function lf_get_view(string $slug): int {
    $map = lf_get_views_map();
    return (int) ($map[$slug] ?? 0);
}

function lf_views_available(): bool {
    return db() !== null;
}
