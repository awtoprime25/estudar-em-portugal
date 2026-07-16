<?php
/**
 * track-visit.php — Beacon de visita ao site inteiro.
 *
 * Re-aplicação do EstudarNoEstrangeiro/final/track-visit.php para o Estudar
 * em Portugal (sister site). Recebe o slug via JS sendBeacon no pagehide.
 *
 *   - Sem ?slug= → registar visita ao site inteiro (slug sentinela 'global-site-visit')
 *   - ?slug=qualquer → registar visita a um slug específico (artigo do blog, etc.)
 *
 * Devolve JSON `{ok:true}` mesmo em erro silencioso (o beacon não tem retry).
 */
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db-helper.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$slug = (string) ($_GET['slug'] ?? $_POST['slug'] ?? '');
$slug = preg_replace('/[^a-z0-9\-\_\/]/', '', strtolower(trim($slug)));
if ($slug === '') {
    $slug = 'global-site-visit';
}

if (function_exists('lf_track_view')) {
    @lf_track_view($slug);
}

// Beacon responses must be small and never include PII.
echo json_encode(['ok' => true, 'slug' => $slug]);
