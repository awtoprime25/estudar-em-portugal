<?php
/**
 * views-stats.php — Dashboard de auditoria de visitas (estudar-em-portugal).
 *
 * Mostra visitas humanas vs bots ao SITE INTEIRO (qualquer página), 1x por
 * pessoa/dia — ver site_visit_track() em includes/db-helper.php e a chamada
 * em includes/footer.php. Também mostra "Top artigos" (tabela `artigos`),
 * já pronta para quando o blog deixar de ser estático — enquanto não houver
 * artigos publicados, essa secção mostra um estado vazio, sem erro.
 *
 * Mesmo padrão de noticias-local/deploy/views-stats.php, adaptado à BD
 * própria do estudar-em-portugal (config.php → DB_NAME=ginasiosdavinci_estudaremportugal).
 *
 * Autenticação: token em VIEWS_STATS_TOKEN (.env ou default de dev em
 * config.php). URL: /views-stats.php?key=<TOKEN>&days=<N>
 * Sem token ou token errado ⇒ 401 (comparação em tempo constante).
 *
 * Read-only: não grava nada em site_visits.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db-helper.php';

// ── AUTH ─────────────────────────────────────────────────────────────────────
$token = (string) ($_GET['key'] ?? '');
if (VIEWS_STATS_TOKEN === '' || !hash_equals(VIEWS_STATS_TOKEN, $token)) {
    header('HTTP/1.0 401 Unauthorized');
    header('Content-Type: text/plain; charset=utf-8');
    echo "401 Unauthorized\n\nEste painel requer um token VIEWS_STATS_TOKEN.\n";
    echo "Uso: " . htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? '/views-stats.php') . "?key=<TOKEN>\n";
    exit;
}

$days   = max(1, min(90, (int) ($_GET['days'] ?? 30)));
$minDay = date('Y-m-d', strtotime("-{$days} days"));

$d = db();
if (!$d) {
    header('HTTP/1.0 503 Service Unavailable');
    echo "<!doctype html><meta charset='utf-8'><title>BD indisponível</title>"
       . "<div style='font-family:Arial,sans-serif;max-width:640px;margin:60px auto;padding:24px;border-left:4px solid #dc2626;background:#fef2f2;'>"
       . "<h1 style='margin-top:0;'>Não foi possível ligar à base de dados.</h1>"
       . "<p>Este painel é read-only. Confirma DB_HOST/DB_NAME/DB_USER/DB_PASS "
       . "(estudar-em-portugal/.env, ou os defaults de dev local em config.php).</p>"
       . "<p style='color:#555;'>DB_HOST=" . htmlspecialchars(DB_HOST) . " · DB_NAME=" . htmlspecialchars(DB_NAME) . " · DB_USER=" . htmlspecialchars(DB_USER) . "</p>"
       . "</div>";
    exit;
}

// 1) Resumo agregado (humanos vs bots)
$summary = ['h' => 0, 'b' => 0, 'u' => 0];
if ($stmt = $d->prepare(
    "SELECT
        SUM(CASE WHEN is_bot = 0 THEN 1 ELSE 0 END) AS humans,
        SUM(CASE WHEN is_bot = 1 THEN 1 ELSE 0 END) AS bots,
        COUNT(DISTINCT CASE WHEN is_bot = 0 THEN ip_hash END) AS uniq_ips
     FROM site_visits WHERE dia >= ?"
)) {
    $stmt->bind_param('s', $minDay);
    $stmt->execute();
    if ($r = $stmt->get_result()->fetch_assoc()) {
        $summary['h'] = (int) ($r['humans']   ?? 0);
        $summary['b'] = (int) ($r['bots']     ?? 0);
        $summary['u'] = (int) ($r['uniq_ips'] ?? 0);
    }
    $stmt->close();
}

// 2) Top países (só humanos)
$topCountries = [];
if ($stmt = $d->prepare(
    "SELECT pais, COUNT(*) AS hits
     FROM site_visits WHERE dia >= ? AND is_bot = 0 AND pais IS NOT NULL
     GROUP BY pais ORDER BY hits DESC LIMIT 12"
)) {
    $stmt->bind_param('s', $minDay);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $topCountries[] = $r;
    $stmt->close();
}

// 3) Timeline diária (humanos e bots lado-a-lado)
$timeline = [];
if ($stmt = $d->prepare(
    "SELECT dia,
        SUM(CASE WHEN is_bot=0 THEN 1 ELSE 0 END) AS humans,
        SUM(CASE WHEN is_bot=1 THEN 1 ELSE 0 END) AS bots
     FROM site_visits WHERE dia >= ?
     GROUP BY dia ORDER BY dia ASC"
)) {
    $stmt->bind_param('s', $minDay);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $timeline[$r['dia']] = $r;
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

// 4) Top artigos — tabela `artigos` já existe (self-healing), mas o blog
//    ainda é estático: esperado devolver [] até haver artigos publicados.
$topArticles = artigos_top(15);

$countryNames = [
    'PT' => 'Portugal', 'BR' => 'Brasil', 'FR' => 'França', 'ES' => 'Espanha',
    'GB' => 'Reino Unido', 'DE' => 'Alemanha', 'IT' => 'Itália', 'NL' => 'Países Baixos',
    'IE' => 'Irlanda', 'US' => 'EUA', 'CA' => 'Canadá', 'AO' => 'Angola',
    'MZ' => 'Moçambique', 'CV' => 'Cabo Verde', 'CH' => 'Suíça', 'BE' => 'Bélgica',
];
function bnvs_flag(string $cc): string {
    if (!preg_match('/^[A-Z]{2}$/', $cc)) return '🏳️';
    $b1 = 0x1F1E6 - ord('A') + ord($cc[0]);
    $b2 = 0x1F1E6 - ord('A') + ord($cc[1]);
    return mb_chr($b1, 'UTF-8') . mb_chr($b2, 'UTF-8');
}
function bnvs_pct(int $a, int $b): string {
    $t = $a + $b;
    return $t > 0 ? sprintf('%.1f%%', ($a / $t) * 100) : '—';
}

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Referrer-Policy: same-origin');
$now = date('Y-m-d H:i:s');
?><!doctype html>
<html lang="pt-PT">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Auditoria Visitas — estudar-em-portugal</title>
<style>
:root{
  --bg:#0a1628; --bg-card:#11233f; --accent:#1ab8c8; --text:#e6edf3; --muted:#8aa0b8;
  --good:#22c55e; --warn:#f59e0b; --border:rgba(255,255,255,.08);
}
*{box-sizing:border-box}
body{margin:0;font:14px/1.55 -apple-system,BlinkMacSystemFont,"Inter",Segoe UI,Arial,sans-serif;background:var(--bg);color:var(--text);padding:2rem}
.wrap{max-width:1200px;margin:0 auto}
.header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem}
h1{font-size:1.4rem;font-weight:800;margin:0}
h1 span{color:var(--accent)}
.subtitle{color:var(--muted);font-size:.82rem;margin-top:.2rem}
.toolbar{display:flex;gap:.4rem;flex-wrap:wrap}
.toolbar a{padding:.4rem .8rem;border-radius:8px;background:var(--bg-card);color:var(--text);text-decoration:none;font-size:.8rem;border:1px solid var(--border)}
.toolbar a.active{background:var(--accent);color:#0a1628;border-color:transparent;font-weight:600}
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
.bar > i{display:block;height:100%;background:var(--accent)}
.timeline{display:flex;align-items:flex-end;gap:2px;height:120px;padding:1rem 1.2rem}
.bar-col{flex:1;display:flex;flex-direction:column-reverse;align-items:stretch;gap:1px;min-width:0}
.bar-h,.bar-b{border-radius:2px 2px 0 0;width:100%}
.bar-h{background:var(--accent)}
.bar-b{background:var(--warn);opacity:.85}
.tllabel{text-align:center;color:var(--muted);font-size:.65rem;padding:.5rem .4rem .8rem;border-top:1px solid var(--border)}
.footer{color:var(--muted);font-size:.75rem;margin-top:2rem;text-align:center}
.muted{color:var(--muted)}
.flag{font-size:1.1rem;margin-right:.4rem}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div>
      <h1>Auditoria de Visitas <span>·</span> estudar-em-portugal</h1>
      <div class="subtitle">Site inteiro, 1x por pessoa/dia · refreshed <?= htmlspecialchars($now) ?></div>
    </div>
    <div class="toolbar">
      <a href="?key=<?= urlencode($token) ?>&days=7"   class="<?= $days===7?'active':'' ?>">7 d</a>
      <a href="?key=<?= urlencode($token) ?>&days=14"  class="<?= $days===14?'active':'' ?>">14 d</a>
      <a href="?key=<?= urlencode($token) ?>&days=30"  class="<?= $days===30?'active':'' ?>">30 d</a>
      <a href="?key=<?= urlencode($token) ?>&days=60"  class="<?= $days===60?'active':'' ?>">60 d</a>
      <a href="?key=<?= urlencode($token) ?>&days=90"  class="<?= $days===90?'active':'' ?>">90 d</a>
    </div>
  </div>

  <div class="cards">
    <div class="card">
      <div class="lbl">Visitas humanas</div>
      <div class="val"><?= number_format($summary['h'], 0, ',', '.') ?></div>
      <div class="sub">Últimos <?= $days ?> dias · bots excluídos</div>
    </div>
    <div class="card">
      <div class="lbl">IPs únicos humanos</div>
      <div class="val"><?= number_format($summary['u'], 0, ',', '.') ?></div>
      <div class="sub">SHA-256, deduplicados por dia</div>
    </div>
    <div class="card">
      <div class="lbl">Bots detetados</div>
      <div class="val"><?= number_format($summary['b'], 0, ',', '.') ?></div>
      <div class="sub">Regista, não conta no público</div>
    </div>
    <div class="card">
      <div class="lbl">Genuinidade</div>
      <div class="val" style="color:<?= $summary['h']>=$summary['b']?'var(--good)':'var(--warn)' ?>">
        <?= bnvs_pct($summary['h'], $summary['b']) ?>
      </div>
      <div class="sub">% de visitas humanas vs total</div>
    </div>
  </div>

  <div class="grid">
    <div class="panel">
      <h2>🌍 Top países · visitas humanas</h2>
      <?php if (!$topCountries): ?>
        <div style="padding:1.4rem;color:var(--muted);">Sem dados ainda para este período.</div>
      <?php else:
        $maxHits = max(1, (int) $topCountries[0]['hits']); ?>
        <table>
          <thead><tr><th>País</th><th class="num">Visitas</th><th style="width:35%"></th></tr></thead>
          <tbody>
            <?php foreach ($topCountries as $r):
              $cc = (string) $r['pais'];
              $name = $countryNames[$cc] ?? $cc;
              $hits = (int) $r['hits'];
              $w = round(($hits / $maxHits) * 100, 1); ?>
            <tr>
              <td><span class="flag"><?= htmlspecialchars(bnvs_flag($cc)) ?></span><?= htmlspecialchars($name) ?> <span class="muted">(<?= htmlspecialchars($cc) ?>)</span></td>
              <td class="num"><?= number_format($hits, 0, ',', '.') ?></td>
              <td><div class="bar"><i style="width:<?= $w ?>%"></i></div></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <div class="panel">
      <h2>📰 Top artigos · leituras totais</h2>
      <?php if (!$topArticles): ?>
        <div style="padding:1.4rem;color:var(--muted);">Sem artigos publicados ainda — o blog está em preparação. Esta tabela já existe e enche-se sozinha assim que os artigos forem publicados (ver <code>artigo_regista_view()</code> em includes/db-helper.php).</div>
      <?php else: ?>
        <table>
          <thead><tr><th>Artigo</th><th class="num">Leituras</th></tr></thead>
          <tbody>
            <?php foreach ($topArticles as $r): ?>
            <tr>
              <td><a href="blog.php?slug=<?= urlencode((string)$r['slug']) ?>" target="_blank" rel="noopener" style="color:var(--accent);text-decoration:none"><?= htmlspecialchars((string)$r['titulo']) ?></a></td>
              <td class="num"><?= number_format((int)$r['contador_views'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

  <div class="panel" style="margin-bottom:1.5rem">
    <h2>📈 Timeline diária ( humano · bot ) · últimos <?= $days ?> dias</h2>
    <?php
      $maxDay = 1;
      foreach ($timelineFull as $day) if (($day['humans'] + $day['bots']) > $maxDay) $maxDay = $day['humans'] + $day['bots'];
      $labelEvery = max(1, (int) ceil(count($timelineFull) / 14));
      $i = 0;
    ?>
    <div class="timeline">
      <?php foreach ($timelineFull as $day):
        $h = $day['humans']; $b = $day['bots'];
        $hPx = $maxDay > 0 ? ($h / $maxDay) * 100 : 0;
        $bPx = $maxDay > 0 ? ($b / $maxDay) * 100 : 0;
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

  <div class="footer">Painel interno · token via URL (constant-time) · retenção 30 dias · apenas leitura.</div>
</div>
</body>
</html>
