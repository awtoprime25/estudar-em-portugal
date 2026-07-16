<?php
$pageTitle       = 'Política de Privacidade | Lá Fora — Estudar em Portugal';
$pageDescription = 'Política de privacidade do site Estudar em Portugal — Lá Fora.';
$activeNav       = '';
require_once __DIR__ . '/includes/header.php';
?>
<main id="conteudo">
  <section class="page-hero">
    <div class="container">
      <h1>Política de Privacidade</h1>
      <p>Última atualização: <?= date('d/m/Y') ?></p>
    </div>
  </section>
  <section class="content-block">
    <p><em>Este é um texto provisório gerado durante a implementação do site. A política de privacidade definitiva (RGPD/LGPD) deve ser revista e fornecida pela Ginásios da Vinci / departamento jurídico antes da publicação.</em></p>
    <h2>1. Dados recolhidos</h2>
    <p>Quando preenches o formulário de contacto, recolhemos nome, email, telefone e a mensagem enviada, apenas para responder ao teu pedido de consultoria.</p>
    <h2>2. Contactos</h2>
    <p>Para exercer os teus direitos sobre os dados pessoais, escreve para <a href="mailto:<?= e(CONTACT_EMAIL) ?>" style="color:var(--teal);"><?= e(CONTACT_EMAIL) ?></a>.</p>
  </section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
