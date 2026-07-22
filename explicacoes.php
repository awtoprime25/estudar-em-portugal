<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/davinci-units.php';
$pageTitle       = 'Cursos Preparatórios para os Exames Nacionais Portugueses | Estudar em Portugal';
$pageDescription = 'Preparação online individual para os Exames Nacionais Portugueses — explicações, reforço e tutoria com professores portugueses, feita para brasileiros.';
$activeNav       = 'explicacoes';

$extraJsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type'       => 'Course',
            'name'        => 'Curso Preparatório para os Exames Nacionais Portugueses',
            'description' => $pageDescription,
            'provider'    => ['@type' => 'Organization', 'name' => 'Ginásios Da Vinci', 'sameAs' => 'https://www.ginasiosdavinci.com/'],
            'url'         => SITE_URL . 'explicacoes.php',
            'inLanguage'  => 'pt-PT',
            'hasCourseInstance' => [
                '@type'             => 'CourseInstance',
                'courseMode'        => 'online',
                'courseWorkload'    => 'PT1H',
                'instructor'        => ['@type' => 'Organization', 'name' => 'Ginásios Da Vinci'],
            ],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => SITE_URL],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Explicações', 'item' => SITE_URL . 'explicacoes.php'],
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
        <span class="eyebrow">EXAMES NACIONAIS — PORTUGAL</span>
        <h1>Cursos Preparatórios para os <span class="accent">Exames Nacionais</span> Portugueses</h1>
        <p class="lede">Aulas individuais, online, com professores portugueses. Preparação feita para brasileiros que querem estudar em Portugal.</p>
        <div class="hero__ctas">
          <a href="https://www.ginasiosdavinci.com/inscricao2026-27/" target="_blank" rel="noopener" class="btn-pill btn-teal">Marcar aula experimental gratuita</a>
        </div>
        <p style="color:var(--muted-on-dark);font-size:13px;">Sem compromisso.</p>
      </div>
      <div class="hero__art">
        <div class="hero__circle">
          <img src="<?= site_image('hero-estudante-lisboa') ?>" alt="Aula online de preparação para os exames nacionais portugueses">
        </div>
      </div>
    </div>
  </section>

  <!-- ============ 3 ÍCONES ============ -->
  <section class="section">
    <div class="container">
      <div class="icon-row">
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-person"></i></div>
          <h3>Individual</h3>
          <p>Aulas 1-a-1, ritmo adaptado a ti.</p>
        </div>
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-mortarboard"></i></div>
          <h3>Professor português</h3>
          <p>Quem domina o exame por dentro.</p>
        </div>
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-laptop"></i></div>
          <h3>100% online</h3>
          <p>Estuda do Brasil, sem sair de casa.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ REDE (números) ============ -->
  <section class="section section-dark">
    <div class="container">
      <div class="section-head on-dark">
        <div>
          <h2>A rede número 1 de explicações, reforço e tutoria de Portugal</h2>
        </div>
      </div>
      <div class="stats-row stats-row--3">
        <div class="stat"><div class="stat__num">+<?= lf_davinci_unidades() ?></div><div class="stat__label">unidades em Portugal</div></div>
        <div class="stat"><div class="stat__num">+90.000</div><div class="stat__label">alunos já preparados</div></div>
        <div class="stat"><div class="stat__num"><?= date('Y') - 2008 ?> anos</div><div class="stat__label">de liderança em Portugal</div></div>
      </div>
      <p style="color:var(--muted-on-dark);max-width:720px;line-height:1.7;">Somos a rede número 1 de aulas particulares, explicações, reforço e tutoria de Portugal — com <?= lf_davinci_unidades() ?> unidades no país e mais de 90 mil alunos já preparados. Agora, essa mesma experiência chega até você, no Brasil, 100% online.</p>
      <p style="color:var(--muted-on-dark);max-width:720px;line-height:1.7;font-size:14px;">Também em <a href="https://www.explicanet.com/" target="_blank" rel="noopener" style="color:var(--teal-light);">explicanet.com</a> e <a href="https://www.ginasiosdavinci.com/explicacoes-online-portugal/" target="_blank" rel="noopener" style="color:var(--teal-light);">ginasiosdavinci.com/explicações online</a>.</p>
    </div>
  </section>

  <!-- ============ BENEFÍCIOS ============ -->
  <section class="section">
    <div class="container">
      <div class="section-head">
        <div>
          <span class="eyebrow">Benefícios</span>
          <h2>O que torna a nossa preparação diferente</h2>
        </div>
      </div>
      <div class="content-block content-block--wide" style="padding-top:12px;">
        <ul>
          <li><strong>Aulas 100% individuais</strong> — Sem turmas, sem ritmo alheio. O plano de estudo é feito à tua medida, focado nas tuas dificuldades específicas.</li>
          <li><strong>Professores portugueses certificados</strong> — Conhecem o exame por dentro, sabem exatamente o que os examinadores procuram e como é a estrutura das provas.</li>
          <li><strong>Flexibilidade total de horário</strong> — Estuda do Brasil, no horário que te dá jeito, sem preocupações de fuso horário rígido.</li>
          <li><strong>Simulados com correção personalizada</strong> — Pratica com exames reais e recebe feedback direto do teu professor.</li>
          <li><strong>Acompanhamento contínuo</strong> — Não é uma aula isolada, é um plano até ao dia do exame.</li>
        </ul>
      </div>
    </div>
  </section>

  <!-- ============ COMO FUNCIONA ============ -->
  <section class="section section-dark">
    <div class="container">
      <div class="section-head on-dark">
        <div>
          <span class="eyebrow">Passo a passo</span>
          <h2>Como funciona</h2>
        </div>
      </div>
      <div class="steps">
        <div class="step">
          <div class="step__num">1</div>
          <h3>Aula experimental gratuita</h3>
          <p>Conheces o professor e definimos o teu nível.</p>
        </div>
        <div class="step">
          <div class="step__num">2</div>
          <h3>Plano de estudo personalizado</h3>
          <p>Baseado no exame e no prazo que tens.</p>
        </div>
        <div class="step">
          <div class="step__num">3</div>
          <h3>Aulas semanais individuais</h3>
          <p>100% online, com material de apoio incluído.</p>
        </div>
        <div class="step">
          <div class="step__num">4</div>
          <h3>Simulados regulares</h3>
          <p>Para acompanhar a tua evolução ao longo do tempo.</p>
        </div>
        <div class="step">
          <div class="step__num">5</div>
          <h3>Preparação final</h3>
          <p>Revisão intensiva antes do exame.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ CTA FINAL ============ -->
  <section class="section">
    <div class="container">
      <div class="article-cta">
        <h3>Marca a tua aula experimental gratuita</h3>
        <p>Preparação individual, com professores portugueses, para brasileiros que querem estudar em Portugal.</p>
        <a href="https://www.ginasiosdavinci.com/inscricao2026-27/" target="_blank" rel="noopener" class="btn-pill btn-teal">Quero começar agora</a>
      </div>
    </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
