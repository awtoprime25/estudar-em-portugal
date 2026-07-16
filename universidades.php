<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/universidades-data.php';
require_once __DIR__ . '/includes/subpage-data.php';

$pageTitle       = 'Universidades em Portugal — Mapa Completo | Lá Fora';
$pageDescription = 'Explora ' . count(UNIVERSIDADES) . ' universidades e institutos politécnicos em Portugal — filtra por cidade e descobre onde estudar.';
$activeNav       = 'universidades';

$extraHeadHtml = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">';

$cidades = [];
foreach (UNIVERSIDADES as $u) {
    $cidades[$u['cidade']] = ($cidades[$u['cidade']] ?? 0) + 1;
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
        'tipo'     => $u['tipo'],
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
        <p class="lede"><?= count(UNIVERSIDADES) ?> universidades e institutos politécnicos, de norte a sul e nas ilhas — filtra por cidade e explora onde podes estudar.</p>
      </div>
    </div>
  </section>

  <section class="section" style="padding-bottom:0;">
    <div class="container">
      <div class="blog-filters" id="uniFilters">
        <button class="filter-btn active" data-filtro="todas">Todas <span>(<?= count(UNIVERSIDADES) ?>)</span></button>
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
          <div class="uni-list" id="uniList"></div>
        </div>
      </div>
    </div>
  </section>

  <section class="section section-dark">
    <div class="container">
      <div class="article-cta">
        <h3>Não sabes qual escolher?</h3>
        <p>A equipa Da Vinci × StudyWing ajuda-te a encontrar a universidade e o curso certos para o teu perfil.</p>
        <a href="comparar.php#formulario" class="btn-pill btn-teal">Agendar consultoria gratuita</a>
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
