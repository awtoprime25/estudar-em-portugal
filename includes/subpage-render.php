<?php
/**
 * includes/subpage-render.php
 * Renderiza as páginas de destino (cidade) e de curso a partir dos dados
 * em subpage-data.php. Cada ficheiro fino destino-*.php / curso-*.php
 * chama uma destas funções — mantém URLs limpas sem duplicar markup.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/subpage-data.php';

function render_destino_page(string $slug): void
{
    $city = DESTINOS[$slug] ?? null;
    if ($city === null) {
        http_response_code(404);
        $pageTitle       = 'Página não encontrada | Lá Fora';
        $pageDescription = 'Esta página não existe.';
        $noindex         = true;
        require_once __DIR__ . '/header.php';
        echo '<main id="conteudo"><div class="container" style="padding:80px 0;text-align:center;"><h1>Página não encontrada</h1><p><a href="destinos.php">Ver todos os destinos</a></p></div></main>';
        require_once __DIR__ . '/footer.php';
        return;
    }

    $pageTitle       = 'Estudar em ' . $city['nome'] . ' — Guia para Brasileiros | Lá Fora';
    $pageDescription = $city['resumo'];
    $activeNav       = 'destinos';
    $pageSlug        = 'destino-' . $slug;

    $extraJsonLd = json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type'       => 'TouristDestination',
                'name'        => $city['nome'] . ', Portugal',
                'description' => $city['resumo'],
                'url'         => SITE_URL . 'destino-' . $slug . '.php',
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => SITE_URL],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Destinos', 'item' => SITE_URL . '#destinos'],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $city['nome'], 'item' => SITE_URL . 'destino-' . $slug . '.php'],
                ],
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    require_once __DIR__ . '/header.php';
    ?>
    <main id="conteudo">

      <section class="hero">
        <div class="container hero__grid">
          <div class="hero__copy">
            <span class="eyebrow"><?= e($city['eyebrow']) ?></span>
            <h1>Estudar em <span class="accent"><?= e($city['nome']) ?></span></h1>
            <p class="lede"><?= e($city['resumo']) ?></p>
            <div class="hero__ctas">
              <a href="comparar.php#formulario" class="btn-pill btn-teal">Agendar consultoria gratuita</a>
            </div>
          </div>
          <div class="hero__art">
            <div class="hero__circle">
              <img src="<?= e(site_image($city['imagem'])) ?>" alt="Estudar em <?= e($city['nome']) ?>">
            </div>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="icon-row">
            <?php foreach ($city['highlights'] as $h): ?>
            <div class="icon-card">
              <div class="icon-card__glyph"><i class="bi <?= e($h['icon']) ?>"></i></div>
              <h3><?= e($h['titulo']) ?></h3>
              <p><?= e($h['texto']) ?></p>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="section section-dark">
        <div class="container">
          <div class="section-head on-dark">
            <div><h2>Universidades em <?= e($city['nome']) ?></h2></div>
          </div>
          <div class="content-block content-block--wide" style="padding-top:12px;">
            <ul>
              <?php foreach ($city['universidades'] as $u): ?>
              <li><?= e($u) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="section-head">
            <div><h2>Custo de vida e vida académica</h2></div>
          </div>
          <div class="content-block content-block--wide" style="padding-top:12px;">
            <p><strong>Custo de vida:</strong> <?= e($city['custo_vida']) ?></p>
            <p><?= e($city['vida_academica']) ?></p>
            <p><strong>Transportes:</strong> <?= e($city['transportes']) ?></p>
          </div>
        </div>
      </section>

      <?php if (!empty($city['cursos_destaque'])): ?>
      <section class="section section-dark">
        <div class="container">
          <div class="section-head on-dark">
            <div><h2>Cursos em destaque em <?= e($city['nome']) ?></h2></div>
          </div>
          <div class="icon-row">
            <?php foreach ($city['cursos_destaque'] as $cSlug): $c = CURSOS[$cSlug] ?? null; if (!$c) continue; ?>
            <a href="curso-<?= e($cSlug) ?>.php" class="icon-card" style="text-decoration:none;color:inherit;">
              <div class="icon-card__glyph"><i class="bi <?= e($c['icone']) ?>"></i></div>
              <h3><?= e($c['nome']) ?></h3>
              <p><?= e($c['duracao']) ?></p>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
      <?php endif; ?>

      <section class="section">
        <div class="container">
          <div class="article-cta">
            <h3>Queres estudar em <?= e($city['nome']) ?>?</h3>
            <p>A equipa Da Vinci × StudyWing acompanha-te desde a escolha do curso até à chegada a Portugal.</p>
            <a href="comparar.php#formulario" class="btn-pill btn-teal">Agendar consultoria gratuita</a>
          </div>
        </div>
      </section>

    </main>
    <?php
    require_once __DIR__ . '/footer.php';
}

function render_curso_page(string $slug): void
{
    $course = CURSOS[$slug] ?? null;
    if ($course === null) {
        http_response_code(404);
        $pageTitle       = 'Página não encontrada | Lá Fora';
        $pageDescription = 'Esta página não existe.';
        $noindex         = true;
        require_once __DIR__ . '/header.php';
        echo '<main id="conteudo"><div class="container" style="padding:80px 0;text-align:center;"><h1>Página não encontrada</h1><p><a href="./">Voltar ao início</a></p></div></main>';
        require_once __DIR__ . '/footer.php';
        return;
    }

    $pageTitle       = e($course['nome']) . ' em Portugal para Brasileiros | Lá Fora';
    $pageDescription = $course['resumo'];
    $pageSlug        = 'curso-' . $slug;

    $extraJsonLd = json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type'       => 'Course',
                'name'        => $course['nome'],
                'description' => $course['resumo'],
                'provider'    => ['@type' => 'Organization', 'name' => 'Ginásios Da Vinci', 'sameAs' => 'https://www.ginasiosdavinci.com/'],
                'url'         => SITE_URL . 'curso-' . $slug . '.php',
                'inLanguage'  => 'pt-PT',
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => SITE_URL],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => $course['nome'], 'item' => SITE_URL . 'curso-' . $slug . '.php'],
                ],
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    require_once __DIR__ . '/header.php';
    ?>
    <main id="conteudo">

      <section class="hero">
        <div class="container hero__grid">
          <div class="hero__copy">
            <span class="eyebrow"><?= e($course['eyebrow']) ?></span>
            <h1><?= e($course['nome']) ?> <span class="accent">em Portugal</span></h1>
            <p class="lede"><?= e($course['resumo']) ?></p>
            <div class="hero__ctas">
              <a href="comparar.php#formulario" class="btn-pill btn-teal">Agendar consultoria gratuita</a>
            </div>
          </div>
          <div class="hero__art">
            <div class="hero__circle hero__circle--icon">
              <i class="bi <?= e($course['icone']) ?>"></i>
            </div>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="icon-row">
            <?php foreach ($course['highlights'] as $h): ?>
            <div class="icon-card">
              <div class="icon-card__glyph"><i class="bi <?= e($h['icon']) ?>"></i></div>
              <h3><?= e($h['titulo']) ?></h3>
              <p><?= e($h['texto']) ?></p>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="section section-dark">
        <div class="container">
          <div class="section-head on-dark">
            <div><h2>Duração e reconhecimento</h2></div>
          </div>
          <div class="content-block content-block--wide" style="padding-top:12px;">
            <p><strong>Duração:</strong> <?= e($course['duracao']) ?></p>
            <p><?= e($course['observacoes']) ?></p>
            <p><strong>Saídas profissionais:</strong> <?= e($course['saidas']) ?></p>
          </div>
        </div>
      </section>

      <?php if (!empty($course['universidades_destaque'])): ?>
      <section class="section">
        <div class="container">
          <div class="section-head">
            <div><h2>Onde estudar <?= e($course['nome']) ?></h2></div>
          </div>
          <div class="icon-row">
            <?php foreach ($course['universidades_destaque'] as $dSlug): $d = DESTINOS[$dSlug] ?? null; if (!$d) continue; ?>
            <a href="destino-<?= e($dSlug) ?>.php" class="icon-card" style="text-decoration:none;color:inherit;">
              <div class="icon-card__glyph"><i class="bi bi-geo-alt"></i></div>
              <h3><?= e($d['nome']) ?></h3>
              <p><?= e($d['eyebrow']) ?></p>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
      <?php endif; ?>

      <section class="section section-dark">
        <div class="container">
          <div class="article-cta">
            <h3>Queres estudar <?= e($course['nome']) ?> em Portugal?</h3>
            <p>A equipa Da Vinci × StudyWing acompanha-te desde a escolha da universidade até à chegada a Portugal.</p>
            <a href="comparar.php#formulario" class="btn-pill btn-teal">Agendar consultoria gratuita</a>
          </div>
        </div>
      </section>

    </main>
    <?php
    require_once __DIR__ . '/footer.php';
}
