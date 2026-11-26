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
  window.addEventListener('load', function () { restore(); try { sessionStorage.removeItem(KEY); } catch (x) {} });
})();

// Hover zoom: the photo scales up around the mouse position.
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.zoom').forEach(function (z) {
    z.style.setProperty('--zs', z.getAttribute('data-zoom') || '1.3');
    z.addEventListener('mousemove', function (e) {
      var r = z.getBoundingClientRect();
      z.style.setProperty('--zx', ((e.clientX - r.left) / r.width * 100) + '%');
      z.style.setProperty('--zy', ((e.clientY - r.top) / r.height * 100) + '%');
    });
  });
});

// Product page: picking a size updates the price, the quantity limit and the note.
document.addEventListener('DOMContentLoaded', function () {
  var root = document.querySelector('.pd');
  if (!root) return;
  var price = document.getElementById('pdPrice'), qty = root.querySelector('.pv-qty'), hint = root.querySelector('.pd-hint');
  var btn = root.querySelector('.pd-buy button'), btnText = btn ? btn.textContent : '';
  // Sizes shown as plain boxes (guests / admins) just say they are out of stock when clicked.
  root.querySelectorAll('.sz.off').forEach(function (el) {
    if (el.querySelector('input')) return;
    function say() {
      if (!hint) return;
      hint.textContent = 'Size ' + el.getAttribute('data-size') + ' is out of stock.';
      hint.classList.add('warn');
    }
    el.addEventListener('click', say);
    el.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); say(); } });
  });
  // Picking a size: in stock = normal order button; sold out = button becomes "Out of stock" and is disabled.
  root.querySelectorAll('.pd-sizes input[type=radio]').forEach(function (r) {
    r.addEventListener('change', function () {
      var q = parseInt(r.getAttribute('data-qty'), 10) || 0;
      if (q <= 0) {
        if (btn) { btn.textContent = 'Out of stock'; btn.disabled = true; }
        if (qty) qty.disabled = true;
        if (hint) { hint.textContent = 'Size ' + r.getAttribute('data-size') + ' is out of stock.'; hint.classList.add('warn'); }
        return;
      }
      if (btn) { btn.textContent = btnText; btn.disabled = false; }
      if (qty) qty.disabled = false;
      if (hint) hint.classList.remove('warn');
      if (price) price.textContent = '\u20b1' + Number(r.getAttribute('data-price')).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      if (qty) { qty.max = q; if (+qty.value > q) qty.value = 1; }
      if (hint) hint.textContent = q <= 3 ? 'Only ' + q + ' left in this size' : '';
    });
  });
});


// Admin stock: remember which shoe blocks are open after a save.
document.addEventListener('DOMContentLoaded', function () {
  var blocks = document.querySelectorAll('details.sg');
  if (!blocks.length) return;
  var KEY = 'snr_open_groups';
  function load() { try { return JSON.parse(sessionStorage.getItem(KEY) || '[]'); } catch (x) { return []; } }
  function save(list) { try { sessionStorage.setItem(KEY, JSON.stringify(list)); } catch (x) {} }
  var saved = load();
  blocks.forEach(function (d) {
    var id = d.getAttribute('data-g');
    if (saved.indexOf(id) !== -1) d.open = true;
    d.addEventListener('toggle', function () {
      var list = load().filter(function (g) { return g !== id; });
      if (d.open) list.push(id);
      save(list);
    });
  });
});


// Admin stock: "Edit" unlocks the stocks and price boxes, then the same button becomes "Save".
document.addEventListener('click', function (e) {
  var b = e.target.closest ? e.target.closest('.js-edit') : null;
  if (!b) return;
  var f = b.closest('form');
  if (f.classList.contains('editing')) {            // second click = Save
    if (f.reportValidity && !f.reportValidity()) return;
    if (f.requestSubmit) f.requestSubmit();
    else { try { sessionStorage.setItem('snr_scroll', JSON.stringify({ p: location.pathname, y: window.scrollY })); } catch (x) {} f.submit(); }
    return;
  }
  f.classList.add('editing');
  f.querySelectorAll('input[readonly]').forEach(function (i) { i.removeAttribute('readonly'); i.removeAttribute('tabindex'); });
  b.textContent = 'Save';
  var q = f.querySelector('input[name=qty]'); q.focus(); q.select();
});