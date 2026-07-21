<?php
$pageTitle       = 'Destinos para Estudar em Portugal | Ginásios Da Vinci';
$pageDescription = 'Explore os 7 principais destinos para estudar em Portugal: Lisboa, Porto, Coimbra, Braga, Aveiro, Évora e Faro. Conheça as universidades, custos de vida e oportunidades de cada cidade.';
$activeNav       = 'destinos';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/subpage-data.php';
?>

<main id="conteudo">

  <section class="page-hero">
    <div class="container">
      <h1>Destinos para estudar em Portugal</h1>
      <p>Conheça os principais destinos: desde Lisboa, a capital vibrante, até Faro, com o clima mais ameno. Cada cidade oferece uma experiência única de vida académica e universidades de referência.</p>
    </div>
  </section>

  <section class="section section-dark">
    <div class="container">
      <div class="page-search page-search--dark">
        <i class="bi bi-search page-search__icon" aria-hidden="true"></i>
        <input type="search" id="destinoSearch" placeholder="Pesquisar cidade…" aria-label="Pesquisar destinos" autocomplete="off">
      </div>
      <p class="page-search__empty" id="destinoEmpty">Nenhuma cidade encontrada com esse nome.</p>
      <div class="city-list">
<?php
$num = 1;
foreach (DESTINOS as $slug => $city) {
    echo sprintf(
        '        <a href="destino-%s.php" class="city-row">
          <span class="city-row__num">%02d</span>
          <h3>%s</h3>
          <p>%s</p>
          <div class="city-row__thumb"><img src="%s" alt="%s"></div>
        </a>' . PHP_EOL,
        e($slug),
        $num,
        e($city['nome']),
        e($city['resumo']),
        e(site_image($city['imagem'])),
        e($city['nome'])
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
    if(!input) return;
    var rows = [].slice.call(document.querySelectorAll('.city-list .city-row'));
    function norm(s){ return (s||'').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g,''); }
    input.addEventListener('input', function(){
      var q = norm(input.value.trim());
      var any = false;
      rows.forEach(function(r){
        var match = q === '' || norm(r.textContent).indexOf(q) !== -1;
        r.style.display = match ? '' : 'none';
        if(match) any = true;
      });
      empty.style.display = any ? 'none' : 'block';
    });
  })();
  </script>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
