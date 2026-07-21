<?php
$pageTitle       = 'Contato — Estudar em Portugal';
$pageDescription = 'Agende uma consultoria gratuita e fale com a nossa equipa sobre estudar em Portugal e na Europa.';
$activeNav       = 'contato';
require_once __DIR__ . '/includes/header.php';
?>

<main id="conteudo">
  <section class="page-hero">
    <div class="container">
      <h1>Vamos falar sobre o teu futuro na Europa</h1>
      <p>Agenda uma consultoria gratuita. Respondemos por email, telefone ou WhatsApp — ou preenche o formulário no fundo desta página.</p>
    </div>
  </section>

  <section class="container contact-grid">
    <div class="contact-card">
      <h3>Fala connosco</h3>
      <ul>
        <li><i class="bi bi-telephone"></i> <a href="tel:<?= e(CONTACT_PHONE_TEL) ?>"><?= e(CONTACT_PHONE) ?></a></li>
        <li><i class="bi bi-envelope"></i> <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a></li>
        <li><i class="bi bi-geo-alt"></i> <?= e(CONTACT_ADDRESS_LINE) ?></li>
        <li><i class="bi bi-clock"></i> Segunda a sexta, 09h00 – 18h00</li>
      </ul>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
