<?php
require_once __DIR__ . '/config.php';

$pageTitle       = 'Perguntas Frequentes — Estudar em Portugal | Lá Fora';
$pageDescription = 'Respostas às perguntas mais comuns de brasileiros sobre estudar em Portugal: Concurso Especial, ENEM, propinas, visto de estudante, exames e mais.';
$activeNav       = 'faq';

$categorias = [
    'Acesso e Concurso Especial' => [
        ['O que é o Concurso Especial de Estudantes Internacionais?',
         'É o processo de candidatura criado pelo Decreto-Lei n.º 36/2014 para quem não tem nacionalidade portuguesa nem de outro país da União Europeia. Funciona à parte do concurso nacional: as vagas são reservadas e os critérios de avaliação são diferentes, com cada universidade ou politécnico a organizar o seu próprio processo.'],
        ['Preciso de fazer exames portugueses para entrar?',
         'Na maioria dos casos não — quem se candidata pelo Concurso Especial pode usar o ENEM como prova de acesso, sem precisar de fazer os exames nacionais portugueses. Exceção: quem tem dupla nacionalidade brasileira e portuguesa/UE segue o regime geral, com exames obrigatórios.'],
        ['Tenho dupla nacionalidade (brasileira e portuguesa/UE) — o que muda?',
         'Não podes usar o Concurso Especial de Estudantes Internacionais. O teu caminho é o regime geral, o mesmo dos estudantes portugueses: exames nacionais obrigatórios e propina de estudante nacional/UE (bem mais baixa).'],
        ['Como é o acesso por CTeSP, licenciatura, mestrado ou doutorado?',
         'O Concurso Especial cobre sobretudo o primeiro acesso ao ensino superior (CTeSP e licenciatura). Para mestrado e doutorado, o processo é normalmente uma candidatura direta à instituição, com análise do percurso académico anterior — os requisitos exatos variam por curso e universidade.'],
    ],
    'Custos e Propinas' => [
        ['Quanto custa estudar em Portugal?',
         'A propina de estudante internacional varia entre cerca de 3.500€ e mais de 16.000€ por ano, conforme o curso e a instituição. A isso soma-se o custo de vida, entre 500€ e 1.200€/mês dependendo da cidade.'],
        ['O que é o desconto CPLP?',
         'É uma redução na propina de estudante internacional, aplicada por muitas universidades portuguesas a candidatos de países da Comunidade dos Países de Língua Portuguesa — incluindo o Brasil — que pode chegar a 45%. É desconto na propina, não é bolsa de estudo.'],
        ['Existem bolsas de estudo para brasileiros em Portugal?',
         'Existem bolsas de mérito e bolsas sociais em algumas instituições, mas não são garantidas nem automáticas. O mais comum e confiável é o desconto CPLP na propina — vale sempre confirmar caso a caso com a universidade escolhida.'],
    ],
    'Visto e Documentação' => [
        ['Como funciona o visto de estudante para Portugal?',
         'Depois de admitido, trata-se o visto de estudante junto ao consulado português no Brasil. Já em Portugal, é preciso regularizar a residência junto à AIMA (Agência para a Integração, Migrações e Asilo). Consulta a nossa página de Visto de Estudante para o passo a passo completo.'],
        ['Quando devo começar o processo de visto?',
         'O mais cedo possível — logo depois da matrícula, nunca depois. O processo consular pode demorar vários meses, e deixar para tratar disso perto do início das aulas é um dos erros mais comuns e mais caros.'],
        ['Que documentos preciso legalizar antes de vir para Portugal?',
         'O histórico escolar e o diploma do ensino secundário normalmente precisam de tradução (se não estiverem em português, inglês, francês ou espanhol) e de reconhecimento, via apostila de Haia ou consulado português no Brasil.'],
    ],
    'Explicações e Exames' => [
        ['As aulas de preparação são para os exames portugueses ou para o ENEM?',
         'São para os Exames Nacionais Portugueses, focadas em quem segue o regime geral (dupla nacionalidade) ou quem prefere reforçar conteúdos mesmo entrando pelo Concurso Especial. São aulas individuais, online, com professores portugueses.'],
        ['As aulas são ao vivo ou gravadas?',
         'São aulas individuais ao vivo, online, 1-a-1 com o professor — não são vídeos gravados.'],
        ['Quais exames posso precisar fazer, conforme o curso?',
         'Depende do curso: Medicina/Engenharias/Ciências pedem normalmente Matemática A + Física e Química A ou Biologia e Geologia; Economia/Gestão pedem Matemática A + Economia A; Direito/Letras/Ciências Sociais pedem Português + uma opção; Arquitetura/Design pedem Desenho A + Matemática A ou Geometria Descritiva A. Confirma sempre o par instituição/curso exato antes de decidir.'],
    ],
    'Cidades e Universidades' => [
        ['Qual é a melhor cidade para estudar em Portugal?',
         'Depende do perfil: Lisboa e Porto têm mais universidades e oferta de cursos; Coimbra tem a vida académica mais tradicional; Braga, Aveiro, Évora e Faro são mais acessíveis e mais calmas. Explora o nosso mapa de universidades para comparar.'],
        ['Quantas universidades existem em Portugal?',
         'Portugal tem universidades públicas em quase todas as regiões, além de institutos politécnicos e universidades privadas. Explora o nosso mapa completo de universidades para veres todas, filtradas por cidade.'],
        ['As aulas em Portugal são em português ou em inglês?',
         'A esmagadora maioria dos cursos de licenciatura e mestrado em Portugal é lecionada em português — o mesmo português que já falas, sem barreira de idioma. Alguns mestrados específicos são em inglês.'],
    ],
    'Portugal vs. Outros Países' => [
        ['Portugal ou outro país da Europa — qual escolher?',
         'Se valorizas idioma em português, custo mais acessível e inserção rápida, Portugal costuma ser a opção mais direta. Se procuras programas 100% em inglês ou especializações técnicas muito específicas, outros destinos europeus podem fazer mais sentido. Usa o nosso comparador para veres lado a lado.'],
        ['O diploma português vale em toda a Europa?',
         'Sim. Pelo sistema ECTS (Espaço Europeu de Ensino Superior, Processo de Bolonha), qualquer diploma emitido por uma universidade portuguesa acreditada é reconhecido em todos os países da UE e do EEE.'],
    ],
];

$faqSchema = [];
foreach ($categorias as $lista) {
    foreach ($lista as $q) {
        $faqSchema[] = ['@type' => 'Question', 'name' => $q[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q[1]]];
    }
}

$extraJsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        ['@type' => 'FAQPage', 'mainEntity' => $faqSchema],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => SITE_URL],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Perguntas Frequentes', 'item' => SITE_URL . 'faq.php'],
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

require_once __DIR__ . '/includes/header.php';
?>

<main id="conteudo">
  <section class="page-hero">
    <div class="container">
      <h1>Perguntas Frequentes</h1>
      <p>Tudo o que brasileiros mais perguntam sobre estudar em Portugal — acesso, custos, visto, exames e escolha de universidade.</p>
    </div>
  </section>

  <section class="container" style="padding-top:56px;padding-bottom:72px;">
    <?php foreach ($categorias as $categoria => $lista): ?>
    <div style="margin-bottom:48px;">
      <h2 style="font-size:22px;font-weight:700;margin-bottom:16px;"><?= e($categoria) ?></h2>
      <div class="faq-accordion">
        <?php foreach ($lista as $i => $q): ?>
        <details class="faq-item">
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
    <?php endforeach; ?>

    <div class="article-cta">
      <h3>Não encontraste a tua resposta?</h3>
      <p>Fala com a equipa Da Vinci × StudyWing e tira as tuas dúvidas numa consultoria gratuita.</p>
      <a href="comparar.php#formulario" class="btn-pill btn-teal">Agendar consultoria gratuita</a>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
