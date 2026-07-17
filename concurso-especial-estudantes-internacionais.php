<?php
require_once __DIR__ . '/config.php';
$pageTitle       = 'Concurso Especial de Acesso para Estudantes Internacionais em Portugal: Guia Completo para Brasileiros | Estudar em Portugal';
$pageDescription = 'Guia completo do Concurso Especial de Acesso a Portugal: quem se enquadra, exames, documentos, prazos, custos e desconto CPLP para brasileiros.';
$activeNav       = 'concurso-especial';

$faq = [
    ['O que é o Concurso Especial de Estudantes Internacionais em Portugal?',
     'É o processo de candidatura criado pelo Decreto-Lei n.º 36/2014 para quem não tem nacionalidade portuguesa nem de outro país da União Europeia. Funciona à parte do concurso nacional: as vagas são reservadas e os critérios de avaliação são diferentes, com cada universidade ou politécnico a organizar o seu próprio processo.'],
    ['Quem pode se candidatar pelo Concurso Especial?',
     'Quem não tem nacionalidade portuguesa nem de país da UE/EEE, não reside legalmente em Portugal há mais de dois anos, não é familiar de português ou de nacional de um Estado-membro da UE, e possui um diploma de ensino secundário que dê acesso ao ensino superior no seu país de origem.'],
    ['Preciso de fazer exames portugueses para entrar pelo Concurso Especial?',
     'Na maioria dos casos não. Quem se candidata pelo Concurso Especial pode usar o ENEM como prova de acesso, sem precisar de fazer os exames nacionais portugueses — ao contrário de quem tem dupla nacionalidade PT/UE, que segue o regime geral com exames obrigatórios.'],
    ['Quanto custa estudar em Portugal como estudante internacional?',
     'A propina de estudante internacional varia entre cerca de 3.500€ e mais de 16.000€ por ano, conforme o curso e a instituição. Por o Brasil pertencer à CPLP, muitas universidades aplicam um desconto que pode chegar a 45% sobre esse valor.'],
    ['O que é o desconto CPLP?',
     'É uma redução na propina de estudante internacional, aplicada por muitas universidades portuguesas a candidatos de países da Comunidade dos Países de Língua Portuguesa — incluindo o Brasil. É um desconto na propina, não uma bolsa de estudo.'],
];

$extraJsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type'         => 'Article',
            'headline'      => 'Concurso Especial de Acesso para Estudantes Internacionais em Portugal: o Guia Completo para Brasileiros',
            'description'   => $pageDescription,
            'url'           => SITE_URL . 'concurso-especial-estudantes-internacionais.php',
            'inLanguage'    => 'pt-PT',
            'datePublished' => '2026-07-15',
            'dateModified'  => '2026-07-15',
            'author'        => ['@type' => 'Organization', 'name' => 'Estudar em Portugal — Da Vinci × StudyWing', 'url' => SITE_URL],
            'publisher'     => ['@type' => 'Organization', 'name' => 'Estudar em Portugal — Da Vinci × StudyWing', 'url' => SITE_URL],
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => SITE_URL . 'concurso-especial-estudantes-internacionais.php'],
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
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Acesso ao Ensino Superior', 'item' => SITE_URL . 'concurso-especial-estudantes-internacionais.php'],
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

require_once __DIR__ . '/includes/header.php';
?>

<main id="conteudo">
  <section class="page-hero">
    <div class="container">
      <h1>Concurso Especial de Acesso para Estudantes Internacionais em Portugal: o Guia Completo para Brasileiros</h1>
      <p>Se você é brasileiro e quer estudar em Portugal, o caminho não é o mesmo dos estudantes portugueses. Existe um concurso específico, com regras, prazos e vantagens próprias — e entender como ele funciona é o primeiro passo para não perder vaga nem tempo.</p>
    </div>
  </section>

  <section class="content-block content-block--wide">

    <h2 id="o-que-e">O que é o Concurso Especial de Estudantes Internacionais</h2>
    <p>É o processo de candidatura criado pelo governo português (Decreto-Lei n.º 36/2014) para quem não tem nacionalidade portuguesa nem de outro país da União Europeia. Funciona à parte do concurso nacional que os alunos portugueses disputam: as vagas são reservadas, os critérios de avaliação são diferentes, e cada universidade ou politécnico organiza o seu próprio processo, dentro de um calendário nacional de referência.</p>
    <p>Na prática, isso significa que o estudante brasileiro não compete pelas mesmas vagas que um aluno que fez o ensino secundário em Portugal — ele concorre com outros candidatos internacionais, dentro de um número de vagas definido por cada instituição.</p>
    <p>Mas atenção: nem todo brasileiro entra por esse concurso. O caminho certo depende de você ter, ou não, nacionalidade portuguesa ou de outro país da União Europeia.</p>

    <h2 id="qual-caminho">Qual é o seu caminho: com ou sem nacionalidade portuguesa/UE</h2>

    <h3>Se você NÃO tem nacionalidade portuguesa nem de outro país da UE (caso mais comum)</h3>
    <p>Você se enquadra no Concurso Especial de Estudantes Internacionais se:</p>
    <ul>
      <li>Não tem nacionalidade portuguesa nem de nenhum país da União Europeia/Espaço Econômico Europeu;</li>
      <li>Não reside legalmente em Portugal há mais de dois anos ininterruptos até 1 de janeiro do ano de ingresso;</li>
      <li>Não é familiar de português ou de nacional de um Estado-membro da UE;</li>
      <li>Possui um diploma de ensino secundário (ou equivalente) do seu país de origem que dê direito a acessar o ensino superior lá.</li>
    </ul>
    <p>Nesse caminho, você:</p>
    <ul>
      <li>Concorre em vagas próprias, separadas dos candidatos portugueses;</li>
      <li>Pode usar o ENEM como prova de acesso, sem precisar fazer exames portugueses;</li>
      <li>Paga a propina de estudante internacional (mais alta, mas com possível desconto CPLP — veja abaixo).</li>
    </ul>
    <p>O restante deste guia, a partir daqui, é focado nesse caminho.</p>

    <h3>Se você TEM dupla nacionalidade — brasileira e portuguesa, ou brasileira e de outro país da UE</h3>
    <p>Você não pode se candidatar pelo Concurso Especial de Estudantes Internacionais — está expressamente fora desse regime. Seu caminho é o regime geral de acesso, o mesmo dos estudantes portugueses:</p>
    <ul>
      <li>Precisa fazer os exames nacionais portugueses (provas de ingresso), com base nas notas do 12.º ano ou equivalente — o ENEM não substitui essas provas aqui;</li>
      <li>Compete pelas vagas normais do curso, junto com os candidatos portugueses, e não pelas vagas reservadas a internacionais;</li>
      <li>Paga a propina de estudante nacional/UE, bem mais barata que a de internacional.</li>
    </ul>
    <p>Se esse é o seu caso, o conteúdo abaixo sobre o Concurso Especial não se aplica a você — <a href="contato.php" style="color:var(--teal);font-weight:600;">fale com a gente</a> para entender o processo do regime geral.</p>

    <h2 id="exames-regime-geral">Quais exames você precisa fazer (regime geral)</h2>
    <p>Não existe uma lista única — cada curso superior exige 1 ou 2 provas de ingresso específicas, definidas por cada instituição. Mas o princípio é sempre o mesmo: são os exames nacionais do 12.º ano português, os mesmos que qualquer aluno português faz no final do secundário.</p>
    <p>Alguns exemplos comuns por área (sempre confirme o par instituição/curso exato no Índice de Cursos da DGES, porque muda ano a ano):</p>
    <ul>
      <li><strong>Medicina, Engenharias, Ciências:</strong> Matemática A + Física e Química A ou Biologia e Geologia</li>
      <li><strong>Economia/Gestão:</strong> Matemática A + Economia A</li>
      <li><strong>Direito, Letras, Ciências Sociais:</strong> Português + uma opção (História A/B, Geografia A, etc.)</li>
      <li><strong>Arquitetura, Design:</strong> Desenho A + Matemática A ou Geometria Descritiva A</li>
    </ul>

    <h2 id="mais-facil">Qual caminho é "mais fácil"? Depende do que você está comparando</h2>
    <p>É comum pensar que não ter nacionalidade portuguesa/UE — e por isso entrar pelo Concurso Especial de Internacionais com o ENEM — é sempre o caminho mais fácil. Não é bem assim:</p>

    <h3>A favor do Concurso Especial (sem nacionalidade PT/UE)</h3>
    <ul>
      <li>Evita estudar e viajar para fazer o exame português;</li>
      <li>Aproveita uma nota que o aluno já tem ou vai ter de qualquer forma (ENEM);</li>
      <li>Menos barreira de conteúdo e vocabulário técnico da prova portuguesa.</li>
    </ul>
    <h3>Contra</h3>
    <ul>
      <li>As vagas são limitadas e reservadas — um número fixo para todos os internacionais concorrerem entre si, o que pode ser bem mais apertado do que parece em cursos concorridos (Medicina, por exemplo);</li>
      <li>A propina é bem mais alta, mesmo com o desconto CPLP;</li>
      <li>Evitar o exame português não significa necessariamente conseguir a vaga com mais facilidade.</li>
    </ul>
    <p>Em resumo: o Concurso Especial facilita evitar a prova portuguesa, mas não garante que seja mais fácil conseguir a vaga — isso depende do curso e da concorrência entre internacionais naquele ano. Para cursos menos concorridos, tende a ser o caminho mais tranquilo; para cursos concorridos, a disputa pelas vagas reservadas pode ser tão ou mais difícil que o regime geral.</p>

    <h2 id="na-pratica">O Concurso Especial na prática (para quem não tem nacionalidade portuguesa/UE)</h2>
    <p><strong>Documentos exigidos.</strong> Embora cada instituição possa pedir detalhes específicos, os documentos-base são:</p>
    <ul>
      <li>Passaporte;</li>
      <li>Diploma de conclusão do ensino secundário (histórico escolar do Brasil);</li>
      <li>Declaração ou certificado de equivalência do ensino secundário brasileiro ao português (quando exigido);</li>
      <li>Comprovante de nota do ENEM — muitas instituições aceitam o ENEM como prova de acesso, dispensando exames locais, desde que a nota mínima exigida seja atingida;</li>
      <li>Comprovante de conhecimento de português (ou inglês, se o curso for lecionado em inglês);</li>
      <li>Declaração, sob compromisso de honra, de que não possui nacionalidade portuguesa nem de outro país da UE.</li>
    </ul>
    <p>Documentos emitidos no Brasil normalmente precisam de tradução (quando não estão em português, inglês, francês ou espanhol) e de reconhecimento — via apostila de Haia ou consulado português no Brasil.</p>

    <h2 id="passo-a-passo">Como funciona o processo, passo a passo</h2>
    <ol>
      <li>Escolha do curso e da instituição — cada faculdade define o número de vagas para internacionais e pode ter calendário próprio.</li>
      <li>Candidatura online — feita na plataforma da própria instituição, dentro do prazo da fase escolhida (a maioria das universidades abre 2 a 3 fases ao longo do ano).</li>
      <li>Envio de documentos — upload dos documentos digitalizados dentro do prazo.</li>
      <li>Validação e ordenação das candidaturas — a instituição avalia a documentação e ordena os candidatos, normalmente com base na nota do ENEM ou de prova equivalente.</li>
      <li>Divulgação de resultados — lista de aprovados e, se houver, lista de espera.</li>
      <li>Pré-matrícula / reserva de vaga — dentro do prazo indicado, sob risco de perder a vaga.</li>
      <li>Matrícula definitiva — com entrega dos documentos originais e pagamento da propina.</li>
    </ol>
    <p>Depois de aprovado, ainda é preciso tratar do <a href="visto-de-estudante.php">visto de estudante</a> junto ao consulado português no Brasil, e, já em Portugal, regularizar a residência junto à AIMA.</p>

    <h2 id="quanto-custa">Quanto custa</h2>
    <p>A propina de estudante internacional é mais alta do que a de um aluno nacional, variando bastante por curso e instituição — em algumas faculdades vai de cerca de 3.500€ a mais de 16.000€ por ano.</p>
    <div class="highlight-box">
      <strong>Desconto CPLP:</strong> por o Brasil fazer parte da CPLP (Comunidade dos Países de Língua Portuguesa), muitas universidades oferecem desconto na propina — que pode chegar a 45% em algumas instituições. Vale sempre confirmar se a universidade escolhida aplica esse desconto e como solicitá-lo.
    </div>

    <h2 id="erros-comuns">Os erros mais comuns</h2>
    <div class="warning-box">
      <ul style="margin:0;">
        <li>Perder o prazo da fase de candidatura — não há tolerância;</li>
        <li>Não legalizar o histórico escolar a tempo (apostila/tradução demoram);</li>
        <li>Achar que ter parente português dá acesso automático — na verdade, isso muda o regime inteiro, e nem sempre é vantagem se as provas de ingresso portuguesas não foram feitas;</li>
        <li>Confundir o desconto CPLP com bolsa — é redução de propina, não é dinheiro para viver;</li>
        <li>Deixar para tratar do visto depois da matrícula — o processo consular pode levar meses.</li>
      </ul>
    </div>

    <h2 id="como-ajudamos">Como a Da Vinci e a StudyWing ajudam nesse processo</h2>
    <p>Esse processo tem muitas etapas, prazos diferentes por instituição e exigências burocráticas que mudam de universidade para universidade — e é exatamente aí que um acompanhamento especializado faz diferença.</p>
    <p><strong>Da Vinci</strong> acompanha o aluno na etapa académica: orientação sobre qual curso e instituição combinam com o perfil e a nota do ENEM do candidato, apoio na organização do histórico escolar e na equivalência de disciplinas, e preparação para os requisitos específicos de cada curso (como provas de pré-requisito em algumas áreas).</p>
    <p><strong>StudyWing</strong> cuida da parte de aconselhamento e conexão com a universidade: apoio na escolha entre as opções disponíveis, orientação sobre prazos e fases de candidatura, e uma consultoria gratuita para tirar dúvidas antes de decidir.</p>
    <p>Juntas, as duas evitam o erro mais caro do processo: perder prazo ou enviar documentação errada por falta de orientação. Em vez de tentar decifrar sozinho o edital de cada universidade, o aluno tem alguém acompanhando cada fase — da escolha do curso até a matrícula em Portugal.</p>

    <div class="article-cta">
      <h3>Quer saber se você se encaixa no Concurso Especial de Estudantes Internacionais?</h3>
      <p>Fale com um consultor da StudyWing e agende uma conversa gratuita.</p>
      <a href="contato.php" class="btn-pill btn-teal">Falar com um consultor</a>
    </div>

  </section>

  <!-- ============ FAQ (AEO/GEO) ============ -->
  <section class="section" id="faq">
    <div class="container" style="max-width:860px;">
      <div class="section-head">
        <div>
          <span class="eyebrow">Perguntas frequentes</span>
          <h2>FAQ — Concurso Especial de Estudantes Internacionais</h2>
        </div>
      </div>
      <div class="faq-accordion" data-faq>
        <?php foreach ($faq as $i => $q): ?>
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

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
