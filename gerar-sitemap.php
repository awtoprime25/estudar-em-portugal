<?php
/**
 * gerar-sitemap.php — Regenera sitemap.xml (páginas estáticas + blog_posts).
 *
 * Uso:
 *   - Incluído por cron-gerar-blog.php após cada post gerado (define
 *     ENP_SITEMAP_INCLUDE antes do require, para não correr a auth abaixo).
 *   - Standalone via web: gerar-sitemap.php?key=<VIEWS_STATS_TOKEN>
 */

if (!defined('ENP_SITEMAP_INCLUDE')) {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/includes/blog-db.php';

    $token = (string) ($_GET['key'] ?? '');
    if (VIEWS_STATS_TOKEN === '' || !hash_equals(VIEWS_STATS_TOKEN, $token)) {
        header('HTTP/1.0 401 Unauthorized');
        header('Content-Type: text/plain; charset=utf-8');
        echo "401 Unauthorized\nUso: gerar-sitemap.php?key=<VIEWS_STATS_TOKEN>\n";
        exit;
    }
} else {
    require_once __DIR__ . '/includes/blog-db.php';
}
require_once __DIR__ . '/includes/subpage-data.php';

if (!function_exists('enp_gerar_sitemap')) {
    function enp_gerar_sitemap(): int {
        $staticPages = [
            ['loc' => '', 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => 'sobre.php', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => 'universidades.php', 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['loc' => 'cursos.php', 'priority' => '0.85', 'changefreq' => 'monthly'],
            ['loc' => 'visto-de-estudante.php', 'priority' => '0.85', 'changefreq' => 'monthly'],
            ['loc' => 'faq.php', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => 'comparar.php', 'priority' => '0.95', 'changefreq' => 'weekly'],
            ['loc' => 'explicacoes.php', 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['loc' => 'concurso-especial-estudantes-internacionais.php', 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['loc' => 'blog.php', 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => 'contato.php', 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => 'termos.php', 'priority' => '0.2', 'changefreq' => ''],
            ['loc' => 'privacidade.php', 'priority' => '0.2', 'changefreq' => ''],
        ];
        foreach (array_keys(DESTINOS) as $slug) {
            $staticPages[] = ['loc' => 'destino-' . $slug . '.php', 'priority' => '0.85', 'changefreq' => 'monthly'];
        }
        foreach (array_keys(CURSOS) as $slug) {
            $staticPages[] = ['loc' => 'curso-' . $slug . '.php', 'priority' => '0.85', 'changefreq' => 'monthly'];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
             . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $today = date('Y-m-d');
        foreach ($staticPages as $p) {
            $xml .= "  <url>\n"
                  . '    <loc>' . htmlspecialchars(SITE_URL . $p['loc'], ENT_QUOTES, 'UTF-8') . "</loc>\n"
                  . "    <lastmod>{$today}</lastmod>\n"
                  . ($p['changefreq'] !== '' ? "    <changefreq>{$p['changefreq']}</changefreq>\n" : '')
                  . "    <priority>{$p['priority']}</priority>\n"
                  . "  </url>\n";
        }

        $d = db();
        $count = count($staticPages);
        if ($d) {
            $res = $d->query("SELECT slug, published_at, updated_at FROM blog_posts WHERE status='published' ORDER BY published_at DESC");
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $lastmod = $row['updated_at'] ?: $row['published_at'] ?: $today;
                    $lastmod = date('Y-m-d', strtotime((string) $lastmod));
                    $xml .= "  <url>\n"
                          . '    <loc>' . htmlspecialchars(SITE_URL . 'artigo.php?slug=' . $row['slug'], ENT_QUOTES, 'UTF-8') . "</loc>\n"
                          . "    <lastmod>{$lastmod}</lastmod>\n"
                          . "    <changefreq>monthly</changefreq>\n"
                          . "    <priority>0.7</priority>\n"
                          . "  </url>\n";
                    $count++;
                }
            }
        }

        $xml .= '</urlset>' . "\n";
        @file_put_contents(__DIR__ . '/sitemap.xml', $xml);
        return $count;
    }
}

if (!defined('ENP_SITEMAP_INCLUDE')) {
    $n = enp_gerar_sitemap();
    header('Content-Type: text/plain; charset=utf-8');
    echo "OK: sitemap.xml regenerado com {$n} URLs.\n";
}
