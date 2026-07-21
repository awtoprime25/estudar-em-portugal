<?php
/**
 * includes/studywing-form.php
 * Formulário StudyWing partilhado — incluído por footer.php, aparece no
 * fundo de TODAS as páginas do site (era só em comparar.php). Espera que
 * config.php já tenha corrido (usa a constante ENP_CSRF).
 */
?>
  <!-- ============ FORMULÁRIO STUDYWING ============ -->
  <section class="section section-dark" id="formulario">
    <div class="container">
      <div class="formulario-grid">
        <div class="formulario-intro">
          <span class="eyebrow">StudyWing</span>
          <h2>Fala diretamente com um consultor StudyWing</h2>
          <p>Preenche os passos abaixo e a equipa StudyWing contacta-te por email ou WhatsApp em menos de 24 horas. Tudo gratuito, sem compromisso.</p>
          <ul class="formulario-bullets">
            <li><i class="bi bi-shield-check"></i> <span>Sem spam, sem partilha com terceiros.</span></li>
            <li><i class="bi bi-clock-history"></i> <span>Resposta em ≤ 24 horas úteis.</span></li>
            <li><i class="bi bi-mortarboard"></i> <span>Especialistas em PT + 13 países europeus.</span></li>
          </ul>
        </div>

        <form id="studywing-form" class="studywing-form" action="ajax-comp.php" method="POST" novalidate data-studywing>
          <input type="hidden" name="csrf" value="<?= e(ENP_CSRF) ?>">
          <!-- ===== STEP 1: dados pessoais ===== -->
          <fieldset class="studywing-step is-active" data-step="1">
            <legend>Dados pessoais</legend>
            <div class="studywing-grid">
              <label>
                <span>Nome *</span>
                <input type="text" name="nome" required>
              </label>
              <label>
                <span>Email *</span>
                <input type="email" name="email" required>
              </label>
              <label>
                <span>Telefone / WhatsApp *</span>
                <input type="tel" name="tel" placeholder="+55 11 9XXXX-XXXX" required>
              </label>
              <label>
                <span>Cidade, estado e país onde moras? *</span>
                <input type="text" name="localidade" required placeholder="Cidade, Estado / País">
              </label>
              <label class="studywing-grid--full">
                <span>Nacionalidade do aluno *</span>
                <select name="nacionalidade" required>
                  <option value="">Escolhe…</option>
                  <option value="brasileira">Brasileira</option>
                  <option value="portuguesa">Portuguesa</option>
                  <option value="dupla-br-pt">Dupla — brasileira e portuguesa/UE</option>
                  <option value="outra-cplp">Outra nacionalidade CPLP</option>
                  <option value="outra">Outra</option>
                </select>
              </label>
            </div>
            <button type="button" class="btn-pill btn-teal" data-next-step>
              Próximo →
            </button>
          </fieldset>

          <!-- ===== STEP 2: curso e objetivo ===== -->
          <fieldset class="studywing-step" data-step="2">
            <legend>Curso e objetivo</legend>
            <div class="studywing-grid">
              <label class="studywing-grid--full">
                <span>O que procuras, no fundo? *</span>
                <select name="objetivo" required>
                  <option value="">Escolhe…</option>
                  <option value="diploma-ue">Um diploma reconhecido na União Europeia</option>
                  <option value="mudar-vida">Mudar de vida / viver na Europa a longo prazo</option>
                  <option value="raizes-familia">Já tenho família ou raízes em Portugal</option>
                  <option value="explorando">Ainda estou a explorar as opções</option>
                </select>
              </label>
              <label>
                <span>Que tipo de formação procuras? *</span>
                <select name="tipo_curso" required>
                  <option value="">Escolhe…</option>
                  <option value="licenciatura">Licenciatura</option>
                  <option value="mestrado">Mestrado</option>
                  <option value="mestrado-integrado">Mestrado Integrado</option>
                  <option value="doutoramento">Doutoramento</option>
                  <option value="ctesp">Curso técnico superior (CTeSP)</option>
                  <option value="pos-graduacao">Pós-graduação</option>
                  <option value="nao-sei">Ainda não sei</option>
                </select>
              </label>
              <label>
                <span>Que curso pretendes estudar/reconhecer em Portugal? *</span>
                <input type="text" name="areas" placeholder="Medicina, Engenharia Informática, Gestão…" required>
              </label>
              <label class="studywing-grid--full">
                <span>Quando pretendes estar em Portugal? *</span>
                <select name="quando" required>
                  <option value="">Escolhe…</option>
                  <option value="2026-set">Setembro 2026 (intake principal)</option>
                  <option value="2027-jan">Janeiro / Fevereiro 2027</option>
                  <option value="2027-set">Setembro 2027</option>
                  <option value="ainda-decidi">Ainda estou a decidir</option>
                </select>
              </label>
            </div>
            <div class="studywing-actions">
              <button type="button" class="btn-pill btn-outline-light" data-prev-step>← Voltar</button>
              <button type="button" class="btn-pill btn-teal" data-next-step>Próximo →</button>
            </div>
          </fieldset>

          <!-- ===== STEP 3: perfil e orçamento ===== -->
          <fieldset class="studywing-step" data-step="3">
            <legend>Perfil e orçamento</legend>
            <div class="studywing-grid">
              <label class="studywing-grid--full">
                <span>Qual o teu grau de escolaridade atual? *</span>
                <select name="ano" required>
                  <option value="">Escolhe…</option>
                  <option value="3-ano-em">A cursar o Ensino Médio</option>
                  <option value="cursinho">Ensino Médio completo, a fazer cursinho/pré-vestibular</option>
                  <option value="formado">Ensino Médio completo</option>
                  <option value="graduacao">Já concluí uma graduação</option>
                </select>
              </label>
              <label class="studywing-grid--full">
                <span>Contando ~1.000€/mês de custo de vida em Portugal, como estás neste momento? *</span>
                <select name="situacao_financeira" required>
                  <option value="">Escolhe…</option>
                  <option value="garantido">Já tenho esse valor garantido</option>
                  <option value="a-juntar">Estou a juntar, mas ainda não tenho tudo</option>
                  <option value="preciso-ajuda">Preciso de ajuda para perceber como chegar lá</option>
                  <option value="nao-pensei">Ainda não parei para pensar nisso</option>
                </select>
              </label>
              <label class="studywing-grid--full">
                <span>Como pensas pagar os estudos e a estadia? *</span>
                <select name="financiamento" required>
                  <option value="">Escolhe…</option>
                  <option value="recursos-proprios">Recursos próprios / da família</option>
                  <option value="bolsa">Bolsa ou financiamento estudantil</option>
                  <option value="trabalho">Vou trabalhar part-time enquanto estudo</option>
                  <option value="nao-decidi">Ainda não decidi</option>
                </select>
              </label>
            </div>
            <div class="studywing-actions">
              <button type="button" class="btn-pill btn-outline-light" data-prev-step>← Voltar</button>
              <button type="button" class="btn-pill btn-teal" data-next-step>Próximo →</button>
            </div>
          </fieldset>

          <!-- ===== STEP 4: preferências finais ===== -->
          <fieldset class="studywing-step" data-step="4">
            <legend>Preferências finais</legend>
            <div class="studywing-grid">
              <label class="studywing-grid--full">
                <span>Destino preferido inicial *</span>
                <select name="destino" required>
                  <option value="">Escolhe…</option>
                  <option value="portugal">🇵🇹  Portugal (recomendado p/ BR)</option>
                  <option value="holanda">🇳🇱  Países Baixos / Holanda</option>
                  <option value="alemanha">🇩🇪  Alemanha</option>
                  <option value="reino-unido">🇬🇧  Reino Unido</option>
                  <option value="espanha">🇪🇸  Espanha</option>
                  <option value="italia">🇮🇹  Itália</option>
                  <option value="franca">🇫🇷  França</option>
                  <option value="republica-checa">🇨🇿  República Checa</option>
                  <option value="irlanda">🇮🇪  Irlanda</option>
                  <option value="aberto">🌍  Estou aberto — surpresa-me!</option>
                </select>
              </label>
              <div class="studywing-grid--full studywing-radio-group">
                <span class="studywing-radio-group__label">Em que fase estás? *</span>
                <label class="studywing-radio-option">
                  <input type="radio" name="momento" value="pesquisando" required>
                  <span>Só comecei a pesquisar agora</span>
                </label>
                <label class="studywing-radio-option">
                  <input type="radio" name="momento" value="duvidas-sozinho">
                  <span>Quero esclarecer dúvidas, mas pensava tratar sozinho</span>
                </label>
                <label class="studywing-radio-option">
                  <input type="radio" name="momento" value="quero-assessoria">
                  <span>Quero contratar já o acompanhamento completo da Da Vinci × StudyWing</span>
                </label>
              </div>
              <label class="studywing-grid--full">
                <span>Outras observações?</span>
                <textarea name="obs" rows="3" placeholder="Dúvidas, contexto adicional, condicionantes…"></textarea>
              </label>
              <label class="studywing-grid--full studywing-consent">
                <input type="checkbox" name="termos" value="1" required>
                <span>Aceito os <a href="privacidade.php" target="_blank">termos de privacidade</a> e o consentimento para ser contactado pela equipa Da Vinci × StudyWing.</span>
              </label>
            </div>
            <div class="studywing-actions">
              <button type="button" class="btn-pill btn-outline-light" data-prev-step>← Voltar</button>
              <button type="submit" class="btn-pill btn-teal">Enviar → StudyWing</button>
            </div>
          </fieldset>

          <!-- progress bar -->
          <div class="studywing-progress" data-progress>
            <span class="studywing-progress__dot is-active" data-dot="1"></span>
            <span class="studywing-progress__line"></span>
            <span class="studywing-progress__dot" data-dot="2"></span>
            <span class="studywing-progress__line"></span>
            <span class="studywing-progress__dot" data-dot="3"></span>
            <span class="studywing-progress__line"></span>
            <span class="studywing-progress__dot" data-dot="4"></span>
          </div>

          <div class="studywing-message" data-feedback hidden></div>
        </form>
      </div>
    </div>
  </section>
