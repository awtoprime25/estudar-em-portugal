(function () {
  'use strict';

  // Todas as páginas deste site vivem na raiz (sem subpastas) — sem necessidade
  // de resolver profundidade de path.
  var CHAT_URL    = 'chat.php';
  var CSRF_URL    = 'csrf-token.php';
  var FORM_URL    = 'contato.php';
  var FORM_LABEL  = 'Pedir consulta gratuita →';
  var SESSION_KEY      = 'enp_chat_leo_v1';
  var PANEL_CLOSED_KEY = 'enp_panel_user_closed';
  var BUBBLE_KEY       = 'enp_bubble_dismissed';

  var css = [
    '#lf-chat{position:fixed;bottom:24px;right:24px;z-index:10001;font-family:Inter,sans-serif}',
    '#lf-chat-btn{width:60px;height:60px;border-radius:50%;background:#1ab8c8;border:none;cursor:pointer;',
    'box-shadow:0 4px 20px rgba(26,184,200,0.5);display:flex;align-items:center;justify-content:center;',
    'transition:transform .2s,box-shadow .2s;outline:none}',
    '#lf-chat-btn:hover{transform:scale(1.08);box-shadow:0 6px 28px rgba(26,184,200,0.7)}',
    '#lf-chat-btn svg{width:28px;height:28px;fill:#fff;transition:opacity .2s}',
    '#lf-chat-panel{display:none;flex-direction:column;width:360px;height:520px;max-width:calc(100vw - 48px);',
    'background:#0A1A2F;border:1px solid rgba(255,255,255,0.1);border-radius:16px;',
    'box-shadow:0 12px 48px rgba(0,0,0,0.5);overflow:hidden;margin-bottom:12px}',
    '#lf-chat-panel.lf-open{display:flex}',
    '.lf-chat-header{display:flex;align-items:center;justify-content:space-between;',
    'padding:14px 18px;background:rgba(26,184,200,0.12);border-bottom:1px solid rgba(255,255,255,0.08)}',
    '.lf-chat-header-left{display:flex;align-items:center;gap:10px}',
    '.lf-avatar{width:36px;height:36px;border-radius:50%;background:#1ab8c8;display:flex;',
    'align-items:center;justify-content:center;font-size:16px;font-weight:700;color:#0A1A2F}',
    '.lf-header-info strong{display:block;color:#fff;font-size:0.93rem;font-weight:600}',
    '.lf-header-info span{color:#1ab8c8;font-size:0.75rem}',
    '.lf-header-actions{display:flex;align-items:center;gap:10px}',
    '.lf-new-chat-btn{background:none;border:1px solid rgba(255,255,255,0.2);border-radius:6px;',
    'cursor:pointer;color:rgba(255,255,255,0.5);font-size:0.7rem;padding:3px 8px;',
    'transition:all .2s;white-space:nowrap;font-family:Inter,sans-serif}',
    '.lf-new-chat-btn:hover{color:#fff;border-color:rgba(255,255,255,0.5)}',
    '.lf-close-btn{background:none;border:none;cursor:pointer;color:rgba(255,255,255,0.5);',
    'font-size:22px;line-height:1;padding:0;transition:color .2s}',
    '.lf-close-btn:hover{color:#fff}',
    '#lf-chat-msgs{flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:10px;',
    'scrollbar-width:thin;scrollbar-color:rgba(255,255,255,0.15) transparent}',
    '#lf-chat-msgs::-webkit-scrollbar{width:4px}',
    '#lf-chat-msgs::-webkit-scrollbar-thumb{background:rgba(255,255,255,0.15);border-radius:4px}',
    '.lf-msg{max-width:82%;padding:10px 14px;border-radius:14px;font-size:0.875rem;line-height:1.5;word-break:break-word}',
    '.lf-msg.bot{background:rgba(255,255,255,0.07);color:#e8edf5;border-bottom-left-radius:4px;align-self:flex-start}',
    '.lf-msg.user{background:#1ab8c8;color:#0A1A2F;font-weight:500;border-bottom-right-radius:4px;align-self:flex-end}',
    '.lf-loader{display:flex;gap:5px;padding:10px 14px;background:rgba(255,255,255,0.07);',
    'border-radius:14px;border-bottom-left-radius:4px;align-self:flex-start}',
    '.lf-loader span{width:7px;height:7px;border-radius:50%;background:#1ab8c8;',
    'animation:lf-bounce 1s infinite ease-in-out}',
    '.lf-loader span:nth-child(2){animation-delay:.2s}',
    '.lf-loader span:nth-child(3){animation-delay:.4s}',
    '@keyframes lf-bounce{0%,80%,100%{transform:scale(0.6);opacity:.5}40%{transform:scale(1);opacity:1}}',
    '@keyframes lf-pulse-glow{0%,100%{box-shadow:0 0 14px rgba(26,184,200,0.6),0 4px 14px rgba(0,0,0,0.3)}50%{box-shadow:0 0 28px rgba(26,184,200,0.95),0 0 50px rgba(26,184,200,0.35),0 4px 14px rgba(0,0,0,0.3)}}',
    '.lf-form-btn{display:block;margin-top:10px;background:linear-gradient(135deg,#1ab8c8 0%,#0fa5b5 100%);',
    'color:#fff!important;font-weight:800;padding:11px 16px;border-radius:10px;text-decoration:none!important;',
    'font-size:0.84rem;text-align:center;letter-spacing:0.02em;',
    'animation:lf-pulse-glow 2s ease-in-out infinite;}',
    '.lf-chat-input-row{display:flex;gap:8px;padding:12px 14px;border-top:1px solid rgba(255,255,255,0.08)}',
    '#lf-input{flex:1;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);',
    'border-radius:10px;padding:9px 13px;color:#fff;font-size:0.875rem;outline:none;',
    'font-family:Inter,sans-serif;resize:none;transition:border-color .2s}',
    '#lf-input:focus{border-color:#1ab8c8}',
    '#lf-input::placeholder{color:rgba(255,255,255,0.35)}',
    '#lf-send{background:#1ab8c8;border:none;border-radius:10px;width:40px;height:40px;',
    'cursor:pointer;display:flex;align-items:center;justify-content:center;',
    'flex-shrink:0;transition:background .2s;align-self:flex-end}',
    '#lf-send:hover{background:#14a0ae}',
    '#lf-send svg{width:18px;height:18px;fill:#0A1A2F}',
    '#lf-send:disabled{opacity:.45;cursor:not-allowed}',
    '#lf-bubble{position:absolute;bottom:72px;right:0;background:#fff;color:#0A1A2F;',
    'padding:8px 14px 8px 16px;border-radius:14px;font-size:0.875rem;font-weight:700;',
    'white-space:nowrap;box-shadow:0 4px 20px rgba(0,0,0,0.25);',
    'display:flex;align-items:center;gap:8px;',
    'opacity:0;transform:translateY(6px);transition:opacity .3s,transform .3s;pointer-events:none}',
    '#lf-bubble.lf-bubble-show{opacity:1;transform:translateY(0);pointer-events:auto}',
    '#lf-bubble::after{content:"";position:absolute;bottom:-6px;right:20px;',
    'width:12px;height:12px;background:#fff;transform:rotate(45deg);border-radius:2px;',
    'box-shadow:2px 2px 4px rgba(0,0,0,0.08)}',
    '#lf-bubble-close{background:none;border:none;cursor:pointer;color:rgba(0,0,0,0.35);',
    'font-size:14px;line-height:1;padding:0;flex-shrink:0;transition:color .15s}',
    '#lf-bubble-close:hover{color:#0A1A2F}',
    '@media (max-width:480px){#lf-chat{bottom:16px;right:16px}#lf-chat-panel{width:calc(100vw - 32px)}}',
  ].join('');

  var styleEl = document.createElement('style');
  styleEl.textContent = css;
  document.head.appendChild(styleEl);

  var wrapper = document.createElement('div');
  wrapper.id = 'lf-chat';
  wrapper.innerHTML = [
    '<div id="lf-bubble">Dúvidas? 💬<button id="lf-bubble-close" aria-label="Fechar">&#x2715;</button></div>',
    '<div id="lf-chat-panel">',
    '  <div class="lf-chat-header">',
    '    <div class="lf-chat-header-left">',
    '      <div class="lf-avatar">L</div>',
    '      <div class="lf-header-info"><strong>Leo</strong><span>Assistente virtual · Ginásios Da Vinci</span></div>',
    '    </div>',
    '    <div class="lf-header-actions">',
    '      <button class="lf-new-chat-btn" id="lf-new-chat" aria-label="Novo chat">&#x21BA; Novo chat</button>',
    '      <button class="lf-close-btn" id="lf-close" aria-label="Fechar">&#x2715;</button>',
    '    </div>',
    '  </div>',
    '  <div id="lf-chat-msgs"></div>',
    '  <div class="lf-chat-input-row">',
    '    <input id="lf-input" type="text" placeholder="Escreve aqui..." maxlength="500" autocomplete="off">',
    '    <button id="lf-send" aria-label="Enviar">',
    '      <svg viewBox="0 0 24 24"><path d="M2 21l21-9L2 3v7l15 2-15 2z"/></svg>',
    '    </button>',
    '  </div>',
    '</div>',
    '<button id="lf-chat-btn" aria-label="Abrir chat">',
    '  <svg viewBox="0 0 24 24"><path d="M20 2H4a2 2 0 00-2 2v18l4-4h14a2 2 0 002-2V4a2 2 0 00-2-2z"/></svg>',
    '</button>',
  ].join('');
  document.body.appendChild(wrapper);

  var bubble     = document.getElementById('lf-bubble');
  var panel      = document.getElementById('lf-chat-panel');
  var btn        = document.getElementById('lf-chat-btn');
  var closeBtn   = document.getElementById('lf-close');
  var newChatBtn = document.getElementById('lf-new-chat');
  var msgs       = document.getElementById('lf-chat-msgs');
  var input      = document.getElementById('lf-input');
  var sendBtn    = document.getElementById('lf-send');
  var csrfToken  = '';
  var greeted    = false;

  var savedHistory = [];
  try { savedHistory = JSON.parse(sessionStorage.getItem(SESSION_KEY) || '[]'); } catch (e) {}

  function saveHistory() {
    try { sessionStorage.setItem(SESSION_KEY, JSON.stringify(savedHistory)); } catch (e) {}
  }

  function fetchCsrf(cb) {
    fetch(CSRF_URL)
      .then(function (r) { return r.json(); })
      .then(function (d) { csrfToken = d.token || ''; cb(); })
      .catch(function () { cb(); });
  }

  function addMsg(text, role) {
    var el = document.createElement('div');
    el.className = 'lf-msg ' + role;

    if (role === 'bot') {
      var safe = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

      safe = safe.replace(/\[FORM_LINK\]/g,
        '<a href="' + FORM_URL + '" class="lf-form-btn">🚀 ' + FORM_LABEL + '</a>');

      safe = safe.replace(/\[([^\]]+)\]\((https?:\/\/[^)]+)\)/g,
        '<a href="$2" target="_blank" rel="noopener" style="color:#1ab8c8;text-decoration:underline;">$1</a>');

      safe = safe.replace(/(https?:\/\/[^\s<>"']+)/g,
        '<a href="$1" target="_blank" rel="noopener" ' +
        'style="color:#1ab8c8;text-decoration:underline;word-break:break-all;">$1</a>');

      el.innerHTML = safe;
    } else {
      el.textContent = text;
    }

    msgs.appendChild(el);
    msgs.scrollTop = msgs.scrollHeight;
    return el;
  }

  function addLoader() {
    var el = document.createElement('div');
    el.className = 'lf-loader';
    el.innerHTML = '<span></span><span></span><span></span>';
    msgs.appendChild(el);
    msgs.scrollTop = msgs.scrollHeight;
    return el;
  }

  function restoreHistory() {
    savedHistory.forEach(function (m) {
      addMsg(m.content, m.role === 'user' ? 'user' : 'bot');
    });
  }

  function send() {
    var text = input.value.trim();
    if (!text || sendBtn.disabled) return;

    addMsg(text, 'user');
    savedHistory.push({ role: 'user', content: text });
    saveHistory();
    input.value = '';
    sendBtn.disabled = true;

    var loader = addLoader();

    var body = new URLSearchParams();
    body.append('message', text);
    body.append('csrf_token', csrfToken);

    fetch(CHAT_URL, { method: 'POST', body: body })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        loader.remove();
        if (data.error) {
          addMsg('Ups, algo correu mal: ' + data.error, 'bot');
        } else {
          addMsg(data.reply, 'bot');
          savedHistory.push({ role: 'assistant', content: data.reply });
          saveHistory();
          fetchCsrf(function () {});
        }
        sendBtn.disabled = false;
        input.focus();
      })
      .catch(function () {
        loader.remove();
        addMsg('Sem resposta do servidor. Verifica a tua ligação.', 'bot');
        sendBtn.disabled = false;
      });
  }

  function greet() {
    if (greeted) return;
    greeted = true;
    if (savedHistory.length > 0) {
      restoreHistory();
      return;
    }
    setTimeout(function () {
      var greetMsg = 'Olá! Sou o Leo, assistente virtual dos Ginásios Da Vinci. Queres saber mais sobre estudar em Portugal? Em que te posso ajudar?';
      addMsg(greetMsg, 'bot');
      savedHistory.push({ role: 'assistant', content: greetMsg });
      saveHistory();
    }, 350);
  }

  function newChat() {
    try { sessionStorage.removeItem(SESSION_KEY); } catch (e) {}
    savedHistory = [];
    var body = new URLSearchParams();
    body.append('action', 'reset');
    body.append('csrf_token', csrfToken);
    fetch(CHAT_URL, { method: 'POST', body: body }).catch(function () {});
    msgs.innerHTML = '';
    greeted = false;
    fetchCsrf(function () { greet(); input.focus(); });
  }

  function openPanel() {
    panel.classList.add('lf-open');
    bubble.classList.remove('lf-bubble-show');
    try { localStorage.removeItem(PANEL_CLOSED_KEY); } catch (e) {}
    fetchCsrf(function () {
      greet();
      input.focus();
    });
  }

  function closePanel() {
    panel.classList.remove('lf-open');
    try { localStorage.setItem(PANEL_CLOSED_KEY, '1'); } catch (e) {}
  }

  btn.addEventListener('click', function () {
    panel.classList.contains('lf-open') ? closePanel() : openPanel();
  });
  closeBtn.addEventListener('click', closePanel);
  newChatBtn.addEventListener('click', newChat);

  sendBtn.addEventListener('click', send);
  input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
  });

  var bubbleCloseBtn = document.getElementById('lf-bubble-close');

  function dismissBubble() {
    bubble.classList.remove('lf-bubble-show');
    try { localStorage.setItem(BUBBLE_KEY, '1'); } catch (e) {}
  }

  bubbleCloseBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    dismissBubble();
  });

  try {
    if (!localStorage.getItem(PANEL_CLOSED_KEY)) {
      setTimeout(function () {
        openPanel();
        setTimeout(function () {
          if (panel.classList.contains('lf-open')) closePanel();
        }, 5000);
      }, 1500);
    } else if (!localStorage.getItem(BUBBLE_KEY)) {
      setTimeout(function () {
        if (!panel.classList.contains('lf-open')) {
          bubble.classList.add('lf-bubble-show');
        }
      }, 1500);
    }
  } catch (e) {}
})();
