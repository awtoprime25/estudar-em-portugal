<?php
/**
 * includes/universidades-data.php
 * Universidades e institutos politécnicos de Portugal, para o mapa em
 * universidades.php. Coordenadas ao nível da cidade (suficiente para um
 * pin de mapa). citySlug liga à página destino-{slug}.php quando existe.
 *
 * natureza: 'publica' | 'privada'
 * grau:     'universidade' | 'politecnico'
 * Lista construída a partir do índice DGES / Wikipédia de instituições de
 * ensino superior em Portugal — cobre as universidades e politécnicos
 * (públicos e privados) reconhecidos no país. Academias militares/policiais
 * ficam fora por não serem relevantes para candidatos internacionais.
 */

const UNIVERSIDADES = [
    // ── Lisboa ──────────────────────────────────────────────────────────
    ['id' => 'ulisboa', 'nome' => 'Universidade de Lisboa', 'cidade' => 'Lisboa', 'citySlug' => 'lisboa', 'lat' => 38.7526, 'lng' => -9.1568, 'natureza' => 'publica', 'grau' => 'universidade', 'cursos' => ['medicina', 'direito', 'gestao', 'engenharia-informatica', 'engenharia-civil', 'engenharia-mecanica', 'psicologia', 'arquitetura', 'farmacia', 'medicina-veterinaria', 'ciencias-desporto', 'belas-artes', 'biologia', 'economia']],
    ['id' => 'nova-lisboa', 'nome' => 'Universidade NOVA de Lisboa', 'cidade' => 'Lisboa', 'citySlug' => 'lisboa', 'lat' => 38.7369, 'lng' => -9.1610, 'natureza' => 'publica', 'grau' => 'universidade', 'cursos' => ['medicina', 'gestao', 'direito', 'ciencias-comunicacao', 'relacoes-internacionais', 'ciencia-dados', 'economia']],
    ['id' => 'iscte', 'nome' => 'ISCTE — Instituto Universitário de Lisboa', 'cidade' => 'Lisboa', 'citySlug' => 'lisboa', 'lat' => 38.7369, 'lng' => -9.1387, 'natureza' => 'publica', 'grau' => 'universidade', 'cursos' => ['gestao', 'psicologia', 'ciencias-comunicacao', 'relacoes-internacionais', 'marketing']],
    ['id' => 'uaberta', 'nome' => 'Universidade Aberta', 'cidade' => 'Lisboa', 'citySlug' => 'lisboa', 'lat' => 38.7167, 'lng' => -9.1450, 'natureza' => 'publica', 'grau' => 'universidade', 'cursos' => ['gestao', 'educacao-basica', 'ciencias-comunicacao']],
    ['id' => 'catolica-lisboa', 'nome' => 'Universidade Católica Portuguesa (Lisboa)', 'cidade' => 'Lisboa', 'citySlug' => 'lisboa', 'lat' => 38.7444, 'lng' => -9.1785, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['direito', 'gestao', 'psicologia', 'economia', 'marketing']],
    ['id' => 'europeia', 'nome' => 'Universidade Europeia', 'cidade' => 'Lisboa', 'citySlug' => 'lisboa', 'lat' => 38.7595, 'lng' => -9.2265, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['gestao', 'arquitetura', 'marketing', 'design']],
    ['id' => 'lusofona', 'nome' => 'Universidade Lusófona (Lisboa)', 'cidade' => 'Lisboa', 'citySlug' => 'lisboa', 'lat' => 38.7503, 'lng' => -9.1601, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['psicologia', 'arquitetura', 'gestao', 'ciencias-comunicacao', 'design', 'servico-social']],
    ['id' => 'autonoma-lisboa', 'nome' => 'Universidade Autónoma de Lisboa', 'cidade' => 'Lisboa', 'citySlug' => 'lisboa', 'lat' => 38.7295, 'lng' => -9.1487, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['direito', 'gestao', 'relacoes-internacionais']],
    ['id' => 'lusiada-lisboa', 'nome' => 'Universidade Lusíada (Lisboa)', 'cidade' => 'Lisboa', 'citySlug' => 'lisboa', 'lat' => 38.7550, 'lng' => -9.1810, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['direito', 'arquitetura', 'gestao', 'psicologia', 'ciencias-comunicacao']],
    ['id' => 'ispa', 'nome' => 'ISPA — Instituto Universitário', 'cidade' => 'Lisboa', 'citySlug' => 'lisboa', 'lat' => 38.7616, 'lng' => -9.1305, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['psicologia', 'educacao-basica', 'biologia']],
    ['id' => 'isg', 'nome' => 'Instituto Superior de Gestão (ISG)', 'cidade' => 'Lisboa', 'citySlug' => 'lisboa', 'lat' => 38.7280, 'lng' => -9.1500, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['gestao', 'marketing', 'contabilidade', 'economia']],
    ['id' => 'ip-lusofonia', 'nome' => 'Instituto Politécnico da Lusofonia', 'cidade' => 'Lisboa', 'citySlug' => 'lisboa', 'lat' => 38.7500, 'lng' => -9.1550, 'natureza' => 'privada', 'grau' => 'politecnico', 'cursos' => ['gestao', 'ciencias-comunicacao']],
    ['id' => 'ipl', 'nome' => 'Instituto Politécnico de Lisboa', 'cidade' => 'Lisboa', 'citySlug' => 'lisboa', 'lat' => 38.7071, 'lng' => -9.1354, 'natureza' => 'publica', 'grau' => 'politecnico', 'cursos' => ['enfermagem', 'fisioterapia', 'contabilidade', 'turismo', 'educacao-basica']],

    // ── Almada / Oeiras (Grande Lisboa) ─────────────────────────────────
    ['id' => 'egas-moniz', 'nome' => 'Instituto Universitário Egas Moniz', 'cidade' => 'Almada', 'citySlug' => null, 'lat' => 38.6650, 'lng' => -9.1900, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['medicina-dentaria', 'farmacia', 'fisioterapia', 'nutricao', 'enfermagem']],
    ['id' => 'piaget-almada', 'nome' => 'Instituto Piaget — Almada', 'cidade' => 'Almada', 'citySlug' => null, 'lat' => 38.6800, 'lng' => -9.1580, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['psicologia', 'servico-social', 'educacao-basica', 'fisioterapia', 'enfermagem']],
    ['id' => 'ippiaget-sul', 'nome' => 'Instituto Politécnico Jean Piaget do Sul', 'cidade' => 'Almada', 'citySlug' => null, 'lat' => 38.6790, 'lng' => -9.1600, 'natureza' => 'privada', 'grau' => 'politecnico', 'cursos' => ['enfermagem', 'educacao-basica', 'servico-social']],
    ['id' => 'atlantica', 'nome' => 'Atlântica — Instituto Universitário', 'cidade' => 'Oeiras', 'citySlug' => null, 'lat' => 38.6979, 'lng' => -9.3090, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['fisioterapia', 'nutricao', 'enfermagem', 'gestao']],

    // ── Porto ───────────────────────────────────────────────────────────
    ['id' => 'uporto', 'nome' => 'Universidade do Porto', 'cidade' => 'Porto', 'citySlug' => 'porto', 'lat' => 41.1496, 'lng' => -8.6109, 'natureza' => 'publica', 'grau' => 'universidade', 'cursos' => ['medicina', 'arquitetura', 'engenharia-informatica', 'gestao', 'engenharia-civil', 'engenharia-mecanica', 'nutricao', 'farmacia', 'economia', 'biologia', 'medicina-dentaria']],
    ['id' => 'catolica-porto', 'nome' => 'Universidade Católica Portuguesa (Porto)', 'cidade' => 'Porto', 'citySlug' => 'porto', 'lat' => 41.1774, 'lng' => -8.6094, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['gestao', 'direito', 'marketing']],
    ['id' => 'ufp', 'nome' => 'Universidade Fernando Pessoa', 'cidade' => 'Porto', 'citySlug' => 'porto', 'lat' => 41.1670, 'lng' => -8.6250, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['medicina', 'medicina-dentaria', 'nutricao', 'arquitetura', 'ciencias-comunicacao', 'relacoes-internacionais', 'engenharia-informatica', 'psicologia']],
    ['id' => 'lusiada-porto', 'nome' => 'Universidade Lusíada — Norte (Porto)', 'cidade' => 'Porto', 'citySlug' => 'porto', 'lat' => 41.1690, 'lng' => -8.6430, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['direito', 'arquitetura', 'gestao', 'design', 'psicologia', 'relacoes-internacionais']],
    ['id' => 'lusiada-famalicao', 'nome' => 'Universidade Lusíada — Famalicão', 'cidade' => 'Vila Nova de Famalicão', 'citySlug' => null, 'lat' => 41.4084, 'lng' => -8.5182, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['arquitetura', 'economia']],
    ['id' => 'lusofona-porto', 'nome' => 'Universidade Lusófona do Porto', 'cidade' => 'Porto', 'citySlug' => 'porto', 'lat' => 41.1520, 'lng' => -8.6150, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['psicologia', 'gestao', 'ciencias-comunicacao', 'design']],
    ['id' => 'portucalense', 'nome' => 'Universidade Portucalense', 'cidade' => 'Porto', 'citySlug' => 'porto', 'lat' => 41.1550, 'lng' => -8.6320, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['direito', 'gestao', 'psicologia', 'economia']],
    ['id' => 'esap', 'nome' => 'Escola Superior Artística do Porto', 'cidade' => 'Porto', 'citySlug' => 'porto', 'lat' => 41.1490, 'lng' => -8.6080, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['belas-artes', 'design', 'arquitetura']],
    ['id' => 'ipp', 'nome' => 'Instituto Politécnico do Porto', 'cidade' => 'Porto', 'citySlug' => 'porto', 'lat' => 41.1815, 'lng' => -8.6079, 'natureza' => 'publica', 'grau' => 'politecnico', 'cursos' => ['enfermagem', 'engenharia-informatica', 'contabilidade', 'turismo']],

    // ── Maia / Gaia / Matosinhos / Paredes (Área Metropolitana do Porto) ─
    ['id' => 'umaia', 'nome' => 'Universidade da Maia', 'cidade' => 'Maia', 'citySlug' => null, 'lat' => 41.2354, 'lng' => -8.6208, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['gestao', 'psicologia', 'ciencias-desporto', 'ciencias-comunicacao']],
    ['id' => 'ipmaia', 'nome' => 'Instituto Politécnico da Maia', 'cidade' => 'Maia', 'citySlug' => null, 'lat' => 41.2340, 'lng' => -8.6190, 'natureza' => 'privada', 'grau' => 'politecnico', 'cursos' => ['gestao', 'engenharia-informatica', 'ciencias-desporto']],
    ['id' => 'iucs-cespu', 'nome' => 'Instituto Universitário de Ciências da Saúde (CESPU)', 'cidade' => 'Paredes', 'citySlug' => null, 'lat' => 41.2077, 'lng' => -8.3313, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['farmacia', 'medicina-dentaria', 'fisioterapia', 'nutricao']],
    ['id' => 'ipsn-cespu', 'nome' => 'Instituto Politécnico de Saúde do Norte (CESPU)', 'cidade' => 'Paredes', 'citySlug' => null, 'lat' => 41.2080, 'lng' => -8.3320, 'natureza' => 'privada', 'grau' => 'politecnico', 'cursos' => ['enfermagem', 'fisioterapia', 'farmacia']],
    ['id' => 'ippiaget-norte', 'nome' => 'Instituto Politécnico Jean Piaget do Norte', 'cidade' => 'Vila Nova de Gaia', 'citySlug' => null, 'lat' => 41.1239, 'lng' => -8.6118, 'natureza' => 'privada', 'grau' => 'politecnico', 'cursos' => ['enfermagem', 'educacao-basica', 'servico-social']],
    ['id' => 'ispgaya', 'nome' => 'Instituto Superior Politécnico Gaya (ISPGAYA)', 'cidade' => 'Vila Nova de Gaia', 'citySlug' => null, 'lat' => 41.1200, 'lng' => -8.6100, 'natureza' => 'privada', 'grau' => 'politecnico', 'cursos' => ['gestao', 'engenharia-informatica', 'turismo']],
    ['id' => 'isla-gaya', 'nome' => 'ISLA — Instituto Politécnico de Gestão e Tecnologia', 'cidade' => 'Vila Nova de Gaia', 'citySlug' => null, 'lat' => 41.1250, 'lng' => -8.6150, 'natureza' => 'privada', 'grau' => 'politecnico', 'cursos' => ['gestao', 'engenharia-informatica', 'marketing', 'contabilidade']],
    ['id' => 'isssp', 'nome' => 'Instituto Superior de Serviço Social do Porto', 'cidade' => 'Matosinhos', 'citySlug' => null, 'lat' => 41.1839, 'lng' => -8.6844, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['servico-social']],

    // ── Coimbra ─────────────────────────────────────────────────────────
    ['id' => 'uc', 'nome' => 'Universidade de Coimbra', 'cidade' => 'Coimbra', 'citySlug' => 'coimbra', 'lat' => 40.2033, 'lng' => -8.4103, 'natureza' => 'publica', 'grau' => 'universidade', 'cursos' => ['direito', 'medicina', 'psicologia', 'farmacia', 'engenharia-informatica', 'engenharia-civil', 'ciencias-desporto', 'biologia', 'ciencias-comunicacao', 'medicina-dentaria']],
    ['id' => 'ismt', 'nome' => 'Instituto Superior Miguel Torga', 'cidade' => 'Coimbra', 'citySlug' => 'coimbra', 'lat' => 40.1990, 'lng' => -8.4200, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['psicologia', 'servico-social', 'educacao-basica']],
    ['id' => 'euvg', 'nome' => 'Escola Universitária Vasco da Gama', 'cidade' => 'Coimbra', 'citySlug' => 'coimbra', 'lat' => 40.2100, 'lng' => -8.4300, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['medicina-veterinaria']],
    ['id' => 'ipc', 'nome' => 'Instituto Politécnico de Coimbra', 'cidade' => 'Coimbra', 'citySlug' => 'coimbra', 'lat' => 40.2111, 'lng' => -8.4291, 'natureza' => 'publica', 'grau' => 'politecnico', 'cursos' => ['enfermagem', 'fisioterapia', 'contabilidade', 'educacao-basica']],

    // ── Braga ───────────────────────────────────────────────────────────
    ['id' => 'uminho', 'nome' => 'Universidade do Minho', 'cidade' => 'Braga', 'citySlug' => 'braga', 'lat' => 41.5614, 'lng' => -8.3969, 'natureza' => 'publica', 'grau' => 'universidade', 'cursos' => ['engenharia-informatica', 'gestao', 'direito', 'psicologia', 'economia', 'ciencias-comunicacao', 'engenharia-civil', 'design', 'educacao-basica', 'relacoes-internacionais', 'ciencia-dados']],

    // ── Barcelos ────────────────────────────────────────────────────────
    ['id' => 'ipca', 'nome' => 'Instituto Politécnico do Cávado e do Ave', 'cidade' => 'Barcelos', 'citySlug' => null, 'lat' => 41.5388, 'lng' => -8.6151, 'natureza' => 'publica', 'grau' => 'politecnico', 'cursos' => ['gestao', 'design', 'engenharia-informatica', 'ciencias-desporto']],

    // ── Viana do Castelo ────────────────────────────────────────────────
    ['id' => 'ipvc', 'nome' => 'Instituto Politécnico de Viana do Castelo', 'cidade' => 'Viana do Castelo', 'citySlug' => null, 'lat' => 41.6932, 'lng' => -8.8329, 'natureza' => 'publica', 'grau' => 'politecnico', 'cursos' => ['turismo', 'enfermagem', 'gestao', 'educacao-basica']],

    // ── Vila Nova de Cerveira ───────────────────────────────────────────
    ['id' => 'esg-cerveira', 'nome' => 'Escola Superior Gallaecia', 'cidade' => 'Vila Nova de Cerveira', 'citySlug' => null, 'lat' => 41.9350, 'lng' => -8.7430, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['arquitetura', 'design']],

    // ── Aveiro ──────────────────────────────────────────────────────────
    ['id' => 'uaveiro', 'nome' => 'Universidade de Aveiro', 'cidade' => 'Aveiro', 'citySlug' => 'aveiro', 'lat' => 40.6306, 'lng' => -8.6572, 'natureza' => 'publica', 'grau' => 'universidade', 'cursos' => ['engenharia-informatica', 'engenharia-mecanica', 'engenharia-eletrotecnica', 'design', 'turismo', 'biologia', 'ciencia-dados']],

    // ── Évora ───────────────────────────────────────────────────────────
    ['id' => 'uevora', 'nome' => 'Universidade de Évora', 'cidade' => 'Évora', 'citySlug' => 'evora', 'lat' => 38.5716, 'lng' => -7.9077, 'natureza' => 'publica', 'grau' => 'universidade', 'cursos' => ['gestao', 'biologia', 'medicina-veterinaria', 'turismo']],

    // ── Beja ────────────────────────────────────────────────────────────
    ['id' => 'ipbeja', 'nome' => 'Instituto Politécnico de Beja', 'cidade' => 'Beja', 'citySlug' => null, 'lat' => 38.0150, 'lng' => -7.8632, 'natureza' => 'publica', 'grau' => 'politecnico', 'cursos' => ['gestao', 'enfermagem', 'turismo', 'educacao-basica']],

    // ── Faro / Portimão (Algarve) ───────────────────────────────────────
    ['id' => 'ualg', 'nome' => 'Universidade do Algarve', 'cidade' => 'Faro', 'citySlug' => 'faro', 'lat' => 37.0425, 'lng' => -7.9673, 'natureza' => 'publica', 'grau' => 'universidade', 'cursos' => ['gestao', 'enfermagem', 'turismo', 'biologia', 'nutricao']],
    ['id' => 'ismat', 'nome' => 'Instituto Superior Manuel Teixeira Gomes', 'cidade' => 'Portimão', 'citySlug' => null, 'lat' => 37.1364, 'lng' => -8.5380, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['direito', 'gestao', 'engenharia-informatica', 'turismo']],

    // ── Interior / Ilhas ────────────────────────────────────────────────
    ['id' => 'ubi', 'nome' => 'Universidade da Beira Interior', 'cidade' => 'Covilhã', 'citySlug' => null, 'lat' => 40.2802, 'lng' => -7.5051, 'natureza' => 'publica', 'grau' => 'universidade', 'cursos' => ['medicina', 'engenharia-informatica', 'engenharia-eletrotecnica', 'ciencias-comunicacao', 'design']],
    ['id' => 'utad', 'nome' => 'Universidade de Trás-os-Montes e Alto Douro', 'cidade' => 'Vila Real', 'citySlug' => null, 'lat' => 41.3021, 'lng' => -7.7439, 'natureza' => 'publica', 'grau' => 'universidade', 'cursos' => ['gestao', 'medicina-veterinaria', 'biologia', 'turismo']],
    ['id' => 'uac', 'nome' => 'Universidade dos Açores', 'cidade' => 'Ponta Delgada', 'citySlug' => null, 'lat' => 37.7412, 'lng' => -25.6756, 'natureza' => 'publica', 'grau' => 'universidade', 'cursos' => ['gestao', 'biologia', 'turismo', 'educacao-basica']],
    ['id' => 'uma', 'nome' => 'Universidade da Madeira', 'cidade' => 'Funchal', 'citySlug' => null, 'lat' => 32.6669, 'lng' => -16.9241, 'natureza' => 'publica', 'grau' => 'universidade', 'cursos' => ['gestao', 'engenharia-informatica', 'turismo', 'biologia']],

    // ── Institutos Politécnicos — Centro e Interior ─────────────────────
    ['id' => 'ips', 'nome' => 'Instituto Politécnico de Setúbal', 'cidade' => 'Setúbal', 'citySlug' => null, 'lat' => 38.5243, 'lng' => -8.8926, 'natureza' => 'publica', 'grau' => 'politecnico', 'cursos' => ['fisioterapia', 'engenharia-mecanica', 'turismo', 'contabilidade']],
    ['id' => 'ipleiria', 'nome' => 'Instituto Politécnico de Leiria', 'cidade' => 'Leiria', 'citySlug' => null, 'lat' => 39.7436, 'lng' => -8.8070, 'natureza' => 'publica', 'grau' => 'politecnico', 'cursos' => ['enfermagem', 'gestao', 'turismo', 'educacao-basica']],
    ['id' => 'iptomar', 'nome' => 'Instituto Politécnico de Tomar', 'cidade' => 'Tomar', 'citySlug' => null, 'lat' => 39.6033, 'lng' => -8.4109, 'natureza' => 'publica', 'grau' => 'politecnico', 'cursos' => ['turismo', 'engenharia-informatica', 'contabilidade']],
    ['id' => 'ipv', 'nome' => 'Instituto Politécnico de Viseu', 'cidade' => 'Viseu', 'citySlug' => null, 'lat' => 40.6566, 'lng' => -7.9122, 'natureza' => 'publica', 'grau' => 'politecnico', 'cursos' => ['enfermagem', 'contabilidade', 'educacao-basica']],
    ['id' => 'piaget-viseu', 'nome' => 'Instituto Piaget — Viseu', 'cidade' => 'Viseu', 'citySlug' => null, 'lat' => 40.6580, 'lng' => -7.9150, 'natureza' => 'privada', 'grau' => 'universidade', 'cursos' => ['psicologia', 'servico-social', 'educacao-basica', 'fisioterapia', 'enfermagem']],
    ['id' => 'ipg', 'nome' => 'Instituto Politécnico da Guarda', 'cidade' => 'Guarda', 'citySlug' => null, 'lat' => 40.5364, 'lng' => -7.2683, 'natureza' => 'publica', 'grau' => 'politecnico', 'cursos' => ['gestao', 'turismo', 'educacao-basica']],
    ['id' => 'ipcb', 'nome' => 'Instituto Politécnico de Castelo Branco', 'cidade' => 'Castelo Branco', 'citySlug' => null, 'lat' => 39.8222, 'lng' => -7.4931, 'natureza' => 'publica', 'grau' => 'politecnico', 'cursos' => ['enfermagem', 'educacao-basica', 'servico-social']],
    ['id' => 'ipb', 'nome' => 'Instituto Politécnico de Bragança', 'cidade' => 'Bragança', 'citySlug' => null, 'lat' => 41.8071, 'lng' => -6.7573, 'natureza' => 'publica', 'grau' => 'politecnico', 'cursos' => ['gestao', 'educacao-basica', 'biologia']],
    ['id' => 'ipsantarem', 'nome' => 'Instituto Politécnico de Santarém', 'cidade' => 'Santarém', 'citySlug' => null, 'lat' => 39.2369, 'lng' => -8.6859, 'natureza' => 'publica', 'grau' => 'politecnico', 'cursos' => ['gestao', 'educacao-basica', 'turismo']],
    ['id' => 'ipportalegre', 'nome' => 'Instituto Politécnico de Portalegre', 'cidade' => 'Portalegre', 'citySlug' => null, 'lat' => 39.2967, 'lng' => -7.4281, 'natureza' => 'publica', 'grau' => 'politecnico', 'cursos' => ['gestao', 'educacao-basica', 'servico-social']],
];
