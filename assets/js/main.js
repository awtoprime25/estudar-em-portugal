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

  /* Dropdown nav — controlado por clique (.is-open), um aberto de cada vez.
     Nada de :hover/:focus-within no CSS: era isso que prendia dois abertos. */
  var dropdownItems = document.querySelectorAll('.has-dropdown');
  function closeDropdown(li) {
    li.classList.remove('is-open');
    var b = li.querySelector('.nav-dropdown-toggle');
    if (b) b.setAttribute('aria-expanded', 'false');
  }
  function closeAllDropdowns() { dropdownItems.forEach(closeDropdown); }
  dropdownItems.forEach(function (li) {
    var btn = li.querySelector('.nav-dropdown-toggle');
    if (!btn) return;
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();               // não deixa o handler de "clicar fora" fechar já
      var willOpen = !li.classList.contains('is-open');
      closeAllDropdowns();
      if (willOpen) {
        li.classList.add('is-open');
        btn.setAttribute('aria-expanded', 'true');
      }
    });
  });
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.has-dropdown')) closeAllDropdowns();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAllDropdowns();
  });

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
