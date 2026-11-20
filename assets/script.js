// Closes the mobile menu after a link is tapped.
document.addEventListener('DOMContentLoaded', function () {
  var site = document.getElementById('site');
  if (site) site.querySelectorAll('.nav-l a').forEach(function (a) {
    a.addEventListener('click', function () { site.classList.remove('open'); });
  });
});

// Remember the scroll position when a form is saved and put the page back there afterwards.
(function () {
  var KEY = 'snr_scroll';
  document.addEventListener('submit', function (e) {
    if ((e.target.method || '').toLowerCase() !== 'post') return;
    try { sessionStorage.setItem(KEY, JSON.stringify({ p: location.pathname, y: window.scrollY })); } catch (x) {}
  });
  function restore() {
    try {
      var s = JSON.parse(sessionStorage.getItem(KEY) || 'null');
      sessionStorage.removeItem(KEY);
      if (s && s.p === location.pathname) window.scrollTo({ top: s.y, behavior: 'instant' });
    } catch (x) {}
  }
  document.addEventListener('DOMContentLoaded', function () {
    restore();
    // Messages: icon, close button, and they close by themselves.
    document.querySelectorAll('main .msg').forEach(function (m) {
      var isErr = m.classList.contains('err');
      var ico = document.createElement('span');
      ico.className = 'ico'; ico.setAttribute('aria-hidden', 'true'); ico.textContent = isErr ? '!' : '\u2713';
      m.insertBefore(ico, m.firstChild);
      var b = document.createElement('button');
      b.type = 'button'; b.className = 'x'; b.setAttribute('aria-label', 'Close message'); b.textContent = '\u00d7';
      m.setAttribute('role', isErr ? 'alert' : 'status');
      function close() { m.classList.add('out'); setTimeout(function () { m.remove(); }, 260); }
      b.addEventListener('click', close);
      m.appendChild(b);
      setTimeout(close, isErr ? 7000 : 4000);
    });
  });
  window.addEventListener('load', restore);
})();