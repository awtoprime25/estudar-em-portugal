<?php
/**
 * admin/test-geoip.php — Diagnóstico do lookup IP→país (lf_country()).
 *
 * Protegido por token VIEWS_STATS_TOKEN (mesmo padrão dos outros diagnósticos
 * admin/test-*.php). Mostra se ip_country_ranges está populada, e testa o
 * lookup ao vivo para o IP de quem visita esta página — apaga depois de
 * resolvido.
 *
 * URL: admin/test-geoip.php?key=<TOKEN>&ip=<IP opcional para testar>
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db-helper.php';

header('Content-Type: text/plain; charset=utf-8');

$token = (string) ($_GET['key'] ?? '');
if (VIEWS_STATS_TOKEN === '' || !hash_equals(VIEWS_STATS_TOKEN, $token)) {
    header('HTTP/1.0 401 Unauthorized');
    echo "401 Unauthorized\n\nUso: " . htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? '/admin/test-geoip.php') . "?key=<TOKEN>\n";
    exit;
}

$d = db();
echo "=== Ligação à BD ===\n";
echo $d ? "OK\n" : "FALHOU — nada disto funciona sem BD.\n";
if (!$d) exit;

echo "\n=== Tabela ip_country_ranges ===\n";
$res = $d->query('SELECT COUNT(*) AS n FROM ip_country_ranges');
$n = $res ? (int) $res->fetch_assoc()['n'] : -1;
echo "Total de faixas importadas: {$n}\n";
if ($n === 0) {
    echo "VAZIA — o cron-update-geoip.php nunca correu com sucesso (ou fez rollback a meio). Corre-o outra vez e vê se dá erro.\n";
} else {
    $sample = $d->query('SELECT range_start, range_end, country FROM ip_country_ranges ORDER BY RAND() LIMIT 5');
    echo "Amostra de 5 linhas:\n";
    while ($row = $sample->fetch_assoc()) {
        echo "  start={$row['range_start']} end={$row['range_end']} country={$row['country']}\n";
    }
}

echo "\n=== Últimos 5 hits gravados (blog_view_hits) ===\n";
$hits = $d->query("SELECT day, slug, country, LEFT(ip_hash,12) AS ip_id FROM blog_view_hits ORDER BY id DESC LIMIT 5");
if ($hits) {
    while ($row = $hits->fetch_assoc()) {
        echo "  {$row['day']} | {$row['slug']} | country={$row['country']} | ip_id={$row['ip_id']}...\n";
    }
}

echo "\n=== Lookup ao vivo para esta visita ===\n";
$testIp = (string) ($_GET['ip'] ?? '');
$ip = $testIp !== '' ? $testIp : lf_client_ip();
echo "IP a testar         = {$ip}" . ($testIp !== '' ? ' (forçado via ?ip=)' : ' (lf_client_ip() — o teu IP real)') . "\n";
echo "REMOTE_ADDR          = " . ($_SERVER['REMOTE_ADDR'] ?? '(ausente)') . "\n";
echo "HTTP_CF_CONNECTING_IP = " . ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? '(ausente)') . "\n";
echo "HTTP_X_FORWARDED_FOR  = " . ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '(ausente)') . "\n";
echo "HTTP_CF_IPCOUNTRY     = " . ($_SERVER['HTTP_CF_IPCOUNTRY'] ?? '(ausente — sem Cloudflare à frente do site)') . "\n";
echo "É IPv6 (tem ':')      = " . (strpos($ip, ':') !== false ? 'SIM — este sistema só suporta IPv4, o resultado será sempre XX' : 'não') . "\n";

$long = ip2long($ip);
echo "ip2long()             = " . ($long === false ? 'falhou (IP inválido ou IPv6)' : (string) $long) . "\n";

if ($long !== false) {
    $stmt = $d->prepare("SELECT country, range_end FROM ip_country_ranges WHERE range_start <= ? ORDER BY range_start DESC LIMIT 1");
    $stmt->bind_param('i', $long);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        echo "Nenhuma faixa encontrada com range_start <= {$long}.\n";
    } else {
        echo "Faixa mais próxima     = start<=?  range_end={$row['range_end']} country={$row['country']}\n";
        echo "long > range_end?      = " . ($long > (int) $row['range_end'] ? 'SIM — fora da faixa, cai em XX' : 'não — dentro da faixa') . "\n";
    }
}

echo "\nlf_country_from_db(\$ip) para o IP acima = " . lf_country_from_db($ip) . "\n";
echo "lf_country() para esta visita real      = " . lf_country() . " (ignora ?ip=, usa sempre o teu IP real de agora)\n";
