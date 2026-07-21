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
          <a href="#formulario" class="btn-pill btn-teal">Fale connosco</a>
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

  <section class="section" id="assessoria">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow">Assessoria</span>
        <h2>Assessoria para Estudar em Portugal</h2>
      </div>

      <p style="max-width:720px;line-height:1.7;margin-bottom:36px;">Identificamos quais as melhores universidades e bolsas de acordo com o seu perfil — e acompanhamos-te em cada passo, da escolha do curso à chegada a Portugal.</p>

      <div class="icon-row">
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-mortarboard"></i></div>
          <h3>Universidades certas</h3>
          <p>Opções compatíveis com o teu perfil, clima académico e orçamento.</p>
        </div>
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-piggy-bank"></i></div>
          <h3>Bolsas e propinas</h3>
          <p>Mapeamos financiamentos públicos, privados e parcerias internacionais.</p>
        </div>
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-check-circle"></i></div>
          <h3>Candidatura sem erros</h3>
          <p>Documentos, prazos, equivalências ENEM: nós tratamos dos detalhes.</p>
        </div>
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-passport"></i></div>
          <h3>Do ENEM ao visto</h3>
          <p>Desde a prova até à chegada e primeiros passos no país.</p>
        </div>
      </div>

      <p style="text-align:center;margin-top:36px;">
        <a href="#formulario" class="btn-pill btn-teal">Fale connosco</a>
      </p>
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

  <section class="section section-dark" id="quem-somos">
    <div class="container">
      <div class="section-head on-dark">
        <span class="eyebrow">Quem somos</span>
        <h2>Da Vinci × StudyWing</h2>
      </div>

      <div class="content-block content-block--wide" style="margin-bottom:36px;">
        <p>A <strong>Da Vinci</strong> é a rede número 1 em Portugal em serviços de apoio escolar e explicações — com mais de <?= lf_davinci_unidades() ?> unidades no país, mais de 90.000 alunos preparados e <?= date('Y') - 2008 ?> anos de liderança.</p>
      </div>

      <div class="content-block content-block--wide" style="margin-bottom:36px;">
        <p>A <strong>StudyWing</strong> é uma consultora internacional especializada em candidaturas a universidades. Juntas, acompanham o estudante brasileiro da escolha do curso à matrícula em Portugal.</p>
      </div>

      <div class="stats-row" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat"><div class="stat__num">+<?= lf_davinci_unidades() ?></div><div class="stat__label">unidades em Portugal</div></div>
        <div class="stat"><div class="stat__num">+90.000</div><div class="stat__label">alunos preparados</div></div>
        <div class="stat"><div class="stat__num"><?= date('Y') - 2008 ?> anos</div><div class="stat__label">de liderança</div></div>
      </div>
    </div>
  </section>

  <section class="section" id="testemunhos">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow">Testemunhos</span>
        <h2>Quem já fez connosco</h2>
      </div>

      <div class="icon-row">
        <div class="icon-card">
          <p><em>"Entrei em Enfermagem no Porto usando a nota do ENEM. A equipa tratou de tudo — desde a escolha da universidade até ao visto de estudante."</em></p>
          <p style="margin-top:16px;font-weight:600;">Larissa M.</p>
          <p style="font-size:13px;color:var(--muted-on-light);">Enfermagem, Universidade do Porto</p>
        </div>
        <div class="icon-card">
          <p><em>"Tinha dúvidas na candidatura e achava que não ia conseguir. Em 3 meses estava matriculada em Engenharia Informática. Obrigada!"</em></p>
          <p style="margin-top:16px;font-weight:600;">Carolina S.</p>
          <p style="font-size:13px;color:var(--muted-on-light);">Engenharia Informática, Instituto Superior Técnico, Lisboa</p>
        </div>
        <div class="icon-card">
          <p><em>"Sem o apoio deles, nunca teria entrado em Medicina. Foram fundamentais em cada etapa, especialmente com as equivalências."</em></p>
          <p style="margin-top:16px;font-weight:600;">Bruno T.</p>
          <p style="font-size:13px;color:var(--muted-on-light);">Medicina, Universidade de Coimbra</p>
        </div>
      </div>
    </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
