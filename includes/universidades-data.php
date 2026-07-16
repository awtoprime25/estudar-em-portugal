<?php
/**
 * includes/universidades-data.php
 * Universidades e institutos politécnicos de Portugal, para o mapa em
 * universidades.php. Coordenadas ao nível da cidade (suficiente para um
 * pin de mapa). citySlug liga à página destino-{slug}.php quando existe.
 */

const UNIVERSIDADES = [
    ['id' => 'ulisboa', 'nome' => 'Universidade de Lisboa', 'cidade' => 'Lisboa', 'citySlug' => 'lisboa', 'lat' => 38.7526, 'lng' => -9.1568, 'tipo' => 'publica', 'cursos' => ['medicina', 'direito', 'gestao', 'engenharia-informatica', 'engenharia-civil', 'engenharia-mecanica', 'psicologia', 'arquitetura', 'farmacia', 'medicina-veterinaria', 'ciencias-desporto', 'belas-artes', 'biologia', 'economia']],
    ['id' => 'nova-lisboa', 'nome' => 'Universidade NOVA de Lisboa', 'cidade' => 'Lisboa', 'citySlug' => 'lisboa', 'lat' => 38.7369, 'lng' => -9.1610, 'tipo' => 'publica', 'cursos' => ['medicina', 'gestao', 'direito', 'ciencias-comunicacao', 'relacoes-internacionais', 'ciencia-dados', 'economia']],
    ['id' => 'iscte', 'nome' => 'ISCTE — Instituto Universitário de Lisboa', 'cidade' => 'Lisboa', 'citySlug' => 'lisboa', 'lat' => 38.7369, 'lng' => -9.1387, 'tipo' => 'publica', 'cursos' => ['gestao', 'psicologia', 'ciencias-comunicacao', 'relacoes-internacionais', 'marketing']],
    ['id' => 'catolica-lisboa', 'nome' => 'Universidade Católica Portuguesa (Lisboa)', 'cidade' => 'Lisboa', 'citySlug' => 'lisboa', 'lat' => 38.7444, 'lng' => -9.1785, 'tipo' => 'privada', 'cursos' => ['direito', 'gestao', 'psicologia', 'economia', 'marketing']],
    ['id' => 'ipl', 'nome' => 'Instituto Politécnico de Lisboa', 'cidade' => 'Lisboa', 'citySlug' => 'lisboa', 'lat' => 38.7071, 'lng' => -9.1354, 'tipo' => 'politecnico', 'cursos' => ['enfermagem', 'fisioterapia', 'contabilidade', 'turismo', 'educacao-basica']],
    ['id' => 'europeia', 'nome' => 'Universidade Europeia', 'cidade' => 'Lisboa', 'citySlug' => 'lisboa', 'lat' => 38.7595, 'lng' => -9.2265, 'tipo' => 'privada', 'cursos' => ['gestao', 'arquitetura', 'marketing', 'design']],
    ['id' => 'lusofona', 'nome' => 'Universidade Lusófona', 'cidade' => 'Lisboa', 'citySlug' => 'lisboa', 'lat' => 38.7503, 'lng' => -9.1601, 'tipo' => 'privada', 'cursos' => ['psicologia', 'arquitetura', 'gestao', 'ciencias-comunicacao', 'design', 'servico-social']],

    ['id' => 'uporto', 'nome' => 'Universidade do Porto', 'cidade' => 'Porto', 'citySlug' => 'porto', 'lat' => 41.1496, 'lng' => -8.6109, 'tipo' => 'publica', 'cursos' => ['medicina', 'arquitetura', 'engenharia-informatica', 'gestao', 'engenharia-civil', 'engenharia-mecanica', 'nutricao', 'farmacia', 'economia', 'biologia', 'medicina-dentaria']],
    ['id' => 'catolica-porto', 'nome' => 'Universidade Católica Portuguesa (Porto)', 'cidade' => 'Porto', 'citySlug' => 'porto', 'lat' => 41.1774, 'lng' => -8.6094, 'tipo' => 'privada', 'cursos' => ['gestao', 'direito', 'marketing']],
    ['id' => 'ipp', 'nome' => 'Instituto Politécnico do Porto', 'cidade' => 'Porto', 'citySlug' => 'porto', 'lat' => 41.1815, 'lng' => -8.6079, 'tipo' => 'politecnico', 'cursos' => ['enfermagem', 'engenharia-informatica', 'contabilidade', 'turismo']],

    ['id' => 'uc', 'nome' => 'Universidade de Coimbra', 'cidade' => 'Coimbra', 'citySlug' => 'coimbra', 'lat' => 40.2033, 'lng' => -8.4103, 'tipo' => 'publica', 'cursos' => ['direito', 'medicina', 'psicologia', 'farmacia', 'engenharia-informatica', 'engenharia-civil', 'ciencias-desporto', 'biologia', 'ciencias-comunicacao', 'medicina-dentaria']],
    ['id' => 'ipc', 'nome' => 'Instituto Politécnico de Coimbra', 'cidade' => 'Coimbra', 'citySlug' => 'coimbra', 'lat' => 40.2111, 'lng' => -8.4291, 'tipo' => 'politecnico', 'cursos' => ['enfermagem', 'fisioterapia', 'contabilidade', 'educacao-basica']],

    ['id' => 'uminho', 'nome' => 'Universidade do Minho', 'cidade' => 'Braga', 'citySlug' => 'braga', 'lat' => 41.5614, 'lng' => -8.3969, 'tipo' => 'publica', 'cursos' => ['engenharia-informatica', 'gestao', 'direito', 'psicologia', 'economia', 'ciencias-comunicacao', 'engenharia-civil', 'design', 'educacao-basica', 'relacoes-internacionais', 'ciencia-dados']],

    ['id' => 'uaveiro', 'nome' => 'Universidade de Aveiro', 'cidade' => 'Aveiro', 'citySlug' => 'aveiro', 'lat' => 40.6306, 'lng' => -8.6572, 'tipo' => 'publica', 'cursos' => ['engenharia-informatica', 'engenharia-mecanica', 'engenharia-eletrotecnica', 'design', 'turismo', 'biologia', 'ciencia-dados']],

    ['id' => 'uevora', 'nome' => 'Universidade de Évora', 'cidade' => 'Évora', 'citySlug' => 'evora', 'lat' => 38.5716, 'lng' => -7.9077, 'tipo' => 'publica', 'cursos' => ['gestao', 'biologia', 'medicina-veterinaria', 'turismo']],

    ['id' => 'ualg', 'nome' => 'Universidade do Algarve', 'cidade' => 'Faro', 'citySlug' => 'faro', 'lat' => 37.0425, 'lng' => -7.9673, 'tipo' => 'publica', 'cursos' => ['gestao', 'enfermagem', 'turismo', 'biologia', 'nutricao']],

    ['id' => 'ubi', 'nome' => 'Universidade da Beira Interior', 'cidade' => 'Covilhã', 'citySlug' => null, 'lat' => 40.2802, 'lng' => -7.5051, 'tipo' => 'publica', 'cursos' => ['medicina', 'engenharia-informatica', 'engenharia-eletrotecnica', 'ciencias-comunicacao', 'design']],
    ['id' => 'utad', 'nome' => 'Universidade de Trás-os-Montes e Alto Douro', 'cidade' => 'Vila Real', 'citySlug' => null, 'lat' => 41.3021, 'lng' => -7.7439, 'tipo' => 'publica', 'cursos' => ['gestao', 'medicina-veterinaria', 'biologia', 'turismo']],
    ['id' => 'uac', 'nome' => 'Universidade dos Açores', 'cidade' => 'Ponta Delgada', 'citySlug' => null, 'lat' => 37.7412, 'lng' => -25.6756, 'tipo' => 'publica', 'cursos' => ['gestao', 'biologia', 'turismo', 'educacao-basica']],
    ['id' => 'uma', 'nome' => 'Universidade da Madeira', 'cidade' => 'Funchal', 'citySlug' => null, 'lat' => 32.6669, 'lng' => -16.9241, 'tipo' => 'publica', 'cursos' => ['gestao', 'engenharia-informatica', 'turismo', 'biologia']],

    ['id' => 'ips', 'nome' => 'Instituto Politécnico de Setúbal', 'cidade' => 'Setúbal', 'citySlug' => null, 'lat' => 38.5243, 'lng' => -8.8926, 'tipo' => 'politecnico', 'cursos' => ['fisioterapia', 'engenharia-mecanica', 'turismo', 'contabilidade']],
    ['id' => 'ipleiria', 'nome' => 'Instituto Politécnico de Leiria', 'cidade' => 'Leiria', 'citySlug' => null, 'lat' => 39.7436, 'lng' => -8.8070, 'tipo' => 'politecnico', 'cursos' => ['enfermagem', 'gestao', 'turismo', 'educacao-basica']],
    ['id' => 'ipv', 'nome' => 'Instituto Politécnico de Viseu', 'cidade' => 'Viseu', 'citySlug' => null, 'lat' => 40.6566, 'lng' => -7.9122, 'tipo' => 'politecnico', 'cursos' => ['enfermagem', 'contabilidade', 'educacao-basica']],
    ['id' => 'ipg', 'nome' => 'Instituto Politécnico da Guarda', 'cidade' => 'Guarda', 'citySlug' => null, 'lat' => 40.5364, 'lng' => -7.2683, 'tipo' => 'politecnico', 'cursos' => ['gestao', 'turismo', 'educacao-basica']],
    ['id' => 'ipcb', 'nome' => 'Instituto Politécnico de Castelo Branco', 'cidade' => 'Castelo Branco', 'citySlug' => null, 'lat' => 39.8222, 'lng' => -7.4931, 'tipo' => 'politecnico', 'cursos' => ['enfermagem', 'educacao-basica', 'servico-social']],
    ['id' => 'ipb', 'nome' => 'Instituto Politécnico de Bragança', 'cidade' => 'Bragança', 'citySlug' => null, 'lat' => 41.8071, 'lng' => -6.7573, 'tipo' => 'politecnico', 'cursos' => ['gestao', 'educacao-basica', 'biologia']],
    ['id' => 'ipsantarem', 'nome' => 'Instituto Politécnico de Santarém', 'cidade' => 'Santarém', 'citySlug' => null, 'lat' => 39.2369, 'lng' => -8.6859, 'tipo' => 'politecnico', 'cursos' => ['gestao', 'educacao-basica', 'turismo']],
    ['id' => 'ipportalegre', 'nome' => 'Instituto Politécnico de Portalegre', 'cidade' => 'Portalegre', 'citySlug' => null, 'lat' => 39.2967, 'lng' => -7.4281, 'tipo' => 'politecnico', 'cursos' => ['gestao', 'educacao-basica', 'servico-social']],
];
