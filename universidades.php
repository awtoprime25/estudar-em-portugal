<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/universidades-data.php';
require_once __DIR__ . '/includes/subpage-data.php';

$pageTitle       = 'Universidades em Portugal | Mapa e Guia';
$pageDescription = 'Explore ' . count(UNIVERSIDADES) . ' universidades e institutos politécnicos em Portugal. Filtre por cidade e natureza e descubra cursos para estudantes internacionais.';
$activeNav       = 'mapa';
$pageModified    = '2026-08-18';

$extraHeadHtml = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">';

$cidades = [];
$naturezas = ['publica' => 0, 'privada' => 0];
foreach (UNIVERSIDADES as $u) {
    $cidades[$u['cidade']] = ($cidades[$u['cidade']] ?? 0) + 1;
    $naturezas[$u['natureza']] = ($naturezas[$u['natureza']] ?? 0) + 1;
}
ksort($cidades);

$mapData = array_map(function ($u) {
    return [
        'id'       => $u['id'],
        'nome'     => $u['nome'],
        'cidade'   => $u['cidade'],
        'citySlug' => $u['citySlug'],
        'lat'      => $u['lat'],
        'lng'      => $u['lng'],
        'natureza' => $u['natureza'],
        'grau'     => $u['grau'],
        'cursos'   => array_values(array_filter(array_map(function ($cSlug) {
            $c = CURSOS[$cSlug] ?? null;
            return $c ? ['slug' => $cSlug, 'nome' => $c['nome']] : null;
        }, $u['cursos']))),
    ];
}, UNIVERSIDADES);

$extraJsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type'       => 'CollectionPage',
            'name'        => 'Universidades em Portugal',
            'description' => $pageDescription,
            'url'         => SITE_URL . 'universidades.php',
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => SITE_URL],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Universidades', 'item' => SITE_URL . 'universidades.php'],
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

require_once __DIR__ . '/includes/header.php';
?>

<main id="conteudo">

  <section class="hero">
    <div class="container hero__grid" style="grid-template-columns:1fr;text-align:center;">
      <div class="hero__copy" style="max-width:720px;margin:0 auto;">
        <span class="eyebrow">MAPA DE UNIVERSIDADES</span>
        <h1>Universidades em <span class="accent">Portugal</span></h1>
        <p class="lede"><?= count(UNIVERSIDADES) ?> universidades e institutos politécnicos — públicos e privados, de norte a sul e nas ilhas — filtre por cidade ou natureza e explore onde pode estudar.</p>
      </div>
    </div>
  </section>

  <section class="section" style="padding-bottom:0;">
    <div class="container">
      <div class="blog-filters" id="uniNaturezaFilters" style="margin-bottom:12px;">
        <button class="filter-btn active" data-natureza="todas">Todas <span>(<?= count(UNIVERSIDADES) ?>)</span></button>
        <button class="filter-btn" data-natureza="publica">Públicas <span>(<?= $naturezas['publica'] ?? 0 ?>)</span></button>
        <button class="filter-btn" data-natureza="privada">Privadas <span>(<?= $naturezas['privada'] ?? 0 ?>)</span></button>
      </div>
      <div class="blog-filters" id="uniFilters">
        <button class="filter-btn active" data-filtro="todas">Todas as cidades <span>(<?= count(UNIVERSIDADES) ?>)</span></button>
        <?php foreach ($cidades as $cidade => $n): ?>
        <button class="filter-btn" data-filtro="<?= e($cidade) ?>"><?= e($cidade) ?> <span>(<?= $n ?>)</span></button>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="uni-map-wrap">
        <div id="uniMap"></div>
        <div class="uni-list-panel">
          <div class="uni-list-header">
            <span>Universidades</span>
            <span class="count" id="uniCount"><?= count(UNIVERSIDADES) ?> instituições</span>
          </div>
          <div class="uni-list" id="uniList">
            <?php foreach (UNIVERSIDADES as $u): ?>
            <article class="uni-card" data-lat="<?= e((string) $u['lat']) ?>" data-lng="<?= e((string) $u['lng']) ?>">
              <h3 class="uni-card__name"><?= e($u['nome']) ?></h3>
              <p class="uni-card__meta">
                <span><?= e($u['cidade']) ?></span>
                <span><?= e($u['grau'] === 'politecnico' ? 'Politécnico' : 'Universidade') ?> <?= e($u['natureza'] === 'privada' ? 'privado' : 'público') ?></span>
                <?php if (!empty($u['citySlug'])): ?>
                <a href="destino-<?= e($u['citySlug']) ?>.php">Ver cidade</a>
                <?php endif; ?>
              </p>
              <?php if (!empty($u['cursos'])): ?>
              <div class="uni-card__cursos">
                <?php foreach ($u['cursos'] as $cSlug): $c = CURSOS[$cSlug] ?? null; if (!$c) continue; ?>
                <a href="curso-<?= e($cSlug) ?>.php"><?= e($c['nome']) ?></a>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>
            </article>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section section-dark">
    <div class="container">
      <div class="article-cta">
        <h3>Não sabe qual escolher?</h3>
        <p>A equipe Da Vinci × StudyWing te ajuda a encontrar a universidade e o curso certos para o seu perfil.</p>
        <a href="#formulario" class="btn-pill btn-flag">Agendar consultoria gratuita</a>
      </div>
    </div>
  </section>

</main>

<script>window.UNIVERSIDADES = <?= json_encode($mapData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>

<?php
$extraJS = 'assets/js/universidades-map.js';
require_once __DIR__ . '/includes/footer.php';
?>
