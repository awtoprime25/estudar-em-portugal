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
 *   - OGI  : og:image dedicado (ogi-comparar.jpg) — gerado via tools/gerar-imagens
 *   - CSRF : token ENP_CSRF gerado em config.php (partilhado com o formulário
 *            StudyWing, que agora aparece no footer de todas as páginas).
 */
require_once __DIR__ . '/config.php';

$pageTitle       = 'Portugal ou Europa? | Comparação 2026';
$pageDescription = 'Compare Portugal com destinos europeus: idioma, propinas, custo de vida, visto e diploma para brasileiros. Veja qual opção combina com o seu perfil.';
$activeNav       = 'comparar';
$pageModified    = '2026-08-18';
$ogImage         = SITE_URL . 'assets/images/ogi-comparar.jpg';

$extraJsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Article',
            'headline' => 'Portugal vs Estudar no Estrangeiro: comparação honesta para 2026',
            'description' => $pageDescription,
            'image' => SITE_URL . 'assets/images/ogi-comparar.jpg',
            'url' => SITE_URL . 'comparar.php',
            'inLanguage' => 'pt-BR',
            'datePublished' => '2026-07-13',
            'dateModified' => $pageModified,
            'author' => ['@type' => 'Organization', 'name' => 'Estudar em Portugal — Da Vinci × StudyWing', 'url' => SITE_URL],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Estudar em Portugal — Da Vinci × StudyWing',
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
                ['@type' => 'Question', 'name' => 'O diploma português vale em toda a Europa?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Os diplomas portugueses integram o Espaço Europeu de Ensino Superior e usam o sistema ECTS, o que facilita o reconhecimento acadêmico em toda a UE e no EEE — o mesmo princípio aplica-se a diplomas da Holanda, Alemanha, Itália, Espanha, etc. Em profissões regulamentadas, pode ser necessário um processo específico de reconhecimento profissional.']],
                ['@type' => 'Question', 'name' => 'Como funciona o visto de estudante para Portugal vs Europa?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Para brasileiros, Portugal oferece visto de estudante com possível enquadramento CPLP; os requisitos exatos devem ser confirmados junto do consulado. Para outros países europeus (Holanda, Alemanha, Itália, etc.), o processo é semelhante mas requer um visto nacional de longa duração ou autorização própria, com prazos e documentos definidos por cada país. A StudyWing acompanha toda a documentação em ambos os casos.']],
                ['@type' => 'Question', 'name' => 'Qual é a melhor opção para mim: Portugal ou outro país da Europa?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Depende do perfil: se valoriza idioma em português, custo acessível e inserção rápida, Portugal é ideal. Se procura programas 100% em inglês, especialização técnica (TU Delft, TU München), ou experiência internacional mais intensa, há destinos europeus mais adequados. Use o formulário StudyWing abaixo — falamos com você em 24h com a análise personalizada.']],
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
           data-br="Idioma, mensalidade (propina), custo de vida, visto, equivalências e diploma — colocamos lado a lado as duas grandes portas de entrada para a Europa, com a curadoria da Da Vinci × StudyWing."
           data-pt="Idioma, propinas, custo de vida, visto, equivalências e diploma — colocamos lado a lado as duas grandes portas de entrada para a Europa, com a curadoria da Da Vinci × StudyWing.">Idioma, mensalidade (propina), custo de vida, visto, equivalências e diploma — colocamos lado a lado as duas grandes portas de entrada para a Europa, com a curadoria da Da Vinci × StudyWing.</p>
        <div class="cmp-hero__ctas">
          <a href="#formulario" class="btn-pill btn-flag">Falar com StudyWing</a>
        </div>
        <div class="cmp-hero__badges">
          <span data-br="ENEM aceito em PT" data-pt="ENEM aceite em PT">ENEM aceito em PT</span>
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
              <td><strong>Português</strong> em ~95% dos cursos de graduação (chamada de "licenciatura" em Portugal) e mestrado. Zero barreira linguística para brasileiros.</td>
              <td>Inglês em quase todos os programas de topo. TOEFL/IELTS geralmente exigido. Holandês, alemão, italiano, etc., só no dia-a-dia.</td>
            </tr>
            <tr>
              <th scope="row"><span class="check"><i class="bi bi-mortarboard"></i></span> Acesso (nota ENEM)</th>
              <td><span class="good">✓</span> <strong>Sim, ENEM aceito</strong> por diversas universidades e institutos politécnicos, conforme os requisitos e protocolos de cada instituição.</td>
              <td><span class="bad">✗</span> ENEM geralmente não é suficiente. É comum precisar de IB, A-Levels, ou Foundation Year de 1 ano.</td>
            </tr>
            <tr>
              <th scope="row"><span class="check"><i class="bi bi-cash-coin"></i></span> Quanto custa por ano (graduação, EUR)</th>
              <td><strong>700€ – 7.500€+</strong> (a "propina", como é chamada em Portugal; varia por instituição, curso e regime — o valor para estudante internacional costuma ser mais alto do que para estudante nacional)</td>
              <td><strong>~0€ – 25.000€</strong><br>Alemanha ~300€; Holanda 2.314€ (UE) / ~10.000€ (BR); UK 10.000–25.000£.</td>
            </tr>
            <tr>
              <th scope="row"><span class="check"><i class="bi bi-house-door"></i></span> Custo de vida mensal (EUR)</th>
              <td><strong>700€ – 1.200€</strong> (Lisboa/Porto no topo; cidades médias e Algarve muito mais baixo)</td>
              <td><strong>900€ – 1.800€</strong> (Amsterdão, Londres, Paris no topo; cidades médias alemãs/espanholas mais acessíveis)</td>
            </tr>
            <tr>
              <th scope="row"><span class="check"><i class="bi bi-passport"></i></span> Visto / documentação</th>
              <td><span class="good">✓</span> <strong>Visto de estudante</strong> para brasileiros, com possível enquadramento CPLP; requisitos e prazos confirmados junto ao consulado, acompanhados pela Team StudyWing.</td>
              <td>Visto nacional de estudante ou autorização própria. A documentação e os prazos variam por país.</td>
            </tr>
            <tr>
              <th scope="row"><span class="check"><i class="bi bi-people"></i></span> Comunidade brasileira</th>
              <td><strong>Enorme</strong> (>300 mil brasileiros vivendo em Portugal). Integração imediata, redes, grupos, eventos.</td>
              <td>Comunidade mais reduzida, mas presente nas grandes cidades. Imersão intercultural mais intensa.</td>
            </tr>
            <tr>
              <th scope="row"><span class="check"><i class="bi bi-award"></i></span> Reconhecimento do diploma</th>
              <td><strong>Integra o Espaço Europeu de Ensino Superior</strong> via sistema ECTS, o que facilita o reconhecimento acadêmico na UE. Profissões regulamentadas podem exigir processo específico.</td>
              <td><strong>Válido em toda a UE</strong> via ECTS. Diploma de TU Delft/Múnich/Bolonha tem peso mundial.</td>
            </tr>
            <tr>
              <th scope="row"><span class="check"><i class="bi bi-briefcase"></i></span> Oportunidades de carreira</th>
              <td>Mercado lusófono forte (PT, BR, MO, AO). Salários iniciais mais baixos que NL/DE/UK, mas custo de vida acompanha.</td>
              <td>Mercado global e salários mais altos. Maior exposição internacional. Networking multilíngue direto.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <p class="compare-table-cta">
        <strong data-br="Não tem certeza?" data-pt="Não tens a certeza?">Não tem certeza?</strong>
        <span data-br="Peça a análise da StudyWing — em 24h traçamos o plano A, B e C para o seu perfil."
              data-pt="Pede a análise da StudyWing — em 24h traçamos o plano A, B e C para o teu perfil.">Peça a análise da StudyWing — em 24h traçamos o plano A, B e C para o seu perfil.</span>
        <a href="#formulario" class="btn-pill btn-flag">Quero a análise →</a>
      </p>
      <div class="content-block content-block--wide" style="margin-top:32px;">
        <h3>Fontes para confirmar os requisitos</h3>
        <p>Valores, vistos e regras de candidatura mudam por país, instituição e ano. Consulte as fontes oficiais antes de tomar uma decisão:</p>
        <ul>
          <li><a href="https://www.dges.gov.pt/pt/pagina/concurso-especial-para-estudantes-internacionais" target="_blank" rel="noopener noreferrer">DGES — acesso de estudantes internacionais em Portugal</a></li>
          <li><a href="https://www.gov.pt/servicos/pedir-um-visto-de-residencia-para-estudo-intercambio-de-estudantes-estagio-profissional-ou-voluntariado" target="_blank" rel="noopener noreferrer">gov.pt — visto de residência para estudo</a></li>
          <li><a href="https://aima.gov.pt/pt/estudar" target="_blank" rel="noopener noreferrer">AIMA — residência para estudantes em Portugal</a></li>
        </ul>
        <p style="font-size:13px;color:var(--muted-on-light);">Conteúdo revisto em <?= e(date('d/m/Y', strtotime($pageModified))) ?>.</p>
      </div>
    </div>
  </section>

  <!-- ============ QUANDO ESCOLHER CADA CAMINHO ============ -->
  <section class="section section-dark" id="quando">
    <div class="container">
      <div class="section-head on-dark">
        <div>
          <span class="eyebrow" data-br="PARA QUEM É CADA OPÇÃO" data-pt="PARA QUEM É CADA OPÇÃO">PARA QUEM É CADA OPÇÃO</span>
          <h2 data-br="Escolha Portugal se…" data-pt="Escolhe Portugal se…">Escolha Portugal se…</h2>
        </div>
      </div>

      <div class="choose-grid">
        <article class="choose-card choose-card--pt">
          <div class="choose-card__head">
            <span class="flag-glyph">🇵🇹</span>
            <h3 data-br="Portugal é a sua escolha certa" data-pt="Portugal é a tua escolha certa">Portugal é a sua escolha certa</h3>
          </div>
          <ul>
            <li data-br="Quer se candidatar só com a nota do ENEM (sem TOEFL/IELTS)"
                data-pt="Queres candidatar-te só com a nota do ENEM (sem TOEFL/IELTS)">Quer se candidatar só com a nota do ENEM (sem TOEFL/IELTS)</li>
            <li data-br="Valoriza aulas em português (sem barreira linguística)"
                data-pt="Valorizas aulas em português (sem barreira linguística)">Valoriza aulas em português (sem barreira linguística)</li>
            <li data-br="Procura uma transição suave (cultura próxima, grande comunidade brasileira)"
                data-pt="Procuras uma transição suave (cultura próxima, grande comunidade brasileira)">Procura uma transição suave (cultura próxima, grande comunidade brasileira)</li>
            <li data-br="Orçamento mais controlado (mensalidades mais baixas, vida acessível)"
                data-pt="Orçamento mais controlado (propinas baixas, vida acessível)">Orçamento mais controlado (mensalidades mais baixas, vida acessível)</li>
            <li data-br="Sonha com carreira em mercado lusófono (Brasil, Portugal, PALOP)"
                data-pt="Sonhas com carreira em mercado lusófono (Brasil, Portugal, PALOP)">Sonha com carreira em mercado lusófono (Brasil, Portugal, PALOP)</li>
          </ul>
        </article>

        <article class="choose-card choose-card--eu">
          <div class="choose-card__head">
            <span class="flag-glyph">🇪🇺</span>
            <h3 data-br="Europa continental é a sua escolha certa" data-pt="Europa continental é a tua escolha certa">Europa continental é a sua escolha certa</h3>
          </div>
          <ul>
            <li data-br="Já tem IELTS/TOEFL e quer um curso 100% em inglês"
                data-pt="Já tens IELTS/TOEFL e queres um curso 100% em inglês">Já tem IELTS/TOEFL e quer um curso 100% em inglês</li>
            <li data-br="Mirou universidades de elite (TU Delft, TU München, Bolonha, Sciences Po)"
                data-pt="Miraste universidades de elite (TU Delft, TU München, Bolonha, Sciences Po)">Mirou universidades de elite (TU Delft, TU München, Bolonha, Sciences Po)</li>
            <li data-br="Procura especialização técnica reconhecida globalmente"
                data-pt="Procuras especialização técnica reconhecida globalmente">Procura especialização técnica reconhecida globalmente</li>
            <li data-br="Aceita/adora desafio linguístico e multicultural"
                data-pt="Aceitas/adoras desafio linguístico e multicultural">Aceita/adora desafio linguístico e multicultural</li>
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
          ['ENEM em Portugal?', 'Sim. Universidades públicas portuguesas e algumas privadas aceitam a nota do ENEM como prova de ingresso. É o maior atalho para brasileiros se candidatarem sem exames adicionais.'],
          ['Aulas em inglês ou português?', 'Em Portugal: 95% em português. Em outros países europeus: a maioria dos programas top é em inglês (Holanda, UK, Países nórdicos), com exceção da Alemanha/Itália/Espanha onde há mais oferta em língua local.'],
          ['Custo real por ano?', 'Em Portugal: 700–7.500€ de mensalidade (a "propina") por ano, mais 700–1.200€/mês de custo de vida. Na Europa em geral varia muito — Alemanha ~300€/ano, Holanda e Reino Unido entre 10 mil e 25 mil €/ano, com custo de vida mais alto. Fazer as contas direito antes de escolher é essencial.'],
          ['Visto de estudante — qual a diferença?', 'Portugal tem um visto nacional de estudo e regras próprias para brasileiros. Outros países exigem um visto nacional de longa duração ou autorização de residência, com requisitos definidos por cada país. Em qualquer caso, confirme os procedimentos junto das autoridades competentes.'],
          ['O diploma vale em toda a Europa?', 'Em ambos, o diploma integra o Espaço Europeu de Ensino Superior via ECTS (Processo de Bolonha), o que facilita o reconhecimento acadêmico entre países-membros — profissões regulamentadas podem exigir um processo específico. Diplomas não-europeus exigem reconhecimento específico e podem passar por conversão.'],
          ['Como decido entre os dois caminhos?', 'Depende de 4 fatores: ENEM/IELTS disponível, orçamento, carreira-alvo, e afinidade linguística. O formulário StudyWing abaixo pede exatamente isto; em 24h voltamos com o plano personalizado.'],
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
          <a href="artigo.php?slug=<?= urlencode((string) $r['slug']) ?>" class="cmp-blog-card__media">
            <?php $cardImg = $r['imagem_url'] ?: 'assets/images/ogi-comparar.jpg'; ?>
            <img src="<?= e($cardImg) ?>" alt="<?= e($r['titulo']) ?>" loading="lazy">
            <span class="cmp-blog-card__pair"><?= e(ucwords($pairLabel)) ?></span>
          </a>
          <div class="cmp-blog-card__body">
            <h3><a href="artigo.php?slug=<?= urlencode((string) $r['slug']) ?>"><?= e($r['titulo']) ?></a></h3>
            <p><?= e(mb_substr((string)($r['descricao_meta'] ?? ''), 0, 140, 'UTF-8')) ?>…</p>
            <?php $views = (int) $r['contador_views']; ?>
            <?php if ($views > 0): ?><span class="cmp-blog-card__views"><i class="bi bi-eye"></i> <?= $views ?> leituras</span><?php endif; ?>
          </div>
        </article>
        <?php endforeach; endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
