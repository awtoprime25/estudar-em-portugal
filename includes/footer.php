<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db-helper.php';

// Contador de visitas ao site inteiro (1x por pessoa/dia, bots excluídos).
// Aproveita este include por ser o único ponto comum a todas as páginas.
if (function_exists('site_visit_track')) {
    site_visit_track();
}
?>
<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <div class="footer-brand-logos">
                <img src="assets/logo-davinci.svg" alt="Ginásios Da Vinci" class="footer-logo">
                <span class="footer-brand-times" aria-hidden="true">×</span>
                <img src="assets/images/logotipo-studywing.png" alt="StudyWing — Parceiro Internacional" class="footer-logo footer-logo--studywing">
            </div>
            <p>O seu futuro começa com a decisão que você faz hoje. Programa <strong>Estudar em Portugal</strong> — dos Ginásios Da Vinci, em colaboração com a <strong>StudyWing</strong>, para admissão universitária em Portugal e na Europa.</p>
            <p class="footer-tagline">Há <strong><?= date('Y') - 2008 ?></strong>+ anos a colocar alunos nas melhores universidades.</p>
        </div>

        <div class="footer-col">
            <h6>Contatos</h6>
            <ul>
                <li><a href="tel:<?= e(CONTACT_PHONE_TEL) ?>"><?= e(CONTACT_PHONE) ?></a></li>
                <li><a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a></li>
                <li><?= e(CONTACT_ADDRESS_LINE) ?></li>
            </ul>
        </div>

        <div class="footer-col">
            <h6>Recursos</h6>
            <ul>
                <li><a href="sobre.php">Sobre nós</a></li>
                <li><a href="universidades.php">Mapa de universidades</a></li>
                <li><a href="cursos.php">Todos os cursos</a></li>
                <li><a href="visto-de-estudante.php">Visto de estudante</a></li>
                <li><a href="faq.php">Perguntas frequentes</a></li>
                <li><a href="blog.php">Blog Estudar em Portugal</a></li>
                <li><a href="comparar.php">Comparar destinos</a></li>
                <li><a href="<?= e(BLOG_URL) ?>" target="_blank" rel="noopener">Blog Estudar no Estrangeiro</a></li>
                <li><a href="<?= e(SOCIAL_INSTAGRAM) ?>" target="_blank" rel="noopener">Instagram</a></li>
                <li><a href="<?= e(SOCIAL_FACEBOOK) ?>" target="_blank" rel="noopener">Facebook</a></li>
                <li><a href="<?= e(SOCIAL_YOUTUBE) ?>" target="_blank" rel="noopener">YouTube</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h6>Programa Estudar em Portugal</h6>
            <ul>
                <li><a href="https://www.ginasiosdavinci.com/" target="_blank" rel="noopener">Ginásios Da Vinci ↗</a></li>
                <li><a href="https://studywing.org/" target="_blank" rel="noopener">StudyWing ↗</a></li>
                <li><a href="https://www.ginasiosdavinci.com/estudar-no-estrangeiro/" target="_blank" rel="noopener">Estudar no Estrangeiro ↗</a></li>
            </ul>
        </div>
    </div>
    <div class="container footer-meta">
        <span>© <?= date('Y') ?> Ginásios Da Vinci × StudyWing. Todos os direitos reservados.</span>
        <span class="footer-legal"><a href="termos.php">Termos de uso</a> · <a href="privacidade.php">Política de privacidade</a></span>
    </div>
</footer>

<script>// Slug de tracking para este pageview.
//   - Páginas "lista" (index, blog.php, contato.php, etc.) → 'global-site-visit'
//   - comparar.php → 'comparar' (a página de galeria)
//   - comparativos gerados (blog/{slug}.php renderizado) → window.TRACK_SLUG já vem inline.
// Atualizado também por cada página individual via $pageSlug quando set.
// Sobrescreve o fallback acima se $pageSlug estiver definido pela página que incluiu.
window.TRACK_SLUG = <?= json_encode(!empty($pageSlug) ? (string) $pageSlug : (($activeNav ?? '') === 'comparar' ? 'comparar' : 'global-site-visit')) ?>;
window.TRACK_URL  = <?= json_encode('track-visit.php') ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset_url('assets/js/main.js')) ?>"></script>
<script src="<?= e(asset_url('assets/js/tracker.js')) ?>"></script>
<?php if (!empty($extraJS)): ?>
<script src="<?= e(asset_url($extraJS)) ?>"></script>
<?php endif; ?>
<script src="<?= e(asset_url('assets/chat-widget.js')) ?>" defer></script>
</body>
</html>
