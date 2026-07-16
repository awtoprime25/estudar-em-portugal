<?php
/**
 * comparar.php — Portugal vs Europa (Estudar no Estrangeiro)
 *
 * Página-irmã da index.php mas com FOCO em comparar os dois maiores
 * caminhos para estudantes brasileiros/internacionais: ficar em Portugal
 * ou ir para outros países da Europa através do programa StudyWing.
 *
 * Características:
 *   - SEO  : meta tags + canonical + OG/Twitter + Schema.org Article
 *   - AEO  : <answer> friendly titles, listas claras
 *   - GEO  : FAQPage Schema + QAPage candidates + metaDescription concisa
 *   - OGI  : og:image dedicado (ogi-comparar.png) — gerado via tools/gerar-imagens
 *   - CSRF : session token gerado ANTES do output para setcookie() funcionar.
 */
require_once __DIR__ . '/config.php';

// CSRF token — DEVE ser gerado antes do output HTML para setcookie e session_start funcionarem.
if (session_status() === PHP_SESSION_NONE) {
    @ini_set('session.use_strict_mode', '1');
    @ini_set('session.cookie_httponly', '1');
    @ini_set('session.cookie_samesite', 'Lax');
    @session_name('enp_sc');
    @session_start();
}
if (empty($_SESSION['enp_csrf']) || !is_string($_SESSION['enp_csrf'])) {
    $_SESSION['enp_csrf'] = bin2hex(random_bytes(16));
}
$enpCsrf = (string) $_SESSION['enp_csrf'];
setcookie('enp_csrf', $enpCsrf, [
    'expires'  => 0,
    'path'     => '/',
    'secure'   => (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off'),
    'httponly' => true,
    'samesite' => 'Lax',
]);

$pageTitle       = 'Portugal vs Estudar no Estrangeiro — Comparação Honesta 2026 | Lá Fora';
$pageDescription = 'Comparação completa para brasileiros: idioma, propinas, custo de vida, visto, equivalências e diploma. Portugal ou outros países da Europa? Tabela comparativa + formulário StudyWing.';
$activeNav       = 'comparar';
$ogImage         = SITE_URL . 'assets/images/ogi-comparar.png';
$extraJS         = 'assets/js/comparar.js';

$extraJsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Article',
            'headline' => 'Portugal vs Estudar no Estrangeiro: comparação honesta para 2026',
            'description' => $pageDescription,
            'image' => SITE_URL . 'assets/images/ogi-comparar.png',
            'url' => SITE_URL . 'comparar.php',
            'inLanguage' => 'pt-PT',
            'datePublished' => '2026-07-13',
            'dateModified' => '2026-07-13',
            'author' => ['@type' => 'Organization', 'name' => 'Lá Fora — Da Vinci × StudyWing', 'url' => SITE_URL],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Lá Fora — Da Vinci × StudyWing',
                'url' => SITE_URL,
                'logo' => ['@type' => 'ImageObject', 'url' => SITE_URL . 'assets/images/logotipo-studywing.png'],
            ],
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => SITE_URL . 'comparar.php'],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => [
                ['@type' => 'Question', 'name' => 'Posso usar a nota do ENEM para estudar em Portugal?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Sim. As universidades portuguesas (públicas e algumas privadas) aceitam a nota do ENEM como prova de ingresso, principalmente através do concurso institucional. Esta é uma das maiores vantagens de Portugal para candidatos brasileiros.']],
                ['@type' => 'Question', 'name' => 'Em Portugal as aulas são em português?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Sim. A esmagadora maioria dos cursos de licenciatura e mestrado em Portugal é lecionada em português, o que elimina a barreira linguística para brasileiros. Alguns mestrados e programas específicos são em inglês, principalmente nas áreas de Gestão e Engenharia.']],
                ['@type' => 'Question', 'name' => 'Quanto custa estudar em Portugal vs outros países da Europa?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Portugal tem propinas entre 700€ e 7.500€/ano conforme instituição e curso. Países Baixos cobram ~2.314€/ano para UE e ~10.000€ para brasileiros; Alemanha é praticamente gratuita (apenas taxas administrativas de ~300€); Reino Unido varia entre 10.000€ e 25.000£/ano.']],
                ['@type' => 'Question', 'name' => 'O diploma português vale em toda a Europa?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Sim. Pelo sistema ECTS (Espaço Europeu de Ensino Superior), qualquer diploma emitido por universidade portuguesa acreditada é reconhecível em todos os países da UE e do EEE. Mesmíssimo princípio aplica-se a diplomas da Holanda, Alemanha, Itália, Espanha, etc.']],
                ['@type' => 'Question', 'name' => 'Como funciona o visto de estudante para Portugal vs Europa?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Para brasileiros, Portugal oferece visto CPLP simplificado e visto de estudante com regras rotinadas. Para outros países europeus (Holanda, Alemanha, Itália, etc.), o processo é semelhante mas requer visto Schengen de estudante, com prazos e documentos próprios. A StudyWing acompanha toda a documentação em ambos os casos.']],
                ['@type' => 'Question', 'name' => 'Qual é a melhor opção para mim: Portugal ou outro país da Europa?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Depende do perfil: se valoriza idioma em português, custo acessível e inserção rápida, Portugal é ideal. Se procura programas 100% em inglês, especialização técnica (TU Delft, TU München), ou experiência internacional mais intensa, há destinos europeus mais adequados. Use o formulário StudyWing em baixo — falamos consigo em 24h com a análise personalizada.']],
            ],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => SITE_URL],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Comparar destinos', 'item' => SITE_URL . 'comparar.php'],
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db-helper.php';

// Pré-fabricados — quando o blog tiver conteúdo dinâmico, estes cards vão
// aparecer automaticamente no fundo da página (sem HTML extra aqui).
$comparar_links = [];
$cardsColecoes  = [];
function _bn_col_key(string $a, string $b): string {
    $x = mb_strtolower(trim($a), 'UTF-8');
    $y = mb_strtolower(trim($b), 'UTF-8');
    return ($x <= $y) ? ($x . '|' . $y) : ($y . '|' . $x);
}
foreach (comparar_links_publicados(18) as $r) {
    $k = _bn_col_key((string) $r['destino_a'], (string) $r['destino_b']);
    $cardsColecoes[$k][] = $r;
}
ksort($cardsColecoes);
?>

<main id="conteudo">

  <!-- ============ HERO ============ -->
  <section class="cmp-hero">
    <div class="container cmp-hero__grid">
      <div class="cmp-hero__copy">
        <span class="eyebrow"
              data-br="COMPARAÇÃO HONESTA — PARA BRASILEIROS"
              data-pt="COMPARAÇÃO HONESTA — PARA CANDIDATOS EUROPEUS">COMPARAÇÃO HONESTA — PARA BRASILEIROS</span>
        <h1 data-html
            data-br="<span class=&quot;accent&quot;>Portugal</span> ou <span class=&quot;accent-alt&quot;>Europa</span>?<br>Comparação sem rodeios."
            data-pt="<span class=&quot;accent&quot;>Portugal</span> ou <span class=&quot;accent-alt&quot;>Europa</span>?<br>Comparação sem rodeios.">Portugal ou Europa?<br>Comparação sem rodeios.</h1>
        <p class="lede"
           data-br="Idioma, propinas, custo de vida, visto, equivalências e diploma — colocamos lado a lado as duas grandes portas de entrada para a Europa, com a curadoria da Da Vinci × StudyWing."
           data-pt="Idioma, propinas, custo de vida, visto, equivalências e diploma — colocamos lado a lado as duas grandes portas de entrada para a Europa, com a curadoria da Da Vinci × StudyWing.">Idioma, propinas, custo de vida, visto, equivalências e diploma — colocamos lado a lado as duas grandes portas de entrada para a Europa, com a curadoria da Da Vinci × StudyWing.</p>
        <div class="cmp-hero__ctas">
          <a href="#tabela" class="btn-pill btn-teal">Ver tabela comparativa</a>
          <a href="#formulario" class="btn-pill btn-outline-light">Falar com StudyWing</a>
        </div>
        <div class="cmp-hero__badges">
          <span data-br="ENEM aceite em PT" data-pt="ENEM aceite em PT">ENEM aceite em PT</span>
          <span data-br="ECTS em toda a UE" data-pt="ECTS em toda a UE">ECTS em toda a UE</span>
          <span data-br="Acompanhamento StudyWing" data-pt="Acompanhamento StudyWing">Acompanhamento StudyWing</span>
        </div>
      </div>

      <div class="cmp-hero__art" aria-hidden="true">
        <div class="cmp-hero__split">
          <div class="cmp-hero__pane cmp-hero__pane--pt">
            <span class="cmp-hero__flag">🇵🇹</span>
            <span class="cmp-hero__label">Portugal</span>
          </div>
          <div class="cmp-hero__vs">VS</div>
          <div class="cmp-hero__pane cmp-hero__pane--eu">
            <span class="cmp-hero__flag">🇪🇺</span>
            <span class="cmp-hero__label">Europa</span>
          </div>
        </div>
        <img class="cmp-hero__image" src="<?= site_image('hero-comparar') ?>" alt="Comparação visual entre Portugal e o resto da Europa para estudar">
      </div>
    </div>
  </section>

  <!-- ============ TABELA COMPARATIVA ============ -->
  <section class="section" id="tabela">
    <div class="container">
      <div class="section-head">
        <div>
          <span class="eyebrow">Dimensão a dimensão</span>
          <h2>Tabela comparativa</h2>
          <p class="section-sub">7 critérios objetivos — sem marketing, sem 'achismos'.</p>
        </div>
        <a href="#formulario" class="see-all">Pedir análise personalizada →</a>
      </div>

      <div class="compare-table-wrapper">
        <table class="compare-table">
          <caption class="sr-only">Comparação direta entre estudar em Portugal e estudar em outros países da Europa.</caption>
          <thead>
            <tr>
              <th scope="col">Critério</th>
              <th scope="col" class="col-pt">
                <span class="flag-pill" data-loc="🇵🇹"><span class="flag-glyph">🇵🇹</span> Portugal</span>
              </th>
              <th scope="col" class="col-eu">
                <span class="flag-pill" data-loc="🇪🇺"><span class="flag-glyph">🇪🇺</span> Europa (outros)</span>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <th scope="row"><span class="check"><i class="bi bi-book"></i></span> Idioma de ensino</th>
              <td><strong>Português</strong> em ~95% dos cursos de licenciatura e mestrado. Zero barreira linguística para brasileiros.</td>
              <td>Inglês em quase todos os programas de topo. TOEFL/IELTS geralmente exigido. Holandês, alemão, italiano, etc., só no dia-a-dia.</td>
            </tr>
            <tr>
              <th scope="row"><span class="check"><i class="bi bi-mortarboard"></i></span> Acesso (nota ENEM)</th>
              <td><span class="good">✓</span> <strong>Sim, ENEM aceite</strong> pela maioria das universidades públicas e algumas privadas, especialmente em Engenharias e Gestão.</td>
              <td><span class="bad">✗</span> ENEM geralmente não é suficiente. É comum precisar de IB, A-Levels, ou Foundation Year de 1 ano.</td>
            </tr>
            <tr>
              <th scope="row"><span class="check"><i class="bi bi-cash-coin"></i></span> Propinas anuais (licenciatura, EUR)</th>
              <td><strong>700€ – 7.500€</strong> (públicas ~700–1.500€; privadas até 7.500€)</td>
              <td><strong>~0€ – 25.000€</strong><br>Alemanha ~300€; Holanda 2.314€ (UE) / ~10.000€ (BR); UK 10.000–25.000£.</td>
            </tr>
            <tr>
              <th scope="row"><span class="check"><i class="bi bi-house-door"></i></span> Custo de vida mensal (EUR)</th>
              <td><strong>700€ – 1.200€</strong> (Lisboa/Porto no topo; cidades médias e Algarve muito mais baixo)</td>
              <td><strong>900€ – 1.800€</strong> (Amsterdão, Londres, Paris no topo; cidades médias alemãs/espanholas mais acessíveis)</td>
            </tr>
            <tr>
              <th scope="row"><span class="check"><i class="bi bi-passport"></i></span> Visto / documentação</th>
              <td><span class="good">✓</span> <strong>Visto CPLP/estudante</strong> bastante simplificado para brasileiros; processo rotinado da Team StudyWing.</td>
              <td>Visto Schengen de estudante (UE). Mais papelada e prazos mais curtos; varia por país.</td>
            </tr>
            <tr>
              <th scope="row"><span class="check"><i class="bi bi-people"></i></span> Comunidade brasileira</th>
              <td><strong>Enorme</strong> (>300 mil brasileiros a viver em Portugal). Integração imediata, redes, grupos, eventos.</td>
              <td>Comunidade mais reduzida, mas presente nas grandes cidades. Imersão intercultural mais intensa.</td>
            </tr>
            <tr>
              <th scope="row"><span class="check"><i class="bi bi-award"></i></span> Reconhecimento do diploma</th>
              <td><strong>Válido em toda a UE</strong> via sistema ECTS. Reconhecimento automático em PT, ES, FR, DE, NL, IT, etc.</td>
              <td><strong>Válido em toda a UE</strong> via ECTS. Diploma de TU Delft/Múnich/Bolonha tem peso mundial.</td>
            </tr>
            <tr>
              <th scope="row"><span class="check"><i class="bi bi-briefcase"></i></span> Saídas profissionais</th>
              <td>Mercado lusófono forte (PT, BR, MO, AO). Salários iniciais mais baixos que NL/DE/UK, mas custo de vida acompanha.</td>
              <td>Mercado global e salários mais altos. Maior exposição internacional. Networking multilingue direto.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <p class="compare-table-cta">
        <strong data-br="Não tens a certeza?" data-pt="Não tens a certeza?">Não tens a certeza?</strong>
        <span data-br="Pedir a análise da StudyWing — em 24h traçamos o plano A, B e C para o teu perfil."
              data-pt="Pede a análise da StudyWing — em 24h traçamos o plano A, B e C para o teu perfil.">Pede a análise da StudyWing — em 24h traçamos o plano A, B e C para o teu perfil.</span>
        <a href="#formulario" class="btn-pill btn-navy">Quero a análise →</a>
      </p>
    </div>
  </section>

  <!-- ============ QUANDO ESCOLHER CADA CAMINHO ============ -->
  <section class="section section-dark" id="quando">
    <div class="container">
      <div class="section-head on-dark">
        <div>
          <span class="eyebrow" data-br="PARA QUEM É CADA OPÇÃO" data-pt="PARA QUEM É CADA OPÇÃO">PARA QUEM É CADA OPÇÃO</span>
          <h2 data-br="Escolhe Portugal se…" data-pt="Escolhe Portugal se…">Escolhe Portugal se…</h2>
        </div>
      </div>

      <div class="choose-grid">
        <article class="choose-card choose-card--pt">
          <div class="choose-card__head">
            <span class="flag-glyph">🇵🇹</span>
            <h3 data-br="Portugal é a tua escolha certa" data-pt="Portugal é a tua escolha certa">Portugal é a tua escolha certa</h3>
          </div>
          <ul>
            <li data-br="Queres candidatar-te só com a nota do ENEM (sem TOEFL/IELTS)"
                data-pt="Queres candidatar-te só com a nota do ENEM (sem TOEFL/IELTS)">Queres candidatar-te só com a nota do ENEM (sem TOEFL/IELTS)</li>
            <li data-br="Valorizas aulas em português (sem barreira linguística)"
                data-pt="Valorizas aulas em português (sem barreira linguística)">Valorizas aulas em português (sem barreira linguística)</li>
            <li data-br="Procuras uma transição suave (cultura próxima, grande comunidade brasileira)"
                data-pt="Procuras uma transição suave (cultura próxima, grande comunidade brasileira)">Procuras uma transição suave (cultura próxima, grande comunidade brasileira)</li>
            <li data-br="Orçamento mais controlado (propinas baixas, vida acessível)"
                data-pt="Orçamento mais controlado (propinas baixas, vida acessível)">Orçamento mais controlado (propinas baixas, vida acessível)</li>
            <li data-br="Sonhas com carreira em mercado lusófono (Brasil, Portugal, PALOP)"
                data-pt="Sonhas com carreira em mercado lusófono (Brasil, Portugal, PALOP)">Sonhas com carreira em mercado lusófono (Brasil, Portugal, PALOP)</li>
          </ul>
        </article>

        <article class="choose-card choose-card--eu">
          <div class="choose-card__head">
            <span class="flag-glyph">🇪🇺</span>
            <h3 data-br="Europa continental é a tua escolha certa" data-pt="Europa continental é a tua escolha certa">Europa continental é a tua escolha certa</h3>
          </div>
          <ul>
            <li data-br="Já tens IELTS/TOEFL e queres um curso 100% em inglês"
                data-pt="Já tens IELTS/TOEFL e queres um curso 100% em inglês">Já tens IELTS/TOEFL e queres um curso 100% em inglês</li>
            <li data-br="Miraste universidades de elite (TU Delft, TU München, Bolonha, Sciences Po)"
                data-pt="Miraste universidades de elite (TU Delft, TU München, Bolonha, Sciences Po)">Miraste universidades de elite (TU Delft, TU München, Bolonha, Sciences Po)</li>
            <li data-br="Procuras especialização técnica reconhecida globalmente"
                data-pt="Procuras especialização técnica reconhecida globalmente">Procuras especialização técnica reconhecida globalmente</li>
            <li data-br="Aceitas/adoras desafio linguístico e multicultural"
                data-pt="Aceitas/adoras desafio linguístico e multicultural">Aceitas/adoras desafio linguístico e multicultural</li>
            <li data-br="Carreira-alvo em empresa global ou setor com mercado internacional"
                data-pt="Carreira-alvo em empresa global ou setor com mercado internacional">Carreira-alvo em empresa global ou setor com mercado internacional</li>
          </ul>
        </article>
      </div>
    </div>
  </section>

  <!-- ============ FAQ ============ -->
  <section class="section" id="faq">
    <div class="container">
      <div class="section-head">
        <div>
          <span class="eyebrow">FAQ (GEO/AI Overviews)</span>
          <h2>Perguntas frequentes</h2>
          <p class="section-sub">As 6 perguntas reais que mais ouvimos no WhatsApp sobre Portugal vs outros países da Europa.</p>
        </div>
      </div>

      <div class="faq-accordion" data-faq>
        <?php
        $faq = [
          ['ENEM em Portugal?', 'Sim. Universidades públicas portuguesas e algumas privadas aceitam a nota do ENEM como prova de ingresso. É a maior atalho para brasileiros candidatarem-se sem exames adicionais.'],
          ['Aulas em inglês ou português?', 'Em Portugal: 95% em português. Em outros países europeus: a maioria dos programas top é em inglês (Holanda, UK, Países nórdicos), com exceção da Alemanha/Itália/Espanha onde há mais oferta em língua local.'],
          ['Custo real por ano?', 'Portugal 700–7.500€ propinas + 700–1.200€/mês vida. Europa: muito variável — DE ~300€ propinas, NL/UK 10–25k€/ano, com vida mais alta. Fazer as contas realistas antes de escolher é crítico.'],
          ['Visto de estudante — qual a diferença?', 'Portugal tem regime CPLP simplificado para brasileiros, o que encurta prazos e burocracia. Outros países exigem visto Schengen de estudante padrão, com regras próprias. Em qualquer caso, a StudyWing trata da papelada toda.'],
          ['O diploma vale em toda a Europa?', 'Sim, em ambos. Através do ECTS (Processo de Bolonha), qualquer diploma europeu é reconhecível em todos os países-membros. Diplomas não-europeus exigem reconhecimento específico e podem passar por conversão.'],
          ['Como decido entre os dois caminhos?', 'Depende de 4 fatores: ENEM/IELTS disponível, orçamento, carreira-alvo, e appetite linguístico. O formulário StudyWing abaixo pede exatamente isto; em 24h voltamos com o plano personalizado.'],
        ];
        foreach ($faq as $i => $q): ?>
        <details class="faq-item" data-faq-item <?= $i === 0 ? 'open' : '' ?>>
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

  <!-- ============ FORMULÁRIO STUDYWING (3 STEP) ============ -->
  <section class="section section-dark" id="formulario">
    <div class="container">
      <div class="formulario-grid">
        <div class="formulario-intro">
          <span class="eyebrow">StudyWing in 3 steps</span>
          <h2 data-br="Fala diretamente com um consultor StudyWing"
              data-pt="Fala diretamente com um consultor StudyWing">Fala diretamente com um consultor StudyWing</h2>
          <p data-br="Preenche os 3 passos e a equipa StudyWing contacta-te por email ou WhatsApp em menos de 24 horas. Tudo gratuito, sem compromisso."
             data-pt="Preenche os 3 passos e a equipa StudyWing contacta-te por email ou WhatsApp em menos de 24 horas. Tudo gratuito, sem compromisso.">Preenche os 3 passos e a equipa StudyWing contacta-te por email ou WhatsApp em menos de 24 horas. Tudo gratuito, sem compromisso.</p>
          <ul class="formulario-bullets">
            <li><i class="bi bi-shield-check"></i> <span>Sem spam, sem partilha com terceiros.</span></li>
            <li><i class="bi bi-clock-history"></i> <span>Resposta em ≤ 24 horas úteis.</span></li>
            <li><i class="bi bi-mortarboard"></i> <span>Especialistas em PT + 13 países europeus.</span></li>
          </ul>
        </div>

        <form id="studywing-form" class="studywing-form" action="ajax-comp.php" method="POST" novalidate data-studywing>
          <input type="hidden" name="csrf" value="<?= e($enpCsrf) ?>">
          <!-- ===== STEP 1: dados pessoais ===== -->
          <fieldset class="studywing-step is-active" data-step="1">
            <legend>Dados pessoais</legend>
            <div class="studywing-grid">
              <label>
                <span>Nome *</span>
                <input type="text" name="nome" required>
              </label>
              <label>
                <span>Sou… *</span>
                <select name="perfil" required>
                  <option value="">Escolhe…</option>
                  <option value="aluno">Eu sou o aluno</option>
                  <option value="pai-mae">Pai / Mãe do aluno</option>
                  <option value="avo">Avô / Avó</option>
                  <option value="outro">Outro familiar / responsável</option>
                </select>
              </label>
              <label>
                <span>Email *</span>
                <input type="email" name="email" required>
              </label>
              <label>
                <span>Telefone / WhatsApp *</span>
                <input type="tel" name="tel" placeholder="+55 11 9XXXX-XXXX" required>
              </label>
              <label class="studywing-grid--full">
                <span>Onde moras? *</span>
                <input type="text" name="localidade" required placeholder="Cidade, Estado / País">
              </label>
            </div>
            <button type="button" class="btn-pill btn-teal" data-next-step>
              Próximo →
            </button>
          </fieldset>

          <!-- ===== STEP 2: perfil académico ===== -->
          <fieldset class="studywing-step" data-step="2">
            <legend>Perfil académico</legend>
            <div class="studywing-grid">
              <label>
                <span>Onde estás no percurso escolar? *</span>
                <select name="ano" required>
                  <option value="">Escolhe…</option>
                  <option value="3-ano-em">3.º ano do Ensino Médio</option>
                  <option value="cursinho">Estou no cursinho / pré-vestibular</option>
                  <option value="formado">Já terminei o Ensino Médio</option>
                  <option value="graduacao">Já cursei faculdade (quero pós)</option>
                </select>
              </label>
              <label>
                <span>Já fiz ENEM? *</span>
                <select name="enem" required>
                  <option value="">Escolhe…</option>
                  <option value="sim">Sim, já fiz</option>
                  <option value="vou-fazer">Vou fazer este ano</option>
                  <option value="nao">Não fiz / não pretendo</option>
                </select>
              </label>
              <label>
                <span>Já fiz IELTS/TOEFL? *</span>
                <select name="ielts" required>
                  <option value="">Escolhe…</option>
                  <option value="sim">Sim, tenho nota</option>
                  <option value="vou-fazer">Vou fazer este ano</option>
                  <option value="nao">Não</option>
                </select>
              </label>
              <label>
                <span>Média do último ano letivo *</span>
                <input type="text" name="media" placeholder="ex: 8,4 / 100%" required>
              </label>
              <label class="studywing-grid--full">
                <span>Área(s) de estudo de interesse *</span>
                <input type="text" name="areas" placeholder="Medicina, Engenharia Informática, Gestão…" required>
              </label>
            </div>
            <div class="studywing-actions">
              <button type="button" class="btn-pill btn-outline-light" data-prev-step>← Voltar</button>
              <button type="button" class="btn-pill btn-teal" data-next-step>Próximo →</button>
            </div>
          </fieldset>

          <!-- ===== STEP 3: preferências ===== -->
          <fieldset class="studywing-step" data-step="3">
            <legend>Preferências</legend>
            <div class="studywing-grid">
              <label>
                <span>Destino preferido inicial *</span>
                <select name="destino" required>
                  <option value="">Escolhe…</option>
                  <option value="portugal">🇵🇹  Portugal (recomendado p/ BR)</option>
                  <option value="holanda">🇳🇱  Países Baixos / Holanda</option>
                  <option value="alemanha">🇩🇪  Alemanha</option>
                  <option value="reino-unido">🇬🇧  Reino Unido</option>
                  <option value="espanha">🇪🇸  Espanha</option>
                  <option value="italia">🇮🇹  Itália</option>
                  <option value="franca">🇫🇷  França</option>
                  <option value="republica-checa">🇨🇿  República Checa</option>
                  <option value="irlanda">🇮🇪  Irlanda</option>
                  <option value="aberto">🌍  Estou aberto — surpresa-me!</option>
                </select>
              </label>
              <label>
                <span>Quando queres começar? *</span>
                <select name="quando" required>
                  <option value="">Escolhe…</option>
                  <option value="2026-set">Setembro 2026 (intake principal)</option>
                  <option value="2027-jan">Janeiro / Fevereiro 2027</option>
                  <option value="2027-set">Setembro 2027</option>
                  <option value="ainda-decidi">Ainda estou a decidir</option>
                </select>
              </label>
              <label class="studywing-grid--full">
                <span>Outras observações?</span>
                <textarea name="obs" rows="3" placeholder="Dúvidas, contexto adicional, condicionantes…"></textarea>
              </label>
              <label class="studywing-grid--full studywing-consent">
                <input type="checkbox" name="termos" value="1" required>
                <span>Aceito os <a href="privacidade.php" target="_blank">termos de privacidade</a> e o consentimento para ser contactado pela equipa Da Vinci × StudyWing.</span>
              </label>
            </div>
            <div class="studywing-actions">
              <button type="button" class="btn-pill btn-outline-light" data-prev-step>← Voltar</button>
              <button type="submit" class="btn-pill btn-teal">Enviar → StudyWing</button>
            </div>
          </fieldset>

          <!-- progress bar -->
          <div class="studywing-progress" data-progress>
            <span class="studywing-progress__dot is-active" data-dot="1"></span>
            <span class="studywing-progress__line"></span>
            <span class="studywing-progress__dot" data-dot="2"></span>
            <span class="studywing-progress__line"></span>
            <span class="studywing-progress__dot" data-dot="3"></span>
          </div>

          <div class="studywing-message" data-feedback hidden></div>
        </form>
      </div>
    </div>
  </section>

  <?php if (!empty($cardsColecoes)): ?>
  <!-- ============ BLOG RELACIONADO — pré-fabricado pelo gerador_artigo_comparar.php ============ -->
  <section class="section" id="blog">
    <div class="container">
      <div class="section-head">
        <div>
          <span class="eyebrow">Do blog de comparações</span>
          <h2>Comparações detalhadas entre destinos</h2>
        </div>
        <a href="blog.php" class="see-all">Ver todo o blog →</a>
      </div>

      <div class="cmp-blog-grid">
        <?php
        $shown = 0;
        foreach ($cardsColecoes as $pair => $rows):
            $pairLabel = str_replace('|', ' & ', $pair);
            foreach ($rows as $r):
                if ($shown >= 6) break 2;
                $shown++;
        ?>
        <article class="cmp-blog-card">
          <a href="blog/<?= e($r['slug']) ?>.php" class="cmp-blog-card__media">
            <?php $cardImg = $r['imagem_url'] ?: 'assets/images/ogi-comparar.png'; ?>
            <img src="<?= e($cardImg) ?>" alt="<?= e($r['titulo']) ?>" loading="lazy">
            <span class="cmp-blog-card__pair"><?= e(ucwords($pairLabel)) ?></span>
          </a>
          <div class="cmp-blog-card__body">
            <h3><a href="blog/<?= e($r['slug']) ?>.php"><?= e($r['titulo']) ?></a></h3>
            <p><?= e(mb_substr((string)($r['descricao_meta'] ?? ''), 0, 140, 'UTF-8')) ?>…</p>
            <span class="cmp-blog-card__views"><i class="bi bi-eye"></i> <?= (int)$r['contador_views'] ?> leituras</span>
          </div>
        </article>
        <?php endforeach; endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
