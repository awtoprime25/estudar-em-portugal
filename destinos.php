<?php
require_once __DIR__ . '/includes/subpage-data.php';

$pageTitle       = 'Destinos para Estudar em Portugal | Ginásios Da Vinci';
$pageDescription = 'Explore ' . count(DESTINOS) . ' destinos para estudar em Portugal, de norte a sul e nas ilhas: Lisboa, Porto, Coimbra, Braga e muito mais. Conheça as universidades, custos de vida e oportunidades de cada cidade.';
$activeNav       = 'destinos';
require_once __DIR__ . '/includes/header.php';

$regioesOrdem = ['Norte', 'Centro', 'Lisboa e Vale do Tejo', 'Alentejo', 'Algarve', 'Ilhas (Açores e Madeira)'];
$regioes = [];
foreach (DESTINOS as $city) {
    $regioes[$city['regiao']] = ($regioes[$city['regiao']] ?? 0) + 1;
}
?>

<main id="conteudo">

  <section class="page-hero">
    <div class="container">
      <h1>Destinos para estudar em Portugal</h1>
      <p>Conheça <?= count(DESTINOS) ?> destinos para estudar em Portugal, de norte a sul e nas ilhas — desde Lisboa, a capital vibrante, até pequenas cidades universitárias do interior. Cada cidade oferece uma experiência única de vida académica e universidades de referência.</p>
    </div>
  </section>

  <section class="section section-dark">
    <div class="container">
      <div class="blog-filters" id="destinoRegiaoFilters">
        <button class="filter-btn active" data-regiao="todas">Todas <span>(<?= count(DESTINOS) ?>)</span></button>
        <?php foreach ($regioesOrdem as $regiao): if (empty($regioes[$regiao])) continue; ?>
        <button class="filter-btn" data-regiao="<?= e($regiao) ?>"><?= e($regiao) ?> <span>(<?= $regioes[$regiao] ?>)</span></button>
        <?php endforeach; ?>
      </div>
      <div class="page-search page-search--dark">
        <i class="bi bi-search page-search__icon" aria-hidden="true"></i>
        <input type="search" id="destinoSearch" placeholder="Pesquisar cidade…" aria-label="Pesquisar destinos" autocomplete="off">
      </div>
      <p class="page-search__empty" id="destinoEmpty">Nenhuma cidade encontrada com esse nome.</p>
      <div class="city-list">
<?php
$num = 1;
foreach (DESTINOS as $slug => $city) {
    $cityImg  = !empty($city['imagem']) ? site_image_exists($city['imagem']) : null;
    $thumbHtml = $cityImg
        ? sprintf('<div class="city-row__thumb"><img src="%s" alt="%s"></div>', e($cityImg), e($city['nome']))
        : '<div class="city-row__thumb city-row__thumb--icon"><i class="bi bi-geo-alt"></i></div>';
    echo sprintf(
        '        <a href="destino-%s.php" class="city-row" data-regiao="%s">
          <span class="city-row__num">%02d</span>
          <h3>%s</h3>
          <p>%s</p>
          %s
        </a>' . PHP_EOL,
        e($slug),
        e($city['regiao']),
        $num,
        e($city['nome']),
        e($city['resumo']),
        $thumbHtml
    );
    $num++;
}
?>
      </div>

      <p class="also-europe">Também apoiamos candidaturas na Europa: <strong>Espanha · Irlanda · Países Baixos · Alemanha</strong></p>
    </div>
  </section>

  <script>
  (function(){
    var input = document.getElementById('destinoSearch');
    var empty = document.getElementById('destinoEmpty');
    var filterBtns = document.querySelectorAll('#destinoRegiaoFilters .filter-btn');
    var rows = [].slice.call(document.querySelectorAll('.city-list .city-row'));
    var activeRegiao = 'todas';
    function norm(s){ return (s||'').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g,''); }
    function apply(){
      var q = norm(input ? input.value.trim() : '');
      var any = false;
      rows.forEach(function(r){
        var matchesRegiao = activeRegiao === 'todas' || r.dataset.regiao === activeRegiao;
        var matchesSearch = q === '' || norm(r.textContent).indexOf(q) !== -1;
        var match = matchesRegiao && matchesSearch;
        r.style.display = match ? '' : 'none';
        if(match) any = true;
      });
      if (empty) empty.style.display = any ? 'none' : 'block';
    }
    filterBtns.forEach(function(btn){
      btn.addEventListener('click', function(){
        filterBtns.forEach(function(b){ b.classList.remove('active'); });
        btn.classList.add('active');
        activeRegiao = btn.getAttribute('data-regiao');
        apply();
      });
    });
    if (input) input.addEventListener('input', apply);
  })();
  </script>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
