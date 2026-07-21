<?php
require_once __DIR__ . '/config.php';
$pageTitle       = 'Como se acede ao ensino superior em Portugal | Estudar em Portugal';
$pageDescription = 'Guia de acesso ao ensino superior em Portugal para estudantes brasileiros e internacionais: graduação, CTeSP, mestrado e doutoramento.';
$activeNav       = 'acesso';

$extraJsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type'       => 'Course',
            'name'        => 'Como se acede ao ensino superior em Portugal',
            'description' => $pageDescription,
            'provider'    => ['@type' => 'Organization', 'name' => 'Estudar em Portugal — Da Vinci × StudyWing', 'url' => SITE_URL],
            'url'         => SITE_URL . 'acesso-ensino-superior.php',
            'inLanguage'  => 'pt-PT',
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => SITE_URL],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Acesso ao Ensino Superior', 'item' => SITE_URL . 'acesso-ensino-superior.php'],
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
        <span class="eyebrow">ACESSO AO ENSINO SUPERIOR — PORTUGAL</span>
        <h1>Como se acede ao <span class="accent">ensino superior</span> em Portugal</h1>
        <p class="lede">Guia completo para estudantes brasileiros e internacionais que querem concorrer a cursos de graduação, CTeSP, mestrado e doutoramento em Portugal.</p>
        <div class="hero__ctas">
          <a href="#formulario" class="btn-pill btn-teal">Fale connosco</a>
        </div>
        <p style="color:var(--muted-on-dark);font-size:13px;">Sem compromisso.</p>
      </div>
      <div class="hero__art">
        <div class="hero__circle">
          <img src="<?= site_image('hero-estudante-lisboa') ?>" alt="Estudante internacional em Lisboa a estudar na universidade">
        </div>
      </div>
    </div>
  </section>

  <!-- ============ OS GRAUS ============ -->
  <section class="section">
    <div class="container">
      <div class="section-head">
        <div>
          <span class="eyebrow">Os graus</span>
          <h2>Os graus que podes tirar</h2>
        </div>
      </div>
      <div class="icon-row">
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-mortarboard"></i></div>
          <h3>CTeSP</h3>
          <p>Curso Técnico Superior Profissional — 2 anos de formação prática, dá acesso direto a licenciatura.</p>
        </div>
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-book"></i></div>
          <h3>Licenciatura</h3>
          <p>1º ciclo, 3 a 4 anos. Diploma de graduação que qualifica para trabalho ou prosseguimento em mestrado.</p>
        </div>
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-award"></i></div>
          <h3>Mestrado</h3>
          <p>2º ciclo, 1 a 2 anos. Especialização após licenciatura. Mestrados Integrados (5-6 anos) em áreas como Medicina incluem licenciatura.</p>
        </div>
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-beaker"></i></div>
          <h3>Doutoramento</h3>
          <p>3º ciclo, focado em investigação. Acesso após mestrado ou licenciatura com mérito, conforme a instituição.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ AS DUAS VIAS ============ -->
  <section class="section section-dark">
    <div class="container">
      <div class="section-head on-dark">
        <div>
          <h2>As duas vias de acesso</h2>
        </div>
      </div>
      <div class="content-block content-block--wide">
        <p style="color:var(--muted-on-dark);margin-bottom:32px;">O caminho para entrar no ensino superior em Portugal depende da tua nacionalidade.</p>

        <h3 style="color:var(--white);margin-bottom:16px;">1. Regime Geral</h3>
        <p style="color:var(--muted-on-dark);margin-bottom:24px;">Para candidatos com nacionalidade portuguesa, da União Europeia ou com dupla nacionalidade (PT/UE + outra). Segues o mesmo processo dos estudantes portugueses: exames nacionais do 12.º ano português e concurso unificado.</p>

        <h3 style="color:var(--white);margin-bottom:16px;">2. Concurso Especial para Estudantes Internacionais</h3>
        <p style="color:var(--muted-on-dark);margin-bottom:16px;">Para candidatos sem nacionalidade portuguesa nem de país da UE — o caso mais comum para brasileiros. Funciona com:</p>
        <ul style="color:var(--muted-on-dark);margin-bottom:24px;">
          <li>Vagas próprias e reservadas;</li>
          <li>Aceitação de nota ENEM como prova de acesso, sem exames portugueses obrigatórios;</li>
          <li>Processo de candidatura que varia por instituição.</li>
        </ul>
        <p style="color:var(--muted-on-dark);"><a href="concurso-especial-estudantes-internacionais.php" style="color:var(--teal);font-weight:600;">Ver o guia completo do Concurso Especial para Estudantes Internacionais →</a></p>
      </div>
    </div>
  </section>

  <!-- ============ PROPINAS E ENEM ============ -->
  <section class="section">
    <div class="container">
      <div class="section-head">
        <div>
          <h2>Propinas e ENEM</h2>
        </div>
      </div>
      <div class="content-block content-block--wide">
        <p>Como estudante internacional, pagas uma propina mais alta do que um aluno nacional — mas Portugal e o Brasil fazem parte da CPLP (Comunidade dos Países de Língua Portuguesa), o que dá acesso a descontos significativos que podem chegar até 45% em várias instituições.</p>
        <p>A nota do ENEM é aceite como prova de acesso em grande parte das universidades e politécnicos para o Concurso Especial, dispensando a necessidade de fazer exames portugueses. Isso simplifica o processo se já fizeste ou vais fazer o exame.</p>
      </div>
    </div>
  </section>

  <!-- ============ PASSO A PASSO ============ -->
  <section class="section section-dark">
    <div class="container">
      <div class="section-head on-dark">
        <div>
          <span class="eyebrow">Processo</span>
          <h2>Passo a passo</h2>
        </div>
      </div>
      <div class="steps">
        <div class="step">
          <div class="step__num">1</div>
          <h3>Escolha do curso e instituição</h3>
          <p>Pesquisa o curso e a universidade que se encaixa no teu projeto. Cada instituição tem vagas, prazos e requisitos próprios.</p>
        </div>
        <div class="step">
          <div class="step__num">2</div>
          <h3>Candidatura online</h3>
          <p>Regista-te na plataforma da instituição e submete a candidatura dentro do prazo da fase escolhida (normalmente 2-3 fases ao longo do ano).</p>
        </div>
        <div class="step">
          <div class="step__num">3</div>
          <h3>Envio de documentos</h3>
          <p>Upload de passaporte, histórico escolar, equivalência de ensino secundário, nota ENEM e outros documentos exigidos — com tradução e apostila quando necessário.</p>
        </div>
        <div class="step">
          <div class="step__num">4</div>
          <h3>Resultados e matrícula</h3>
          <p>Aguarda a divulgação de resultados. Se aprovado, formaliza a pré-matrícula e a matrícula definitiva dentro dos prazos.</p>
        </div>
        <div class="step">
          <div class="step__num">5</div>
          <h3>Visto de estudante</h3>
          <p>Inicia o processo de visto junto ao consulado português no Brasil. <a href="visto-de-estudante.php" style="color:var(--teal);font-weight:600;">Consulta o guia completo do visto →</a></p>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ CTA FINAL ============ -->
  <section class="section">
    <div class="container">
      <div class="article-cta">
        <h3>Queres saber por onde começar?</h3>
        <p>A Da Vinci acompanha-te na escolha e preparação académica; a StudyWing cuida da orientação e contacto com a universidade. Juntas, garantem que não perdes prazos nem envias documentação errada.</p>
        <a href="#formulario" class="btn-pill btn-teal">Fale connosco</a>
      </div>
    </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
