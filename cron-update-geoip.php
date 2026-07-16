<?php
/**
 * cron-update-geoip.php — Popula ip_country_ranges (fallback de país sem CDN).
 *
 * Re-aplicação do EstudarNoEstrangeiro/final/cron-update-geoip.php. Idempotente
 * (TRUNCATE + reimport dentro de transação). Cron mensal recomendado.
 *
 * Fonte: user-country-ipv4-num.csv (sapics/ip-location-db — PDDL público).
 * IPv6 não é importado (fix issue = upgrade schema se tráfego IPv6 justificar).
 *
 * Modos:
 *   - CLI:    `php cron-update-geoip.php`
 *   - Web:    `cron-update-geoip.php?key=<VIEWS_STATS_TOKEN>` (manual)
 *
 * Em XAMPP local sem curl disponível, a tabela fica vazia — registos passam
 * com country='XX' (não-bloqueante).
 */
require_once __DIR__ . '/config.php';

$isCli = (PHP_SAPI === 'cli');
$token = (string) ($_GET['key'] ?? '');
$cfgTok = (string) VIEWS_STATS_TOKEN;

if (!$isCli) {
    if ($cfgTok === '' || !hash_equals($cfgTok, $token)) {
        header('HTTP/1.0 401 Unauthorized');
        header('Content-Type: text/plain; charset=utf-8');
        echo "401 Unauthorized\n";
        exit;
    }
    header('Content-Type: text/plain; charset=utf-8');
}

function out_csv(string $msg): void {
    global $isCli;
    if ($isCli) fwrite(STDOUT, $msg . "\n");
    else        echo htmlspecialchars($msg) . "\n";
}

$url  = 'https://github.com/sapics/ip-location-db/releases/download/latest/user-country-ipv4-num.csv';
$csv  = false;

if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_USERAGENT      => 'estudar-em-portugal-geoip-updater',
    ]);
    $csv = curl_exec($ch);
    if ($csv === false) out_csv('curl erro: ' . curl_error($ch));
    curl_close($ch);
}
if ($csv === false || $csv === '') {
    $csv = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 60]]));
}
if ($csv === false || $csv === '') {
    out_csv('ERRO: download falhou (sem rede?).');
    exit(1);
}

$lines = explode("\n", trim($csv));
out_csv('Linhas descarregadas: ' . count($lines));

mysqli_report(MYSQLI_REPORT_OFF);
$c = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($c->connect_errno) {
    out_csv('ERRO BD: ' . $c->connect_error);
    exit(2);
}
$c->set_charset('utf8mb4');
$c->query(
    "CREATE TABLE IF NOT EXISTS ip_country_ranges (
        range_start INT UNSIGNED NOT NULL,
        range_end   INT UNSIGNED NOT NULL,
        country     CHAR(2) NOT NULL,
        PRIMARY KEY (range_start)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

$c->begin_transaction();
$c->query('TRUNCATE TABLE ip_country_ranges');

$batch = [];
$batchSize = 2000;
$inserted = 0;
$ok = true;

foreach ($lines as $line) {
    $parts = explode(',', trim($line));
    if (count($parts) !== 3) continue;
    [$start, $end, $cc] = $parts;
    if (!ctype_digit($start) || !ctype_digit($end)) continue;
    $cc = strtoupper(substr($cc, 0, 2));
    if (!preg_match('/^[A-Z]{2}$/', $cc)) continue;
    $batch[] = '(' . (int) $start . ',' . (int) $end . ",'" . $c->real_escape_string($cc) . "')";
    if (count($batch) >= $batchSize) {
        $ok = $c->query('INSERT INTO ip_country_ranges (range_start, range_end, country) VALUES ' . implode(',', $batch));
        if (!$ok) break;
        $inserted += count($batch);
        $batch = [];
    }
}
if ($ok && $batch) {
    $ok = $c->query('INSERT INTO ip_country_ranges (range_start, range_end, country) VALUES ' . implode(',', $batch));
    if ($ok) $inserted += count($batch);
}

if ($ok) {
    $c->commit();
    out_csv("OK: $inserted ranges importados em " . date('Y-m-d H:i:s'));
    @file_put_contents(sys_get_temp_dir() . '/estudar_em_portugal_geoip.log',
        sprintf("[%s] imported=%d\n", date('Y-m-d H:i:s'), $inserted),
        FILE_APPEND | LOCK_EX);
} else {
    $c->rollback();
    out_csv('ERRO INSERT: ' . $c->error);
    exit(3);
}
$c->close();
