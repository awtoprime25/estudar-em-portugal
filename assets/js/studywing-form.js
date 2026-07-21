(function () {
  "use strict";

  /* studywing-form.js NÃO duplica nav-toggle e locale-switch — esses já estão em
     main.js. Carregado em todas as páginas (footer.php) porque o formulário
     StudyWing e o acordeão de FAQ agora aparecem em várias páginas, não só comparar.php. */

  /* ============================================================
     FAQ accordion — best practice (1 aberta de cada vez, GEO/AEO)
     ============================================================ */
  document.querySelectorAll('[data-faq-item]').forEach(function (el) {
    el.addEventListener('toggle', function () {
      if (el.open) {
        document.querySelectorAll('[data-faq-item]').forEach(function (o) {
          if (o !== el) o.open = false;
        });
      }
    });
  });

  /* ============================================================
     StudyWing multi-step form (3 steps, fetch JSON, progress dots)
     ============================================================ */
  var form = document.querySelector('[data-studywing]');
  if (form) {
    var steps    = form.querySelectorAll('.studywing-step');
    var dots     = form.querySelectorAll('[data-dot]');
    var lines    = form.querySelectorAll('.studywing-progress__line');
    var feedback = form.querySelector('[data-feedback]');

    function paintProgress(activeStep) {
      dots.forEach(function (dot, idx) {
        dot.classList.toggle('is-active', idx + 1 <= activeStep);
      });
      lines.forEach(function (line, idx) {
        line.classList.toggle('is-active', idx + 1 < activeStep);
      });
    }

    function goToStep(target) {
      steps.forEach(function (step) {
        step.classList.toggle('is-active', step.getAttribute('data-step') === String(target));
      });
      paintProgress(target);
      var firstInput = form.querySelector('.studywing-step.is-active input, .studywing-step.is-active select, .studywing-step.is-active textarea');
      if (firstInput) firstInput.focus({preventScroll: true});
    }

    function validateStep(step) {
      var firstInvalid = null;
      step.querySelectorAll('input, select, textarea').forEach(function (el) {
        if (el.hasAttribute('required') && !el.checkValidity() && !firstInvalid) {
          firstInvalid = el;
        }
      });
      if (firstInvalid) {
        firstInvalid.focus();
        firstInvalid.reportValidity();
        return false;
      }
      return true;
    }

    form.querySelectorAll('[data-next-step]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var stepEl = btn.closest('.studywing-step');
        if (!validateStep(stepEl)) return;
        var next = +(stepEl.getAttribute('data-step') || 1) + 1;
        if (next <= steps.length) goToStep(next);
      });
    });

    form.querySelectorAll('[data-prev-step]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var stepEl = btn.closest('.studywing-step');
        var prev = +(stepEl.getAttribute('data-step') || 1) - 1;
        if (prev >= 1) goToStep(prev);
      });
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var lastStep = form.querySelector('.studywing-step[data-step="4"]');
      if (!validateStep(lastStep)) return;

      var btn = form.querySelector('button[type="submit"]');
      btn.disabled = true;
      btn.textContent = 'A enviar…';
      feedback.hidden = true;

      var values = {};
      Array.prototype.slice.call(form.elements).forEach(function (el) {
        if (el.name && el.type !== 'checkbox') values[el.name] = el.value;
      });
      var checked = form.querySelector('[name="termos"]');
      if (checked) values.termos = checked.checked ? '1' : '';

      fetch('ajax-comp.php', {
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
          steps.forEach(function (s) { s.style.display = 'none'; });
          dots.forEach(function (d) { d.style.background = '#17a079'; });
        } else if (Array.isArray(res.errors) && res.errors.length) {
          feedback.textContent = 'Verifica: ' + res.errors.join(', ');
        }
      })
      .catch(function () {
        feedback.hidden = false;
        feedback.textContent = 'Sem ligação. Tenta de novo em segundos, ou escreve para info@davinci.com.pt';
        feedback.dataset.success = '0';
      })
      .finally(function () {
        btn.disabled = false;
        btn.textContent = 'Enviar → StudyWing';
      });
    });

    goToStep(1);
  }

  /* ============================================================
     Smooth scroll para links # (sem hash duplicado no navegador)
     ============================================================ */
  document.querySelectorAll('a[href^="#"]').forEach(function (link) {
    link.addEventListener('click', function (e) {
      var id = link.getAttribute('href');
      if (id === '#' || id.length < 2) return;
      var tgt = document.querySelector(id);
      if (!tgt) return;
      e.preventDefault();
      tgt.scrollIntoView({ behavior: 'smooth', block: 'start' });
      history.pushState(null, '', id);
    });
  });
})();
