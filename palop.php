<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/davinci-units.php';
$pageTitle       = 'Estudar em Portugal para PALOP e CPLP | Angola, Moçambique, Cabo Verde e mais';
$pageDescription = 'Guia para estudantes de Angola, Moçambique, Cabo Verde, Guiné-Bissau, São Tomé e Príncipe e Timor-Leste que querem estudar em Portugal: Concurso Especial, desconto CPLP, cidades e cursos.';
$activeNav       = 'palop';

$extraJsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type'       => 'EducationalOrganization',
            'name'        => 'Estudar em Portugal — Programa PALOP/CPLP',
            'description' => $pageDescription,
            'provider'    => ['@type' => 'Organization', 'name' => 'Estudar em Portugal — Da Vinci × StudyWing', 'url' => SITE_URL],
            'url'         => SITE_URL . 'palop.php',
            'inLanguage'  => 'pt-PT',
            'areaServed'  => ['Angola', 'Moçambique', 'Cabo Verde', 'Guiné-Bissau', 'São Tomé e Príncipe', 'Timor-Leste'],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => SITE_URL],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'PALOP e CPLP', 'item' => SITE_URL . 'palop.php'],
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

require_once __DIR__ . '/includes/header.php';
?>

<main id="conteudo">

  <!-- ============ HERO ============ -->
  <section class="hero">
    <div class="container hero__grid">
      <div class="hero__copy">
        <span class="eyebrow">PARA ESTUDANTES PALOP E CPLP</span>
        <h1>O teu diploma português<br>começa em <span class="accent">Portugal</span>.</h1>
        <p class="lede">Angola, Moçambique, Cabo Verde, Guiné-Bissau, São Tomé e Príncipe, Timor-Leste — a mesma língua, o mesmo caminho de acesso, e uma equipa que te acompanha da candidatura à chegada.</p>
        <div class="hero__ctas">
          <a href="#formulario" class="btn-pill btn-flag">Fala connosco</a>
          <a href="destinos.php" class="btn-pill btn-outline-light">Ver cidades</a>
        </div>
      </div>
      <div class="hero__art">
        <div class="hero__circle">
          <img src="<?= site_image('hero-palop') ?>" alt="Estudante de um país PALOP com documentos de candidatura em Portugal">
        </div>
      </div>
    </div>
  </section>

  <!-- ============ QUEM SOMOS ============ -->
  <section class="section" id="quem-somos">
    <div class="container">
      <div class="section-head">
        <div>
          <span class="eyebrow">Quem somos</span>
          <h2>Da Vinci × StudyWing</h2>
        </div>
      </div>
      <p style="max-width:720px;line-height:1.7;">A <strong>Da Vinci</strong> é a maior rede de apoio escolar e explicações de Portugal — <strong>somos o nº 1 do país nesta área</strong>, com <?= lf_davinci_unidades() ?> unidades espalhadas pelo território e mais de 90 mil alunos apoiados desde 2008. Em parceria com a <strong>StudyWing</strong>, consultora internacional de admissões universitárias, acompanhamos também estudantes de países da CPLP que querem estudar em Portugal.</p>
      <p style="margin-top:28px;">
        <a href="sobre.php" class="btn-pill btn-navy">Conhecer a nossa história →</a>
      </p>
    </div>
  </section>

  <!-- ============ PORQUÊ PORTUGAL ============ -->
  <section class="section section-dark">
    <div class="container">
      <div class="section-head on-dark">
        <div>
          <span class="eyebrow">Vantagens</span>
          <h2>Porquê escolher Portugal</h2>
        </div>
      </div>
      <div class="icon-row">
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-translate"></i></div>
          <h3>Mesma língua</h3>
          <p>As aulas são em português — sem barreira linguística, sem ano de adaptação.</p>
        </div>
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-globe-europe-africa"></i></div>
          <h3>Diploma europeu</h3>
          <p>Integra o Espaço Europeu de Ensino Superior (ECTS), o que facilita o reconhecimento académico noutros países da UE.</p>
        </div>
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-signpost-split"></i></div>
          <h3>Via de acesso própria</h3>
          <p>O Concurso Especial para Estudantes Internacionais tem vagas reservadas, fora da concorrência com candidatos portugueses.</p>
        </div>
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-people"></i></div>
          <h3>Comunidade estabelecida</h3>
          <p>Comunidades de estudantes lusófonos já consolidadas nas principais cidades universitárias portuguesas.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ CONCURSO ESPECIAL + DESCONTO CPLP ============ -->
  <section class="section">
    <div class="container">
      <div class="section-head">
        <div>
          <span class="eyebrow">Como funciona</span>
          <h2>Concurso Especial e desconto CPLP</h2>
        </div>
      </div>
      <div class="content-block content-block--wide">
        <p>Sendo cidadão de um país sem nacionalidade portuguesa nem da União Europeia, o caminho de entrada é o <strong>Concurso Especial para Estudantes Internacionais</strong>: vagas próprias, reservadas, com processo de candidatura que varia por instituição.</p>
        <p>Por Angola, Moçambique, Cabo Verde, Guiné-Bissau, São Tomé e Príncipe e Timor-Leste fazerem parte da <strong>CPLP</strong> (Comunidade dos Países de Língua Portuguesa), muitas instituições aplicam um desconto sobre a propina que pode chegar até 45% — cada universidade define a sua própria política, por isso vale sempre confirmar junto da instituição escolhida.</p>
        <p style="margin-top:20px;"><a href="concurso-especial-estudantes-internacionais.php" style="color:var(--teal);font-weight:600;">Ver o guia completo do Concurso Especial →</a> · <a href="acesso-ensino-superior.php" style="color:var(--teal);font-weight:600;">Ver todas as vias de acesso →</a></p>
      </div>
    </div>
  </section>

  <!-- ============ CIDADES ============ -->
  <section class="section section-dark" id="destinos">
    <div class="container">
      <div class="section-head on-dark">
        <h2>Onde podes estudar</h2>
      </div>

      <div class="city-list">
        <a href="destino-lisboa.php" class="city-row">
          <span class="city-row__num">01</span>
          <h3>Lisboa</h3>
          <p>Capital vibrante, vida académica intensa.</p>
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
          <p>Sol o ano inteiro, Algarve à porta.</p>
          <div class="city-row__thumb"><img src="<?= site_image('cidade-faro') ?>" alt="Faro"></div>
        </a>
      </div>
      <p style="text-align:center;margin-top:32px;">
        <a href="destinos.php" style="color:var(--teal-light);font-weight:600;">Ver todas as cidades →</a>
      </p>
    </div>
  </section>

  <!-- ============ COMO FUNCIONA ============ -->
  <section class="section">
    <div class="container">
      <div class="section-head">
        <h2>Como te acompanhamos</h2>
      </div>

      <div class="steps">
        <div class="step">
          <div class="step__num">1</div>
          <h3>Conversa inicial</h3>
          <p>Perfil, país de origem, curso pretendido e objetivos — tudo começa com uma chamada.</p>
        </div>
        <div class="step">
          <div class="step__num">2</div>
          <h3>Curso e universidade</h3>
          <p>Opções compatíveis com o teu perfil, com vagas no Concurso Especial.</p>
        </div>
        <div class="step">
          <div class="step__num">3</div>
          <h3>Candidatura e documentos</h3>
          <p>Prazos, equivalências e desconto CPLP, sem dor de cabeça.</p>
        </div>
        <div class="step">
          <div class="step__num">4</div>
          <h3>Visto e chegada</h3>
          <p>Acompanhamento do processo de visto e primeiros passos em Portugal.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ FAQ ============ -->
  <section class="section section-dark">
    <div class="container">
      <div class="section-head on-dark">
        <h2>Perguntas frequentes</h2>
      </div>
      <div class="content-block content-block--wide">
        <h3 style="color:var(--white);">Preciso de visto para estudar em Portugal?</h3>
        <p style="color:var(--muted-on-dark);margin-bottom:24px;">Os requisitos variam consoante a nacionalidade. A nossa equipa acompanha o processo específico do teu país junto do consulado português — <a href="visto-de-estudante.php" style="color:var(--teal-light);font-weight:600;">consulta o guia geral de visto de estudante</a>.</p>

        <h3 style="color:var(--white);">O desconto CPLP aplica-se em todas as universidades?</h3>
        <p style="color:var(--muted-on-dark);margin-bottom:24px;">Não de forma automática — cada instituição define a sua própria política de desconto sobre a propina de estudante internacional. Confirmamos contigo as condições da universidade escolhida.</p>

        <h3 style="color:var(--white);">Posso candidatar-me estando ainda no meu país?</h3>
        <p style="color:var(--muted-on-dark);margin-bottom:0;">Sim — todo o processo de candidatura ao Concurso Especial é feito remotamente, antes de viajares para Portugal.</p>
      </div>
    </div>
  </section>

  <!-- ============ CTA FINAL ============ -->
  <section class="section">
    <div class="container">
      <div class="article-cta">
        <h3>Pronto para dar o próximo passo?</h3>
        <p>Fala connosco e descobre como a Da Vinci × StudyWing pode ajudar-te a estudar em Portugal, esteja de que país da CPLP for.</p>
        <a href="#formulario" class="btn-pill btn-flag">Fala connosco</a>
      </div>
    </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
