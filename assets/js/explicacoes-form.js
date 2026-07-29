(function () {
  "use strict";

  /* explicacoes-form.js — submit AJAX single-step do formulário próprio de
     cursos-preparacao-exames.php (Marcar aula). FORMULÁRIO DISTINTO do
     StudyWing (esse é assets/js/studywing-form.js → ajax-comp.php); este
     envia para ajax-explicacoes.php e não tem passos/progress bar. */
  var form = document.querySelector('[data-explicacoes-form]');
  if (!form) return;

  var feedback = form.querySelector('[data-feedback]');

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    var btn = form.querySelector('button[type="submit"]');
    var btnLabel = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'A enviar…';
    feedback.hidden = true;

    var values = {};
    Array.prototype.slice.call(form.elements).forEach(function (el) {
      if (el.name && el.type !== 'checkbox') values[el.name] = el.value;
    });
    var checked = form.querySelector('[name="termos"]');
    if (checked) values.termos = checked.checked ? '1' : '';

    fetch('ajax-explicacoes.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams(values).toString(),
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
      feedback.hidden = false;
      feedback.textContent = res.message || 'Ocorreu um erro.';
      feedback.dataset.success = res.ok ? '1' : '0';
      if (res.ok) {
        form.querySelector('fieldset').style.display = 'none';
      } else if (Array.isArray(res.errors) && res.errors.length) {
        feedback.textContent = 'Verifique: ' + res.errors.join(', ');
      }
    })
    .catch(function () {
      feedback.hidden = false;
      feedback.textContent = 'Sem conexão. Tente de novo em alguns segundos, ou escreva para info@davinci.com.pt';
      feedback.dataset.success = '0';
    })
    .finally(function () {
      btn.disabled = false;
      btn.textContent = btnLabel;
    });
  });
})();
