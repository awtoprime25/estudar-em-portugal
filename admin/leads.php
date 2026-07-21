<?php
/**
 * admin/leads.php — Painel de pré-inscrições (formulário StudyWing)
 *
 * Protegido por token VIEWS_STATS_TOKEN (mesmo padrão de admin/views-stats.php).
 * Mostra tabela de últimas inscrições com filtro opcional.
 *
 * URL: admin/leads.php?key=<TOKEN>
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
    echo "Uso: " . htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? '/admin/leads.php') . "?key=<TOKEN>\n";
    exit;
}

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

$page    = max(0, (int) ($_GET['page']  ?? 0));
$perPage = 50;
$offset  = $page * $perPage;
$now     = date('Y-m-d H:i:s');

// Contagem total de leads
$totalRows = 0;
if ($stmt = $d->prepare("SELECT COUNT(*) FROM leads")) {
    $stmt->execute();
    $totalRows = (int) ((($stmt->get_result()->fetch_row() ?? [])[0]) ?? 0);
    $stmt->close();
}

// Últimas inscrições
$leads = [];
if ($stmt = $d->prepare(
    "SELECT id, created_at, nome, email, tel, localidade, nacionalidade, destino, objetivo
     FROM leads ORDER BY created_at DESC LIMIT ? OFFSET ?"
)) {
    $stmt->bind_param('ii', $perPage, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $leads[] = $r;
    $stmt->close();
}

$maxPage = max(0, (int) ceil($totalRows / $perPage) - 1);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Referrer-Policy: same-origin');
?><!doctype html>
<html lang="pt-PT">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pré-inscrições — Estudar em Portugal</title>
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
.toolbar a{padding:.4rem .8rem;border-radius:8px;background:var(--bg-card);color:var(--text);text-decoration:none;font-size:.8rem;border:1px solid var(--border)}
.toolbar a.active{background:var(--accent);color:#0a1628;border-color:transparent;font-weight:600}
.panel{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;overflow:hidden}
.panel h2{margin:0;padding:1rem 1.2rem;font-size:.95rem;border-bottom:1px solid var(--border);background:rgba(0,0,0,.15);font-weight:700}
table{width:100%;border-collapse:collapse;font-size:.84rem}
th,td{padding:.6rem .9rem;text-align:left;border-bottom:1px solid var(--border)}
th{font-weight:600;color:var(--muted);background:rgba(0,0,0,.18)}
tr:last-child td{border-bottom:none}
.muted{color:var(--muted)}
.footer{color:var(--muted);font-size:.75rem;margin-top:2rem;text-align:center}
.pagination{padding:.8rem 1.2rem;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;font-size:.78rem;flex-wrap:wrap;gap:.6rem}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div>
      <h1>Pré-inscrições <span>·</span> Estudar em Portugal</h1>
      <div class="subtitle">Últimas inscrições · refreshed <?= htmlspecialchars($now) ?></div>
    </div>
  </div>

  <div class="panel" style="margin-bottom:1.5rem">
    <h2>Formulário StudyWing (<?= number_format($totalRows, 0, ',', '.') ?> inscrições)</h2>
    <?php if (!$leads): ?>
      <div style="padding:1.4rem;color:var(--muted);">Sem inscrições ainda.</div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Data</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Telefone</th>
            <th>Localidade</th>
            <th>Nacionalidade</th>
            <th>Destino</th>
            <th>Objetivo</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($leads as $r): ?>
          <tr>
            <td class="muted" style="font-size:.75rem;"><?= htmlspecialchars((string) $r['created_at']) ?></td>
            <td><?= htmlspecialchars((string) $r['nome']) ?></td>
            <td style="font-size:.75rem;"><a href="mailto:<?= htmlspecialchars((string) $r['email']) ?>" style="color:var(--accent);text-decoration:none"><?= htmlspecialchars((string) $r['email']) ?></a></td>
            <td style="font-size:.75rem;"><?= htmlspecialchars((string) $r['tel']) ?></td>
            <td style="font-size:.75rem;"><?= htmlspecialchars((string) $r['localidade']) ?></td>
            <td style="font-size:.75rem;"><?= htmlspecialchars((string) $r['nacionalidade']) ?></td>
            <td style="font-size:.75rem;"><?= htmlspecialchars((string) $r['destino']) ?></td>
            <td style="font-size:.75rem;"><?= htmlspecialchars((string) $r['objetivo']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <?php if ($totalRows > $perPage): ?>
    <div class="pagination">
      <span class="muted"><?= number_format($totalRows, 0, ',', '.') ?> inscrições no total · página <?= $page + 1 ?> / <?= $maxPage + 1 ?></span>
      <div class="toolbar">
        <?php if ($page > 0): ?>
          <a href="?key=<?= urlencode($token) ?>&page=<?= $page - 1 ?>">← Anterior</a>
        <?php endif; ?>
        <?php if ($page < $maxPage): ?>
          <a href="?key=<?= urlencode($token) ?>&page=<?= $page + 1 ?>">Próxima →</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="footer">Painel interno · token via URL (constante-time) · apenas leitura.</div>
</div>
</body>
</html>
