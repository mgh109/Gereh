(() => {
  const form = document.getElementById('product-editor');
  if (!form) return;
  const variants = document.getElementById('editor-variants');
  let index = variants.children.length, dirty = false, urls = [];
  const state = document.getElementById('editor-state');
  function changed() { dirty = true; state.textContent = 'تغییرات ذخیره نشده'; }
  function preview() {
    form.querySelector('[data-preview-name]').textContent = form.elements.name.value || 'نام محصول';
    const regular=Number(form.elements.price_toman.value)||0, saleInput=form.elements.sale_price_toman;
    const sale=Number(saleInput.value), discounted=saleInput.value!=='' && Number.isInteger(sale) && sale>0 && sale<regular;
    saleInput.setCustomValidity(saleInput.value!=='' && !discounted?'قیمت با تخفیف باید بیشتر از صفر و کمتر از قیمت اصلی باشد.':'');
    const fmt=n=>new Intl.NumberFormat('fa-IR').format(n)+' تومان';
    form.querySelector('[data-preview-price]').textContent=fmt(discounted?sale:regular);
    const oldPrice=form.querySelector('[data-preview-regular]'),discount=form.querySelector('[data-preview-discount]');
    oldPrice.hidden=!discounted;oldPrice.textContent=discounted?fmt(regular):'';
    discount.hidden=!discounted;discount.textContent=discounted?new Intl.NumberFormat('fa-IR',{maximumFractionDigits:1}).format((regular-sale)/regular*100)+'٪ تخفیف':'';
    form.querySelector('[data-preview-description]').textContent = form.elements.short_description.value;
  }
  form.addEventListener('input', () => { changed(); preview(); });
  form.addEventListener('change', changed);
  document.getElementById('add-variant').addEventListener('click', () => {
    if (variants.children.length >= 100) { state.textContent = 'حداکثر ۱۰۰ تنوع مجاز است.'; return; }
    const row = document.createElement('div'); row.className = 'editor-variant'; row.dataset.variant = '';
    for (const [field, text, type, max] of [['size_label','سایز','text',60],['color_name','رنگ','text',80],['quantity','موجودی','number',0],['sku','SKU','text',80]]) {
      const label = document.createElement('label'), input = document.createElement('input');
      label.textContent = text; input.name = `variants[${index}][${field}]`; input.type = type;
      if (max) input.maxLength = max;
      if (type === 'number') { input.min = '0'; input.max = '100000000'; input.value = '0'; input.required = true; }
      if (field === 'sku') { input.dir = 'ltr'; input.placeholder = 'خودکار'; }
      label.append(input); row.append(label);
    }
    const remove = document.createElement('button'); remove.type = 'button'; remove.textContent = '×'; remove.dataset.removeVariant = ''; remove.setAttribute('aria-label','حذف ردیف تنوع جدید'); row.append(remove);
    variants.append(row); index++; changed(); row.querySelector('input').focus();
  });
  variants.addEventListener('click', event => { const button = event.target.closest('[data-remove-variant]'); if (button) { button.closest('[data-variant]').remove(); changed(); } });
  const images = document.getElementById('editor-images'), previews = document.getElementById('image-previews');
  images.addEventListener('change', () => {
    urls.forEach(URL.revokeObjectURL); urls = []; previews.replaceChildren(); images.setCustomValidity('');
    if (images.files.length > 10 || [...images.files].some(f => f.size > 5*1024*1024 || !['image/jpeg','image/png','image/webp'].includes(f.type))) {
      images.setCustomValidity('حداکثر ۱۰ عکس JPG، PNG یا WebP، هرکدام تا ۵ مگابایت انتخاب کنید.'); images.reportValidity(); return;
    }
    for (const file of images.files) { const img = document.createElement('img'); img.src = URL.createObjectURL(file); urls.push(img.src); img.alt = file.name; img.width = 72; img.height = 72; previews.append(img); }
  });
  form.addEventListener('submit', () => { dirty = false; state.textContent = 'در حال ذخیره…'; });
  window.addEventListener('beforeunload', event => { if (dirty) { event.preventDefault(); event.returnValue = ''; } });
  preview();
})();
