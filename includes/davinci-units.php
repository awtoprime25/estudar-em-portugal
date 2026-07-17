<?php
// ============================================================
//  Unidades Da Vinci em tempo real (BD DaVinciGlobal).
//  Ativas = Inscricoes=1 AND Contactos=1.
//
//  - lf_davinci_unidades(): contagem (int). Fallback 31 se a BD falhar.
//
//  Best-effort: nunca lança. Resultado em cache por request (static).
//  Portado do site irmão EstudarNoEstrangeiro/final/includes/davinci-units.php
//  (mesma BD, mesma lógica — só a lista de unidades para mapa ficou de fora
//  por não ser usada aqui).
// ============================================================

require_once __DIR__ . '/../config.php';

if (!function_exists('lf_davinci_db')) {
    // Ligação partilhada. Devolve mysqli ou null. Tenta localhost <-> 127.0.0.1
    // (resolve socket/TCP entre Linux/prod e Windows/XAMPP).
    function lf_davinci_db(): ?\mysqli
    {
        static $db = false;   // false = ainda não tentámos; null = falhou
        if ($db !== false) { return $db ?: null; }

        if (function_exists('mysqli_report')) {
            mysqli_report(MYSQLI_REPORT_OFF);
        }
        $hosts = [DAVINCI_DB_HOST];
        $hl    = strtolower((string) DAVINCI_DB_HOST);
        if ($hl === 'localhost')                       { $hosts[] = '127.0.0.1'; }
        elseif ($hl === '127.0.0.1' || $hl === '::1')  { $hosts[] = 'localhost'; }

        foreach ($hosts as $host) {
            $conn = mysqli_init();
            if (!$conn) { continue; }
            @mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 3);
            if (@mysqli_real_connect($conn, $host, DAVINCI_DB_USER, DAVINCI_DB_PASS, DAVINCI_DB_NAME)) {
                $conn->set_charset('utf8');
                return $db = $conn;
            }
        }
        error_log('[davinci-units] ligação falhou: ' . mysqli_connect_error());
        return ($db = null);
    }

    // Nome da tabela vem de constante (não de input) — sanitizado por precaução.
    function lf_davinci_tabela(): string
    {
        return preg_replace('/[^A-Za-z0-9_]/', '', (string) DAVINCI_DB_TABLE) ?: 'DaVinciGlobal';
    }
}

if (!function_exists('lf_davinci_unidades')) {

    function lf_davinci_unidades(int $fallback = 31): int
    {
        static $cache = null;
        if ($cache !== null) { return $cache; }

        try {
            $db = lf_davinci_db();
            if (!$db) { return $cache = $fallback; }
            $t   = lf_davinci_tabela();
            $res = $db->query("SELECT COUNT(*) AS n FROM `{$t}` WHERE Inscricoes=1 AND Contactos=1");
            $n   = $res ? (int) $res->fetch_assoc()['n'] : 0;
            // n=0 provavelmente é BD vazia/errada, não zero unidades reais → fallback.
            return $cache = ($n > 0 ? $n : $fallback);
        } catch (\Throwable $e) {
            error_log('[davinci-units] exceção (contagem): ' . $e->getMessage());
            return $cache = $fallback;
        }
    }
}

// ponytail: self-check — corre `php includes/davinci-units.php` (usa fallback se a BD não responder).
if (PHP_SAPI === 'cli' && realpath($argv[0] ?? '') === realpath(__FILE__)) {
    $n = lf_davinci_unidades();
    assert($n > 0, 'contagem tem de ser > 0 (fallback garante isto)');
    echo "unidades Da Vinci: {$n}\n";
}
