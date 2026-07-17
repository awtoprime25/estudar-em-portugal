<?php
$pageTitle       = 'Estudar em Portugal para Brasileiros | Ginásios Da Vinci';
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

  <section class="section" id="cursos">
    <div class="container">
      <div class="section-head">
        <div>
          <span class="eyebrow">Cursos</span>
          <h2>Cursos em destaque</h2>
        </div>
      </div>
      <div class="icon-row" style="grid-template-columns:repeat(4,1fr);">
        <a href="curso-medicina.php" class="icon-card" style="text-decoration:none;color:inherit;">
          <div class="icon-card__glyph"><i class="bi bi-heart-pulse"></i></div>
          <h3>Medicina</h3>
          <p>Mestrado Integrado, 6 anos.</p>
        </a>
        <a href="curso-engenharia-informatica.php" class="icon-card" style="text-decoration:none;color:inherit;">
          <div class="icon-card__glyph"><i class="bi bi-cpu"></i></div>
          <h3>Engenharia Informática</h3>
          <p>Alta empregabilidade.</p>
        </a>
        <a href="curso-direito.php" class="icon-card" style="text-decoration:none;color:inherit;">
          <div class="icon-card__glyph"><i class="bi bi-bank"></i></div>
          <h3>Direito</h3>
          <p>Tradição desde 1290.</p>
        </a>
        <a href="curso-gestao.php" class="icon-card" style="text-decoration:none;color:inherit;">
          <div class="icon-card__glyph"><i class="bi bi-graph-up-arrow"></i></div>
          <h3>Gestão</h3>
          <p>ISEG, NOVA SBE e mais.</p>
        </a>
      </div>
      <p style="text-align:center;margin-top:28px;">
        <a href="curso-enfermagem.php">Enfermagem</a> ·
        <a href="curso-arquitetura.php">Arquitetura</a> ·
        <a href="curso-psicologia.php">Psicologia</a> ·
        <a href="curso-fisioterapia.php">Fisioterapia</a>
      </p>
      <p style="text-align:center;margin-top:16px;">
        <a href="cursos.php">Ver todos os cursos</a> ·
        <a href="universidades.php">Ver mapa de universidades</a>
      </p>
    </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
