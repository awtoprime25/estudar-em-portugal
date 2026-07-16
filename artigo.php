<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/blog-db.php';

$slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($_GET['slug'] ?? ''));
$post = $slug !== '' ? blog_get_by_slug($slug) : null;

if (!$post) {
    http_response_code(404);
    $pageTitle       = 'Artigo não encontrado | Lá Fora';
    $pageDescription = 'O artigo que procuras não existe ou foi removido.';
    $noindex         = true;
    require_once __DIR__ . '/includes/header.php';
?>
<main id="conteudo">
  <section class="content-block" style="text-align:center;">
    <h2>Artigo não encontrado</h2>
    <p><a href="blog.php" style="color:var(--teal);font-weight:600;">← Voltar ao blog</a></p>
  </section>
</main>
<?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$categoryLabels = ['cidade' => 'Cidades', 'curso' => 'Cursos', 'dica' => 'Dicas'];
$heroImg = $post['hero_image'] ?: 'hero-blog-default.svg';
$title   = $post['title'] !== '' ? $post['title'] : 'Blog Lá Fora';
$desc    = $post['meta_description'] !== '' ? $post['meta_description'] : (string) $post['excerpt'];
$dateFmt = $post['published_at'] ? date('d/m/Y', strtotime($post['published_at'])) : '';

$pageTitle       = $title;
$pageDescription = $desc;
$activeNav       = 'blog';
$ogImage         = SITE_URL . 'assets/images/' . $heroImg;

$related = blog_related((int) $post['id'], (string) $post['category'], 3);

$extraJsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@graph'   => [
        [
            '@type' => 'Article',
            'headline' => $title,
            'description' => $desc,
            'image' => $ogImage,
            'url' => SITE_URL . 'artigo.php?slug=' . $post['slug'],
            'inLanguage' => 'pt-PT',
            'datePublished' => $post['published_at'] ? date('Y-m-d', strtotime($post['published_at'])) : date('Y-m-d'),
            'dateModified' => $post['updated_at'] ? date('Y-m-d', strtotime($post['updated_at'])) : date('Y-m-d'),
            'author' => ['@type' => 'Organization', 'name' => 'Lá Fora — Da Vinci × StudyWing', 'url' => SITE_URL],
            'publisher' => ['@type' => 'Organization', 'name' => 'Lá Fora — Da Vinci × StudyWing', 'url' => SITE_URL],
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => SITE_URL . 'artigo.php?slug=' . $post['slug']],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => SITE_URL],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => SITE_URL . 'blog.php'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $title, 'item' => SITE_URL . 'artigo.php?slug=' . $post['slug']],
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$pageSlug = $post['slug'];
require_once __DIR__ . '/includes/header.php';
?>

<main id="conteudo">
  <section class="page-hero" style="background-image:linear-gradient(rgba(10,22,40,.55),rgba(10,22,40,.75)),url('<?= e('assets/images/' . $heroImg) ?>');background-size:cover;background-position:center;">
    <div class="container">
      <p style="color:var(--muted-on-dark);font-size:13px;margin-bottom:10px;">
        <a href="blog.php" style="color:var(--muted-on-dark);">Blog</a> · <?= e($categoryLabels[$post['category']] ?? 'Dicas') ?>
      </p>
      <h1><?= $post['h1_html'] !== '' ? $post['h1_html'] : e($title) ?></h1>
      <p>
        <?php if ($dateFmt): ?><?= e($dateFmt) ?><?php endif; ?>
        <?php if (!empty($post['reading_minutes'])): ?> · <?= (int) $post['reading_minutes'] ?> min de leitura<?php endif; ?>
      </p>
    </div>
  </section>

  <section class="content-block content-block--wide">
    <?= $post['content_html'] ?>

    <div class="article-cta">
      <h3>Queres saber se te enquadras?</h3>
      <p>Fala com a nossa equipa — orientamos-te na escolha da cidade, do curso e tratamos da candidatura. Sem compromisso.</p>
      <a href="contato.php" class="btn-pill btn-teal">Falar com a equipa</a>
    </div>
  </section>

  <?php if ($related): ?>
  <section class="section">
    <div class="container">
      <div class="section-head">
        <div><span class="eyebrow">Continua a ler</span><h2>Artigos relacionados</h2></div>
      </div>
      <div class="cmp-blog-grid">
        <?php foreach ($related as $r): ?>
        <article class="cmp-blog-card">
          <a href="artigo.php?slug=<?= urlencode($r['slug']) ?>" class="cmp-blog-card__media">
            <img src="<?= e('assets/images/' . ($r['hero_image'] ?: 'hero-blog-default.svg')) ?>" alt="<?= e($r['title']) ?>" loading="lazy">
          </a>
          <div class="cmp-blog-card__body">
            <h3><a href="artigo.php?slug=<?= urlencode($r['slug']) ?>"><?= e($r['title']) ?></a></h3>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
