<?php
require_once __DIR__ . '/config.php';

$pageTitle       = 'Visto de estudante em Portugal | Guia';
$pageDescription = 'Veja quando pedir o visto de estudante para Portugal, quais documentos preparar, como tratar do consulado e como regularizar a residência na AIMA.';
$activeNav       = 'visto';
$pageModified    = '2026-08-18';

$faq = [
    ['Quando devo pedir o visto de estudante?',
     'Assim que você estiver admitido e matriculado num curso — o processo consular pode demorar vários meses, por isso o erro mais caro é deixar para tratar disso perto do início das aulas.'],
    ['Onde se pede o visto?',
     'Junto ao consulado português com jurisdição sobre a sua área de residência no Brasil. É preciso confirmar os requisitos e o agendamento diretamente com o consulado, já que os procedimentos podem ser atualizados.'],
    ['O que é a AIMA e quando preciso dela?',
     'A AIMA (Agência para a Integração, Migrações e Asilo) é a entidade portuguesa responsável pela residência de estrangeiros. Depois de chegar a Portugal com o visto de estudante, é junto à AIMA que você regulariza a sua residência para todo o período do curso.'],
    ['O desconto CPLP também vale para o visto?',
     'Não — o desconto CPLP é uma redução na mensalidade (propina, no termo oficial português), sem relação com o processo de visto. São dois processos completamente separados.'],
];

$extraJsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type'            => 'Article',
            'headline'         => 'Visto de Estudante em Portugal para Brasileiros',
            'description'      => $pageDescription,
            'inLanguage'       => 'pt-BR',
            'datePublished'    => '2026-08-18',
            'dateModified'     => $pageModified,
            'author'           => ['@type' => 'Organization', 'name' => 'Estudar em Portugal — Da Vinci × StudyWing', 'url' => SITE_URL],
            'publisher'        => ['@type' => 'Organization', 'name' => 'Estudar em Portugal — Da Vinci × StudyWing', 'url' => SITE_URL],
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => SITE_URL . 'visto-de-estudante.php'],
        ],
        [
            '@type'      => 'FAQPage',
            'mainEntity' => array_map(function ($q) {
                return ['@type' => 'Question', 'name' => $q[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q[1]]];
            }, $faq),
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => SITE_URL],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Visto de Estudante', 'item' => SITE_URL . 'visto-de-estudante.php'],
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

require_once __DIR__ . '/includes/header.php';
?>

<main id="conteudo">

  <section class="hero">
    <div class="container hero__grid">
      <div class="hero__copy">
        <span class="eyebrow">VISTO E RESIDÊNCIA</span>
        <h1>Visto de estudante em <span class="accent">Portugal</span></h1>
        <p class="lede">Depois de conseguir a vaga, falta um passo essencial: regularizar a sua entrada e residência em Portugal. Aqui explicamos como funciona, sem complicar.</p>
        <div class="hero__ctas">
          <a href="#formulario" class="btn-pill btn-flag">Agendar consultoria gratuita</a>
        </div>
      </div>
      <div class="hero__art">
        <div class="hero__circle">
          <img src="<?= site_image('hero-visto-estudante') ?>" alt="Estudante brasileiro com passaporte e visto de estudante em Portugal">
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="icon-row">
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-building"></i></div>
          <h3>Consulado no Brasil</h3>
          <p>O visto de estudante se trata junto ao consulado português com jurisdição sobre a sua residência.</p>
        </div>
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-clock-history"></i></div>
          <h3>Comece cedo</h3>
          <p>O processo consular pode demorar vários meses — se trata logo após a matrícula.</p>
        </div>
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-file-earmark-check"></i></div>
          <h3>AIMA em Portugal</h3>
          <p>Já em Portugal, regulariza a residência junto à Agência para a Integração, Migrações e Asilo.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="section section-dark">
    <div class="container">
      <div class="section-head on-dark">
        <div><h2>Como funciona, passo a passo</h2></div>
      </div>
      <div class="steps">
        <div class="step">
          <div class="step__num">1</div>
          <h3>Confirmação da vaga</h3>
          <p>Só depois de admitido e matriculado é que faz sentido avançar com o visto.</p>
        </div>
        <div class="step">
          <div class="step__num">2</div>
          <h3>Marcação no consulado</h3>
          <p>Agendamento junto ao consulado português com jurisdição sobre a sua área no Brasil.</p>
        </div>
        <div class="step">
          <div class="step__num">3</div>
          <h3>Documentação</h3>
          <p>Carta de admissão, histórico escolar legalizado, passaporte e os documentos que o consulado pedir.</p>
        </div>
        <div class="step">
          <div class="step__num">4</div>
          <h3>Viagem para Portugal</h3>
          <p>Com o visto emitido, viaja dentro do prazo de validade indicado.</p>
        </div>
        <div class="step">
          <div class="step__num">5</div>
          <h3>Regularização na AIMA</h3>
          <p>Já em Portugal, trata da residência para todo o período do curso.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="section-head">
        <div><h2>Documentos para legalizar antes de vir</h2></div>
      </div>
      <div class="content-block content-block--wide" style="padding-top:12px;">
        <p>Documentos emitidos no Brasil — como o histórico escolar e o diploma do ensino secundário — normalmente precisam de <strong>tradução</strong> (quando não estão em português, inglês, francês ou espanhol) e de <strong>reconhecimento</strong>, via apostila de Haia ou consulado português no Brasil.</p>
        <p class="warning-box">Este processo de tradução e reconhecimento pode demorar — não deixe para a última hora. Confirme sempre os requisitos exatos e atualizados diretamente com o consulado, já que os procedimentos podem mudar.</p>
        <p style="font-size:13px;color:var(--muted-on-light);">Conteúdo revisto em <?= e(date('d/m/Y', strtotime($pageModified))) ?>. Os requisitos oficiais podem mudar; confirme sempre a informação nas entidades competentes.</p>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="content-block content-block--wide">
        <h2>Fontes oficiais</h2>
        <ul>
          <li><a href="https://www.gov.pt/servicos/pedir-um-visto-de-residencia-para-estudo-intercambio-de-estudantes-estagio-profissional-ou-voluntariado" target="_blank" rel="noopener noreferrer">gov.pt — pedido de visto de residência para estudo</a></li>
          <li><a href="https://vistos.mne.gov.pt/pt/vistos-nacionais/informacao-geral/tipo-de-visto" target="_blank" rel="noopener noreferrer">Vistos Nacionais — informação oficial sobre tipos de visto</a></li>
          <li><a href="https://aima.gov.pt/pt/estudar" target="_blank" rel="noopener noreferrer">AIMA — estudar e autorização de residência</a></li>
        </ul>
      </div>
    </div>
  </section>

  <section class="section section-dark">
    <div class="container">
      <div class="section-head on-dark">
        <div><h2>Perguntas frequentes</h2></div>
      </div>
      <div class="faq-accordion">
        <?php foreach ($faq as $i => $q): ?>
        <details class="faq-item" <?= $i === 0 ? 'open' : '' ?>>
          <summary class="faq-question">
            <span><?= e($q[0]) ?></span>
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
          </summary>
          <div class="faq-answer">
            <p><?= e($q[1]) ?></p>
          </div>
        </details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="article-cta">
        <h3>Não fique sozinho nesta parte</h3>
        <p>A equipe Da Vinci × StudyWing te acompanha desde a matrícula até a chegada a Portugal — incluindo a orientação sobre visto e residência.</p>
        <a href="#formulario" class="btn-pill btn-flag">Agendar consultoria gratuita</a>
      </div>
    </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
