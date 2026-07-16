/**
 * tracker.js — SendBeacon do slug da página actual.
 *
 * Re-aplicação do EstudarNoEstrangeiro/final/assets/main.js (versão minimalista).
 *
 * Comportamento:
 *   - No carregamento, se window.TRACK_SLUG existe, faz POST silencioso (fallback
 *     para utilizadores sem JS no unload).
 *   - Em pagehide/visibilitychange→hidden, envia beacon para /track-visit.php.
 *   - Beacon não bloqueia navegação (sendBeacon é fire-and-forget com keepalive).
 *   - Em falha (offline, CSP), silencioso.
 *
 * O slug é definido pelo include/footer.php consoante a página:
 *   - index.php / blog.php / contato.php / privacidade.php / termos.php /
 *     sitemap.php (lista) → 'global-site-visit' (fallback)
 *   - comparar.php             → 'comparar'
 *   - comparar/blog/{slug}.php → '{slug}' (definido pelo gerador_artigo_comparar.php)
 *
 * ATENÇÃO: este JS é suplementar ao PHP-include em includes/footer.php (que
 * chama site_visit_track()). Em produção este beacon é a fonte de verdade
 * porque sobrevive a single-page navigations; o PHP-include serve como
 * fallback para utilizadores com JS desligado.
 */
(function () {
    'use strict';

    var TRACK_SLUG = (typeof window !== 'undefined' && typeof window.TRACK_SLUG === 'string')
        ? window.TRACK_SLUG
        : 'global-site-visit';
    TRACK_SLUG = String(TRACK_SLUG).toLowerCase().replace(/[^a-z0-9\-_\/]/g, '').slice(0, 191);
    if (TRACK_SLUG === '') TRACK_SLUG = 'global-site-visit';

    var TRACK_URL = (typeof window !== 'undefined' && typeof window.TRACK_URL === 'string')
        ? window.TRACK_URL
        : 'track-visit.php';

    function fire() {
        try {
            var payload = new Blob([], { type: 'application/x-www-form-urlencoded' });
            var url = TRACK_URL + (TRACK_URL.indexOf('?') >= 0 ? '&' : '?') + 'slug=' + encodeURIComponent(TRACK_SLUG);
            if (navigator.sendBeacon) {
                navigator.sendBeacon(url, payload);
                return;
            }
            // Fallback: fetch keepalive (Safari, env antigos).
            if (typeof fetch === 'function') {
                fetch(url, { method: 'POST', keepalive: true, cache: 'no-store', body: payload, credentials: 'omit' })
                    .catch(function () {});
                return;
            }
            // Fallback de fallback: img 1×1 pixel (não bloqueia).
            var img = new Image();
            img.src = url + '&_=' + Date.now();
        } catch (_) {
            // silencioso
        }
    }

    // Fire imediato (captura pageviews mesmo se utilizador fechar antes de unload).
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(fire, 100);
    } else {
        document.addEventListener('DOMContentLoaded', function () { setTimeout(fire, 100); });
    }

    // pagehide é preferível a beforeunload (mais fiável, não bloqueia BFCache).
    window.addEventListener('pagehide', fire);
    // visibilitychange (captura mudança de separador e regresso)
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') fire();
    });
})();
