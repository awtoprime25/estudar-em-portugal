<?php
/**
 * cron-cleanup-views.php — Limpeza explícita de blog_view_hits > LF_VIEWS_RETENTION_DAYS.
 *
 * Re-aplicação do EstudarNoEstrangeiro/final/cron-cleanup-views.php. Idempotente:
 * executar várias vezes só corre 1 DELETE. Seguro correr várias vezes por dia.
 *
 * Modos:
 *   - CLI:     `php /path/cron-cleanup-views.php`
 *   - Web:     `/cron-cleanup-views.php?key=<VIEWS_STATS_TOKEN>`  (modo manual)
 *
 * Em XAMPP local sem cron configurado, a limpeza probabilística inline (~1% das
 * chamadas em lf_track_view) já cobre 99% dos casos — este script é a "sécurité
 * camada 2" para servidores com partilha de carga.
 *
 * NÃO regista hit próprio (não é uma visita de página).
 */

require_once __DIR__ . '/config.php';

$isCli   = (PHP_SAPI === 'cli');
$token   = (string) ($_GET['key'] ?? '');
$cfgTok  = (string) VIEWS_STATS_TOKEN;

if (!$isCli) {
    if ($cfgTok === '' || !hash_equals($cfgTok, $token)) {
        header('HTTP/1.0 401 Unauthorized');
        header('Content-Type: text/plain; charset=utf-8');
        echo "401 Unauthorized\nUso: cron/framework cron:  php " . basename(__FILE__) . "\nOu: " . htmlspecialchars(basename(__FILE__)) . "?key=<VIEWS_STATS_TOKEN>\n";
        exit;
    }
}

$retention = (int) LF_VIEWS_RETENTION_DAYS;

mysqli_report(MYSQLI_REPORT_OFF);
$c = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($c->connect_errno) {
    $msg = 'Falha BD (' . $c->connect_errno . '): ' . $c->connect_error;
    if ($isCli) { fwrite(STDERR, "[cron-cleanup-views] $msg\n"); }
    else        { header('Content-Type: text/plain; charset=utf-8'); echo "ERRO: " . htmlspecialchars($msg); }
    exit(1);
}
$c->set_charset('utf8mb4');

$stmt = $c->prepare("DELETE FROM blog_view_hits WHERE day < DATE_SUB(CURDATE(), INTERVAL ? DAY)");
if (!$stmt) {
    $msg = 'prepare() falhou: ' . $c->error;
    if ($isCli) { fwrite(STDERR, "[cron-cleanup-views] $msg\n"); }
    else        { echo "ERRO: " . htmlspecialchars($msg); }
    $c->close();
    exit(2);
}
$stmt->bind_param('i', $retention);
$ok = $stmt->execute();
$deleted = $ok ? (int) $stmt->affected_rows : 0;
$stmt->close();
$c->close();

if (!$ok) {
    $msg = 'DELETE falhou';
    if ($isCli) { fwrite(STDERR, "[cron-cleanup-views] $msg\n"); }
    else        { echo "ERRO: " . htmlspecialchars($msg); }
    exit(3);
}

$log = sprintf("[cron-cleanup-views] retention_days=%d deleted_rows=%d at=%s\n",
    $retention, $deleted, date('Y-m-d H:i:s'));

if ($isCli) echo $log;
else {
    header('Content-Type: text/plain; charset=utf-8');
    echo "OK: " . $deleted . " row(s) eliminadas (retenção = $retention dias)\n";
    @file_put_contents(sys_get_temp_dir() . '/estudar_em_portugal_views_cleanup.log', $log, FILE_APPEND | LOCK_EX);
}
