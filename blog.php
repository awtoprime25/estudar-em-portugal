<?php
$pageTitle       = 'Blog — Estudar em Portugal';
$pageDescription = 'Guias, prazos de candidatura e dicas práticas sobre estudar em Portugal para brasileiros: cidades, cursos, vistos, ENEM e Concurso Especial.';
$activeNav       = 'blog';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/blog-db.php';

$categoryLabels = ['cidade' => 'Cidades', 'curso' => 'Cursos', 'dica' => 'Dicas'];
$posts   = blog_list(['limit' => 100]);
$viewsMap = lf_get_views_map();
$total   = count($posts);
$byCat   = ['cidade' => 0, 'curso' => 0, 'dica' => 0];
foreach ($posts as $p) {
    $c = $p['category'] ?? 'dica';
    if (isset($byCat[$c])) $byCat[$c]++;
}
$featured = $posts[0] ?? null;
$rest     = $featured ? array_slice($posts, 1) : $posts;

require_once __DIR__ . '/includes/header.php';
?>

<main id="conteudo">
  <section class="page-hero">
    <div class="container">
      <h1>Blog Estudar em Portugal</h1>
      <p>Guias práticos sobre candidaturas, vistos, custo de vida e escolha de curso para quem quer estudar em Portugal.</p>
    </div>
  </section>

  <section class="container" style="padding-top:56px;padding-bottom:72px;">

    <?php if (!$posts): ?>
      <div class="blog-empty">
        <p><strong>Ainda não há artigos publicados.</strong></p>
        <p>Os primeiros artigos deste blog são gerados automaticamente — volta em breve, ou consulta o
          <a href="<?= e(BLOG_URL) ?>" target="_blank" rel="noopener" style="color:var(--teal);font-weight:600;">blog Estudar no Estrangeiro</a>
          enquanto isso.</p>
      </div>
    <?php else: ?>

      <?php if ($featured): ?>
      <div class="blog-featured">
        <div class="blog-featured__media" style="background-image:url('<?= e('assets/images/' . ($featured['hero_image'] ?: 'hero-blog-default.svg')) ?>');"></div>
        <div class="blog-featured__body">
          <span class="tag">⭐ Destaque · <?= e($categoryLabels[$featured['category']] ?? 'Dicas') ?></span>
          <h2><?= $featured['h1_html'] ? $featured['h1_html'] : e($featured['title']) ?></h2>
          <p><?= e($featured['excerpt'] ?? '') ?></p>
          <a href="artigo.php?slug=<?= urlencode($featured['slug']) ?>" class="btn-pill btn-teal" style="align-self:flex-start;">Ler artigo →</a>
        </div>
      </div>
      <?php endif; ?>

      <div class="blog-filters" data-blog-filters>
        <button class="filter-btn active" data-filter="todos">Todos (<?= $total ?>)</button>
        <button class="filter-btn" data-filter="cidade">Cidades (<?= $byCat['cidade'] ?>)</button>
        <button class="filter-btn" data-filter="curso">Cursos (<?= $byCat['curso'] ?>)</button>
        <button class="filter-btn" data-filter="dica">Dicas (<?= $byCat['dica'] ?>)</button>
      </div>

      <div class="blog-search">
        <i class="bi bi-search"></i>
        <input type="search" id="blogSearch" placeholder="Pesquisar artigos…" autocomplete="off">
      </div>

      <div class="blog-cards" id="blogGrid">
        <?php foreach ($rest as $p): ?>
        <a href="artigo.php?slug=<?= urlencode($p['slug']) ?>" class="blog-card blog-card--full blog-item" data-cat="<?= e($p['category']) ?>">
          <div class="blog-card__media">
            <img src="<?= e('assets/images/' . ($p['hero_image'] ?: 'hero-blog-default.svg')) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
          </div>
          <div class="blog-card__body">
            <span class="tag"><?= e($categoryLabels[$p['category']] ?? 'Dicas') ?></span>
            <h3><?= e($p['title']) ?></h3>
            <p><?= e(mb_substr((string) ($p['excerpt'] ?? ''), 0, 110, 'UTF-8')) ?>…</p>
            <div class="blog-card__footer">
              <span><?= $p['published_at'] ? date('d/m/Y', strtotime($p['published_at'])) : '' ?></span>
              <span><?= (int) ($viewsMap[$p['slug']] ?? 0) ?> leituras</span>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>

      <script>
      (function () {
        var grid = document.getElementById('blogGrid');
        var search = document.getElementById('blogSearch');
        var filterBtns = document.querySelectorAll('[data-blog-filters] .filter-btn');
        if (!grid) return;
        var items = Array.prototype.slice.call(grid.querySelectorAll('.blog-item'));
        var activeCat = 'todos';

        function apply() {
          var term = (search.value || '').toLowerCase().trim();
          items.forEach(function (el) {
            var matchesCat = activeCat === 'todos' || el.dataset.cat === activeCat;
            var text = el.textContent.toLowerCase();
            var matchesSearch = !term || text.indexOf(term) !== -1;
            el.style.display = (matchesCat && matchesSearch) ? '' : 'none';
          });
        }
        filterBtns.forEach(function (btn) {
          btn.addEventListener('click', function () {
            filterBtns.forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            activeCat = btn.getAttribute('data-filter');
            apply();
          });
        });
        search.addEventListener('input', apply);
      })();
      </script>

    <?php endif; ?>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
