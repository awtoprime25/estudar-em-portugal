<?php
require_once __DIR__ . '/includes/subpage-data.php';

$pageTitle       = 'Destinos para estudar em Portugal | Guia';
$pageDescription = 'Explore ' . count(DESTINOS) . ' destinos para estudar em Portugal: universidades, custo de vida, transportes e vida académica de Lisboa ao Algarve e às ilhas.';
$activeNav       = 'destinos';
$pageModified    = '2026-08-18';
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
      <p>Conheça <?= count(DESTINOS) ?> destinos para estudar em Portugal, de norte a sul e nas ilhas — desde Lisboa, a capital vibrante, até pequenas cidades universitárias do interior. Cada cidade oferece uma experiência única de vida acadêmica e universidades de referência.</p>
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
      <div class="destino-pagination" id="destinoPagination"></div>

      <p class="also-europe">Também ajudamos com inscrições em outros destinos da Europa: <strong>Espanha · Irlanda · Países Baixos · Alemanha</strong></p>
    </div>
  </section>

  <script>
  (function(){
    var PAGE_SIZE = 4;
    var input = document.getElementById('destinoSearch');
    var empty = document.getElementById('destinoEmpty');
    var pagination = document.getElementById('destinoPagination');
    var filterBtns = document.querySelectorAll('#destinoRegiaoFilters .filter-btn');
    var rows = [].slice.call(document.querySelectorAll('.city-list .city-row'));
    var activeRegiao = 'todas';
    var currentPage = 1;
    function norm(s){ return (s||'').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g,''); }
    function apply(){
      var q = norm(input ? input.value.trim() : '');
      var matches = rows.filter(function(r){
        var matchesRegiao = activeRegiao === 'todas' || r.dataset.regiao === activeRegiao;
        var matchesSearch = q === '' || norm(r.textContent).indexOf(q) !== -1;
        return matchesRegiao && matchesSearch;
      });
      if (empty) empty.style.display = matches.length ? 'none' : 'block';

      var totalPages = Math.max(1, Math.ceil(matches.length / PAGE_SIZE));
      if (currentPage > totalPages) currentPage = totalPages;
      var start = (currentPage - 1) * PAGE_SIZE;
      var visible = matches.slice(start, start + PAGE_SIZE);

      rows.forEach(function(r){ r.style.display = 'none'; });
      visible.forEach(function(r){ r.style.display = ''; });

      pagination.innerHTML = '';
      if (totalPages <= 1) return;
      var prev = document.createElement('button');
      prev.className = 'filter-btn';
      prev.innerHTML = '<i class="bi bi-chevron-left"></i>';
      prev.disabled = currentPage === 1;
      prev.addEventListener('click', function(){ currentPage--; apply(); });
      pagination.appendChild(prev);
      for (var p = 1; p <= totalPages; p++) {
        (function(p){
          var btn = document.createElement('button');
          btn.className = 'filter-btn' + (p === currentPage ? ' active' : '');
          btn.textContent = p;
          btn.addEventListener('click', function(){ currentPage = p; apply(); });
          pagination.appendChild(btn);
        })(p);
      }
      var next = document.createElement('button');
      next.className = 'filter-btn';
      next.innerHTML = '<i class="bi bi-chevron-right"></i>';
      next.disabled = currentPage === totalPages;
      next.addEventListener('click', function(){ currentPage++; apply(); });
      pagination.appendChild(next);
    }
    filterBtns.forEach(function(btn){
      btn.addEventListener('click', function(){
        filterBtns.forEach(function(b){ b.classList.remove('active'); });
        btn.classList.add('active');
        activeRegiao = btn.getAttribute('data-regiao');
        currentPage = 1;
        apply();
      });
    });
    if (input) input.addEventListener('input', function(){ currentPage = 1; apply(); });
    apply();
  })();
  </script>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
