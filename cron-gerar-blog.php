<?php
/**
 * cron-gerar-blog.php — Gera 1 post de blog automaticamente via IA.
 * Port do gerador do site irmão EstudarNoEstrangeiro, retematizado: aqui os
 * tópicos são sobre ESTUDAR EM PORTUGAL (cidades, cursos, vistos, ENEM,
 * Concurso Especial, CPLP) em vez de "estudar no estrangeiro" genérico.
 *
 * Cron sugerido (cPanel, 3x/semana):
 *   0 6 * * 1,3,5 /usr/bin/php -q /caminho/estudar-em-portugal/cron-gerar-blog.php >> /caminho/estudar-em-portugal/cron-blog.log 2>&1
 *
 * Dependencies: PHP 7.4+, cURL, config.php + .env (BLOG_OPENROUTER_API_KEY, BLOG_MODEL).
 *
 * DRY_RUN / testing:
 *   php -f cron-gerar-blog.php --dry-run
 *   DRY_RUN=1 php -f cron-gerar-blog.php
 *   → usa JSON mock + salta OpenRouter/Gemini. Recomendado para validar o
 *     pipeline local antes de gastar créditos.
 *
 * Proteção: este script só deve correr por cron/CLI — sem guard de SAPI em
 * PHP (não fiável), mas o flock + ausência de link no site tornam-no
 * inofensivo mesmo se acedido via web (não expõe segredos, só gera 1 post).
 */

define('CRON_LOCK', __DIR__ . '/storage/.cron-blog.lock');
define('STALE_LOCK_SECONDS', 1800);
if (!is_dir(__DIR__ . '/storage')) @mkdir(__DIR__ . '/storage', 0775, true);
if (file_exists(CRON_LOCK)) {
    $lockAge = time() - filemtime(CRON_LOCK);
    if ($lockAge > STALE_LOCK_SECONDS && $lockAge < 86400 * 30) {
        @unlink(CRON_LOCK);
    }
}
$lockFp = @fopen(CRON_LOCK, 'c');
if (!$lockFp || !flock($lockFp, LOCK_EX | LOCK_NB)) {
    exit("Another instance is already running.\n");
}
@chmod(CRON_LOCK, 0666);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/blog-db.php';

set_time_limit(420);

// Slugs de páginas estáticas já escritas à mão — nunca gerar por cima.
define('STATIC_BLOG_SLUGS', [
    'explicacoes', 'concurso-especial-estudantes-internacionais',
]);

define('BLOG_DIR',        __DIR__);
define('TRACKER_FILE',    __DIR__ . '/storage/.blog-tracker.json');
define('FALLBACK_IMAGE',  'assets/images/hero-blog-default.svg');
define('IMAGES_DIR',      __DIR__ . '/assets/images');
define('CRON_LOG',        __DIR__ . '/storage/.cron-blog.log');
define('CRON_LOG_MAX_BYTES', 5 * 1024 * 1024);

function cronLog(string $msg): void {
    $ts = date('Y-m-d H:i:s');
    $line = "[$ts] $msg\n";
    if (file_exists(CRON_LOG) && @filesize(CRON_LOG) > CRON_LOG_MAX_BYTES) {
        if (file_exists(CRON_LOG . '.1')) @unlink(CRON_LOG . '.1');
        @rename(CRON_LOG, CRON_LOG . '.1');
    }
    @file_put_contents(CRON_LOG, $line, FILE_APPEND);
    echo $line;
}

// ── Topic Pool — tema único: estudar em Portugal (cidades, cursos, dicas) ──
$TOPIC_POOL = [
    // ---- CIDADES ----
    ['title' => 'Guia Completo para <span>Estudar em Lisboa</span>', 'slug_prefix' => 'estudar-em-lisboa-guia-completo', 'category' => 'cidade', 'category_label' => 'Guia por Cidade · Lisboa', 'category_icon' => 'bi bi-geo-alt', 'keywords' => 'estudar em lisboa, universidades lisboa, custo de vida lisboa, vida academica lisboa', 'tags' => ['Lisboa', 'Guia por Cidade']],
    ['title' => 'Guia Completo para <span>Estudar no Porto</span>', 'slug_prefix' => 'estudar-no-porto-guia-completo', 'category' => 'cidade', 'category_label' => 'Guia por Cidade · Porto', 'category_icon' => 'bi bi-geo-alt', 'keywords' => 'estudar no porto, universidades porto, custo de vida porto, vida academica porto', 'tags' => ['Porto', 'Guia por Cidade']],
    ['title' => 'Guia Completo para <span>Estudar em Coimbra</span>', 'slug_prefix' => 'estudar-em-coimbra-guia-completo', 'category' => 'cidade', 'category_label' => 'Guia por Cidade · Coimbra', 'category_icon' => 'bi bi-geo-alt', 'keywords' => 'estudar em coimbra, universidade de coimbra, custo de vida coimbra, tradicao academica coimbra', 'tags' => ['Coimbra', 'Guia por Cidade']],
    ['title' => 'Guia Completo para <span>Estudar em Braga</span>', 'slug_prefix' => 'estudar-em-braga-guia-completo', 'category' => 'cidade', 'category_label' => 'Guia por Cidade · Braga', 'category_icon' => 'bi bi-geo-alt', 'keywords' => 'estudar em braga, universidade do minho, custo de vida braga', 'tags' => ['Braga', 'Guia por Cidade']],
    ['title' => 'Guia Completo para <span>Estudar em Faro</span>', 'slug_prefix' => 'estudar-em-faro-guia-completo', 'category' => 'cidade', 'category_label' => 'Guia por Cidade · Faro', 'category_icon' => 'bi bi-geo-alt', 'keywords' => 'estudar em faro, universidade do algarve, custo de vida algarve', 'tags' => ['Faro', 'Guia por Cidade']],
    ['title' => 'Guia Completo para <span>Estudar em Évora</span>', 'slug_prefix' => 'estudar-em-evora-guia-completo', 'category' => 'cidade', 'category_label' => 'Guia por Cidade · Évora', 'category_icon' => 'bi bi-geo-alt', 'keywords' => 'estudar em evora, universidade de evora, custo de vida evora', 'tags' => ['Évora', 'Guia por Cidade']],
    ['title' => 'Guia Completo para <span>Estudar em Aveiro</span>', 'slug_prefix' => 'estudar-em-aveiro-guia-completo', 'category' => 'cidade', 'category_label' => 'Guia por Cidade · Aveiro', 'category_icon' => 'bi bi-geo-alt', 'keywords' => 'estudar em aveiro, universidade de aveiro, custo de vida aveiro, engenharia aveiro', 'tags' => ['Aveiro', 'Guia por Cidade']],
    ['title' => '<span>Lisboa vs Porto</span>: Qual Cidade Escolher para Estudar', 'slug_prefix' => 'lisboa-vs-porto-qual-escolher', 'category' => 'cidade', 'category_label' => 'Guia por Cidade · Comparação', 'category_icon' => 'bi bi-geo-alt', 'keywords' => 'lisboa vs porto, comparar lisboa porto, onde estudar em portugal', 'tags' => ['Comparação', 'Guia por Cidade']],

    // ---- CURSOS ----
    ['title' => 'Estudar <span>Medicina em Portugal</span> Sendo Brasileiro', 'slug_prefix' => 'estudar-medicina-portugal-brasileiro', 'category' => 'curso', 'category_label' => 'Guia por Curso · Medicina', 'category_icon' => 'bi bi-book', 'keywords' => 'estudar medicina portugal, medicina para brasileiros, enem medicina portugal', 'tags' => ['Medicina', 'Cursos']],
    ['title' => 'Estudar <span>Engenharia Informática em Portugal</span>', 'slug_prefix' => 'estudar-engenharia-informatica-portugal', 'category' => 'curso', 'category_label' => 'Guia por Curso · Eng. Informática', 'category_icon' => 'bi bi-book', 'keywords' => 'engenharia informatica portugal, licenciatura informatica portugal, tecnologia portugal', 'tags' => ['Engenharia Informática', 'Cursos']],
    ['title' => 'Estudar <span>Direito em Portugal</span> Sendo Brasileiro', 'slug_prefix' => 'estudar-direito-portugal-brasileiro', 'category' => 'curso', 'category_label' => 'Guia por Curso · Direito', 'category_icon' => 'bi bi-book', 'keywords' => 'estudar direito portugal, direito para brasileiros, faculdade de direito portugal', 'tags' => ['Direito', 'Cursos']],
    ['title' => 'Estudar <span>Gestão e Economia em Portugal</span>', 'slug_prefix' => 'estudar-gestao-economia-portugal', 'category' => 'curso', 'category_label' => 'Guia por Curso · Gestão', 'category_icon' => 'bi bi-book', 'keywords' => 'estudar gestao portugal, licenciatura economia portugal, gestao e economia portugal', 'tags' => ['Gestão', 'Cursos']],
    ['title' => 'Estudar <span>Enfermagem em Portugal</span>', 'slug_prefix' => 'estudar-enfermagem-portugal', 'category' => 'curso', 'category_label' => 'Guia por Curso · Enfermagem', 'category_icon' => 'bi bi-book', 'keywords' => 'estudar enfermagem portugal, licenciatura enfermagem portugal, escola superior enfermagem', 'tags' => ['Enfermagem', 'Cursos']],
    ['title' => 'Estudar <span>Arquitetura em Portugal</span>', 'slug_prefix' => 'estudar-arquitetura-portugal', 'category' => 'curso', 'category_label' => 'Guia por Curso · Arquitetura', 'category_icon' => 'bi bi-book', 'keywords' => 'estudar arquitetura portugal, faculdade arquitetura portugal, desenho geometria descritiva', 'tags' => ['Arquitetura', 'Cursos']],
    ['title' => 'Estudar <span>Psicologia em Portugal</span>', 'slug_prefix' => 'estudar-psicologia-portugal', 'category' => 'curso', 'category_label' => 'Guia por Curso · Psicologia', 'category_icon' => 'bi bi-book', 'keywords' => 'estudar psicologia portugal, licenciatura psicologia portugal, ordem psicologos portugal', 'tags' => ['Psicologia', 'Cursos']],
    ['title' => 'Estudar <span>Design em Portugal</span>', 'slug_prefix' => 'estudar-design-portugal', 'category' => 'curso', 'category_label' => 'Guia por Curso · Design', 'category_icon' => 'bi bi-book', 'keywords' => 'estudar design portugal, design comunicacao portugal, faculdade belas artes', 'tags' => ['Design', 'Cursos']],
    ['title' => 'Estudar <span>Farmácia em Portugal</span>', 'slug_prefix' => 'estudar-farmacia-portugal', 'category' => 'curso', 'category_label' => 'Guia por Curso · Farmácia', 'category_icon' => 'bi bi-book', 'keywords' => 'estudar farmacia portugal, ciencias farmaceuticas portugal', 'tags' => ['Farmácia', 'Cursos']],
    ['title' => 'Estudar <span>Fisioterapia em Portugal</span>', 'slug_prefix' => 'estudar-fisioterapia-portugal', 'category' => 'curso', 'category_label' => 'Guia por Curso · Fisioterapia', 'category_icon' => 'bi bi-book', 'keywords' => 'estudar fisioterapia portugal, licenciatura fisioterapia portugal', 'tags' => ['Fisioterapia', 'Cursos']],
    ['title' => 'Estudar <span>Engenharia Civil em Portugal</span>', 'slug_prefix' => 'estudar-engenharia-civil-portugal', 'category' => 'curso', 'category_label' => 'Guia por Curso · Eng. Civil', 'category_icon' => 'bi bi-book', 'keywords' => 'engenharia civil portugal, licenciatura engenharia civil portugal', 'tags' => ['Engenharia Civil', 'Cursos']],
    ['title' => 'Estudar <span>Relações Internacionais em Portugal</span>', 'slug_prefix' => 'estudar-relacoes-internacionais-portugal', 'category' => 'curso', 'category_label' => 'Guia por Curso · Rel. Internacionais', 'category_icon' => 'bi bi-book', 'keywords' => 'relacoes internacionais portugal, licenciatura ri portugal', 'tags' => ['Relações Internacionais', 'Cursos']],
    ['title' => 'Estudar <span>Turismo e Hotelaria em Portugal</span>', 'slug_prefix' => 'estudar-turismo-hotelaria-portugal', 'category' => 'curso', 'category_label' => 'Guia por Curso · Turismo', 'category_icon' => 'bi bi-book', 'keywords' => 'turismo hotelaria portugal, gestao hoteleira portugal, licenciatura turismo', 'tags' => ['Turismo', 'Cursos']],
    ['title' => 'Estudar <span>Nutrição em Portugal</span>', 'slug_prefix' => 'estudar-nutricao-portugal', 'category' => 'curso', 'category_label' => 'Guia por Curso · Nutrição', 'category_icon' => 'bi bi-book', 'keywords' => 'estudar nutricao portugal, ciencias da nutricao portugal', 'tags' => ['Nutrição', 'Cursos']],
    ['title' => 'Estudar <span>Veterinária em Portugal</span>', 'slug_prefix' => 'estudar-veterinaria-portugal', 'category' => 'curso', 'category_label' => 'Guia por Curso · Veterinária', 'category_icon' => 'bi bi-book', 'keywords' => 'estudar veterinaria portugal, medicina veterinaria portugal', 'tags' => ['Veterinária', 'Cursos']],
    ['title' => 'Estudar <span>Comunicação Social em Portugal</span>', 'slug_prefix' => 'estudar-comunicacao-social-portugal', 'category' => 'curso', 'category_label' => 'Guia por Curso · Comunicação', 'category_icon' => 'bi bi-book', 'keywords' => 'jornalismo portugal, comunicacao social portugal, licenciatura comunicacao', 'tags' => ['Comunicação', 'Cursos']],
    ['title' => 'Estudar <span>Marketing Digital em Portugal</span>', 'slug_prefix' => 'estudar-marketing-digital-portugal', 'category' => 'curso', 'category_label' => 'Guia por Curso · Marketing', 'category_icon' => 'bi bi-book', 'keywords' => 'marketing digital portugal, licenciatura marketing portugal', 'tags' => ['Marketing', 'Cursos']],
    ['title' => '<span>Mestrado em Portugal</span>: Guia para Brasileiros já Formados', 'slug_prefix' => 'mestrado-portugal-brasileiros-formados', 'category' => 'curso', 'category_label' => 'Guia por Curso · Mestrado', 'category_icon' => 'bi bi-mortarboard', 'keywords' => 'mestrado portugal brasileiros, pos graduacao portugal, mestrado para estrangeiros', 'tags' => ['Mestrado', 'Cursos']],
    ['title' => '<span>CTeSP</span>: O que É e Como Funciona para Brasileiros', 'slug_prefix' => 'ctesp-o-que-e-brasileiros', 'category' => 'curso', 'category_label' => 'Guia por Curso · CTeSP', 'category_icon' => 'bi bi-mortarboard', 'keywords' => 'ctesp o que e, curso tecnico superior profissional portugal, ctesp brasileiros', 'tags' => ['CTeSP', 'Cursos']],
    ['title' => '<span>Doutoramento em Portugal</span>: Guia para Brasileiros', 'slug_prefix' => 'doutoramento-portugal-brasileiros', 'category' => 'curso', 'category_label' => 'Guia por Curso · Doutoramento', 'category_icon' => 'bi bi-mortarboard', 'keywords' => 'doutoramento portugal brasileiros, phd portugal, doutoramento universidade portuguesa', 'tags' => ['Doutoramento', 'Cursos']],

    // ---- DICAS ----
    ['title' => '<span>Visto de Estudante</span> para Portugal: Guia Passo a Passo', 'slug_prefix' => 'visto-estudante-portugal-passo-a-passo', 'category' => 'dica', 'category_label' => 'Dicas · Vistos', 'category_icon' => 'bi bi-passport', 'keywords' => 'visto estudante portugal, visto estudante brasileiro, consulado portugues visto', 'tags' => ['Vistos', 'Burocracia']],
    ['title' => 'Como Funciona a <span>AIMA</span> para Estudantes Brasileiros', 'slug_prefix' => 'aima-estudantes-brasileiros', 'category' => 'dica', 'category_label' => 'Dicas · Vistos', 'category_icon' => 'bi bi-passport', 'keywords' => 'aima portugal, autorizacao residencia estudante, aima marcacao', 'tags' => ['Vistos', 'Burocracia']],
    ['title' => 'Como Usar a <span>Nota do ENEM</span> para Entrar em Portugal', 'slug_prefix' => 'usar-nota-enem-entrar-portugal', 'category' => 'dica', 'category_label' => 'Dicas · ENEM', 'category_icon' => 'bi bi-clipboard-check', 'keywords' => 'enem para portugal, nota enem universidade portuguesa, enem concurso especial', 'tags' => ['ENEM', 'Candidatura']],
    ['title' => '<span>Propinas em Portugal</span>: Quanto Custa por Universidade', 'slug_prefix' => 'propinas-portugal-quanto-custa', 'category' => 'dica', 'category_label' => 'Dicas · Propinas', 'category_icon' => 'bi bi-cash-coin', 'keywords' => 'propinas portugal, valor propinas estudante internacional, propinas universidades publicas', 'tags' => ['Propinas', 'Financiamento']],
    ['title' => 'Como Pedir o <span>Desconto CPLP</span> na Propina', 'slug_prefix' => 'como-pedir-desconto-cplp-propina', 'category' => 'dica', 'category_label' => 'Dicas · CPLP', 'category_icon' => 'bi bi-cash-coin', 'keywords' => 'desconto cplp propina, cplp brasileiros portugal, reducao propina cplp', 'tags' => ['CPLP', 'Financiamento']],
    ['title' => '<span>Custo de Vida</span>: Lisboa vs Porto vs Cidades Médias', 'slug_prefix' => 'custo-de-vida-lisboa-porto-cidades-medias', 'category' => 'dica', 'category_label' => 'Dicas · Custo de Vida', 'category_icon' => 'bi bi-cash-stack', 'keywords' => 'custo de vida portugal, custo de vida lisboa, custo de vida porto, cidades medias portugal', 'tags' => ['Custo de Vida', 'Comparação']],
    ['title' => '<span>Alojamento Estudantil</span> em Portugal: Residências vs Quartos', 'slug_prefix' => 'alojamento-estudantil-portugal', 'category' => 'dica', 'category_label' => 'Dicas · Alojamento', 'category_icon' => 'bi bi-house-door', 'keywords' => 'alojamento estudantil portugal, residencias universitarias portugal, alugar quarto portugal', 'tags' => ['Alojamento', 'Guia Geral']],
    ['title' => 'Como Abrir <span>Conta Bancária em Portugal</span> Sendo Brasileiro', 'slug_prefix' => 'abrir-conta-bancaria-portugal-brasileiro', 'category' => 'dica', 'category_label' => 'Dicas · Finanças', 'category_icon' => 'bi bi-bank', 'keywords' => 'conta bancaria portugal brasileiro, abrir conta banco portugal, nib portugal', 'tags' => ['Finanças', 'Guia Geral']],
    ['title' => '<span>Seguro de Saúde</span> em Portugal: SNS vs Privado', 'slug_prefix' => 'seguro-saude-portugal-sns-privado', 'category' => 'dica', 'category_label' => 'Dicas · Saúde', 'category_icon' => 'bi bi-heart-pulse', 'keywords' => 'seguro saude portugal, sns estudante estrangeiro, seguro saude privado portugal', 'tags' => ['Saúde', 'Documentação']],
    ['title' => 'Trabalhar <span>Part-Time</span> em Portugal Sendo Estudante', 'slug_prefix' => 'trabalhar-part-time-portugal-estudante', 'category' => 'dica', 'category_label' => 'Dicas · Trabalho', 'category_icon' => 'bi bi-briefcase', 'keywords' => 'trabalho part time portugal estudante, trabalhar estudando portugal, visto trabalho estudante', 'tags' => ['Trabalho', 'Financiamento']],
    ['title' => '<span>Bolsas de Estudo</span> em Portugal para Brasileiros', 'slug_prefix' => 'bolsas-estudo-portugal-brasileiros', 'category' => 'dica', 'category_label' => 'Dicas · Bolsas', 'category_icon' => 'bi bi-gift', 'keywords' => 'bolsas estudo portugal, bolsas dges, bolsa merito portugal brasileiros', 'tags' => ['Bolsas', 'Financiamento']],
    ['title' => '<span>Equivalência</span> do Ensino Médio Brasileiro em Portugal', 'slug_prefix' => 'equivalencia-ensino-medio-brasileiro-portugal', 'category' => 'dica', 'category_label' => 'Dicas · Documentação', 'category_icon' => 'bi bi-file-earmark-check', 'keywords' => 'equivalencia ensino medio portugal, declaracao valor portugal, equivalencia 12 ano', 'tags' => ['Documentação', 'Burocracia']],
    ['title' => 'Como Tratar <span>Tradução e Apostila</span> de Documentos Brasileiros', 'slug_prefix' => 'traducao-apostila-documentos-brasileiros', 'category' => 'dica', 'category_label' => 'Dicas · Documentação', 'category_icon' => 'bi bi-translate', 'keywords' => 'apostila de haia brasil portugal, traducao documentos portugal, tradutor juramentado', 'tags' => ['Documentação', 'Burocracia']],
    ['title' => '<span>Transportes Públicos</span> para Estudantes em Portugal', 'slug_prefix' => 'transportes-publicos-estudantes-portugal', 'category' => 'dica', 'category_label' => 'Dicas · Vida Estudantil', 'category_icon' => 'bi bi-bus-front', 'keywords' => 'passe estudante portugal, transportes publicos lisboa porto, desconto transportes estudante', 'tags' => ['Vida Estudantil', 'Guia Geral']],
    ['title' => '<span>Adaptação Cultural</span> de Brasileiros em Portugal', 'slug_prefix' => 'adaptacao-cultural-brasileiros-portugal', 'category' => 'dica', 'category_label' => 'Dicas · Vida Estudantil', 'category_icon' => 'bi bi-globe2', 'keywords' => 'adaptacao cultural portugal, choque cultural brasileiro portugal, comunidade brasileira portugal', 'tags' => ['Vida Estudantil', 'Guia Geral']],
    ['title' => 'A <span>Comunidade Brasileira</span> em Portugal: O que Esperar', 'slug_prefix' => 'comunidade-brasileira-portugal', 'category' => 'dica', 'category_label' => 'Dicas · Vida Estudantil', 'category_icon' => 'bi bi-people', 'keywords' => 'brasileiros em portugal, comunidade brasileira portugal, imigracao brasileira portugal', 'tags' => ['Vida Estudantil', 'Guia Geral']],
    ['title' => 'Português do Brasil vs <span>Português de Portugal</span>: Diferenças', 'slug_prefix' => 'portugues-brasil-vs-portugal-diferencas', 'category' => 'dica', 'category_label' => 'Dicas · Vida Estudantil', 'category_icon' => 'bi bi-chat-dots', 'keywords' => 'diferencas portugues brasil portugal, sotaque portugues portugal, vocabulario pt pt vs pt br', 'tags' => ['Vida Estudantil', 'Guia Geral']],
    ['title' => 'Reconhecimento do <span>Diploma Português</span> no Brasil', 'slug_prefix' => 'reconhecimento-diploma-portugues-brasil', 'category' => 'dica', 'category_label' => 'Dicas · Documentação', 'category_icon' => 'bi bi-award', 'keywords' => 'revalidar diploma portugues brasil, reconhecimento diploma portugal brasil, ects brasil', 'tags' => ['Documentação', 'Burocracia']],
    ['title' => 'Visto para <span>Familiares Acompanharem</span> o Estudante em Portugal', 'slug_prefix' => 'visto-familiares-acompanhar-estudante-portugal', 'category' => 'dica', 'category_label' => 'Dicas · Vistos', 'category_icon' => 'bi bi-people', 'keywords' => 'visto reagrupamento familiar portugal, pais acompanhar filho estudante portugal', 'tags' => ['Vistos', 'Burocracia']],
    ['title' => '<span>Estágio Curricular</span> em Portugal Durante a Licenciatura', 'slug_prefix' => 'estagio-curricular-portugal-licenciatura', 'category' => 'dica', 'category_label' => 'Dicas · Carreira', 'category_icon' => 'bi bi-briefcase', 'keywords' => 'estagio curricular portugal, estagio erasmus portugal, estagio universidade portuguesa', 'tags' => ['Carreira', 'Estágios']],
    ['title' => 'Os <span>Erros Mais Comuns</span> de Brasileiros ao Candidatar-se a Portugal', 'slug_prefix' => 'erros-comuns-brasileiros-candidatura-portugal', 'category' => 'dica', 'category_label' => 'Dicas · Candidatura', 'category_icon' => 'bi bi-exclamation-triangle', 'keywords' => 'erros candidatura portugal, erros comuns concurso especial, candidatura universidade portuguesa erros', 'tags' => ['Candidatura', 'Guia Geral']],
    ['title' => 'Quando Começar a <span>Planear a Candidatura</span> a Portugal', 'slug_prefix' => 'quando-planear-candidatura-portugal', 'category' => 'dica', 'category_label' => 'Dicas · Candidatura', 'category_icon' => 'bi bi-calendar-check', 'keywords' => 'quando candidatar portugal, calendario candidatura universidades portuguesas, prazos concurso especial', 'tags' => ['Candidatura', 'Guia Geral']],
];

function loadTracker(): array {
    if (!file_exists(TRACKER_FILE)) return ['used_topics' => [], 'total_generated' => 0, 'last_date' => null];
    $raw = @file_get_contents(TRACKER_FILE);
    $data = $raw === false ? null : json_decode($raw, true);
    if (!is_array($data)) return ['used_topics' => [], 'total_generated' => 0, 'last_date' => null];
    return $data;
}

function saveTracker(array $tracker): void {
    $json = json_encode($tracker, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json === false) { cronLog('ERROR: saveTracker json_encode falhou'); return; }
    $written = @file_put_contents(TRACKER_FILE, $json);
    if ($written === false) { cronLog('ERROR: saveTracker falhou a escrever ' . TRACKER_FILE); return; }
    @chmod(TRACKER_FILE, 0666);
}

function pickTopic(array $pool, array $tracker): ?array {
    $used = $tracker['used_topics'] ?? [];
    $available = [];
    foreach ($pool as $i => $topic) {
        if (in_array($topic['slug_prefix'], $used, true)) continue;
        if (in_array($topic['slug_prefix'], STATIC_BLOG_SLUGS, true)) continue;
        $available[$i] = $topic;
    }
    if (empty($available)) {
        cronLog('Todos os ' . count($pool) . ' tópicos do pool usados. Vou gerar tópico novo via IA.');
        return null;
    }
    $keys = array_keys($available);
    return $available[$keys[array_rand($keys)]];
}

function generateTopicIdea(array $tracker): ?array {
    $used = $tracker['used_topics'] ?? [];
    $allExisting = array_unique(array_merge($used, STATIC_BLOG_SLUGS));

    $systemPrompt = 'És um especialista em conteúdo SEO para brasileiros que querem estudar em Portugal. Devolve APENAS um JSON válido, sem markdown, sem texto extra.';
    $userPrompt  = "Sugere UM novo tópico de blog para o site Estudar em Portugal (brasileiros que querem estudar em Portugal).\n";
    $userPrompt .= "Conteúdo já existente (NÃO repetir nem tópicos semelhantes):\n" . implode(', ', $allExisting) . "\n\n";
    $userPrompt .= "O tópico deve ser sobre cidades portuguesas, cursos em Portugal, vistos, ENEM, Concurso Especial, propinas, CPLP, vida estudantil — 100% focado em Portugal para brasileiros.\n";
    $userPrompt .= "Devolve APENAS este JSON:\n{\n  \"title\": \"Título com <span>palavra-chave</span>\",\n  \"slug_prefix\": \"slug-unico-em-minusculas\",\n  \"category\": \"cidade|curso|dica\",\n  \"category_label\": \"Guia por Cidade · X  ou  Guia por Curso · X  ou  Dicas · X\",\n  \"category_icon\": \"bi bi-lightbulb\",\n  \"keywords\": \"keyword1, keyword2, keyword3\",\n  \"tags\": [\"Tag1\", \"Tag2\"]\n}";

    $raw = callOpenRouter($systemPrompt, $userPrompt);
    if (!$raw) { cronLog('ERROR: generateTopicIdea sem resposta'); return null; }

    $data = extractJson($raw);
    if (!$data || empty($data['slug_prefix']) || empty($data['title'])) {
        cronLog('ERROR: generateTopicIdea JSON inválido. Trecho: ' . substr($raw, 0, 200));
        return null;
    }
    $slug = preg_replace('/[^a-z0-9-]/', '', strtolower($data['slug_prefix']));
    if (in_array($slug, $allExisting, true)) {
        cronLog("WARNING: IA sugeriu slug já existente '$slug'");
        return null;
    }
    cronLog("IA gerou novo tópico: {$data['title']} ($slug)");
    return [
        'title' => $data['title'], 'slug_prefix' => $slug,
        'category' => $data['category'] ?? 'dica',
        'category_label' => $data['category_label'] ?? 'Dicas',
        'category_icon' => $data['category_icon'] ?? 'bi bi-lightbulb',
        'keywords' => $data['keywords'] ?? '', 'tags' => (array) ($data['tags'] ?? []),
    ];
}

function extractJson(string $raw): ?array {
    $clean = trim($raw);
    $clean = preg_replace('/^```(?:json)?\s*/', '', $clean, 1);
    $clean = preg_replace('/```\s*$/', '', $clean, 1);
    $clean = trim($clean);
    $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $clean);
    if ($clean === '') return null;
    if ($clean[0] !== '{') {
        $start = strpos($clean, '{'); $end = strrpos($clean, '}');
        if ($start !== false && $end !== false) $clean = substr($clean, $start, $end - $start + 1);
    }
    $data = json_decode($clean, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
    return is_array($data) ? $data : null;
}

function callOpenRouter(string $systemPrompt, string $userPrompt): ?string {
    $maxRetries = 3;
    $lastError = 'no response';
    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        $payload = [
            'model' => BLOG_MODEL,
            'messages' => [['role' => 'system', 'content' => $systemPrompt], ['role' => 'user', 'content' => $userPrompt]],
            'max_tokens' => 8000, 'temperature' => 0.7, 'top_p' => 0.95,
        ];
        $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . BLOG_OPENROUTER_API_KEY, 'HTTP-Referer: ' . SITE_URL, 'X-Title: ' . SITE_SHORT_NAME . ' Cron Blog'],
            CURLOPT_POSTFIELDS => json_encode($payload), CURLOPT_TIMEOUT => 180, CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_USERAGENT => 'EstudarEmPortugal-CronBlog/1.0',
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if ($response === false || $httpCode !== 200) {
            $lastError = "HTTP {$httpCode}" . ($error ? " cURL: {$error}" : '') . ' body=' . substr((string) $response, 0, 200);
            cronLog("OpenRouter tentativa {$attempt}/{$maxRetries} falhou: {$lastError}");
        } else {
            $data = json_decode($response, true);
            $finishReason = $data['choices'][0]['finish_reason'] ?? 'unknown';
            $content = $data['choices'][0]['message']['content'] ?? null;
            if ($finishReason === 'stop' && is_string($content) && $content !== '') {
                if ($attempt > 1) cronLog("OpenRouter recuperou na tentativa {$attempt}.");
                return $content;
            }
            $lastError = "finish_reason={$finishReason}, content_len=" . strlen((string) $content);
            cronLog("OpenRouter tentativa {$attempt}/{$maxRetries}: {$lastError} — vou tentar de novo.");
        }
        if ($attempt < $maxRetries) { $sleep = $attempt * 2; cronLog("  backoff {$sleep}s..."); sleep($sleep); }
    }
    cronLog("OpenRouter: desisti após {$maxRetries} tentativas. Último erro: {$lastError}");
    return null;
}

function generateGeminiImage(array $topic, string $slug, string $title): ?string {
    if (empty(BLOG_OPENROUTER_API_KEY)) { cronLog('  Imagem Gemini saltada: BLOG_OPENROUTER_API_KEY não definida'); return null; }
    $cleanTitle = strip_tags($title);
    $category = $topic['category'];
    $maxRetries = 3;
    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        $result = lf_gemini_image_attempt($cleanTitle, $category, $slug, $attempt);
        if ($result['status'] === 'refused') return null;
        if ($result['status'] === 'ok') return $result['path'];
        if ($attempt < $maxRetries) {
            $sleep = $attempt * 2;
            cronLog("  Imagem Gemini tentativa {$attempt}/{$maxRetries}: {$result['reason']} — retry em {$sleep}s.");
            sleep($sleep);
        } else {
            cronLog("  Imagem Gemini: desisti após {$maxRetries} tentativas. Motivo: {$result['reason']}");
        }
    }
    return null;
}

function lf_gemini_image_attempt(string $cleanTitle, string $category, string $slug, int $attempt): array {
    // Várias opções de cenário/luz/enquadramento/atmosfera por categoria —
    // combinadas ao acaso a cada chamada, para que artigos da mesma
    // categoria não peçam sempre a mesma composição de imagem (e para que
    // um retry tente uma combinação diferente em vez de repetir a que falhou).
    $settingsByCategory = [
        'cidade' => [
            'walking through a historic cobblestone square lined with traditional azulejo-tiled buildings',
            'crossing a university campus courtyard between classes',
            'sitting at an outdoor café table on a narrow tram-lined street',
            'walking up a steep hillside street with colorful tiled façades',
            'standing by a riverside promenade with the old town skyline behind them',
            'browsing a small local bookstore near the university district',
        ],
        'curso' => [
            'in a modern university lecture hall with a laptop open',
            'in a university library surrounded by books and warm reading lamps',
            'in a hands-on lab or studio working on a project',
            'in a small-group study room discussing notes around a table',
            'walking out of a university building carrying books and a backpack',
            'presenting a project on a laptop to a small group of classmates',
        ],
        'dica' => [
            'reviewing paperwork at a kitchen table in a bright student apartment',
            'on a laptop at a co-working space, coffee cup nearby',
            'filling out documents at a table near a sunlit window',
            'video-calling family while holding travel or student documents',
            'packing a suitcase in a small apartment, documents on the bed',
            'sitting on a train with a laptop and a notebook, travel-in-progress feel',
        ],
    ];
    $lighting = [
        'soft golden-hour light', 'bright overcast daylight', 'warm early-morning light',
        'gentle afternoon light through a window', 'cool blue-hour dusk light',
    ];
    $framing = [
        'medium shot, shallow depth of field', 'candid over-the-shoulder shot',
        'wide establishing shot with the subject small in frame', 'close-up with soft bokeh background',
    ];
    $mood = [
        'quiet and focused mood', 'relaxed and hopeful mood', 'lightly candid, in-motion feel',
        'calm and studious atmosphere',
    ];
    $settings = $settingsByCategory[$category] ?? $settingsByCategory['dica'];

    $prompt = 'Generate only an image, no text response. ';
    $prompt .= 'Photorealistic editorial photograph for a blog about studying in Portugal for Brazilian students. ';
    $prompt .= 'NOT an illustration, NOT vector art, NOT flat design, NOT an infographic, no icons, no text overlays, no logos. ';
    $prompt .= 'A Brazilian student ' . $settings[array_rand($settings)] . '. ';
    $prompt .= ucfirst($lighting[array_rand($lighting)]) . ', ' . $framing[array_rand($framing)] . ', ' . $mood[array_rand($mood)] . '. ';
    $prompt .= 'Context: ' . $cleanTitle;

    $payload = ['model' => BLOG_IMAGE_MODEL, 'messages' => [['role' => 'user', 'content' => $prompt]], 'modalities' => ['image', 'text']];
    $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . BLOG_OPENROUTER_API_KEY, 'HTTP-Referer: ' . SITE_URL],
        CURLOPT_POSTFIELDS => json_encode($payload), CURLOPT_TIMEOUT => 90, CURLOPT_CONNECTTIMEOUT => 20,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($response === false || $httpCode !== 200) return ['status' => 'retry', 'reason' => "HTTP {$httpCode}"];

    $data = json_decode($response, true);
    $msg = $data['choices'][0]['message'] ?? null;
    $finishReason = $data['choices'][0]['finish_reason'] ?? '';
    if ($finishReason === 'content_filter') return ['status' => 'refused', 'reason' => 'safety filter'];

    $imgB64 = null;
    if (isset($msg['images'][0]['image_url']['url']) && is_string($msg['images'][0]['image_url']['url'])) {
        $url = $msg['images'][0]['image_url']['url'];
        if (strpos($url, 'base64,') !== false) $imgB64 = substr($url, strpos($url, 'base64,') + 7);
    }
    if (!$imgB64) return ['status' => 'retry', 'reason' => 'sem imagem na resposta'];

    $bytes = base64_decode($imgB64, true);
    if ($bytes === false || strlen($bytes) < 500) return ['status' => 'retry', 'reason' => 'imagem decodificada inválida'];

    $filename = 'hero-blog-' . $slug . '.png';
    $written = @file_put_contents(IMAGES_DIR . '/' . $filename, $bytes);
    if ($written === false) return ['status' => 'retry', 'reason' => 'falha a escrever ficheiro'];
    @chmod(IMAGES_DIR . '/' . $filename, 0664);
    return ['status' => 'ok', 'path' => 'assets/images/' . $filename];
}

function buildUserPrompt(array $topic): string {
    $existingPosts = implode(', ', STATIC_BLOG_SLUGS);
    return <<<PROMPT
Gera um artigo de blog para o site Estudar em Portugal (brasileiros que querem estudar em Portugal).

TÍTULO: {$topic['title']}
CATEGORIA: {$topic['category_label']}
KEYWORDS: {$topic['keywords']}

Artigos já existentes no site (NÃO duplicar conteúdo): {$existingPosts}

O artigo deve:
- Ser original, útil e focado em estudantes BRASILEIROS que querem estudar em Portugal
- Incluir 4-6 secções com <h2 id="..."> e subsecções com <h3> quando fizer sentido
- Ter uma lead (parágrafo inicial forte) com class="lead"
- Incluir uma <div class="highlight-box"> ou <div class="warning-box"> quando relevante
- Terminar com um parágrafo de conclusão que leva ao CTA
- Mencionar factos verificáveis (ENEM, Concurso Especial, CPLP, propinas) apenas se tiveres confiança neles — nunca inventar números

Devolve APENAS o JSON válido, sem markdown, sem code fences, sem texto extra antes ou depois, no formato:
{
  "title": "título simples para <title>",
  "meta_title": "título SEO (≤60 caracteres)",
  "meta_description": "meta description (≤155 caracteres)",
  "category_badge": "{$topic['category_label']}",
  "reading_minutes": 8,
  "slug": "url-amigavel-com-hifens",
  "content_html": "artigo completo em HTML (com <h2 id>, <h3>, <p>, <ul>, <div class=highlight-box>)",
  "related_articles": [{"slug": "slug-existente", "title": "Título"}]
}
PROMPT;
}

function enp_insert_blog_post(array $generated, array $topic, string $slug, string $heroImageFile, string $today): bool {
    $d = db(); if (!$d) { cronLog('ERROR: sem ligação à BD. Post NÃO publicado.'); return false; }
    $titleFull   = trim($generated['meta_title'] ?? $generated['title']);
    $h1          = trim(strip_tags($generated['title']));
    $excerpt     = trim($generated['meta_description'] ?? '');
    $metaKw      = trim($topic['keywords'] ?? '');
    $readMin     = (int) ($generated['reading_minutes'] ?? 8);
    $content     = (string) $generated['content_html'];
    $publishedAt = $today . ' ' . date('H:i:s');

    $sql = "INSERT INTO blog_posts
        (slug,title,h1_html,excerpt,content_html,category,category_label,category_icon,
         hero_image,meta_description,meta_keywords,reading_minutes,status,published_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,'published',?)
        ON DUPLICATE KEY UPDATE
         title=VALUES(title), h1_html=VALUES(h1_html), excerpt=VALUES(excerpt),
         content_html=VALUES(content_html), hero_image=VALUES(hero_image),
         meta_description=VALUES(meta_description), updated_at=NOW()";
    $stmt = $d->prepare($sql);
    if (!$stmt) { cronLog('ERROR: prepare blog_posts falhou: ' . $d->error); return false; }
    $stmt->bind_param(
        'sssssssssssis',
        $slug, $titleFull, $h1, $excerpt, $content, $topic['category'], $topic['category_label'], $topic['category_icon'],
        $heroImageFile, $excerpt, $metaKw, $readMin, $publishedAt
    );
    $ok = $stmt->execute();
    if (!$ok) cronLog('ERROR: insert blog_posts falhou: ' . $stmt->error);
    $stmt->close();
    return $ok;
}

// ═══════════════════════════════════════════════════
// MAIN EXECUTION
// ═══════════════════════════════════════════════════
try {
    cronLog('=== Cron Blog Start (Estudar em Portugal) ===');

    $dryRun = getenv('DRY_RUN') === '1' || (isset($argv) && is_array($argv) && in_array('--dry-run', $argv, true));
    if ($dryRun) cronLog('DRY-RUN ATIVO — sem chamadas reais a OpenRouter/Gemini.');

    if (empty(BLOG_OPENROUTER_API_KEY) && !$dryRun) {
        cronLog('ERROR: BLOG_OPENROUTER_API_KEY não definida no .env');
        exit(1);
    }

    $tracker = loadTracker();
    $today   = date('Y-m-d');

    if (!$dryRun && $tracker['last_date'] === $today) {
        cronLog("Já correu hoje ($today). A saltar.");
        exit(0);
    }

    $topic = pickTopic($TOPIC_POOL, $tracker);
    if ($topic === null) {
        if ($dryRun) {
            $topic = $TOPIC_POOL[0];
        } else {
            for ($attempt = 1; $attempt <= 3; $attempt++) {
                $topic = generateTopicIdea($tracker);
                if ($topic !== null) break;
                sleep(2 * $attempt);
            }
            if ($topic === null) { cronLog('ERROR: não consegui gerar tópico novo após 3 tentativas.'); exit(1); }
        }
    }
    cronLog("Tópico escolhido: {$topic['title']}");

    if ($dryRun) {
        $drySlug = $topic['slug_prefix'] . '-dryrun-' . date('Ymd-His');
        $rawResponse = '{'
            . '"title":"[DRY-RUN] ' . $topic['title'] . '",'
            . '"meta_title":"[TESTE] ' . strip_tags($topic['title']) . '",'
            . '"meta_description":"Post DRY-RUN para validar o pipeline sem gastar créditos.",'
            . '"category_badge":"' . $topic['category_label'] . '",'
            . '"reading_minutes":5,'
            . '"slug":"' . $drySlug . '",'
            . '"content_html":"<p class=\"lead\">Artigo gerado em modo DRY-RUN para validar o pipeline.</p><h2 id=\"intro\">Introdução</h2><p>Se isto aparecer em blog_posts e em blog.php, o pipeline está operacional.</p>",'
            . '"related_articles":[]'
            . '}';
        cronLog("DRY-RUN: JSON enlatado preparado (slug={$drySlug}).");
    } else {
        cronLog('A chamar OpenRouter (modelo: ' . BLOG_MODEL . ')...');
        $rawResponse = callOpenRouter('És um copywriter SEO especializado em estudar em Portugal para brasileiros. Devolve APENAS JSON válido.', buildUserPrompt($topic));
        if (!$rawResponse) { cronLog('ERROR: sem resposta do OpenRouter'); exit(1); }
    }

    $generated = extractJson($rawResponse);
    if (!$generated || !isset($generated['content_html'], $generated['slug'])) {
        cronLog('ERROR: JSON inválido (' . strlen($rawResponse) . ' bytes).');
        exit(1);
    }

    $slug = preg_replace('/[^a-z0-9-]/', '', strtolower($generated['slug']));
    $slug = substr($slug, 0, 80);
    if (empty($slug)) $slug = $topic['slug_prefix'] . '-' . date('Ymd');
    $generated['slug'] = $slug;

    $heroImage = basename(FALLBACK_IMAGE);
    if ($dryRun) {
        cronLog('DRY-RUN: a saltar geração de imagem, uso fallback.');
    } elseif (empty(BLOG_OPENROUTER_API_KEY)) {
        cronLog('Imagem: sem BLOG_OPENROUTER_API_KEY — uso fallback.');
    } else {
        cronLog('A gerar imagem hero com ' . BLOG_IMAGE_MODEL . '...');
        $geminiResult = generateGeminiImage($topic, $slug, $generated['title']);
        if ($geminiResult) { $heroImage = basename($geminiResult); cronLog('Imagem hero gerada por IA.'); }
        else { cronLog('Imagem: IA falhou/recusou — uso fallback.'); }
    }

    if ($dryRun) {
        cronLog("DRY-RUN: inserção na BD ignorada (slug={$slug}).");
    } else {
        $ok = enp_insert_blog_post($generated, $topic, $slug, $heroImage, $today);
        if (!$ok) exit(1);
        cronLog("SUCCESS: post '{$slug}' inserido/atualizado em blog_posts.");
    }

    if (is_file(__DIR__ . '/gerar-sitemap.php')) {
        define('ENP_SITEMAP_INCLUDE', 1);
        require __DIR__ . '/gerar-sitemap.php';
        if (function_exists('enp_gerar_sitemap')) {
            $n = enp_gerar_sitemap();
            cronLog("Sitemap regenerado: {$n} URLs");
        }
    }

    if (!$dryRun) {
        $tracker['used_topics'][]   = $topic['slug_prefix'];
        $tracker['total_generated'] = ($tracker['total_generated'] ?? 0) + 1;
        $tracker['last_date']       = $today;
        $tracker['last_slug']       = $slug;
        saveTracker($tracker);
    } else {
        cronLog('DRY-RUN: tracker não atualizado (para não bloquear o cron real de hoje).');
    }

    cronLog("=== Concluído: {$slug} | Total gerado: " . ($tracker['total_generated'] ?? 0) . ' ===');
} catch (Throwable $e) {
    cronLog('FATAL: ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
    exit(1);
} finally {
    if (isset($lockFp) && $lockFp) { flock($lockFp, LOCK_UN); fclose($lockFp); }
}
