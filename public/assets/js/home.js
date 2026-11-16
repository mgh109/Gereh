(() => {
  const key = 'gereh-favorites';
  let favorites = {};
  try {
    const saved = JSON.parse(localStorage.getItem(key) || '{}');
    if (saved && typeof saved === 'object' && !Array.isArray(saved)) favorites = saved;
  } catch {}
  const feedback = document.getElementById('home-feedback');
  let timer;
  function notify(text) {
    feedback.textContent = text; feedback.hidden = false;
    clearTimeout(timer); timer = setTimeout(() => { feedback.hidden = true; }, 3000);
  }
  function sync() {
    document.querySelectorAll('[data-favorite]').forEach(button => {
      button.setAttribute('aria-pressed', String(Boolean(favorites[button.dataset.id])));
    });
  }
  document.querySelectorAll('[data-favorite]').forEach(button => button.addEventListener('click', () => {
    const id = button.dataset.id;
    if (favorites[id]) delete favorites[id];
    else favorites[id] = {name: button.dataset.name, url: button.dataset.url, image: button.dataset.image, price: Number(button.dataset.price)};
    sync();
    try { localStorage.setItem(key, JSON.stringify(favorites)); }
    catch { notify('ذخیره‌سازی مرورگر در دسترس نیست؛ علاقه‌مندی‌ها فقط در این صفحه حفظ می‌شوند.'); }
  }));
  const track = document.getElementById('home-brand-track');
  const arrows = [...document.querySelectorAll('[data-brand-direction]')];
  function brandState() {
    arrows.forEach(button => { button.disabled = Number(button.dataset.brandDirection) < 0 ? track.scrollLeft <= 1 : track.scrollLeft + track.clientWidth >= track.scrollWidth - 1; });
  }
  arrows.forEach(button => button.addEventListener('click', () => track.scrollBy({left: Number(button.dataset.brandDirection) * track.clientWidth, behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth'})));
  track?.addEventListener('scroll', brandState, {passive: true});
  if (track) { brandState(); window.addEventListener('resize', brandState); }
  document.addEventListener('gereh:cart-updated', event => {
    const count = (event.detail?.lines || []).reduce((sum, line) => sum + Number(line.quantity || 0), 0);
    document.querySelectorAll('[data-cart-count]').forEach(badge => { badge.hidden = !count; badge.textContent = new Intl.NumberFormat('fa-IR').format(count); });
  });
  fetch(document.getElementById('gereh-cart-drawer')?.dataset.summaryUrl || '', {credentials: 'same-origin', cache: 'no-store'})
    .then(response => response.ok ? response.json() : null)
    .then(result => { if (result?.success) document.dispatchEvent(new CustomEvent('gereh:cart-updated', {detail: result.data})); }).catch(() => {});
  sync();
  const grid = document.getElementById('favorites-grid');
  function renderFavorites() {
    if (!grid) return;
    grid.replaceChildren();
    for (const [id, item] of Object.entries(favorites)) {
      if (!item || typeof item.name !== 'string' || typeof item.url !== 'string') continue;
      let url;
      try { url = new URL(item.url, location.origin); } catch { continue; }
      if (url.origin !== location.origin || !/^https?:$/.test(url.protocol)) continue;
      const card = document.createElement('article');card.className = 'favorite-product';
      const picture = document.createElement('div');picture.className = 'favorite-picture';
      const link = document.createElement('a');link.href = url.href;
      if (item.image) {
        try {
          const imageUrl = new URL(item.image, location.origin);
          if (/^https?:$/.test(imageUrl.protocol)) {
            const image = document.createElement('img');image.src = imageUrl.href;image.alt = item.name;image.loading = 'lazy';link.append(image);
          }
        } catch {}
      }
      if (!link.children.length) link.textContent = item.name;
      const remove = document.createElement('button');remove.type = 'button';remove.textContent = '♥';remove.setAttribute('aria-label', 'حذف از علاقه‌مندی‌ها: '+item.name);
      remove.addEventListener('click', () => {
        delete favorites[id];
        try { localStorage.setItem(key, JSON.stringify(favorites)); } catch { notify('ذخیره‌سازی مرورگر در دسترس نیست.'); }
        sync();renderFavorites();
      });
      picture.append(link, remove);
      const title = document.createElement('h2'), titleLink = document.createElement('a');titleLink.href = url.href;titleLink.textContent = item.name;title.append(titleLink);
      card.append(picture, title);
      if (Number.isFinite(item.price) && item.price > 0) {
        const price = document.createElement('p');price.textContent = new Intl.NumberFormat('fa-IR').format(item.price)+' تومان';card.append(price);
      }
      grid.append(card);
    }
    document.getElementById('favorites-count').textContent = new Intl.NumberFormat('fa-IR').format(grid.children.length);
    document.getElementById('favorites-empty').hidden = grid.children.length > 0;
  }
  renderFavorites();
})();

