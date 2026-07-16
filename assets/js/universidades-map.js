(function () {
  "use strict";
  var universidades = window.UNIVERSIDADES || [];
  var mapEl = document.getElementById('uniMap');
  if (!mapEl || !window.L) return;

  var map = L.map('uniMap', { center: [39.6, -8.2], zoom: 6.5, zoomControl: true });
  L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; OpenStreetMap &copy; CARTO',
    subdomains: 'abcd', maxZoom: 19
  }).addTo(map);

  var icon = L.divIcon({
    className: 'custom-marker',
    html: '<div style="width:14px;height:14px;border-radius:50%;background:#0f8ba6;border:2px solid #fff;box-shadow:0 0 0 2px rgba(15,139,166,.35);"></div>',
    iconSize: [14, 14], iconAnchor: [7, 7], popupAnchor: [0, -10]
  });

  function tipoLabel(u) {
    if (u.tipo === 'privada') return 'Privada';
    if (u.tipo === 'politecnico') return 'Politécnico';
    return 'Pública';
  }

  function cursosLinks(u, cls) {
    return u.cursos.map(function (c) {
      return '<a class="' + cls + '" href="curso-' + c.slug + '.php">' + c.nome + '</a>';
    }).join('');
  }

  var markers = [];
  universidades.forEach(function (u) {
    var cidadeLink = u.citySlug ? ' · <a href="destino-' + u.citySlug + '.php" style="color:#0f8ba6;font-weight:600;text-decoration:none;">Ver cidade →</a>' : '';
    var marker = L.marker([u.lat, u.lng], { icon: icon }).bindPopup(
      '<div class="uni-popup">' +
      '<p class="uni-popup-cidade">' + u.cidade + '</p>' +
      '<h4>' + u.nome + '</h4>' +
      '<p>' + tipoLabel(u) + cidadeLink + '</p>' +
      '<div>' + cursosLinks(u, 'uni-popup-link') + '</div>' +
      '</div>'
    );
    marker._uni = u;
    marker.addTo(map);
    markers.push(marker);
  });

  function renderList(filtered) {
    var listEl = document.getElementById('uniList');
    var countEl = document.getElementById('uniCount');
    countEl.textContent = filtered.length + (filtered.length === 1 ? ' instituição' : ' instituições');
    if (!filtered.length) {
      listEl.innerHTML = '<div style="padding:40px 20px;text-align:center;color:#8a93a6;">Nenhuma universidade encontrada.</div>';
      return;
    }
    listEl.innerHTML = filtered.map(function (u) {
      return '<div class="uni-card" data-lat="' + u.lat + '" data-lng="' + u.lng + '">' +
        '<div class="uni-card__name">' + u.nome + '</div>' +
        '<div class="uni-card__meta"><span>' + u.cidade + '</span><span>' + tipoLabel(u) + '</span></div>' +
        (u.cursos.length ? '<div class="uni-card__cursos">' + cursosLinks(u, '') + '</div>' : '') +
        '</div>';
    }).join('');
    listEl.querySelectorAll('.uni-card').forEach(function (card) {
      card.addEventListener('click', function (e) {
        if (e.target.tagName === 'A') return;
        var lat = parseFloat(card.dataset.lat), lng = parseFloat(card.dataset.lng);
        map.flyTo([lat, lng], 11, { duration: 1 });
        markers.forEach(function (m) {
          if (Math.abs(m._uni.lat - lat) < 0.0001 && Math.abs(m._uni.lng - lng) < 0.0001) {
            setTimeout(function () { m.openPopup(); }, 550);
          }
        });
        listEl.querySelectorAll('.uni-card').forEach(function (c) { c.classList.remove('highlight'); });
        card.classList.add('highlight');
      });
    });
  }

  function applyFilter(cidade) {
    document.querySelectorAll('#uniFilters .filter-btn').forEach(function (btn) {
      btn.classList.toggle('active', btn.dataset.filtro === cidade);
    });
    var filtered = (cidade === 'todas') ? universidades : universidades.filter(function (u) { return u.cidade === cidade; });
    var visible = [];
    markers.forEach(function (m) {
      map.removeLayer(m);
      if (cidade === 'todas' || m._uni.cidade === cidade) { m.addTo(map); visible.push(m); }
    });
    if (cidade === 'todas') {
      map.flyTo([39.6, -8.2], 6.5, { duration: 1 });
    } else if (visible.length) {
      map.flyToBounds(L.featureGroup(visible).getBounds().pad(0.4), { duration: 1, maxZoom: 12 });
    }
    renderList(filtered);
  }

  document.querySelectorAll('#uniFilters .filter-btn').forEach(function (btn) {
    btn.addEventListener('click', function () { applyFilter(btn.dataset.filtro); });
  });

  renderList(universidades);
  setTimeout(function () { map.invalidateSize(); }, 200);
})();
