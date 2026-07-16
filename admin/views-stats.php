<?php
/**
 * admin/views-stats.php — Dashboard rico de auditoria de views.
 *
 * Re-aplicação de EstudarNoEstrangeiro/final/admin/views-stats.php adaptado
 * ao schema unificado `blog_views` + `blog_view_hits` do nosso db-helper.
 *
 * Apresenta:
 *   - KPIs do SITE INTEIRO (slug sentinel 'global-site-visit')
 *   - KPIs do BLOG/ARTIGOS (qualquer outro slug)
 *   - Top países (com bandeiras)
 *   - Top artigos por visitas
 *   - Timeline diária (humano · bot)
 *   - Detalhe paginado (IP truncado a 12 chars — RGPD)
 *
 * Filtro `?slug=` opcional: restringe detalhe / top artigos a um slug específico
 * (útil para auditar uma página concreta depois de uma campanha).
 *
 * Auth: constante VIEWS_STATS_TOKEN no config.php (= mesmo token do views-stats
 * da raiz). URL: admin/views-stats.php?key=<TOKEN>&days=<N>
 * Sem token ⇒ 401 (comparação em tempo constante).
 *
 * Read-only. NUNCA regista hit em blog_view_hits.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db-helper.php';

// ── AUTH ─────────────────────────────────────────────────────────────────────
$token = (string) ($_GET['key'] ?? '');
if (VIEWS_STATS_TOKEN === '' || !hash_equals(VIEWS_STATS_TOKEN, $token)) {
    header('HTTP/1.0 401 Unauthorized');
    header('Content-Type: text/plain; charset=utf-8');
    echo "401 Unauthorized\n\nEste painel requer token VIEWS_STATS_TOKEN.\n";
    echo "Uso: " . htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? '/admin/views-stats.php') . "?key=<TOKEN>\n";
    exit;
}

$days    = max(1, min(90, (int) ($_GET['days'] ?? 30)));
$page    = max(0, (int) ($_GET['page']  ?? 0));
$slugFilter = trim((string) ($_GET['slug'] ?? ''));
$slugFilter = preg_replace('/[^a-z0-9\-\_\/]/', '', strtolower($slugFilter));
$perPage = 50;
$offset  = $page * $perPage;

$minDay = date('Y-m-d', strtotime("-{$days} days"));

$d = db();
$bdOk = $d !== null;
if (!$bdOk) {
    header('HTTP/1.0 503 Service Unavailable');
    $hint = sprintf('DB_HOST=%s DB_NAME=%s no .env.', DB_HOST, DB_NAME);
    echo "<!doctype html><meta charset='utf-8'><title>BD indisponível</title>"
       . "<div style='font-family:Arial,sans-serif;max-width:640px;margin:60px auto;padding:24px;border-left:4px solid #dc2626;background:#fef2f2;'>"
       . "<h1 style='margin-top:0;'>Não foi possível ligar à base de dados.</h1>"
       . "<p>" . htmlspecialchars($hint) . "</p>"
       . "<p>Este painel é read-only.</p></div>";
    exit;
}

$SITE_SLUG = 'global-site-visit';

$countryNames = [
    'PT' => 'Portugal', 'BR' => 'Brasil', 'FR' => 'França', 'ES' => 'Espanha',
    'GB' => 'Reino Unido', 'DE' => 'Alemanha', 'IT' => 'Itália', 'NL' => 'Países Baixos',
    'IE' => 'Irlanda', 'US' => 'EUA', 'CA' => 'Canadá', 'AO' => 'Angola',
    'MZ' => 'Moçambique', 'CV' => 'Cabo Verde', 'CH' => 'Suíça', 'BE' => 'Bélgica',
    'PL' => 'Polónia', 'CZ' => 'Chéquia', 'SE' => 'Suécia', 'NO' => 'Noruega',
    'DK' => 'Dinamarca', 'FI' => 'Finlândia', 'AT' => 'Áustria', 'LU' => 'Luxemburgo',
    'AU' => 'Austrália', 'NZ' => 'Nova Zelândia', 'JP' => 'Japão', 'CN' => 'China',
    'IN' => 'Índia', 'MX' => 'México', 'AR' => 'Argentina', 'CL' => 'Chile',
    'CO' => 'Colômbia', 'PE' => 'Peru', 'VE' => 'Venezuela', 'MA' => 'Marrocos',
    'XX' => 'Desconhecido', 'T1' => 'Tor (anónimo)',
];
function eplFlag(string $cc): string {
    if (!preg_match('/^[A-Z]{2}$/', $cc) || $cc === 'XX' || $cc === 'T1') return '🏳️';
    $b1 = 0x1F1E6 - ord('A') + ord($cc[0]);
    $b2 = 0x1F1E6 - ord('A') + ord($cc[1]);
    if ($b1 < 0x1F1E6 || $b2 < 0x1F1E6) return '🏳️';
    return mb_chr($b1, 'UTF-8') . mb_chr($b2, 'UTF-8');
}
function eplPct(int $a, int $b): string {
    $t = $a + $b;
    return $t > 0 ? sprintf('%.1f%%', ($a / $t) * 100) : '—';
}

// ── QUERIES ─────────────────────────────────────────────────────────────────
// Helper: WHERE clause para excluir sentinel
$whereExclSite = "slug <> '" . $d->real_escape_string($SITE_SLUG) . "'";

// 1) Site inteiro (sentinel)
$siteSummary = ['h' => 0, 'b' => 0, 'u' => 0];
if ($stmt = $d->prepare(
    "SELECT
        SUM(CASE WHEN is_bot=0 THEN 1 ELSE 0 END) AS humans,
        SUM(CASE WHEN is_bot=1 THEN 1 ELSE 0 END) AS bots,
        COUNT(DISTINCT CASE WHEN is_bot=0 THEN ip_hash END) AS uniq
     FROM blog_view_hits WHERE day >= ? AND slug = ?"
)) {
    $stmt->bind_param('ss', $minDay, $SITE_SLUG);
    $stmt->execute();
    if ($r = $stmt->get_result()->fetch_assoc()) {
        $siteSummary['h'] = (int) ($r['humans'] ?? 0);
        $siteSummary['b'] = (int) ($r['bots']   ?? 0);
        $siteSummary['u'] = (int) ($r['uniq']   ?? 0);
    }
    $stmt->close();
}

// 1b) Blog (excluindo sentinel) — ou single-slug se filtro
$blogSummary = ['h' => 0, 'b' => 0, 'u' => 0];
$blogWhere = $slugFilter !== ''
    ? "day >= ? AND slug = ?"
    : "day >= ? AND $whereExclSite";
if ($stmt = $d->prepare(
    "SELECT
        SUM(CASE WHEN is_bot=0 THEN 1 ELSE 0 END) AS humans,
        SUM(CASE WHEN is_bot=1 THEN 1 ELSE 0 END) AS bots,
        COUNT(DISTINCT CASE WHEN is_bot=0 THEN ip_hash END) AS uniq
     FROM blog_view_hits WHERE $blogWhere"
)) {
    if ($slugFilter !== '') $stmt->bind_param('ss', $minDay, $slugFilter);
    else                    $stmt->bind_param('s', $minDay);
    $stmt->execute();
    if ($r = $stmt->get_result()->fetch_assoc()) {
        $blogSummary['h'] = (int) ($r['humans'] ?? 0);
        $blogSummary['b'] = (int) ($r['bots']   ?? 0);
        $blogSummary['u'] = (int) ($r['uniq']   ?? 0);
    }
    $stmt->close();
}

// 2) Top países (humans, ex-cluindo sentinel)
$topCountries = [];
if ($stmt = $d->prepare(
    "SELECT country, COUNT(*) AS hits, COUNT(DISTINCT ip_hash) AS uniq
     FROM blog_view_hits
     WHERE day >= ? AND is_bot = 0 AND $whereExclSite" . ($slugFilter !== '' ? " AND slug = ?" : '') . "
     GROUP BY country ORDER BY hits DESC LIMIT 12"
)) {
    if ($slugFilter !== '') $stmt->bind_param('ss', $minDay, $slugFilter);
    else                    $stmt->bind_param('s', $minDay);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $topCountries[] = $r;
    $stmt->close();
}

// 3) Top artigos (ex-cluindo sentinel)
$topArticles = [];
if (function_exists('comparar_top')) $topArticles = comparar_top(15);
if (!$topArticles) {
    // Fallback ao estudar_no_estrangeiro style — query directa sobre blog_view_hits
    if ($stmt = $d->prepare(
        "SELECT slug, COUNT(*) AS hits, COUNT(DISTINCT ip_hash) AS uniq
         FROM blog_view_hits
         WHERE day >= ? AND is_bot = 0 AND $whereExclSite
         GROUP BY slug ORDER BY hits DESC LIMIT 15"
    )) {
        $stmt->bind_param('s', $minDay);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) $topArticles[] = $r;
        $stmt->close();
    }
}

// 4) Timeline diária (blog, excluindo sentinel)
$timeline = [];
if ($stmt = $d->prepare(
    "SELECT day,
        SUM(CASE WHEN is_bot=0 THEN 1 ELSE 0 END) AS humans,
        SUM(CASE WHEN is_bot=1 THEN 1 ELSE 0 END) AS bots
     FROM blog_view_hits WHERE day >= ? AND $whereExclSite" . ($slugFilter !== '' ? ' AND slug = ?' : '') . "
     GROUP BY day ORDER BY day ASC"
)) {
    if ($slugFilter !== '') $stmt->bind_param('ss', $minDay, $slugFilter);
    else                    $stmt->bind_param('s', $minDay);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $timeline[$r['day']] = $r;
    $stmt->close();
}
$cursor = strtotime($minDay);
$end    = strtotime(date('Y-m-d'));
$timelineFull = [];
while ($cursor <= $end) {
    $day = date('Y-m-d', $cursor);
    $timelineFull[$day] = [
        'day'    => $day,
        'humans' => (int) ($timeline[$day]['humans'] ?? 0),
        'bots'   => (int) ($timeline[$day]['bots']   ?? 0),
    ];
    $cursor = strtotime('+1 day', $cursor);
}

// 5) Detalhe paginado (12 chars do hash ID para audit id)
$detail = [];
$totalRows = 0;
$detailWhere = "day >= ?" . ($slugFilter !== '' ? ' AND slug = ?' : '');
if ($stmt = $d->prepare("SELECT COUNT(*) FROM blog_view_hits WHERE $detailWhere")) {
    if ($slugFilter !== '') $stmt->bind_param('ss', $minDay, $slugFilter);
    else                    $stmt->bind_param('s', $minDay);
    $stmt->execute();
    $totalRows = (int) ((($stmt->get_result()->fetch_row() ?? [])[0]) ?? 0);
    $stmt->close();
}
if ($stmt = $d->prepare(
    "SELECT id, day, slug, country, is_bot, SUBSTRING(ip_hash, 1, 12) AS ip_id
     FROM blog_view_hits WHERE $detailWhere ORDER BY id DESC LIMIT ? OFFSET ?"
)) {
    if ($slugFilter !== '') $stmt->bind_param('ssii', $minDay, $slugFilter, $perPage, $offset);
    else                    $stmt->bind_param('sii', $minDay, $perPage, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $detail[] = $r;
    $stmt->close();
}

$now = date('Y-m-d H:i:s');
$retentionDays = (int) LF_VIEWS_RETENTION_DAYS;

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Referrer-Policy: same-origin');
?><!doctype html>
<html lang="pt-PT">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Auditoria Views — Estudar em Portugal</title>
<style>
:root{
  --bg:#0a1628; --bg-card:#11233f; --accent:#1ab8c8; --text:#e6edf3; --muted:#8aa0b8;
  --good:#22c55e; --warn:#f59e0b; --bad:#ef4444; --border:rgba(255,255,255,.08);
}
*{box-sizing:border-box}
body{margin:0;font:14px/1.55 -apple-system,BlinkMacSystemFont,"Inter",Segoe UI,Arial,sans-serif;background:var(--bg);color:var(--text);padding:2rem}
.wrap{max-width:1200px;margin:0 auto}
.header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem}
h1{font-size:1.4rem;font-weight:800;margin:0}
h1 span{color:var(--accent)}
.subtitle{color:var(--muted);font-size:.82rem;margin-top:.2rem}
.toolbar{display:flex;gap:.4rem;flex-wrap:wrap}
.toolbar a,.toolbar input,.toolbar button{padding:.4rem .8rem;border-radius:8px;background:var(--bg-card);color:var(--text);text-decoration:none;font-size:.8rem;border:1px solid var(--border);font-family:inherit}
.toolbar a.active,.toolbar button.active{background:var(--accent);color:#0a1628;border-color:transparent;font-weight:600}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:1rem;margin-bottom:1.5rem}
.card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:1.1rem}
.card .lbl{font-size:.72rem;text-transform:uppercase;letter-spacing:.5px;color:var(--muted)}
.card .val{font-size:1.7rem;font-weight:800;margin-top:.25rem}
.card .sub{font-size:.75rem;color:var(--muted);margin-top:.25rem}
.grid{display:grid;grid-template-columns:1.2fr 1fr;gap:1.2rem;margin-bottom:1.5rem}
@media(max-width:900px){.grid{grid-template-columns:1fr}}
.panel{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;overflow:hidden}
.panel h2{margin:0;padding:1rem 1.2rem;font-size:.95rem;border-bottom:1px solid var(--border);background:rgba(0,0,0,.15);font-weight:700}
table{width:100%;border-collapse:collapse;font-size:.84rem}
th,td{padding:.6rem .9rem;text-align:left;border-bottom:1px solid var(--border)}
th{font-weight:600;color:var(--muted);background:rgba(0,0,0,.18)}
tr:last-child td{border-bottom:none}
td.num{text-align:right;font-variant-numeric:tabular-nums}
.bar{height:6px;background:rgba(255,255,255,.07);border-radius:3px;overflow:hidden;margin-top:.25rem}
.bar>i{display:block;height:100%;background:var(--accent)}
.timeline{display:flex;align-items:flex-end;gap:2px;height:120px;padding:1rem 1.2rem}
.bar-col{flex:1;display:flex;flex-direction:column-reverse;align-items:stretch;gap:1px;min-width:0}
.bar-h,.bar-b{border-radius:2px 2px 0 0;width:100%}
.bar-h{background:var(--accent)}
.bar-b{background:var(--warn);opacity:.85}
.tllabel{text-align:center;color:var(--muted);font-size:.65rem;padding:.5rem .4rem .8rem;border-top:1px solid var(--border)}
.detail-table{font-size:.78rem}
.badge{display:inline-block;padding:.15rem .5rem;border-radius:99px;font-size:.7rem;font-weight:600}
.badge.bot{background:rgba(245,158,11,.15);color:var(--warn);border:1px solid rgba(245,158,11,.3)}
.badge.human{background:rgba(34,197,94,.15);color:var(--good);border:1px solid rgba(34,197,94,.3)}
.footer{color:var(--muted);font-size:.75rem;margin-top:2rem;text-align:center}
.muted{color:var(--muted)}
.flag{font-size:1.1rem;margin-right:.4rem}
.filter{margin-bottom:1rem}
.filter input{padding:.4rem .8rem;border-radius:6px;border:1px solid var(--border);background:var(--bg-card);color:var(--text);font-size:.85rem;min-width:240px}
.legacy{color:var(--muted);font-size:.74rem;margin-top:.5rem}
.legacy code{color:var(--accent)}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div>
      <h1>Auditoria de Views <span>·</span> Estudar em Portugal</h1>
      <div class="subtitle">Retenção <?= $retentionDays ?> dias · refreshed <?= htmlspecialchars($now) ?> <?= $slugFilter !== '' ? '· slug=' . htmlspecialchars($slugFilter) : '' ?></div>
    </div>
    <div class="toolbar">
      <a href="?key=<?= urlencode($token) ?>&days=7&slug=<?= urlencode($slugFilter) ?>"  class="<?= $days===7?'active':'' ?>">7 d</a>
      <a href="?key=<?= urlencode($token) ?>&days=14&slug=<?= urlencode($slugFilter) ?>" class="<?= $days===14?'active':'' ?>">14 d</a>
      <a href="?key=<?= urlencode($token) ?>&days=30&slug=<?= urlencode($slugFilter) ?>" class="<?= $days===30?'active':'' ?>">30 d</a>
      <a href="?key=<?= urlencode($token) ?>&days=60&slug=<?= urlencode($slugFilter) ?>" class="<?= $days===60?'active':'' ?>">60 d</a>
      <a href="?key=<?= urlencode($token) ?>&days=90&slug=<?= urlencode($slugFilter) ?>" class="<?= $days===90?'active':'' ?>">90 d</a>
    </div>
  </div>

  <form class="filter" method="get">
    <input type="hidden" name="key" value="<?= htmlspecialchars($token) ?>">
    <input type="hidden" name="days" value="<?= $days ?>">
    <span class="toolbar" style="display:inline-flex;align-items:center;gap:.4rem;">
      <label style="color:var(--muted);font-size:.8rem;font-weight:600;">Slug:</label>
      <input type="text" name="slug" value="<?= htmlspecialchars($slugFilter) ?>" placeholder="ex: comparar-portugal-holanda">
      <button type="submit" class="active">Filtrar</button>
      <?php if ($slugFilter !== ''): ?>
        <a href="?key=<?= urlencode($token) ?>&days=<?= $days ?>" style="background:transparent;border:1px solid var(--border);">Limpar</a>
      <?php endif; ?>
    </span>
  </form>

  <h2 style="font-size:.95rem;margin:0 0 .6rem;color:var(--muted);">🌐 Site inteiro (sentinela global-site-visit)</h2>
  <div class="cards">
    <div class="card"><div class="lbl">Visitas humanas</div><div class="val"><?= number_format($siteSummary['h'], 0, ',', '.') ?></div><div class="sub">Últimos <?= $days ?> d · 1x/pessoa/dia</div></div>
    <div class="card"><div class="lbl">IPs únicos humanos</div><div class="val"><?= number_format($siteSummary['u'], 0, ',', '.') ?></div><div class="sub">HMAC-SHA256</div></div>
    <div class="card"><div class="lbl">Bots detetados</div><div class="val"><?= number_format($siteSummary['b'], 0, ',', '.') ?></div><div class="sub">GPTBot, ClaudeBot, Ahrefs…</div></div>
    <div class="card"><div class="lbl">Genuinidade</div><div class="val" style="color:<?= $siteSummary['h']>=$siteSummary['b']?'var(--good)':'var(--warn)' ?>"><?= eplPct($siteSummary['h'], $siteSummary['b']) ?></div><div class="sub">humanos vs total</div></div>
  </div>

  <h2 style="font-size:.95rem;margin:1.5rem 0 .6rem;color:var(--muted);">
    📰 <?= $slugFilter !== '' ? 'Slug: ' . htmlspecialchars($slugFilter) : 'Blog / Comparações' ?>
  </h2>
  <div class="cards">
    <div class="card"><div class="lbl">Visitas humanas</div><div class="val"><?= number_format($blogSummary['h'], 0, ',', '.') ?></div><div class="sub">excluindo sentinela</div></div>
    <div class="card"><div class="lbl">IPs únicos humanos</div><div class="val"><?= number_format($blogSummary['u'], 0, ',', '.') ?></div><div class="sub">deduplicados por dia</div></div>
    <div class="card"><div class="lbl">Bots detetados</div><div class="val"><?= number_format($blogSummary['b'], 0, ',', '.') ?></div><div class="sub">incl. AI crawlers</div></div>
    <div class="card"><div class="lbl">Genuinidade</div><div class="val" style="color:<?= $blogSummary['h']>=$blogSummary['b']?'var(--good)':'var(--warn)' ?>"><?= eplPct($blogSummary['h'], $blogSummary['b']) ?></div><div class="sub">humanos vs total</div></div>
  </div>

  <div class="grid">
    <div class="panel">
      <h2>🌍 Top países · visitas humanas</h2>
      <?php if (!$topCountries): ?>
        <div style="padding:1.4rem;color:var(--muted);">Sem dados para este período.</div>
      <?php else:
        $maxHits = max(1, (int) $topCountries[0]['hits']); ?>
        <table>
          <thead><tr><th>País</th><th class="num">Visitas</th><th class="num">IPs únicos</th><th class="num">Total%</th><th style="width:30%"></th></tr></thead>
          <tbody>
            <?php foreach ($topCountries as $r):
              $cc = (string) $r['country'];
              $name = $countryNames[$cc] ?? $cc;
              $hits = (int) $r['hits'];
              $uniq = (int) $r['uniq'];
              $w = round(($hits / $maxHits) * 100, 1); ?>
            <tr>
              <td><span class="flag"><?= htmlspecialchars(eplFlag($cc)) ?></span><?= htmlspecialchars($name) ?> <span class="muted">(<?= htmlspecialchars($cc) ?>)</span></td>
              <td class="num"><?= number_format($hits, 0, ',', '.') ?></td>
              <td class="num"><?= number_format($uniq, 0, ',', '.') ?></td>
              <td class="num muted"><?= $blogSummary['h'] > 0 ? round(($hits / $blogSummary['h']) * 100, 1) . '%' : '—' ?></td>
              <td><div class="bar"><i style="width:<?= $w ?>%"></i></div></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <div class="panel">
      <h2>📰 Top artigos / comparações</h2>
      <?php if (!$topArticles): ?>
        <div style="padding:1.4rem;color:var(--muted);">Sem dados — ou nenhum artigo gerado ainda pelo gerador_artigo_comparar.php.</div>
      <?php else:
        $maxHits = max(1, (int) ($topArticles[0]['hits'] ?? $topArticles[0]['contador_views'] ?? 1));
        foreach ($topArticles as $r):
          $slug = (string) ($r['slug'] ?? '');
          $hits = (int) ($r['hits'] ?? $r['contador_views'] ?? 0);
          if ($hits <= 0) continue;
          $w = round(($hits / $maxHits) * 100, 1); ?>
        <div style="padding:1rem 1.2rem;border-bottom:1px solid var(--border);">
          <div style="display:flex;justify-content:space-between;gap:.6rem;align-items:baseline;flex-wrap:wrap;">
            <a href="../blog/<?= htmlspecialchars($slug) ?>.php" target="_blank" rel="noopener"
               style="color:var(--accent);text-decoration:none;font-size:.9rem;font-weight:600;"><?= htmlspecialchars($slug) ?></a>
            <span class="num muted"><?= number_format($hits, 0, ',', '.') ?> visitas</span>
          </div>
          <div class="bar"><i style="width:<?= $w ?>%"></i></div>
          <?php if (!empty($r['titulo'])): ?>
            <div class="muted" style="font-size:.78rem;margin-top:.3rem;"><?= htmlspecialchars((string) $r['titulo']) ?></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="panel" style="margin-bottom:1.5rem">
    <h2>📈 Timeline diária · últimos <?= $days ?> dias</h2>
    <?php
      $maxDay = 1;
      foreach ($timelineFull as $d_ => $day) if (($day['humans'] + $day['bots']) > $maxDay) $maxDay = $day['humans'] + $day['bots'];
      $labelEvery = max(1, (int) ceil(count($timelineFull) / 14));
      $i = 0;
    ?>
    <div class="timeline">
      <?php foreach ($timelineFull as $day):
        $h = $day['humans']; $b = $day['bots'];
        $tot = $h + $b;
        $hPx = $tot > 0 ? ($h / $maxDay) * 100 : 0;
        $bPx = $tot > 0 ? ($b / $maxDay) * 100 : 0;
        $showLabel = ($i % $labelEvery) === 0; $i++;
        $dayLabel = $showLabel ? date('d/m', strtotime($day['day'])) : ''; ?>
      <div class="bar-col" title="<?= htmlspecialchars($day['day']) ?> · <?= $h ?> humanos · <?= $b ?> bots">
        <?php if ($h > 0): ?><div class="bar-h" style="height:<?= $hPx ?>%"></div><?php endif; ?>
        <?php if ($b > 0): ?><div class="bar-b" style="height:<?= $bPx ?>%"></div><?php endif; ?>
        <?php if ($showLabel): ?><div class="tllabel"><?= htmlspecialchars($dayLabel) ?></div><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="padding:.6rem 1.2rem .9rem;font-size:.78rem;color:var(--muted);display:flex;gap:1rem;flex-wrap:wrap;border-top:1px solid var(--border)">
      <span><span style="display:inline-block;width:10px;height:10px;background:var(--accent);border-radius:2px;margin-right:.3rem"></span> humano</span>
      <span><span style="display:inline-block;width:10px;height:10px;background:var(--warn);border-radius:2px;margin-right:.3rem"></span> bot</span>
      <span class="muted">Max dia: <?= $maxDay ?> visitas</span>
    </div>
  </div>

  <div class="panel" style="margin-bottom:1.5rem">
    <h2>🔍 Últimos hits (sem IP real — só hash truncado) · página <?= $page + 1 ?> / <?= max(1, (int) ceil($totalRows / $perPage)) ?></h2>
    <table class="detail-table">
      <thead><tr><th>Dia</th><th>Slug</th><th>País</th><th>Tipo</th><th>IP-id (12 chars)</th></tr></thead>
      <tbody>
        <?php if (!$detail): ?>
          <tr><td colspan="5" style="padding:1.2rem;color:var(--muted);text-align:center;">Sem registos neste período.</td></tr>
        <?php else: foreach ($detail as $r):
          $cc = (string) $r['country'];
          $name = $countryNames[$cc] ?? $cc; ?>
        <tr>
          <td><?= htmlspecialchars((string) $r['day']) ?></td>
          <td>
            <?php
              $rslug = (string) $r['slug'];
              if ($rslug === $SITE_SLUG): ?>
              <span class="muted"><?= htmlspecialchars($rslug) ?> (sentinel)</span>
            <?php else: ?>
              <a href="../blog/<?= htmlspecialchars($rslug) ?>.php" target="_blank" rel="noopener" style="color:var(--accent);text-decoration:none"><?= htmlspecialchars($rslug) ?></a>
            <?php endif; ?>
          </td>
          <td><span class="flag"><?= htmlspecialchars(eplFlag($cc)) ?></span><?= htmlspecialchars($name) ?> <span class="muted">(<?= htmlspecialchars($cc) ?>)</span></td>
          <td><span class="badge <?= ((int) $r['is_bot']) === 1 ? 'bot' : 'human' ?>"><?= ((int) $r['is_bot']) === 1 ? 'bot' : 'humano' ?></span></td>
          <td class="muted"><code style="font-size:.7rem"><?= htmlspecialchars((string) $r['ip_id']) ?>…</code></td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
    <div style="padding:.8rem 1.2rem;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;font-size:.78rem;flex-wrap:wrap;gap:.6rem">
      <span class="muted"><?= number_format($totalRows, 0, ',', '.') ?> hits no total</span>
      <div class="toolbar">
        <?php if ($page > 0): ?>
          <a href="?key=<?= urlencode($token) ?>&days=<?= $days ?>&slug=<?= urlencode($slugFilter) ?>&page=<?= $page - 1 ?>">← Anterior</a>
        <?php endif; ?>
        <?php if ((($page + 1) * $perPage) < $totalRows): ?>
          <a href="?key=<?= urlencode($token) ?>&days=<?= $days ?>&slug=<?= urlencode($slugFilter) ?>&page=<?= $page + 1 ?>">Próxima →</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="legacy">
    <p>Schema principal: <code>blog_view_hits</code> + <code>blog_views</code> + <code>ip_country_ranges</code> (auto-criadas em <code>includes/db-helper.php</code>, migração 1060/1061 silenciosa).</p>
    <p>Compatibilidade legacy: as tabelas <code>site_visits</code> / <code>ip_addresses</code> continuam a ser escritas por <code>site_visit_track()</code> (chamada em <code>includes/footer.php</code>) — usadas pelo dashboard antigo em <code>views-stats.php</code> da raiz. O dashboard em <code>admin/views-stats.php</code> é a fonte de verdade a partir de agora.</p>
  </div>

  <div class="footer">Painel interno · token via URL (constante-time) · retenção <?= $retentionDays ?> dias · apenas leitura.</div>
</div>
</body>
</html>
