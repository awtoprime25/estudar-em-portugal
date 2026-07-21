<?php
$pageTitle       = 'Estudar em Portugal para Brasileiros | Ginásios Da Vinci';
$pageDescription = 'Seu diploma europeu começa em Portugal. Mesma língua, universidades reconhecidas em toda a Europa e acompanhamento completo da candidatura à chegada. Agende uma consultoria gratuita.';
$activeNav       = 'inicio';
require_once __DIR__ . '/includes/davinci-units.php';
require_once __DIR__ . '/includes/header.php';
?>

<main id="conteudo">

  <section class="hero">
    <div class="container hero__grid">
      <div class="hero__copy">
        <span class="eyebrow"
              data-br="ESTUDAR EM PORTUGAL — PARA BRASILEIROS"
              data-pt="ESTUDAR EM PORTUGAL — PARA CANDIDATOS EUROPEUS">ESTUDAR EM PORTUGAL — PARA BRASILEIROS</span>

        <h1 data-html
            data-br="Seu diploma europeu<br>começa em <span class=&quot;accent&quot;>Portugal</span>."
            data-pt="O teu diploma europeu<br>começa em <span class=&quot;accent&quot;>Portugal</span>.">Seu diploma europeu<br>começa em <span class="accent">Portugal</span>.</h1>

        <p class="lede"
           data-br="Mesma língua, universidades reconhecidas em toda a Europa e uma equipe que acompanha você da candidatura à chegada."
           data-pt="Mesma língua, universidades reconhecidas em toda a Europa e uma equipa que te acompanha da candidatura à chegada.">Mesma língua, universidades reconhecidas em toda a Europa e uma equipe que acompanha você da candidatura à chegada.</p>

        <div class="hero__ctas">
          <a href="#formulario" class="btn-pill btn-flag">Fale connosco</a>
          <a href="destinos.php" class="btn-pill btn-outline-light">Explorar cidades</a>
        </div>

        <div class="hero__badges">
          <span data-br="Nota do ENEM aceita" data-pt="Equivalências europeias">Nota do ENEM aceita</span>
          <span>Aulas em português</span>
          <span>Porta de entrada para a Europa</span>
        </div>
      </div>

      <div class="hero__art">
        <div class="hero__circle">
          <img src="<?= site_image('hero-estudante-lisboa') ?>" alt="Estudante brasileira em Lisboa, a caminho da universidade">
        </div>
      </div>
    </div>

    <div class="city-ticker">
      <div class="container">
        <span>Lisboa</span><span>Porto</span><span>Coimbra</span><span>Braga</span><span>Aveiro</span><span>Évora</span><span>Faro</span>
      </div>
    </div>
  </section>

  <section class="section" id="quem-somos">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow">Quem somos</span>
        <h2>Da Vinci × StudyWing</h2>
      </div>
      <p style="max-width:720px;line-height:1.7;">Somos a parceria entre a <strong>Da Vinci</strong>, a maior rede de apoio escolar e explicações de Portugal, e a <strong>StudyWing</strong>, consultora internacional de admissões universitárias. Juntas, acompanhamos o estudante brasileiro do primeiro contacto até à matrícula em Portugal — com <?= lf_davinci_unidades() ?> unidades no país a dar-nos o apoio local no terreno.</p>
      <p style="margin-top:28px;">
        <a href="sobre.php" class="btn-pill btn-navy">Conhecer a nossa história →</a>
      </p>
    </div>
  </section>

  <section class="section section-dark" id="como-funciona">
    <div class="container">
      <div class="section-head on-dark">
        <h2>Como funciona</h2>
      </div>

      <div class="steps">
        <div class="step">
          <div class="step__num">1</div>
          <h3>Conversa inicial</h3>
          <p>Perfil, objetivos e orçamento — tudo começa com uma chamada.</p>
        </div>
        <div class="step">
          <div class="step__num">2</div>
          <h3>Curso e universidade</h3>
          <p>Opções compatíveis com o seu perfil, em Portugal e na Europa.</p>
        </div>
        <div class="step">
          <div class="step__num">3</div>
          <h3>Candidatura e documentos</h3>
          <p>Prazos, equivalências e nota do ENEM, sem dor de cabeça.</p>
        </div>
        <div class="step">
          <div class="step__num">4</div>
          <h3>Visto e chegada</h3>
          <p>Visto de estudante e primeiros passos no país, com apoio local.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="section" id="assessoria">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow">Assessoria</span>
        <h2>Assessoria para Estudar em Portugal</h2>
      </div>

      <p style="max-width:720px;line-height:1.7;margin-bottom:36px;">Identificamos quais as melhores universidades e bolsas de acordo com o seu perfil — e acompanhamos-te em cada passo, da escolha do curso à chegada a Portugal.</p>

      <div class="icon-diamond-wrap">
        <div class="icon-diamond" id="assessoriaDiamond">
          <svg class="icon-diamond__lines" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
            <polygon points="50,6 94,50 50,94 6,50"></polygon>
          </svg>
          <button type="button" class="icon-diamond__rotate" id="assessoriaDiamondRotate" aria-label="Ver o próximo destaque">
            <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
          </button>
          <div class="icon-diamond__node" data-slot="top"></div>
          <div class="icon-diamond__node" data-slot="right"></div>
          <div class="icon-diamond__node" data-slot="bottom"></div>
          <div class="icon-diamond__node" data-slot="left"></div>
        </div>
        <div class="icon-diamond__detail" id="assessoriaDiamondDetail"></div>
      </div>

      <p style="text-align:center;margin-top:36px;">
        <a href="#formulario" class="btn-pill btn-flag">Fale connosco</a>
      </p>
    </div>
  </section>

  <script>
  (function(){
    var wrap = document.getElementById('assessoriaDiamond');
    if (!wrap) return;
    var detail = document.getElementById('assessoriaDiamondDetail');
    var rotateBtn = document.getElementById('assessoriaDiamondRotate');
    var slots = ['top', 'right', 'bottom', 'left'];
    var items = [
      { icon: 'bi-mortarboard', title: 'Universidades certas', text: 'Opções compatíveis com o teu perfil, clima académico e orçamento.' },
      { icon: 'bi-piggy-bank',  title: 'Bolsas e propinas',    text: 'Mapeamos financiamentos públicos, privados e parcerias internacionais.' },
      { icon: 'bi-check-circle', title: 'Candidatura sem erros', text: 'Documentos, prazos, equivalências ENEM: nós tratamos dos detalhes.' },
      { icon: 'bi-passport',    title: 'Do ENEM ao visto',     text: 'Desde a prova até à chegada e primeiros passos no país.' }
    ];
    var topIndex = 0;

    function render() {
      slots.forEach(function (slot, slotIdx) {
        var item = items[(topIndex + slotIdx) % items.length];
        var node = wrap.querySelector('[data-slot="' + slot + '"]');
        node.innerHTML = '<div class="icon-diamond__glyph"><i class="bi ' + item.icon + '" aria-hidden="true"></i></div><h3>' + item.title + '</h3>';
        node.classList.toggle('is-active', slot === 'top');
      });
      var active = items[topIndex];
      detail.innerHTML = '<div class="icon-diamond__detail-glyph"><i class="bi ' + active.icon + '" aria-hidden="true"></i></div><h3>' + active.title + '</h3><p>' + active.text + '</p>';
    }
    render();

    rotateBtn.addEventListener('click', function () {
      topIndex = (topIndex + 1) % items.length;
      wrap.classList.add('is-rotating');
      detail.classList.add('is-fading');
      setTimeout(function () {
        render();
        wrap.classList.remove('is-rotating');
        detail.classList.remove('is-fading');
      }, 180);
    });
  })();
  </script>

  <section class="section section-dark" id="destinos">
    <div class="container">
      <div class="section-head on-dark">
        <h2>Onde você pode estudar</h2>
      </div>

      <div class="city-list">
        <a href="destino-lisboa.php" class="city-row">
          <span class="city-row__num">01</span>
          <h3>Lisboa</h3>
          <p>Capital vibrante, vida acadêmica intensa.</p>
          <div class="city-row__thumb"><img src="<?= site_image('cidade-lisboa') ?>" alt="Lisboa"></div>
        </a>
        <a href="destino-porto.php" class="city-row">
          <span class="city-row__num">02</span>
          <h3>Porto</h3>
          <p>Tradição universitária à beira do Douro.</p>
          <div class="city-row__thumb"><img src="<?= site_image('cidade-porto') ?>" alt="Porto"></div>
        </a>
        <a href="destino-coimbra.php" class="city-row">
          <span class="city-row__num">03</span>
          <h3>Coimbra</h3>
          <p>A cidade universitária por excelência.</p>
          <div class="city-row__thumb"><img src="<?= site_image('cidade-coimbra') ?>" alt="Coimbra"></div>
        </a>
        <a href="destino-braga.php" class="city-row">
          <span class="city-row__num">04</span>
          <h3>Braga</h3>
          <p>Jovem, acessível e em crescimento.</p>
          <div class="city-row__thumb"><img src="<?= site_image('cidade-braga') ?>" alt="Braga"></div>
        </a>
        <a href="destino-faro.php" class="city-row">
          <span class="city-row__num">05</span>
          <h3>Faro</h3>
          <p>Sol o ano inteiro, Algarve à porta e custo de vida mais baixo.</p>
          <div class="city-row__thumb"><img src="<?= site_image('cidade-faro') ?>" alt="Faro"></div>
        </a>
        <a href="destino-evora.php" class="city-row">
          <span class="city-row__num">06</span>
          <h3>Évora</h3>
          <p>Cidade património da UNESCO, tranquila e acessível.</p>
          <div class="city-row__thumb"><img src="<?= site_image('cidade-evora') ?>" alt="Évora"></div>
        </a>
        <a href="destino-aveiro.php" class="city-row">
          <span class="city-row__num">07</span>
          <h3>Aveiro</h3>
          <p>A "Veneza portuguesa", polo de engenharia e tecnologia.</p>
          <div class="city-row__thumb"><img src="<?= site_image('cidade-aveiro') ?>" alt="Aveiro"></div>
        </a>
      </div>

      <p class="also-europe">Também apoiamos candidaturas na Europa: <strong>Espanha · Irlanda · Países Baixos · Alemanha</strong></p>
    </div>
  </section>

  <section class="section" id="explicacoes-preview">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow">Explicações</span>
        <h2>Cursos Preparatórios para os Exames Nacionais Portugueses</h2>
      </div>
      <p style="max-width:720px;line-height:1.7;">Aulas individuais, online ao vivo, com professores portugueses experientes. Somos nº1 em Portugal em explicações, reforço e tutoria — preparação feita para brasileiros que querem estudar em Portugal.</p>
      <p style="margin-top:24px;">
        <a href="explicacoes.php" class="btn-pill btn-teal">Marcar aula experimental gratuita</a>
      </p>
      <p style="margin-top:20px;font-size:13px;color:var(--muted-on-light);">Também em <a href="https://www.explicanet.com/" target="_blank" rel="noopener">explicanet.com</a> e <a href="https://www.ginasiosdavinci.com/explicacoes-online-portugal/" target="_blank" rel="noopener">ginasiosdavinci.com/explicações online</a>.</p>
    </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
