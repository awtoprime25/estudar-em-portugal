-- ============================================================
--  estudar-em-portugal — schema.sql
--
--  Espelho exato das CREATE TABLE já feitas de forma idempotente
--  (self-healing) em includes/db-helper.php::bn_ensure_analytics_tables().
--  Não é obrigatório correr este ficheiro — a app cria tudo sozinha na
--  1ª ligação à BD. Serve para:
--    1) rever o schema antes de apontar para a BD de produção;
--    2) importar via phpMyAdmin/cPanel sem depender do 1º pedido HTTP.
--
--  Uso: phpMyAdmin → seleciona a BD (ver DB_NAME no .env) → Importar → este ficheiro.
-- ============================================================

-- ---- Views do site inteiro (legado, ver footer.php::site_visit_track()) ----
CREATE TABLE IF NOT EXISTS site_visits (
    id       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ip_hash  CHAR(64) NOT NULL,
    pais     CHAR(2) NULL,
    is_bot   TINYINT(1) NOT NULL DEFAULT 0,
    dia      DATE NOT NULL,
    UNIQUE KEY uniq_visit (ip_hash, dia),
    KEY idx_dia (dia),
    KEY idx_dia_bot (dia, is_bot)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ip_geo_cache (
    ip_hash     CHAR(64) NOT NULL PRIMARY KEY,
    pais        CHAR(2) NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS stats_pais_historico (
    pais            CHAR(2) NOT NULL PRIMARY KEY,
    leituras        INT NOT NULL DEFAULT 0,
    ultimo_aumento  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ip_addresses (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ip_country_ranges (
    range_start INT UNSIGNED NOT NULL,
    range_end   INT UNSIGNED NOT NULL,
    country     CHAR(2) NOT NULL,
    PRIMARY KEY (range_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Views por página/artigo (sistema unificado lf_track_view(), ver
--      includes/db-helper.php). slug='global-site-visit' = site inteiro;
--      qualquer outro slug = artigo/página específica. Ambos ficam na
--      MESMA tabela, distinguidos pelo slug — não são a mesma contagem
--      que site_visits acima (esse é legado, mantido em paralelo). ------
CREATE TABLE IF NOT EXISTS blog_views (
    slug       VARCHAR(191) NOT NULL PRIMARY KEY,
    views      INT UNSIGNED NOT NULL DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_view_hits (
    id      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    slug    VARCHAR(191) NOT NULL,
    ip_hash CHAR(64) NOT NULL,
    country CHAR(2) NOT NULL DEFAULT 'XX',
    is_bot  TINYINT(1) NOT NULL DEFAULT 0,
    day     DATE NOT NULL,
    UNIQUE KEY uniq_hit (slug, ip_hash, day),
    KEY idx_day_country (day, country),
    KEY idx_day_bot     (day, is_bot)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Artigos legado (preparado, sem uso ativo hoje) -----------------
CREATE TABLE IF NOT EXISTS artigos (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS artigo_views (
    artigo_id   BIGINT UNSIGNED NOT NULL,
    ip_hash     CHAR(64) NOT NULL,
    viewed_at   DATETIME NOT NULL,
    PRIMARY KEY (artigo_id, ip_hash),
    KEY idx_viewed_at (viewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Comparações Portugal vs Europa (gerador_artigo_comparar.php) ---
CREATE TABLE IF NOT EXISTS comparar_artigos (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comparar_artigo_views (
    artigo_id   BIGINT UNSIGNED NOT NULL,
    ip_hash     CHAR(64) NOT NULL,
    viewed_at   DATETIME NOT NULL,
    PRIMARY KEY (artigo_id, ip_hash),
    KEY idx_viewed_at (viewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Chatbot "Leo" — histórico de conversas (chat.php) --------------
CREATE TABLE IF NOT EXISTS chat_messages (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Blog gerado por IA (cron-gerar-blog.php) -----------------------
CREATE TABLE IF NOT EXISTS blog_posts (
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
    author           VARCHAR(120)  NOT NULL DEFAULT 'Equipa Estudar em Portugal',
    status           ENUM('published','draft') NOT NULL DEFAULT 'published',
    published_at     DATETIME      NULL,
    updated_at       DATETIME      NULL,
    created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_slug (slug),
    KEY idx_status_date (status, published_at),
    KEY idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
