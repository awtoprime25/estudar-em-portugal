<?php
$pageTitle       = 'Estudar em Portugal para Brasileiros | Lá Fora — Ginásios da Vinci';
$pageDescription = 'Seu diploma europeu começa em Portugal. Mesma língua, universidades reconhecidas em toda a Europa e acompanhamento completo da candidatura à chegada. Agende uma consultoria gratuita.';
$activeNav       = 'inicio';
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
          <a href="contato.php" class="btn-pill btn-teal">Agendar consultoria</a>
          <a href="blog.php" class="btn-pill btn-outline-light">Ler o blog</a>
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

  <section class="section" id="como-funciona">
    <div class="container">
      <div class="section-head">
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

  <section class="section section-dark" id="destinos">
    <div class="container">
      <div class="section-head on-dark">
        <h2>Onde você pode estudar</h2>
        <a href="#destinos" class="see-all">Ver todos os destinos →</a>
      </div>

      <div class="city-list">
        <div class="city-row">
          <span class="city-row__num">01</span>
          <h3>Lisboa</h3>
          <p>Capital vibrante, vida acadêmica intensa.</p>
          <div class="city-row__thumb"><img src="<?= site_image('cidade-lisboa') ?>" alt="Lisboa"></div>
        </div>
        <div class="city-row">
          <span class="city-row__num">02</span>
          <h3>Porto</h3>
          <p>Tradição universitária à beira do Douro.</p>
          <div class="city-row__thumb"><img src="<?= site_image('cidade-porto') ?>" alt="Porto"></div>
        </div>
        <div class="city-row">
          <span class="city-row__num">03</span>
          <h3>Coimbra</h3>
          <p>A cidade universitária por excelência.</p>
          <div class="city-row__thumb"><img src="<?= site_image('cidade-coimbra') ?>" alt="Coimbra"></div>
        </div>
        <div class="city-row">
          <span class="city-row__num">04</span>
          <h3>Braga</h3>
          <p>Jovem, acessível e em crescimento.</p>
          <div class="city-row__thumb"><img src="<?= site_image('cidade-braga') ?>" alt="Braga"></div>
        </div>
        <div class="city-row">
          <span class="city-row__num">05</span>
          <h3>Faro</h3>
          <p>Sol o ano inteiro, Algarve à porta e custo de vida mais baixo.</p>
          <div class="city-row__thumb"><img src="<?= site_image('cidade-faro') ?>" alt="Faro"></div>
        </div>
        <div class="city-row">
          <span class="city-row__num">06</span>
          <h3>Évora</h3>
          <p>Cidade património da UNESCO, tranquila e acessível.</p>
          <div class="city-row__thumb"><img src="<?= site_image('cidade-evora') ?>" alt="Évora"></div>
        </div>
        <div class="city-row">
          <span class="city-row__num">07</span>
          <h3>Aveiro</h3>
          <p>A "Veneza portuguesa", polo de engenharia e tecnologia.</p>
          <div class="city-row__thumb"><img src="<?= site_image('cidade-aveiro') ?>" alt="Aveiro"></div>
        </div>
      </div>

      <p class="also-europe">Também apoiamos candidaturas na Europa: <strong>Espanha · Irlanda · Países Baixos · Alemanha</strong></p>
    </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
