(function () {
  "use strict";

  /* Mobile nav toggle */
  var toggle = document.querySelector('[data-nav-toggle]');
  var nav = document.getElementById('primaryNav');
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var open = nav.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    nav.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        nav.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  /* PT-BR / PT-PT copy toggle */
  var switcher = document.querySelector('[data-locale-switch]');
  if (switcher) {
    var buttons = switcher.querySelectorAll('button[data-locale]');
    var targets = document.querySelectorAll('[data-br]');

    function applyLocale(locale) {
      targets.forEach(function (el) {
        var value = el.getAttribute('data-' + locale);
        if (value === null) return;
        if (el.hasAttribute('data-html')) {
          el.innerHTML = value;
        } else {
          el.textContent = value;
        }
      });
      buttons.forEach(function (btn) {
        btn.classList.toggle('is-active', btn.getAttribute('data-locale') === locale);
      });
      document.documentElement.lang = locale === 'br' ? 'pt-BR' : 'pt-PT';
    }

    buttons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        applyLocale(btn.getAttribute('data-locale'));
      });
    });
  }
})();
