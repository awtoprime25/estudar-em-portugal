<?php
/**
 * gerador_artigo_comparar.php
 *
 * Adaptado de EstudarNoEstrangeiro/gerador_artigo_idiomas.php para o projeto
 * estudar-em-portugal/. Em vez de "aprendizagem de idiomas", gera artigos
 * comparativos entre Portugal (este site) e outros países europeus.
 *
 * Saída: artigo gravado em
 *   - `comparar_artigos` (BD) — pronto a mostrar em comparar.php e em blog/
 *   - `blog/{slug}.php`       — ficheiro PHP renderizado, include header/footer
 *
 * Tracking: tanto compara_link_regista_view() (legacy contar_views 24h) como
 * lf_track_view() (schema unificado, dedup 24h via UNIQUE+ip_hash+day)
 * são chamados no ficheiro gerado. Diferença de contadores é documentada em
 * admin/views-stats.php.
 */

declare(strict_types=1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db-helper.php';

// ============================================================
// .env loader (sem Composer) — idêntico ao EstudarNoEstrangeiro
// ============================================================
$enpEnvFile = __DIR__ . '/.env';
$enpApiKey  = (string) (getenv('OPENROUTER_API_KEY') ?: '');
$enpModel   = (string) (getenv('OPENAI_MODEL') ?: 'openai/gpt-4o-mini');
if (is_readable($enpEnvFile)) {
    foreach (file($enpEnvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $enpLine) {
        $enpLine = trim($enpLine);
        if ($enpLine === '' || $enpLine[0] === '#' || strpos($enpLine, '=') === false) continue;
        [$enpK, $enpV] = explode('=', $enpLine, 2);
        $enpK = trim($enpK); $enpV = trim($enpV);
        if ($enpK === 'OPENROUTER_API_KEY') $enpApiKey = $enpV;
        if ($enpK === 'OPENAI_MODEL')        $enpModel  = $enpV;
    }
}

// ============================================================
// Tópicos pré-fabricados (fila self-healing)
// ============================================================
function enp_topicos_seed(): array {
    return [
        ['Portugal vs Holanda: custo de vida estudantil 2026',           'Portugal', 'Holanda',         'Custo de vida',     'custo mensal'],
        ['Portugal vs Alemanha: propinas e acesso ao ensino superior', 'Portugal', 'Alemanha',        'Propinas',          'propinas e taxas'],
        ['Portugal vs Reino Unido: visto de estudante para brasileiros','Portugal', 'Reino Unido',     'Visto',             'visto e burocracia'],
        ['Portugal vs Espanha: equivalências e diploma',                 'Portugal', 'Espanha',         'Sistema de ensino', 'ECTS equivalências'],
        ['Portugal vs Itália: estudar Medicina sendo brasileiro',         'Portugal', 'Itália',          'Admissão',          'medicina ENEM vs IMAT'],
        ['Portugal vs República Checa: estudar Engenharia Informática',  'Portugal', 'República Checa', 'Admissão',          'foundation year'],
        ['Portugal vs Irlanda: custo de propinas e qualidade de vida',    'Portugal', 'Irlanda',         'Custo de vida',     'propinas Dublin'],
        ['Portugal vs França: estudar Gestão sendo brasileiro',          'Portugal', 'França',          'Admissão',          'Grandes Écoles'],
    ];
}

function enp_ensure_topicos_table(): void {
    $d = db(); if (!$d) return;
    @$d->query("CREATE TABLE IF NOT EXISTS comparar_topicos (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        titulo VARCHAR(255) NOT NULL,
        destino_a VARCHAR(60) NOT NULL,
        destino_b VARCHAR(60) NOT NULL,
        categoria VARCHAR(60) NOT NULL,
        foco VARCHAR(80) NOT NULL,
        count INT UNSIGNED NOT NULL DEFAULT 0,
        ultimo_uso TIMESTAMP NULL,
        KEY idx_count (count),
        KEY idx_ultimo_uso (ultimo_uso)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function enp_seed_topicos(): void {
    $d = db(); if (!$d) return;
    enp_ensure_topicos_table();
    if ($stmt = $d->query("SELECT COUNT(*) AS n FROM comparar_topicos")) {
        $r = $stmt->fetch_assoc();
        if ((int)($r['n'] ?? 0) > 0) return;
    }
    foreach (enp_topicos_seed() as $t) {
        $stmt = $d->prepare("INSERT INTO comparar_topicos (titulo, destino_a, destino_b, categoria, foco) VALUES (?,?,?,?,?)");
        if (!$stmt) continue;
        $stmt->bind_param('sssss', $t[0], $t[1], $t[2], $t[3], $t[4]);
        $stmt->execute();
        $stmt->close();
    }
}
enp_seed_topicos();

// ============================================================
// Helpers
// ============================================================
function enp_slug(string $s): string {
    $s = mb_strtolower($s, 'UTF-8');
    $map = ['á'=>'a','à'=>'a','ã'=>'a','â'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ç'=>'c'];
    $s = strtr($s, $map);
    $s = preg_replace('/[^a-z0-9\s-]/', '', $s);
    $s = preg_replace('/\s+/', '-', $s);
    $s = preg_replace('/-+/', '-', $s);
    return trim($s, '-');
}

function enp_scrub_body(string $body): string {
    $body = preg_replace('/<\?(?:php\b|\=)?|\?>/i', '', $body);
    $body = str_replace('__halt_compiler', '', $body);
    $body = str_replace(['</script>', '</SCRIPT>'], ['<\/script>', '<\/SCRIPT>'], $body);
    $body = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $body);
    $body = preg_replace('/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/i', '', $body);
    return $body;
}

/**
 * Renderiza o artigo JSON num ficheiro PHP standalone em blog/{slug}.php.
 *
 * Técnica usada: nowdoc heredoc (`<<<'ENPHP'`) com placeholders `__LI__
 * __TI__ __PI__ __SI__` (string-safe em qualquer contexto) e `__BODY__`.
 * O nowdoc NÃO interpola — então o body é literal até `ENPHP;`. Depois, `strtr`
 * substitui os placeholders pelos valores JSON-encoded (binário-safe em
 * HTML/JS/PHP single-quoted), recorrendo a `json_encode()` que escapa quotes
 * and backslashes correctamente.
 *
 * Resultado: ficheiro gerado é PHP válido (sintaxe), HTML válido (escape),
 * JSON-LD válido (escape via header.php str_replace de " <\/script>").
 */
function enp_render_blog_php(array $artigo, array $topico): string {
    $slug = (string) ($artigo['slug'] ?? '');
    if ($slug === '') return '';

    $titulo   = (string) ($artigo['titulo']         ?? '');
    $meta     = (string) ($artigo['descricao_meta']?? '');
    $img      = (string) ($artigo['imagem_url']     ?? '');
    $a        = (string) ($topico['destino_a']      ?? 'Portugal');
    $b        = (string) ($topico['destino_b']      ?? 'Europa');
    $h1       = (string) ($artigo['h1_html']        ?? $titulo);
    $bodySafe = enp_scrub_body((string)($artigo['descricao_longa'] ?? ''));

    // Schema.org JSON-LD (Article + FAQPage + BreadcrumbList) — escape rigoroso.
    $faqItems = [
        ['q' => 'Posso usar a nota do ENEM para estudar em '.$a.'?',
         'a' => 'Sim, '.$a.' aceita a nota do ENEM como prova de acesso em vários cursos. Na '.$b.', geralmente é exigido IB ou Foundation Year.'],
        ['q' => 'Quanto custa em média estudar em '.$b.'?',
         'a' => 'Em '.$b.', propinas tipicamente entre 2.000€ e 25.000€ por ano (varia por país), e custo de vida 900€ a 1.800€/mês.'],
        ['q' => 'Como funciona o visto para '.$b.'?',
         'a' => 'É necessário visto Schengen de estudante. A StudyWing trata de toda a documentação (carta de aceitação, seguro, comprovativos financeiros).'],
        ['q' => 'O diploma vale em toda a União Europeia?',
         'a' => 'Sim. Tanto o diploma português como o de outros países UE é reconhecível por força do sistema ECTS em todos os 27 países-membros.'],
    ];
    $faqEntities = [];
    foreach ($faqItems as $f) {
        $faqEntities[] = [
            '@type' => 'Question',
            'name'  => $f['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
        ];
    }
    $schemaArr = [
        '@context' => 'https://schema.org',
        '@graph'   => [
            [
                '@type'         => 'Article',
                'headline'      => $titulo,
                'description'   => $meta,
                'image'         => SITE_URL . 'assets/images/' . ($img !== '' ? $img : 'ogi-comparar.png'),
                'url'           => SITE_URL . 'blog/' . $slug . '.php',
                'inLanguage'    => 'pt-PT',
                'datePublished' => date('Y-m-d'),
                'dateModified'  => date('Y-m-d'),
                'author'        => ['@type' => 'Organization', 'name' => 'Estudar em Portugal — Da Vinci × StudyWing', 'url' => SITE_URL],
                'publisher'     => [
                    '@type' => 'Organization',
                    'name'  => 'Estudar em Portugal — Da Vinci × StudyWing',
                    'url'   => SITE_URL,
                    'logo'  => ['@type' => 'ImageObject', 'url' => SITE_URL . 'assets/images/logotipo-studywing.png'],
                ],
                'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => SITE_URL . 'blog/' . $slug . '.php'],
            ],
            ['@type' => 'FAQPage', 'mainEntity' => $faqEntities],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => SITE_URL],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Comparar', 'item' => SITE_URL . 'comparar.php'],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $titulo, 'item' => SITE_URL . 'blog/' . $slug . '.php'],
                ],
            ],
        ],
    ];
    // JSON_UNESCAPED_UNICODE mantém emojis (🇵🇹 / 🇪🇺) legíveis no source.
    // JSON_UNESCAPED_SLASHES mantém URLs sem escape desnecessário de "/".
    $schemaJson = json_encode($schemaArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // Nowdoc (aspas obrigatórias) = zero interpolação no body.
    return <<<'ENPHP'
<?php
// ARTIGO GERADO EM MODO BLOG-COMPARAR — não editar manualmente;
// re-executar gerador_artigo_comparar.php pode reescrever este conteúdo.
$enpSlug        = __LI__;
$enpPageTitle   = __TI__ . " | Estudar em Portugal — Da Vinci × StudyWing";
$enpMeta        = __PI__;
$enpOgImage     = SITE_URL . 'assets/images/__SI__';
$enpExtraJsonLd = __JS__;
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db-helper.php';
require_once __DIR__ . '/../config.php';

if (!bn_is_bot()) {
    $enpIp    = $_SERVER['REMOTE_ADDR'] ?? '';
    $enpHash  = hash('sha256', $enpIp . '|' . CSRF_SECRET);
    $enpArtigo = comparar_link_by_slug($enpSlug);
    if ($enpArtigo) {
        if (comparar_link_regista_view((int) $enpArtigo['id'], $enpHash, $enpIp, $_SERVER['HTTP_USER_AGENT'] ?? '')) {
            comparar_link_increment_views((int) $enpArtigo['id']);
        }
    }
    if (function_exists('lf_track_view')) { @lf_track_view($enpSlug); }
}
$pageSlug = $enpSlug;
?>
<script>window.TRACK_SLUG=<?= json_encode($enpSlug, JSON_UNESCAPED_SLASHES) ?>;window.TRACK_URL='../track-visit.php';</script>
<main id="conteudo">
    <article class="content-block">
        <header class="article-header" style="margin-bottom:24px;">
            <p style="color:#3fd0e0;font-weight:700;font-size:13px;letter-spacing:.08em;text-transform:uppercase;">
                __A__ &times; __B__
            </p>
            <h1 style="font-size:34px;line-height:1.15;color:#0a1628;margin:8px 0 12px;">__H1__</h1>
            <p style="color:#55607a;font-size:15px;line-height:1.6;">__PI_RAW__</p>
        </header>
        __BODY__
        <hr style="border:0;border-top:1px solid #e4e8ef;margin:40px 0;">
        <section style="background:#0a1628;color:#fff;padding:32px;border-radius:18px;margin-top:24px;">
            <h2 style="margin:0 0 12px;">Pronto para avançar?</h2>
            <p style="color:rgba(255,255,255,.85);margin:0 0 18px;line-height:1.6;">
                A equipa StudyWing traça contigo o plano A, B e C para entrar em
                <strong>__A__</strong> ou <strong>__B__</strong>. Candidaturas, vistos, equivalências — tudo num só processo.
            </p>
            <a href="#formulario" class="btn-pill" style="background:#3fd0e0;color:#0a1628;">Falar com a StudyWing →</a>
        </section>
    </article>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
ENPHP;

    // strtr contextual — cada placeholder com a substituição adequada ao destino:
    //   • __LI__/__TI__/__PI__/__JS__  → PHP-double-quoted assign ou JSON-LD
    //     script: json_encode() é apropriado (produz literal JS/PHP válido).
    //   • __A__/__B__/__H1__/__PI_RAW__ → HTML body visível: e() (htmlspecialchars),
    //     NUNCA json_encode (envolveria em aspas e quebraria o texto visível).
    //   • __SI__                       → PHP single-quoted URL string: raw,
    //     caracteres seguros (alphanumerics + -_.+/) não precisam de escape.
    //   • __BODY__                     → HTML body já scrubado pelo enp_scrub_body().
    $output = strtr($output, [
        '__LI__'      => json_encode($slug, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        '__TI__'      => json_encode($titulo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        '__PI__'      => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        '__JS__'      => json_encode($schemaJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        '__A__'       => e($a),
        '__B__'       => e($b),
        '__H1__'      => e($h1),
        '__PI_RAW__'  => e($meta),
        '__SI__'      => $img !== '' ? $img : 'ogi-comparar.png',
        '__BODY__'    => $bodySafe,
    ]);

    // header.php já substitui `</script>` por `<\/script>` no $extraJsonLd, mas
    // o schema JSON aqui está em single-quoted PHP string, por isso escapamos
    // também qualquer `</script>` literal no JSON (defesa em profundidade).
    return str_replace('</script>', '<\\/script>', $output);
}

/**
 * Pipeline (DRY-RUN default).
 */
function enp_artigo_template(string $titulo, string $a, string $b): array {
    $h1 = "Comparação: {$a} vs {$b} — o que considerar antes de decidir";
    return [
        'tema'           => $titulo,
        'h1_html'        => $h1,
        'titulo'         => $titulo . ' | Estudar em Portugal',
        'descricao_meta' => "Comparação honesta entre {$a} e {$b} para brasileiros: propinas, custo de vida, visto, equivalências e diploma. Tudo num só artigo.",
        'keywords'       => mb_strtolower("comparar {$a} {$b}, {$a} vs {$b}, estudar {$b} sendo brasileiro, ENEM {$b}, propinas {$b}, visto {$b}", 'UTF-8'),
        'slug'           => enp_slug('comparar-' . $a . '-' . $b),
        'descricao_longa' => '<p class="lead">Este artigo foi gerado em modo <strong>DRY-RUN</strong> para validar o pipeline end-to-end (texto + imagem + ficheiro + tracker + sitemap) sem gastar créditos LLM. Se esta página aparecer em <code>blog/' . enp_slug('comparar-' . $a . '-' . $b) . '.php</code>, o gerador está operacional.</p>'
            . '<h2>Contexto da comparação ' . $a . ' &times; ' . $b . '</h2>'
            . '<p>Em ' . $a . ', o sistema de ensino superior europeu X. Em ' . $b . ', sistema de ensino superior europeu Y. Ambos reconhecem créditos ECTS e têm equivalências automáticas.</p>'
            . '<h2>Tabela comparativa (referência rápida)</h2>'
            . '<table class="compare-table"><thead><tr><th>Critério</th><th>' . $a . '</th><th>' . $b . '</th></tr></thead><tbody>'
            . '<tr><th>Propinas anuais</th><td>~1.500€</td><td>~10.000€</td></tr>'
            . '<tr><th>Custo de vida mensal</th><td>~800€</td><td>~1.400€</td></tr>'
            . '<tr><th>Idioma</th><td>Português</td><td>Inglês</td></tr>'
            . '<tr><th>ENEM aceite</th><td>Sim</td><td>Não</td></tr>'
            . '<tr><th>Diploma válido UE</th><td>Sim (ECTS)</td><td>Sim (ECTS)</td></tr>'
            . '</tbody></table>'
            . '<h2>Para quem é cada destino</h2>'
            . '<p>Se valorizas <strong>custo controlado</strong> e <strong>idioma em português</strong>, ' . $a . ' é ideal. Se procuras <strong>especialização técnica</strong> ou <strong>courses 100% em inglês</strong>, ' . $b . ' leva vantagem.</p>'
            . '<h2>Próximos passos</h2>'
            . '<p>Pedir a análise da StudyWing — formulário no fim desta página.</p>',
        'imagem_url'     => 'ogi-comparar.png',
    ];
}

function enp_openrouter_chat(string $prompt, ?string $apiKey, string $model): ?array {
    if (!$apiKey) return null;
    $payload = [
        'model'          => $model,
        'response_format'=> ['type' => 'json_object'],
        'messages'       => [['role' => 'system', 'content' => $prompt]],
        'temperature'    => 0.7,
    ];
    $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey],
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT        => 90,
    ]);
    $body = curl_exec($ch);
    if ($body === false) { curl_close($ch); return null; }
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200 || !$body) return null;
    $j = json_decode($body, true);
    if (!is_array($j) || empty($j['choices'][0]['message']['content'])) return null;
    $content = (string) $j['choices'][0]['message']['content'];
    $content = str_replace(['```json','```'], '', $content);
    $arr = json_decode($content, true);
    return is_array($arr) ? $arr : null;
}

function enp_build_prompt(string $titulo, string $a, string $b, string $cat, string $foco): string {
    $af = e($a); $bf = e($b); $cf = e($cat); $ff = e($foco); $tf = e($titulo);
    return "És um copywriter especializado em conteúdo académico para estudantes internacionais que comparam destinos na Europa. Escreves para o blog do programa 'Estudar em Portugal' (Ginásios Da Vinci em colaboração com a StudyWing) no site comparar.php.\n\nEscreve um artigo comparativo completo sobre o tema:\n\nTÍTULO-BASE: {$tf}\nDESTINO A:    {$af}\nDESTINO B:    {$bf}\nCATEGORIA:    {$cf}\nFOCO:         {$ff}\n\nREGRAS:\n- NÃO menciones lafora.pt, ginasiosdavinci.com, studywing.org, ou qualquer URL externa\n- NÃO escrevas texto fora do JSON\n- Português de Portugal, jornalístico, natural, não promocional\n- Mínimo 900 palavras\n\nEstrutura obrigatória:\n1. Introdução clara ao tema (relação {$af} vs {$bf}).\n2. <h2>O que é {$cat} e porque importa para quem estuda em {$af} ou {$bf}</h2> — resposta direta.\n3. <h2>Comparação direta: {$af} vs {$bf}</h2> — usar tabela HTML (<table class=\"compare-table\">) com 6-8 critérios-chave.\n4. <h2>Prós e contras de cada destino</h2> — listas com <ul><li>.\n5. <h2>Para que perfil serve {$af}?</h2>\n6. <h2>Para que perfil serve {$bf}?</h2>\n7. <h2>Custos e vistos — números reais</h2>.\n8. <h2>Perguntas frequentes sobre {$af} vs {$bf}</h2> — 4 a 5 perguntas com respostas curtas e diretas.\n9. <h2>Conclusão</h2> — decidir quando cada destino é melhor.\n\nSEO+AEO+GEO:\n- Frases claras e respondem a perguntas directas (AEO).\n- Incluir palavras-chave naturais: \"comparar {$af} e {$bf}\", \"{$af} vs {$bf}\", \"estudar {$bf} sendo brasileiro\", \"propinas {$bf}\".\n- FAQ section em perguntas que começam com palavras interrogativas reais.\n\nFormato obrigatório JSON:\n{\n  \"tema\": \"tema principal\",\n  \"titulo\": \"título SEO final (≤ 60 caracteres)\",\n  \"h1_html\": \"h1 em HTML (≤ 70 caracteres)\",\n  \"descricao_meta\": \"meta description (≤ 155 caracteres)\",\n  \"keywords\": \"lista de palavras-chave separadas por vírgula\",\n  \"imagem_url\": \"ogi-comparar.png\",\n  \"descricao_longa\": \"artigo completo em HTML (com <h2>, <h3>, <ul>, <table>, <p>)\",\n  \"slug\": \"url-amigavel-apenas-com-hifens\"\n}\n";
}

function enp_run_pipeline(bool $dryrun = false): array {
    $d = db(); if (!$d) return ['ok' => false, 'msg' => 'BD indisponível'];

    $res = $d->query("SELECT * FROM comparar_topicos ORDER BY count ASC, ultimo_uso ASC LIMIT 1");
    if (!$res || $res->num_rows === 0) return ['ok' => false, 'msg' => 'Fila vazia'];
    $topico = $res->fetch_assoc();

    $a = (string) $topico['destino_a'];
    $b = (string) $topico['destino_b'];
    $useDryrun = $dryrun || $enpApiKey === '';

    $artigo = null;
    if (!$useDryrun) {
        $prompt = enp_build_prompt(
            (string) $topico['titulo'], $a, $b,
            (string) $topico['categoria'], (string) $topico['foco']
        );
        $artigo = enp_openrouter_chat($prompt, $enpApiKey, $enpModel);
    }
    if (!$artigo) {
        $artigo = enp_artigo_template((string) $topico['titulo'], $a, $b);
    }

    if (comparar_link_by_slug((string) $artigo['slug'])) {
        $d->query("UPDATE comparar_topicos SET count=count+1, ultimo_uso=NOW() WHERE id=" . (int) $topico['id']);
        return ['ok' => true, 'msg' => 'Slug já existia (' . $artigo['slug'] . ') — tópico contado, próximo.'];
    }

    comparar_link_insert([
        'slug'           => (string) $artigo['slug'],
        'titulo'         => (string) $artigo['titulo'],
        'h1_html'        => (string) ($artigo['h1_html']        ?? $artigo['titulo']),
        'destino_a'      => $a,
        'destino_b'      => $b,
        'categoria'      => (string) $topico['categoria'],
        'descricao_meta' => (string) $artigo['descricao_meta'],
        'imagem_url'     => (string) ($artigo['imagem_url']     ?? 'ogi-comparar.png'),
        'status'         => 'publicado',
        'data_publicacao'=> date('Y-m-d H:i:s'),
    ]);

    $dir = __DIR__ . '/blog';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $conteudo = enp_render_blog_php($artigo, $topico);
    if ($conteudo !== '') {
        @file_put_contents($dir . '/' . $artigo['slug'] . '.php', $conteudo);
    }

    $d->query("UPDATE comparar_topicos SET count=count+1, ultimo_uso=NOW() WHERE id=" . (int) $topico['id']);
    return ['ok' => true, 'msg' => 'Artigo gerado: ' . $artigo['slug'], 'slug' => (string) $artigo['slug']];
}

// ============================================================
// CLI vs Web dispatch
// ============================================================
$enpModo = $_GET['acao'] ?? (php_sapi_name() === 'cli' ? 'cli' : 'html');
if ($enpModo === 'cli') {
    $res = enp_run_pipeline((bool)($_GET['dryrun'] ?? 0));
    echo ($res['msg'] ?? '—') . "\n";
    exit(0);
}

$enpToken    = (string) ($_GET['key'] ?? $_POST['key'] ?? '');
$authOk      = VIEWS_STATS_TOKEN !== '' && hash_equals(VIEWS_STATS_TOKEN, $enpToken);
$cronTrigger = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $authOk;

$pageTitle       = 'Gerador Artigos Comparar — Admin | Estudar em Portugal';
$pageDescription = 'Painel admin do gerador_artigo_comparar.php';
$activeNav       = '';
$noindex         = true;
require_once __DIR__ . '/includes/header.php';
?>
<main id="conteudo" class="content-block">
    <h1 style="font-size:30px;color:#0a1628;margin:24px 0 14px;">Gerador de artigos comparativos</h1>
    <p style="color:#55607a;line-height:1.65;">
        Pipelines adaptados de <code>EstudarNoEstrangeiro/gerador_artigo_idiomas.php</code>.
        Carrega <code>OPENROUTER_API_KEY</code> no <code>.env</code> para usar LLM real.
        Sem chave, cai automaticamente em modo <strong>DRY-RUN</strong> (template).
    </p>

    <?php if (!$authOk): ?>
        <form method="get" style="background:#f5f7fa;border-radius:14px;padding:22px;margin-top:18px;">
            <label style="display:block;font-weight:600;font-size:14px;margin-bottom:8px;">Token admin (VIEWS_STATS_TOKEN)</label>
            <input name="key" type="text" required style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid #dde2ea;font-size:14px;">
            <button type="submit" class="btn-pill btn-navy" style="margin-top:12px;">Entrar</button>
        </form>
    <?php elseif ($cronTrigger): ?>
        <?php
        $r = enp_run_pipeline(false);
        echo '<div style="background:#0a1628;color:#fff;padding:14px 18px;border-radius:10px;margin:18px 0;font-weight:600;">'
           . htmlspecialchars($r['msg'] ?? '—') . '</div>';
        ?>
    <?php endif; ?>

    <h2 style="font-size:20px;color:#0a1628;margin:32px 0 12px;">Tópicos da fila</h2>
    <table class="compare-table" style="background:#fff;">
        <thead><tr><th>#</th><th>Título-base</th><th>A × B</th><th>Categoria</th><th>Foco</th><th>count</th></tr></thead>
        <tbody>
        <?php
        $d = db();
        if ($d) {
            $r = $d->query("SELECT id, titulo, destino_a, destino_b, categoria, foco, count FROM comparar_topicos ORDER BY count ASC, id ASC LIMIT 30");
            while ($row = $r->fetch_assoc()) {
                echo '<tr><td>' . (int)$row['id'] . '</td><td>' . e($row['titulo']) . '</td>'
                   . '<td>' . e($row['destino_a']) . ' × ' . e($row['destino_b']) . '</td>'
                   . '<td>' . e($row['categoria']) . '</td>'
                   . '<td>' . e($row['foco']) . '</td>'
                   . '<td>' . (int)$row['count'] . '</td></tr>';
            }
        }
        ?>
        </tbody>
    </table>

    <?php if ($authOk): ?>
    <form method="post" style="margin-top:18px;">
        <input type="hidden" name="key" value="<?= e($enpToken) ?>">
        <button type="submit" class="btn-pill btn-teal">▶ Gerar próximo artigo agora</button>
    </form>
    <?php endif; ?>

    <h2 style="font-size:20px;color:#0a1628;margin:32px 0 12px;">Artigos publicados</h2>
    <table class="compare-table" style="background:#fff;">
        <thead><tr><th>Slug</th><th>Título</th><th>Países</th><th>Cat</th><th>Views</th><th>Publicado</th></tr></thead>
        <tbody>
        <?php
        $d = db();
        if ($d) {
            $r = $d->query("SELECT slug, titulo, destino_a, destino_b, categoria, contador_views, data_publicacao FROM comparar_artigos WHERE status='publicado' ORDER BY data_publicacao DESC LIMIT 30");
            if ($r && $r->num_rows) {
                while ($row = $r->fetch_assoc()) {
                    echo '<tr><td><a href="blog/' . e($row['slug']) . '.php" target="_blank" style="color:#0f8ba6;">' . e($row['slug']) . '</a></td>'
                       . '<td>' . e($row['titulo']) . '</td>'
                       . '<td>' . e($row['destino_a']) . ' × ' . e($row['destino_b']) . '</td>'
                       . '<td>' . e($row['categoria']) . '</td>'
                       . '<td>' . (int)$row['contador_views'] . '</td>'
                       . '<td>' . e((string)$row['data_publicacao']) . '</td></tr>';
                }
            } else {
                echo '<tr><td colspan="6" style="color:#8892a6;padding:24px;text-align:center;">Sem artigos publicados ainda — clique em "Gerar próximo artigo" acima.</td></tr>';
            }
        }
        ?>
        </tbody>
    </table>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
