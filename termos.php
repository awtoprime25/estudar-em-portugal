<?php
$pageTitle       = 'Termos de Uso | Estudar em Portugal';
$pageDescription = 'Termos de uso do site Estudar em Portugal.';
$activeNav       = '';
require_once __DIR__ . '/includes/header.php';
?>
<main id="conteudo">
  <section class="page-hero">
    <div class="container">
      <h1>Termos de Uso</h1>
      <p>Última atualização: <?= date('d/m/Y') ?></p>
    </div>
  </section>
  <section class="content-block">
    <p><em>Este é um texto provisório gerado durante a implementação do site. Os termos de uso definitivos devem ser revistos e fornecidos pela Ginásios da Vinci / departamento jurídico antes da publicação.</em></p>
    <h2>1. Âmbito</h2>
    <p>Este site presta informação e consultoria sobre candidaturas a universidades em Portugal e na Europa, prestada pela Ginásios da Vinci.</p>
    <h2>2. Contactos</h2>
    <p>Para questões sobre estes termos, contacta-nos em <a href="mailto:<?= e(CONTACT_EMAIL) ?>" style="color:var(--teal);"><?= e(CONTACT_EMAIL) ?></a>.</p>
  </section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
