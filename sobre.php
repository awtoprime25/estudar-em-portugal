<?php
require_once __DIR__ . '/config.php';
$pageTitle       = 'Sobre Nós — Da Vinci × StudyWing | Lá Fora';
$pageDescription = 'Conhece a parceria Da Vinci × StudyWing: a maior rede de apoio escolar de Portugal e uma consultoria internacional especializada, juntas para levar brasileiros às melhores universidades portuguesas.';
$activeNav       = 'sobre';

$extraJsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type'       => 'AboutPage',
            'name'        => 'Sobre Nós — Da Vinci × StudyWing',
            'description' => $pageDescription,
            'url'         => SITE_URL . 'sobre.php',
            'mainEntity'  => ['@id' => SITE_URL . '#org'],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => SITE_URL],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Sobre nós', 'item' => SITE_URL . 'sobre.php'],
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
        <span class="eyebrow">A NOSSA HISTÓRIA</span>
        <h1>Duas marcas, <span class="accent">uma missão</span></h1>
        <p class="lede">A ponte entre o Brasil e as melhores universidades de Portugal. Uma parceria entre duas organizações que partilham a mesma missão: transformar o futuro de quem quer estudar lá fora.</p>
        <div class="hero__ctas">
          <a href="comparar.php#formulario" class="btn-pill btn-teal">Agendar consultoria gratuita</a>
        </div>
      </div>
      <div class="hero__art">
        <div class="hero__circle">
          <img src="<?= site_image('parceria-davinci-studywing') ?>" alt="Parceria Da Vinci × StudyWing">
        </div>
      </div>
    </div>
  </section>

  <!-- ============ 3 ÍCONES ============ -->
  <section class="section">
    <div class="container">
      <div class="icon-row">
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-trophy"></i></div>
          <h3>A rede de apoio escolar nº 1 em Portugal</h3>
          <p>Confiança de milhares de alunos e famílias em todo o país. Agora, com toda a qualidade Da Vinci, onde estiveres.</p>
        </div>
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-mortarboard"></i></div>
          <h3>Consultoria internacional especializada</h3>
          <p>A StudyWing acompanha candidaturas universitárias há mais de uma década, agora dedicada ao Brasil.</p>
        </div>
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-globe-americas"></i></div>
          <h3>Feito para brasileiros</h3>
          <p>Concurso Especial, desconto CPLP e acompanhamento em cada etapa, do Brasil até Portugal.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ DA VINCI ============ -->
  <section class="section section-dark">
    <div class="container">
      <div class="section-head on-dark">
        <div>
          <span class="eyebrow">Apoio escolar</span>
          <h2>Ginásios Da Vinci</h2>
        </div>
      </div>
      <div class="content-block content-block--wide" style="padding-top:12px;">
        <p>A Da Vinci é a maior rede de centros de apoio escolar de Portugal, com mais de 30 unidades em todo o país e mais de 90 mil alunos desde 2008.</p>
        <p>Ao longo do seu percurso, tem-se afirmado como referência nacional no apoio escolar, nas explicações e na formação, oferecendo soluções educativas adaptadas às necessidades de cada aluno.</p>
        <p>Com uma abordagem próxima, personalizada e orientada para resultados, a Da Vinci combina experiência, inovação pedagógica e acompanhamento contínuo — ajudando os alunos a superar desafios académicos e a construir um percurso de sucesso.</p>
      </div>
      <div class="stats-row" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat"><div class="stat__num">+30</div><div class="stat__label">unidades em Portugal</div></div>
        <div class="stat"><div class="stat__num">+90.000</div><div class="stat__label">alunos já apoiados</div></div>
        <div class="stat"><div class="stat__num"><?= date('Y') - 2008 ?> anos</div><div class="stat__label">de liderança em Portugal</div></div>
      </div>
    </div>
  </section>

  <!-- ============ STUDYWING ============ -->
  <section class="section">
    <div class="container">
      <div class="section-head">
        <div>
          <span class="eyebrow">Consultoria académica internacional</span>
          <h2>StudyWing</h2>
        </div>
      </div>
      <div class="content-block content-block--wide" style="padding-top:12px;">
        <p>A StudyWing é uma consultora especializada em candidaturas a universidades internacionais. Com uma vasta experiência acumulada, ajuda estudantes a encontrar e a candidatar-se às universidades certas para o seu perfil.</p>
        <p>Para o programa Lá Fora, essa experiência está agora dedicada aos brasileiros que querem estudar em Portugal: da escolha do curso e da universidade, à candidatura pelo Concurso Especial de Estudantes Internacionais, passando pelo visto e pela chegada.</p>
        <p>A experiência internacional da StudyWing é um pilar do programa — garante que cada aluno brasileiro tem acesso à orientação certa em cada etapa da candidatura.</p>
      </div>
    </div>
  </section>

  <!-- ============ A PARCERIA ============ -->
  <section class="section section-dark">
    <div class="container">
      <div class="section-head on-dark">
        <div>
          <span class="eyebrow">A parceria</span>
          <h2>Lá Fora: Da Vinci × StudyWing</h2>
        </div>
      </div>
      <div class="content-block content-block--wide" style="padding-top:12px;">
        <p>O <strong>Lá Fora</strong> é o resultado da parceria entre a Da Vinci e a StudyWing — uma colaboração que combina a presença nacional da Da Vinci em Portugal com a experiência internacional da StudyWing em candidaturas universitárias.</p>
        <p>Juntas, ajudam estudantes brasileiros a entrar em universidades portuguesas em cidades como Lisboa, Porto, Coimbra, Braga, Faro, Évora e Aveiro — com a mesma língua, diploma reconhecido em toda a Europa, e um acompanhamento próximo do primeiro contacto até à chegada.</p>
        <p class="also-europe">Também apoiamos candidaturas na Europa: <strong>Espanha · Irlanda · Países Baixos · Alemanha</strong></p>
      </div>
    </div>
  </section>

  <!-- ============ VALORES ============ -->
  <section class="section">
    <div class="container">
      <div class="section-head">
        <div>
          <span class="eyebrow">Os nossos valores</span>
          <h2>O que nos move</h2>
        </div>
      </div>
      <div class="icon-row" style="grid-template-columns:repeat(4,1fr);padding-top:12px;">
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-people"></i></div>
          <h3>Orientação humanizada</h3>
          <p>Não somos uma plataforma. Somos pessoas a falar com pessoas — cada aluno é único, e o seu plano também.</p>
        </div>
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-eye"></i></div>
          <h3>Transparência total</h3>
          <p>Explicamos cada passo, cada custo, cada opção. Sem surpresas, sem letras pequeninas.</p>
        </div>
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-graph-up-arrow"></i></div>
          <h3>Resultados comprovados</h3>
          <p>Experiência, orientação personalizada e conhecimento aprofundado do processo de candidatura.</p>
        </div>
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-compass"></i></div>
          <h3>Acompanhamento 360°</h3>
          <p>Da escolha do curso à matrícula, do visto ao primeiro dia de aulas — estamos lá sempre.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ CTA FINAL ============ -->
  <section class="section section-dark">
    <div class="container">
      <div class="article-cta">
        <h3>Pronto para dar o próximo passo?</h3>
        <p>Fala connosco e descobre como a Da Vinci × StudyWing pode ajudar-te a estudar em Portugal.</p>
        <a href="comparar.php#formulario" class="btn-pill btn-teal">Agendar consultoria gratuita</a>
      </div>
    </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
