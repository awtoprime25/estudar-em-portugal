<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/davinci-units.php';
$pageTitle       = 'Aulas Particulares para os Exames Nacionais de Portugal | Estudar em Portugal';
$pageDescription = 'Aulas particulares e reforço online para os Exames Nacionais de Portugal — também chamadas de explicações — com professores portugueses, feitas para brasileiros.';
$activeNav       = 'explicacoes';
$pageSlug        = 'cursos-preparacao-exames';
$extraJS         = 'assets/js/explicacoes-form.js';

$extraJsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type'       => 'Course',
            'name'        => 'Curso Preparatório para os Exames Nacionais Portugueses',
            'description' => $pageDescription,
            'provider'    => ['@type' => 'Organization', 'name' => 'Ginásios Da Vinci', 'sameAs' => 'https://www.ginasiosdavinci.com/'],
            'url'         => SITE_URL . 'cursos-preparacao-exames',
            'inLanguage'  => 'pt-BR',
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
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Explicações', 'item' => SITE_URL . 'cursos-preparacao-exames'],
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
        <h1>Aulas Particulares de Preparação para <span class="accent">Exames Portugueses</span></h1>
        <p class="lede">Aulas individuais e 100% online, com professores portugueses — o que em Portugal é chamado de explicações. Preparação sob medida para brasileiros que querem estudar em Portugal.</p>
        <div class="hero__ctas">
          <a href="#form-explicacoes" class="btn-pill btn-teal">Marcar aula</a>
        </div>
      </div>
      <div class="hero__art">
        <div class="hero__circle">
          <img src="<?= site_image('hero-explicacoes') ?>" alt="Aula online de preparação para os exames nacionais portugueses">
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
          <p>Aulas 1-a-1, ritmo adaptado a você.</p>
        </div>
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-mortarboard"></i></div>
          <h3>Professor português</h3>
          <p>Quem domina o exame por dentro.</p>
        </div>
        <div class="icon-card">
          <div class="icon-card__glyph"><i class="bi bi-laptop"></i></div>
          <h3>100% online</h3>
          <p>Estude do Brasil, sem sair de casa.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ REDE (números) ============ -->
  <section class="section section-dark">
    <div class="container">
      <div class="section-head on-dark">
        <div>
          <h2>A rede número 1 de aulas particulares e reforço escolar de Portugal</h2>
        </div>
      </div>
      <div class="stats-row stats-row--3">
        <div class="stat"><div class="stat__num">+<?= lf_davinci_unidades() ?></div><div class="stat__label">unidades em Portugal</div></div>
        <div class="stat"><div class="stat__num">+90.000</div><div class="stat__label">alunos já preparados</div></div>
        <div class="stat"><div class="stat__num"><?= date('Y') - 2008 ?> anos</div><div class="stat__label">de liderança em Portugal</div></div>
      </div>
      <p style="color:var(--muted-on-dark);max-width:720px;line-height:1.7;">Somos a rede número 1 de aulas particulares e reforço escolar de Portugal — também chamados de explicações e tutoria por aqui — com <?= lf_davinci_unidades() ?> unidades no país e mais de 90 mil alunos já preparados. Agora, essa mesma experiência chega até você, no Brasil, 100% online.</p>
      <p style="color:var(--muted-on-dark);max-width:720px;line-height:1.7;font-size:14px;">Também em <a href="https://www.explicanet.com/" target="_blank" rel="noopener" style="color:var(--teal-light);">explicanet.com</a> e <a href="https://www.ginasiosdavinci.com/explicacoes-online-portugal/" target="_blank" rel="noopener" style="color:var(--teal-light);">ginasiosdavinci.com/explicações online</a>.</p>
    </div>
  </section>

  <!-- ============ BENEFÍCIOS ============ -->
  <section class="section">
    <div class="container">
      <div class="section-head">
        <div>
          <span class="eyebrow">Benefícios</span>
          <h2>O que torna nossa preparação diferente</h2>
        </div>
      </div>
      <div class="content-block content-block--wide" style="padding-top:12px;">
        <ul>
          <li><strong>Aulas 100% individuais</strong> — Sem turmas, sem ritmo alheio. O plano de estudo é feito à sua medida, focado nas suas dificuldades específicas.</li>
          <li><strong>Professores portugueses certificados</strong> — Conhecem o exame por dentro, sabem exatamente o que os examinadores procuram e como é a estrutura das provas.</li>
          <li><strong>Flexibilidade total de horário</strong> — Estude do Brasil, no horário que for melhor para você, sem preocupações de fuso horário rígido.</li>
          <li><strong>Simulados com correção personalizada</strong> — Pratique com exames reais e receba feedback direto do seu professor.</li>
          <li><strong>Acompanhamento contínuo</strong> — Não é uma aula isolada, é um plano até o dia do exame.</li>
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
          <h3>Marcar aula</h3>
          <p>Você conhece o professor e definimos o seu nível.</p>
        </div>
        <div class="step">
          <div class="step__num">2</div>
          <h3>Plano de estudo personalizado</h3>
          <p>Baseado no exame e no prazo que você tem.</p>
        </div>
        <div class="step">
          <div class="step__num">3</div>
          <h3>Aulas semanais individuais</h3>
          <p>100% online, com material de apoio incluído.</p>
        </div>
        <div class="step">
          <div class="step__num">4</div>
          <h3>Simulados regulares</h3>
          <p>Para acompanhar a sua evolução ao longo do tempo.</p>
        </div>
        <div class="step">
          <div class="step__num">5</div>
          <h3>Preparação final</h3>
          <p>Revisão intensiva antes do exame.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ FORMULÁRIO PRÓPRIO (marcação de aula) ============ -->
  <section class="section section-dark" id="form-explicacoes">
    <div class="container">
      <div class="formulario-grid">
        <div class="formulario-intro">
          <span class="eyebrow">Marcar aula</span>
          <h2>Marque a sua aula de preparação</h2>
          <p>Preencha o formulário e a equipe Da Vinci entra em contato por email ou WhatsApp em menos de 24 horas.</p>
          <ul class="formulario-bullets">
            <li><i class="bi bi-person"></i> <span>Aulas individuais, professores portugueses.</span></li>
            <li><i class="bi bi-clock-history"></i> <span>Resposta em ≤ 24 horas úteis.</span></li>
            <li><i class="bi bi-laptop"></i> <span>100% online, do Brasil ou de Portugal.</span></li>
          </ul>
        </div>

        <form id="explicacoes-form" class="studywing-form" action="ajax-explicacoes.php" method="POST" novalidate data-explicacoes-form>
          <input type="hidden" name="csrf" value="<?= e(ENP_CSRF) ?>">
          <input type="text" name="website" value="" autocomplete="off" tabindex="-1" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;" aria-hidden="true">

          <fieldset class="studywing-step is-active" data-step="1">
            <legend>Dados da marcação</legend>
            <div class="studywing-grid">
              <label>
                <span>Nome *</span>
                <input type="text" name="nome" required>
              </label>
              <label>
                <span>Email *</span>
                <input type="email" name="email" required>
              </label>
              <label>
                <span>Celular / WhatsApp *</span>
                <input type="tel" name="tel" placeholder="+55 11 9XXXX-XXXX" required>
              </label>
              <label>
                <span>Responsável de Educação</span>
                <input type="text" name="responsavel_educacao" placeholder="Se o estudante for menor de idade">
              </label>
              <label class="studywing-grid--full">
                <span>Cidade, estado e país onde mora? *</span>
                <input type="text" name="localidade" required placeholder="Cidade, Estado / País">
              </label>
              <label class="studywing-grid--full">
                <span>Qual a nacionalidade do estudante? *</span>
                <select name="nacionalidade" required>
                  <option value="">Escolha…</option>
                  <option value="brasileira">Brasileira</option>
                  <option value="portuguesa">Portuguesa</option>
                  <option value="dupla-br-pt">Dupla — brasileira e portuguesa/UE</option>
                  <option value="outra-cplp">Outra nacionalidade CPLP</option>
                  <option value="outra">Outra</option>
                </select>
              </label>
              <label class="studywing-grid--full">
                <span>Disciplina e Ano *</span>
                <select name="disciplina_ano" required>
                  <option value="">Escolha…</option>
                  <optgroup label="11.º ano">
                    <option value="alemao-11">Alemão</option>
                    <option value="biologia-geologia-11">Biologia e Geologia</option>
                    <option value="economia-a-11">Economia A</option>
                    <option value="espanhol-iniciacao-11">Espanhol – Iniciação</option>
                    <option value="espanhol-continuacao-11">Espanhol – Continuação</option>
                    <option value="filosofia-11">Filosofia</option>
                    <option value="fisica-quimica-a-11">Física e Química A</option>
                    <option value="frances-11">Francês</option>
                    <option value="geografia-a-11">Geografia A</option>
                    <option value="geometria-descritiva-a-11">Geometria Descritiva A</option>
                    <option value="historia-b-11">História B</option>
                    <option value="historia-cultura-artes-11">História da Cultura e das Artes</option>
                    <option value="ingles-11">Inglês</option>
                    <option value="italiano-11">Italiano</option>
                    <option value="latim-a-11">Latim A</option>
                    <option value="literatura-portuguesa-11">Literatura Portuguesa</option>
                    <option value="mandarim-11">Mandarim</option>
                    <option value="matematica-aplicada-css-11">Matemática Aplicada às Ciências Sociais</option>
                    <option value="matematica-b-11">Matemática B</option>
                  </optgroup>
                  <optgroup label="12.º ano">
                    <option value="desenho-a-12">Desenho A</option>
                    <option value="historia-a-12">História A</option>
                    <option value="matematica-a-12">Matemática A</option>
                    <option value="portugues-12">Português</option>
                    <option value="portugues-lingua-nao-materna-12">Português Língua Não Materna</option>
                    <option value="portugues-lingua-segunda-12">Português Língua Segunda</option>
                  </optgroup>
                </select>
              </label>
              <label class="studywing-grid--full">
                <span>Outras observações?</span>
                <textarea name="obs" rows="3" placeholder="Dúvidas, contexto adicional, condicionantes…"></textarea>
              </label>
              <label class="studywing-grid--full studywing-consent">
                <input type="checkbox" name="termos" value="1" required>
                <span>Aceito os <a href="https://www.ginasiosdavinci.com/privacidade-websites/" target="_blank" rel="noopener">termos de privacidade</a> e o consentimento para ser contatado pela equipe Da Vinci.</span>
              </label>
            </div>
            <div class="studywing-actions" style="justify-content:flex-end;">
              <button type="submit" class="btn-pill btn-teal">Marcar aula →</button>
            </div>
          </fieldset>

          <div class="studywing-message" data-feedback hidden></div>
        </form>
      </div>
    </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
