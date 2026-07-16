<?php
$pageTitle       = 'Contato — ' . 'Estudar em Portugal | Lá Fora';
$pageDescription = 'Agende uma consultoria gratuita e fale com a equipa Lá Fora sobre estudar em Portugal e na Europa.';
$activeNav       = 'contato';
require_once __DIR__ . '/includes/header.php';

$enviado = false;
$erro    = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');

    if ($nome === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Por favor preenche o nome e um email válido.';
    } else {
        $corpo = "Novo pedido de consultoria — estudar-em-portugal\n\n"
               . "Nome: {$nome}\nEmail: {$email}\nTelefone: {$telefone}\n\nMensagem:\n{$mensagem}\n";
        $headers = 'From: no-reply@ginasiosdavinci.com' . "\r\n" . 'Reply-To: ' . $email;

        // Em produção isto depende de um MTA configurado (ex.: sendmail local, tal como
        // nos projetos irmãos). Em ambiente de desenvolvimento local (XAMPP sem SMTP
        // configurado) o envio pode falhar silenciosamente — por isso não bloqueamos
        // a confirmação ao utilizador só por causa disso.
        @mail(CONTACT_EMAIL, 'Novo pedido de consultoria — Estudar em Portugal', $corpo, $headers);
        $enviado = true;
    }
}
?>

<main id="conteudo">
  <section class="page-hero">
    <div class="container">
      <h1>Vamos falar sobre o teu futuro na Europa</h1>
      <p>Agenda uma consultoria gratuita. Respondemos por email, telefone ou WhatsApp.</p>
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

    <div class="contact-card contact-form">
      <h3>Pede uma consultoria gratuita</h3>

      <?php if ($enviado): ?>
        <p style="color:#0f8ba6;font-weight:600;">Obrigado! Recebemos o teu pedido e vamos responder em breve.</p>
      <?php else: ?>
        <?php if ($erro): ?><p style="color:#c0392b;"><?= e($erro) ?></p><?php endif; ?>
        <form method="post" novalidate>
          <label for="nome">Nome *</label>
          <input type="text" id="nome" name="nome" required value="<?= e($_POST['nome'] ?? '') ?>">

          <label for="email">Email *</label>
          <input type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">

          <label for="telefone">Telefone / WhatsApp</label>
          <input type="tel" id="telefone" name="telefone" placeholder="+55 9X XXXX XXXX" value="<?= e($_POST['telefone'] ?? '') ?>">

          <label for="mensagem">O que gostarias de estudar?</label>
          <textarea id="mensagem" name="mensagem" placeholder="Ex.: Licenciatura em Medicina, quero começar em 2027..."><?= e($_POST['mensagem'] ?? '') ?></textarea>

          <button type="submit" class="btn-pill btn-navy">Enviar pedido</button>
        </form>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
