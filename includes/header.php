<?php
/**
 * includes/header.php
 * Cabeçalho partilhado: <head> + nav. Espera (opcionalmente) que a página
 * que o inclui já tenha definido $pageTitle, $pageDescription, $activeNav,
 * $ogImage, $extraJsonLd e $noindex.
 */
require_once __DIR__ . '/../config.php';

$pageTitle       = $pageTitle       ?? SITE_NAME;
$pageDescription = $pageDescription ?? SITE_DESCRIPTION;
$activeNav       = $activeNav       ?? '';
$ogImage         = $ogImage         ?? SITE_URL . 'assets/images/ogi-comparar.png';
$noindex         = !empty($noindex);
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0a1628">

    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <?php if ($noindex): ?>
    <meta name="robots" content="noindex, nofollow">
    <?php else: ?>
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">
    <link rel="canonical" href="<?= e(SITE_URL . ltrim(strtok($_SERVER['REQUEST_URI'] ?? '/', '?'), '/')) ?>">
    <?php endif; ?>

    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:url" content="<?= e(SITE_URL . ltrim(strtok($_SERVER['REQUEST_URI'] ?? '/', '?'), '/')) ?>">
    <meta property="og:site_name" content="<?= e(SITE_SHORT_NAME) ?> — Da Vinci × StudyWing">
    <meta property="og:locale" content="pt_PT">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($pageDescription) ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": ["LocalBusiness", "EducationalOrganization"],
          "@id": "<?= e(SITE_URL) ?>#org",
          "name": "<?= e(SITE_NAME) ?> — Da Vinci × StudyWing",
          "alternateName": "Lá Fora",
          "url": "<?= e(SITE_URL) ?>",
          "telephone": "<?= e(CONTACT_PHONE_TEL) ?>",
          "email": "<?= e(CONTACT_EMAIL) ?>",
          "address": {
            "@type": "PostalAddress",
            "streetAddress": "Largo do Carmo nº51",
            "postalCode": "8000-148",
            "addressLocality": "Faro",
            "addressCountry": "PT"
          },
          "areaServed": [
            {"@type": "Country", "name": "Portugal"},
            {"@type": "Country", "name": "Brazil"},
            {"@type": "Continent", "name": "Europe"}
          ],
          "brand": [
            {"@type": "Brand", "name": "Ginásios Da Vinci"},
            {"@type": "Brand", "name": "StudyWing"}
          ],
          "parentOrganization": {
            "@type": "Organization",
            "name": "Da Vinci \u00d7 StudyWing (Programa L\u00e1 Fora)",
            "url": "<?= e(SITE_URL) ?>",
            "member": [
              {"@type": "Organization", "name": "Ginásios Da Vinci", "url": "https://www.ginasiosdavinci.com/"},
              {"@type": "Organization", "name": "StudyWing", "url": "https://studywing.org/"}
            ]
          },
          "serviceType": "Consultoria de admissão universitária na Europa"
        },
        {
          "@type": "WebSite",
          "@id": "<?= e(SITE_URL) ?>#site",
          "url": "<?= e(SITE_URL) ?>",
          "name": "<?= e(SITE_NAME) ?>",
          "inLanguage": "pt-PT",
          "publisher": {"@id": "<?= e(SITE_URL) ?>#org"}
        }
      ]
    }
    </script>
    <?php if (!empty($extraJsonLd)):
        // Defesa contra XSS / parser-break: neutralizar </script> literals.
        $extraJsonLdSafe = str_replace(['</script>', '</SCRIPT>', '</ Script>'], ['<\\/script>', '<\\/SCRIPT>', '<\\/ Script>'], (string) $extraJsonLd);
    ?>
    <script type="application/ld+json"><?= $extraJsonLdSafe ?></script>
    <?php endif; ?>

    <link rel="alternate" hreflang="pt-BR" href="<?= e(SITE_URL . ltrim(strtok($_SERVER['REQUEST_URI'] ?? '/', '?'), '/')) ?>">
    <link rel="alternate" hreflang="pt-PT" href="<?= e(SITE_URL . ltrim(strtok($_SERVER['REQUEST_URI'] ?? '/', '?'), '/')) ?>">
    <link rel="alternate" hreflang="x-default" href="<?= e(SITE_URL . ltrim(strtok($_SERVER['REQUEST_URI'] ?? '/', '?'), '/')) ?>">

    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/style.css')) ?>">
    <?= $extraHeadHtml ?? '' ?>
</head>
<body>

<a href="#conteudo" class="skip-link">Saltar para o conteúdo</a>

<header class="site-nav">
    <div class="container site-nav__inner">
        <a href="./" class="brand" aria-label="<?= e(SITE_SHORT_NAME) ?> — Início">
            <div class="brand-logos">
                <img src="assets/logo-davinci.svg" alt="Ginásios Da Vinci" class="brand__logo brand__logo--davinci">
                <span class="brand__times" aria-hidden="true">×</span>
                <img src="assets/images/logotipo-studywing.png" alt="StudyWing — Parceiro Internacional" class="brand__logo brand__logo--studywing">
            </div>
        </a>

        <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="primaryNav" aria-label="Abrir menu">
            <span></span><span></span><span></span>
        </button>

        <nav id="primaryNav" class="primary-nav" aria-label="Navegação principal">
            <ul>
                <li><a href="./#destinos" class="<?= $activeNav === 'destinos' ? 'is-active' : '' ?>">Destinos</a></li>
                <li><a href="./#como-funciona" class="<?= $activeNav === 'como-funciona' ? 'is-active' : '' ?>">Como funciona</a></li>
                <li><a href="explicacoes.php" class="<?= $activeNav === 'explicacoes' ? 'is-active' : '' ?>">Explicações</a></li>
                <li><a href="concurso-especial-estudantes-internacionais.php" class="<?= $activeNav === 'concurso-especial' ? 'is-active' : '' ?>">Concurso Especial</a></li>
                <li><a href="comparar.php" class="<?= $activeNav === 'comparar' ? 'is-active' : '' ?>">Comparar</a></li>
                <li><a href="blog.php" class="<?= $activeNav === 'blog' ? 'is-active' : '' ?>">Blog</a></li>
                <li><a href="contato.php" class="<?= $activeNav === 'contato' ? 'is-active' : '' ?>">Contato</a></li>
            </ul>
            <div class="nav-actions">
                <div class="locale-switch" data-locale-switch role="group" aria-label="Idioma / variante">
                    <button type="button" class="is-active" data-locale="br">PT-BR</button>
                    <button type="button" data-locale="pt">PT-PT</button>
                </div>
                <a href="comparar.php#formulario" class="btn btn-pill btn-light-outline">Agendar consultoria</a>
            </div>
        </nav>
    </div>
</header>
