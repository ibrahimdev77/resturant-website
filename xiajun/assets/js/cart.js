/* ============================================================
   XIAJUN – cart (talks to /api/cart.php, session based)
   ============================================================ */
(function () {
  'use strict';
  const body = document.body;
  const API = body.dataset.base + '/api/cart.php';
  const CSRF = body.dataset.csrf;
  const FEE = parseFloat(body.dataset.fee || '3');
  const FREE = parseFloat(body.dataset.free || '40');
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const money = (n) => '$' + Number(n).toFixed(2);
  const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  async function call(payload) {
    const res = await fetch(API, {
      method: payload ? 'POST' : 'GET',
      headers: { 'Content-Type': 'application/json' },
      body: payload ? JSON.stringify({ ...payload, csrf: CSRF }) : undefined,
      credentials: 'same-origin'
    });
    const data = await res.json().catch(() => ({ ok: false, error: 'Something went wrong.' }));
    if (!res.ok || !data.ok) throw new Error(data.error || 'Something went wrong.');
    return data;
  }

  /* ---------- Fly-to-cart animation ---------- */
  function fly(fromEl, emoji) {
    const target = document.getElementById('cartLink');
    if (reduce || !fromEl || !target || !fromEl.animate) return Promise.resolve();
    const a = fromEl.getBoundingClientRect(), b = target.getBoundingClientRect();
    const el = document.createElement('span');
    el.className = 'fly'; el.textContent = emoji || '🥢';
    el.style.left = a.left + a.width / 2 - 16 + 'px';
    el.style.top = a.top + a.height / 2 - 16 + 'px';
    document.body.appendChild(el);
    const dx = b.left + b.width / 2 - (a.left + a.width / 2);
    const dy = b.top + b.height / 2 - (a.top + a.height / 2);
    const anim = el.animate([
      { transform: 'translate(0,0) scale(1) rotate(0)', opacity: 1 },
      { transform: `translate(${dx * .45}px,${dy * .45 - 90}px) scale(1.5) rotate(160deg)`, opacity: 1, offset: .45 },
      { transform: `translate(${dx}px,${dy}px) scale(.35) rotate(360deg)`, opacity: .4 }
    ], { duration: 850, easing: 'cubic-bezier(.5,0,.4,1)' });
    return anim.finished.then(() => el.remove()).catch(() => el.remove());
  }

  /* ---------- Add buttons (menu + home) ---------- */
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-add]');
    if (!btn) return;
    e.preventDefault();
    if (btn.disabled) return;
    btn.disabled = true;
    const id = +btn.dataset.add;
    const card = btn.closest('.dish');
    const emoji = card ? card.querySelector('.dish-art span')?.textContent : '🥢';
    const art = card ? card.querySelector('.dish-art') : btn;
    try {
      const flight = fly(art, emoji);
      const data = await call({ action: 'add', id });
      await flight;
      XJ.setBadge(data.count); XJ.bump();
      XJ.toast((data.added || 'Dish') + ' added to your cart');
    } catch (err) {
      XJ.toast(err.message, 'error');
    } finally {
      btn.disabled = false;
    }
  });

  /* ---------- Cart page ---------- */
  const root = document.getElementById('cartRoot');
  if (!root) return;

  function summaryHTML(d) {
    const fee = d.total >= FREE || d.total === 0 ? 0 : FEE;
    const left = Math.max(0, FREE - d.total);
    const pct = Math.min(100, (d.total / FREE) * 100);
    return `
      <aside class="panel summary">
        <h2 style="font-size:1.7rem">Your order</h2>
        <dl>
          <dt>Subtotal</dt><dd>${money(d.total)}</dd>
          <dt>Delivery</dt><dd>${fee === 0 ? 'Free' : money(fee)}</dd>
        </dl>
        <div class="free-bar"><i style="width:${pct}%"></i></div>
        <p class="note-small">${left > 0 ? `Add ${money(left)} more for free delivery.` : 'You have free delivery.'}</p>
        <dl><dt class="grand">Total</dt><dd class="grand">${money(d.total + fee)}</dd></dl>
        <a class="btn btn--solid" style="width:100%" href="${body.dataset.base}/checkout.php">Go to checkout</a>
        <button class="btn btn--small" id="clearCart" style="width:100%;margin-top:.8rem;border-color:rgba(217,164,65,.3)">Empty cart</button>
      </aside>`;
  }

  function lineHTML(i) {
    return `
      <div class="cart-line" data-id="${i.id}">
        <div class="cart-emoji">${esc(i.emoji)}</div>
        <div><h3>${esc(i.name)}</h3><span class="cn">${esc(i.name_cn)}</span> · <span class="note-small">${money(i.price)} each</span></div>
        <div class="qty" role="group" aria-label="Quantity for ${esc(i.name)}">
          <button data-q="-1" aria-label="Decrease">−</button><span>${i.qty}</span><button data-q="1" aria-label="Increase">+</button>
        </div>
        <div class="line-total">${money(i.line)}</div>
        <button class="remove" data-remove>Remove</button>
      </div>`;
  }

  function render(d) {
    XJ.setBadge(d.count);
    if (!d.items.length) {
      root.innerHTML = `
        <div class="empty-cart">
          <div class="big">🥟</div>
          <h2>Your cart is empty</h2>
          <p style="margin:1rem auto 2rem">The dumplings are a good place to start.</p>
          <a class="btn btn--solid" href="${body.dataset.base}/menu.php">Browse the menu</a>
        </div>`;
      return;
    }
    root.innerHTML = `
      <div class="cart-layout">
        <div class="panel" id="lines">${d.items.map(lineHTML).join('')}</div>
        ${summaryHTML(d)}
      </div>`;
  }

  let state = null;
  async function load() {
    try { state = await call(); render(state); }
    catch (err) { root.innerHTML = '<p class="alert error">Could not load your cart. Refresh the page and try again.</p>'; }
  }

  root.addEventListener('click', async (e) => {
    const line = e.target.closest('.cart-line');
    try {
      if (e.target.closest('#clearCart')) { state = await call({ action: 'clear' }); render(state); XJ.toast('Cart emptied'); return; }
      if (!line) return;
      const id = +line.dataset.id;
      const item = state.items.find((x) => x.id === id);
      if (e.target.closest('[data-remove]')) {
        line.classList.add('removing');
        const [data] = await Promise.all([call({ action: 'remove', id }), new Promise((r) => setTimeout(r, reduce ? 0 : 420))]);
        state = data; render(state); return;
      }
      const q = e.target.closest('[data-q]');
      if (q) {
        const next = item.qty + (+q.dataset.q);
        if (next <= 0) { line.querySelector('[data-remove]').click(); return; }
        state = await call({ action: 'set', id, qty: next }); render(state); XJ.bump();
      }
    } catch (err) { XJ.toast(err.message, 'error'); }
  });

  load();
})();
