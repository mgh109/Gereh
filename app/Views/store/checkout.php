<?= view('store/partials/header', ['title' => 'تکمیل خرید | گره', 'description' => 'ثبت اطلاعات گیرنده و تکمیل سفارش']) ?>
<main class="gereh-container mx-auto min-h-[70vh] max-w-5xl px-4 py-8">
  <header class="mb-6"><a href="<?= site_url('cart') ?>" class="gereh-muted text-sm">بازگشت به سبد خرید</a><h1 class="gereh-heading mt-3 text-2xl font-bold">تکمیل سفارش</h1></header>
  <?php if (!$checkoutEnabled): ?>
    <div role="status" class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">پرداخت آنلاین هنوز برای این فروشگاه فعال نشده است. می‌توانید اطلاعات سفارش را بررسی کنید، اما تا زمان تأیید درگاه امکان پرداخت وجود ندارد.</div>
  <?php endif; ?>
  <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_340px]">
    <section class="gereh-card rounded-2xl p-5 sm:p-7">
      <h2 class="mb-5 font-bold">مشخصات تحویل‌گیرنده</h2>
      <form id="checkout-form" class="grid gap-4 sm:grid-cols-2" novalidate>
        <label class="block text-sm">نام و نام خانوادگی گیرنده<input class="gereh-input mt-2 w-full" name="receiver_name" maxlength="160" required autocomplete="name"></label>
        <label class="block text-sm">موبایل گیرنده<input class="gereh-input mt-2 w-full" name="receiver_mobile" type="tel" inputmode="numeric" pattern="09[0-9]{9}" maxlength="11" dir="ltr" required autocomplete="tel"></label>
        <label class="block text-sm">استان<input class="gereh-input mt-2 w-full" name="province" maxlength="100" required autocomplete="address-level1"></label>
        <label class="block text-sm">شهر<input class="gereh-input mt-2 w-full" name="city" maxlength="100" required autocomplete="address-level2"></label>
        <label class="block text-sm sm:col-span-2">آدرس دقیق<textarea class="gereh-input mt-2 w-full" name="address" rows="3" maxlength="2000" required autocomplete="street-address"></textarea></label>
        <label class="block text-sm">کد پستی<input class="gereh-input mt-2 w-full" name="postal_code" inputmode="numeric" maxlength="10" pattern="[0-9]{10}" dir="ltr" required autocomplete="postal-code"></label>
        <div class="sm:col-span-2">
          <h3 class="mb-2 text-sm font-bold">روش ارسال</h3>
          <div id="shipping-options" role="radiogroup" aria-label="روش ارسال" class="space-y-3">
          <?php foreach ($shippingMethods as $i=>$method): ?>
          <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-stone-200 p-4 text-sm">
            <input class="mt-1" type="radio" name="shipping_method" value="<?= esc($method['code'],'attr') ?>" <?= $i===0?'checked':'' ?> required>
            <span class="flex-1"><strong><?= esc($method['title']) ?></strong>
            <small class="gereh-muted mt-1 block"><?= esc($method['description']??'') ?></small>
            <?php if($method['estimated_days_min']!==null): ?>
            <small class="gereh-muted mt-1 block">زمان تقریبی <?= (int)$method['estimated_days_min'] ?> تا <?= (int)($method['estimated_days_max']??$method['estimated_days_min']) ?> روز کاری</small>
            <?php endif; ?></span>
            <strong><?= (int)$method['shipping_toman']===0?'رایگان':number_format((int)$method['shipping_toman']).' تومان' ?></strong>
          </label>
          <?php endforeach; ?>
          </div>
          <p id="shipping-message" role="status" aria-live="polite" class="mt-2 text-xs text-stone-500">هزینه ارسال پس از انتخاب استان به‌روز می‌شود.</p>
        </div>
        <div class="sm:col-span-2"><p role="status" aria-live="polite" id="checkout-message" class="text-sm"></p></div>
      </form>
    </section>
    <aside class="gereh-card h-fit rounded-2xl p-5">
      <h2 class="mb-4 font-bold">خلاصه پرداخت</h2>
      <div class="space-y-3 border-b border-stone-200 pb-4 text-sm">
        <?php foreach ($cart['lines'] as $line): ?>
        <div class="flex justify-between gap-2"><span><?= esc($line['product']['name']) ?> <span class="gereh-muted">× <?= (int)$line['quantity'] ?></span></span><span><?= number_format((int)$line['subtotal']) ?> تومان</span></div>
        <?php endforeach; ?>
      </div>
      <div class="mt-4 flex justify-between text-sm"><span>جمع کالاها</span><strong><?= number_format((int)$cart['total']) ?> تومان</strong></div>
      <div class="mt-3 flex justify-between text-sm"><span>ارسال</span><strong id="shipping-price">—</strong></div>
      <div class="my-4 flex justify-between border-t border-stone-200 pt-4"><strong>مبلغ نهایی</strong><strong id="grand-total">—</strong></div>
      <button type="submit" form="checkout-form" id="pay-button" class="gereh-button mt-4 w-full" <?= !$checkoutEnabled?'disabled':'' ?>>تأیید و انتقال به درگاه</button>
      <p class="gereh-muted mt-4 text-xs">موجودی و مبلغ نهایی در سرور مجدداً بررسی می‌شود.</p>
    </aside>
  </div>
</main>
<script>
(()=>{
  const enabled=<?= $checkoutEnabled ? 'true' : 'false' ?>;
  const form=document.getElementById('checkout-form'),button=document.getElementById('pay-button'),message=document.getElementById('checkout-message');
  const keyName='gereh_checkout_idempotency';
  const fmt=n=>new Intl.NumberFormat('fa-IR').format(Number(n)||0)+' تومان';
  let availableMethods=<?= json_encode($shippingMethods,JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
  let quoteTimer=null;
  const priceEl=document.getElementById('shipping-price'),totalEl=document.getElementById('grand-total'),optionsEl=document.getElementById('shipping-options');
  const shippingMessage=document.getElementById('shipping-message');
  const renderQuotes=()=>{
    const selected=form.querySelector('input[name="shipping_method"]:checked')?.value;
    optionsEl.replaceChildren();
    for(const method of availableMethods){
      const label=document.createElement('label');label.className='flex cursor-pointer items-start gap-3 rounded-xl border border-stone-200 p-4 text-sm';
      const input=document.createElement('input');input.type='radio';input.name='shipping_method';input.value=method.code;input.required=true;input.className='mt-1';input.checked=selected===method.code || (!selected&&method===availableMethods[0]);
      const body=document.createElement('span');body.className='flex-1';
      const title=document.createElement('strong');title.textContent=method.title;
      const detail=document.createElement('small');detail.className='gereh-muted mt-1 block';detail.textContent=method.description||'';
      const eta=document.createElement('small');eta.className='gereh-muted mt-1 block';
      if(method.estimated_days_min!==null)eta.textContent='زمان تقریبی '+method.estimated_days_min+' تا '+(method.estimated_days_max??method.estimated_days_min)+' روز کاری';
      const price=document.createElement('strong');price.textContent=method.shipping_toman===0?'رایگان':fmt(method.shipping_toman);
      body.append(title,detail,eta);label.append(input,body,price);optionsEl.append(label);
    }
    if(!optionsEl.querySelector('input:checked'))optionsEl.querySelector('input')?.click();
    updateTotal();
  };
  const updateTotal=()=>{
    const selected=form.querySelector('input[name="shipping_method"]:checked')?.value;
    const quote=availableMethods.find(x=>x.code===selected);
    priceEl.textContent=quote?(quote.shipping_toman===0?'رایگان':fmt(quote.shipping_toman)):'انتخاب روش ارسال';
    totalEl.textContent=quote?fmt(quote.total_toman):'—';
    button.disabled=!enabled||!quote;
  };
  const updateQuotes=async()=>{
    shippingMessage.textContent='در حال محاسبه هزینه ارسال…';
    button.disabled=true;
    try{
      const province=String(form.elements.namedItem('province')?.value||'').trim();
      const response=await fetch('<?= site_url('api/v1/shipping/quotes') ?>?province='+encodeURIComponent(province),{credentials:'same-origin',cache:'no-store'});
      const result=await response.json();
      if(!response.ok||!result?.success)throw Error(result?.message||'محاسبه ارسال ناموفق بود');
      availableMethods=result.data.methods||[];
      renderQuotes();shippingMessage.textContent=availableMethods.length?'مبلغ‌ها با قیمت فعلی سبد محاسبه شده‌اند.':'هیچ روش ارسال فعالی برای این استان وجود ندارد.';
    }catch(error){availableMethods=[];renderQuotes();shippingMessage.textContent=error.message;}
  };
  form.addEventListener('change',event=>{if(event.target.name==='shipping_method')updateTotal();if(event.target.name==='province')updateQuotes();});
  form.elements.namedItem('province').addEventListener('input',()=>{clearTimeout(quoteTimer);quoteTimer=setTimeout(updateQuotes,350)});
  renderQuotes();
  form.addEventListener('submit',async event=>{
    event.preventDefault();
    if(!enabled)return;
    if(!form.reportValidity())return;
    button.disabled=true;message.textContent='در حال آماده‌سازی سفارش...';
    try{
      const csrfResponse=await fetch('<?= site_url('api/v1/auth/csrf') ?>',{credentials:'same-origin',cache:'no-store'});
      if(!csrfResponse.ok)throw Error('دریافت اطلاعات امنیتی ممکن نیست.');
      const csrf=await csrfResponse.json();
      let key=sessionStorage.getItem(keyName);
      if(!key){key=crypto.randomUUID().replace(/-/g,'');sessionStorage.setItem(keyName,key);}
      const fields=new FormData(form);
      const recipient={};
      for(const field of ['receiver_name','receiver_mobile','province','city','address','postal_code']){
        recipient[field]=String(fields.get(field)||'').trim();
      }
      const chosen=form.querySelector('input[name="shipping_method"]:checked')?.value;
      if(!chosen)throw Error('روش ارسال را انتخاب کنید.');
      const response=await fetch('<?= site_url('api/v1/checkout') ?>',{
        method:'POST',credentials:'same-origin',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf.data.token,'Idempotency-Key':key},
        body:JSON.stringify({recipient,shipping_method:chosen})
      });
      const data=await response.json().catch(()=>null);
      if(!response.ok || !data?.success)throw Error(data?.message||'ثبت سفارش انجام نشد.');
      const redirect=data?.data?.payment?.redirect_url;
      const url=new URL(redirect);
      if(!['https://www.zarinpal.com','https://sandbox.zarinpal.com'].includes(url.origin))throw Error('نشانی درگاه معتبر نیست.');
      sessionStorage.removeItem(keyName);
      location.assign(url.href);
    }catch(error){message.textContent=error.message;button.disabled=false;}
  });
})();
</script>
<?= view('store/partials/footer') ?>
