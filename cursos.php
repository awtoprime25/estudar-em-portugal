<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/subpage-data.php';
require_once __DIR__ . '/includes/universidades-data.php';

$pageTitle       = 'Cursos em Portugal para Brasileiros | Lá Fora';
$pageDescription = 'Medicina, Engenharia Informática, Direito, Gestão e mais — descobre a duração, as universidades de referência e as saídas profissionais de cada curso em Portugal.';
$activeNav       = 'cursos';

$countByCurso = [];
foreach (UNIVERSIDADES as $u) {
    foreach ($u['cursos'] as $cSlug) {
        $countByCurso[$cSlug] = ($countByCurso[$cSlug] ?? 0) + 1;
    }
}

$extraJsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type'       => 'CollectionPage',
            'name'        => 'Cursos em Portugal para Brasileiros',
            'description' => $pageDescription,
            'url'         => SITE_URL . 'cursos.php',
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => SITE_URL],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Cursos', 'item' => SITE_URL . 'cursos.php'],
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

require_once __DIR__ . '/includes/header.php';
?>

<main id="conteudo">
  <section class="page-hero">
    <div class="container">
      <h1>Cursos em Portugal</h1>
      <p>Duração, universidades de referência e saídas profissionais — tudo o que precisas de saber antes de escolher o teu curso em Portugal.</p>
    </div>
  </section>

  <section class="container" style="padding-top:56px;padding-bottom:72px;">
    <div class="blog-cards" style="grid-template-columns:repeat(3,1fr);">
      <?php foreach (CURSOS as $slug => $c): ?>
      <a href="curso-<?= e($slug) ?>.php" class="blog-card blog-card--full">
        <div class="blog-card__media" style="display:flex;align-items:center;justify-content:center;background:var(--navy-900);">
          <i class="bi <?= e($c['icone']) ?>" style="font-size:52px;color:var(--teal-light);"></i>
        </div>
        <div class="blog-card__body">
          <span class="tag"><?= e($c['eyebrow']) ?></span>
          <h3><?= e($c['nome']) ?></h3>
          <p><?= e($c['duracao']) ?></p>
          <?php if (!empty($countByCurso[$slug])): ?>
          <p style="font-size:12.5px;color:var(--teal);font-weight:600;"><?= $countByCurso[$slug] ?> universidade<?= $countByCurso[$slug] > 1 ? 's' : '' ?> encontrada<?= $countByCurso[$slug] > 1 ? 's' : '' ?> no mapa</p>
          <?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <div class="article-cta" style="margin-top:48px;">
      <h3>Queres ver onde estudar cada curso?</h3>
      <p>Explora o mapa completo de universidades portuguesas e filtra por cidade.</p>
      <a href="universidades.php" class="btn-pill btn-teal">Ver mapa de universidades</a>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
