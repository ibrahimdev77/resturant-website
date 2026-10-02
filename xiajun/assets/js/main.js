/* ============================================================
   XIAJUN – site-wide behaviour
   ============================================================ */
(function () {
  'use strict';
  const body = document.body;
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Toasts (shared) ---------- */
  window.XJ = {
    toast(msg, type) {
      const stack = document.getElementById('toasts');
      if (!stack) return;
      const t = document.createElement('div');
      t.className = 'toast' + (type === 'error' ? ' error' : '');
      t.textContent = msg;
      stack.appendChild(t);
      setTimeout(() => t.remove(), 3100);
    },
    bump() {
      const b = document.getElementById('cartBadge');
      if (!b) return;
      b.classList.remove('bump'); void b.offsetWidth; b.classList.add('bump');
    },
    setBadge(n) {
      const b = document.getElementById('cartBadge');
      if (!b) return;
      b.textContent = n; b.dataset.count = n;
    }
  };

  /* ---------- Gate page transition ---------- */
  const gate = document.querySelector('.gate');
  if (gate && !reduce) {
    document.addEventListener('click', (e) => {
      const a = e.target.closest('a[href]');
      if (!a || e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;
      if (a.target === '_blank' || a.hasAttribute('download')) return;
      const url = new URL(a.href, location.href);
      if (url.origin !== location.origin) return;
      if (url.pathname === location.pathname && url.hash) return;
      e.preventDefault();
      gate.classList.add('closing');
      setTimeout(() => { location.href = a.href; }, 520);
    });
    window.addEventListener('pageshow', (e) => {
      if (e.persisted) { gate.classList.remove('closing'); gate.classList.add('gone'); }
    });
  }

  /* ---------- Sticky header ---------- */
  const header = document.getElementById('siteHeader');
  const onScroll = () => header && header.classList.toggle('scrolled', window.scrollY > 40);
  window.addEventListener('scroll', onScroll, { passive: true }); onScroll();

  /* ---------- Mobile nav ---------- */
  const toggle = document.getElementById('navToggle');
  const nav = document.getElementById('mainNav');
  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const open = nav.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open);
    });
    nav.addEventListener('click', (e) => { if (e.target.closest('a')) nav.classList.remove('open'); });
  }

  /* ---------- Button ripple ---------- */
  document.addEventListener('pointerdown', (e) => {
    const btn = e.target.closest('.btn');
    if (!btn) return;
    const r = btn.getBoundingClientRect();
    const size = Math.max(r.width, r.height);
    const s = document.createElement('span');
    s.className = 'ripple';
    s.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX - r.left - size / 2}px;top:${e.clientY - r.top - size / 2}px`;
    btn.appendChild(s);
    setTimeout(() => s.remove(), 700);
  });

  /* ---------- Reveals (ink wipe + staggered pop) ---------- */
  const targets = document.querySelectorAll('.wipe, .pop');
  document.querySelectorAll('.stagger').forEach((group) => {
    [...group.children].forEach((c, i) => { c.classList.add('pop'); c.style.setProperty('--i', (i % 6) * 0.08 + 's'); });
  });
  const all = document.querySelectorAll('.wipe, .pop');
  if ('IntersectionObserver' in window && !reduce) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((en) => { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
    all.forEach((el) => io.observe(el));
  } else { all.forEach((el) => el.classList.add('in')); }

  /* ---------- Count-up numbers ---------- */
  const counters = document.querySelectorAll('[data-count-to]');
  if (counters.length && 'IntersectionObserver' in window) {
    const co = new IntersectionObserver((entries) => {
      entries.forEach((en) => {
        if (!en.isIntersecting) return;
        const el = en.target, end = +el.dataset.countTo, dur = 1600, t0 = performance.now();
        const tick = (t) => {
          const p = Math.min(1, (t - t0) / dur);
          el.textContent = Math.round(end * (1 - Math.pow(1 - p, 3))).toLocaleString();
          if (p < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick); co.unobserve(el);
      });
    }, { threshold: 0.6 });
    counters.forEach((c) => co.observe(c));
  }

  /* ---------- Rising embers canvas ---------- */
  const cv = document.getElementById('embers');
  if (cv && !reduce) {
    const ctx = cv.getContext('2d');
    let W, H, parts = [];
    const resize = () => {
      W = cv.width = window.innerWidth; H = cv.height = window.innerHeight;
      const n = Math.min(60, Math.floor(W / 24));
      parts = Array.from({ length: n }, () => spawn(true));
    };
    const spawn = (anywhere) => ({
      x: Math.random() * W, y: anywhere ? Math.random() * H : H + 10,
      r: Math.random() * 2.2 + .6, vy: Math.random() * .5 + .2, vx: (Math.random() - .5) * .3,
      ph: Math.random() * 6.28, hue: Math.random() < .7 ? 38 : 8
    });
    const frame = () => {
      ctx.clearRect(0, 0, W, H);
      for (const p of parts) {
        p.y -= p.vy; p.x += p.vx + Math.sin(p.ph) * .25; p.ph += .02;
        if (p.y < -10) Object.assign(p, spawn(false));
        const a = .25 + Math.sin(p.ph * 2) * .2;
        const g = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, p.r * 5);
        g.addColorStop(0, `hsla(${p.hue},90%,65%,${a})`);
        g.addColorStop(1, `hsla(${p.hue},90%,50%,0)`);
        ctx.fillStyle = g; ctx.beginPath(); ctx.arc(p.x, p.y, p.r * 5, 0, 6.283); ctx.fill();
      }
      requestAnimationFrame(frame);
    };
    resize(); frame();
    window.addEventListener('resize', resize);
  }

  /* ---------- Menu page: filter + search ---------- */
  if (body.dataset.page === 'menu') {
    const chips = document.querySelectorAll('.chip');
    const dishes = document.querySelectorAll('.dish');
    const groups = document.querySelectorAll('.menu-group');
    const search = document.getElementById('menuSearch');
    const spicy = document.getElementById('spicyOnly');
    const empty = document.getElementById('menuEmpty');
    let cat = 'all';
    const apply = () => {
      const q = (search.value || '').trim().toLowerCase();
      let shown = 0;
      dishes.forEach((d) => {
        const okCat = cat === 'all' || d.dataset.cat === cat;
        const okQ = !q || d.dataset.search.includes(q);
        const okSp = !spicy.checked || +d.dataset.spicy > 0;
        const show = okCat && okQ && okSp;
        d.classList.toggle('hide', !show);
        if (show) {
          shown++;
          if (!reduce) d.animate([{ opacity: 0, transform: 'scale(.92)' }, { opacity: 1, transform: 'none' }], { duration: 380, easing: 'ease-out' });
        }
      });
      groups.forEach((g) => { g.style.display = g.querySelector('.dish:not(.hide)') ? '' : 'none'; });
      empty.classList.toggle('show', shown === 0);
    };
    chips.forEach((c) => c.addEventListener('click', () => {
      chips.forEach((x) => x.classList.remove('active')); c.classList.add('active');
      cat = c.dataset.cat; apply();
      if (cat !== 'all') {
        const g = document.getElementById('cat-' + cat);
        if (g) window.scrollTo({ top: g.getBoundingClientRect().top + scrollY - 150, behavior: 'smooth' });
      }
    }));
    search.addEventListener('input', apply);
    spicy.addEventListener('change', apply);
  }
})();
