<?= view('store/partials/header', ['title' => 'تکمیل خرید | گره', 'description' => 'ثبت اطلاعات گیرنده و تکمیل سفارش']) ?>
<?php $iranLocations = \App\Services\IranLocations::all(); ?>
<?php
$torobConfigured = $checkoutEnabled && \App\Services\Payments\TorobPayClient::enabled();
$bankLabel = env('GEREH_PAYMENT_PROVIDER', 'zarinpal') === 'zibal' ? 'زیبال' : 'زرین‌پال';
?>
<style>
.checkout-methods { display: grid; gap: 8px; }
.checkout-method {
  display: flex; align-items: center; gap: 9px; padding: 9px 11px;
  border: 1px solid #d8cec0; border-radius: 12px; cursor: pointer;
  font-size: .8125rem; line-height: 1.45;
}
.checkout-method[hidden] { display: none; }
.checkout-method:has(input:checked) { border-color: #21634f; background: #21634f0b; }
.checkout-method:has(input:focus-visible) { outline: 2px solid #21634f; outline-offset: 2px; }
.checkout-method:has(input:disabled) { cursor: default; opacity: .65; }
.checkout-method input { flex: none; accent-color: #21634f; }
.checkout-method-copy { min-width: 0; flex: 1; }
.checkout-method-copy small { display: block; margin-block-start: 2px; }
.checkout-method-price { flex: none; white-space: nowrap; font-size: .75rem; }
.checkout-method-icon {
  display: grid; flex: none; place-items: center; width: 34px; height: 34px;
  border-radius: 8px; background: #e5eee8; color: #123b2c;
  font-size: .625rem; font-weight: 700;
}
.checkout-method-icon-credit { background: #9be761; color: #163d19; }
</style>
<main class="checkout-screen gereh-container mx-auto min-h-[70vh] max-w-5xl px-4 py-8">
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
        <label class="block text-sm">استان<select class="gereh-input mt-2 w-full" name="province" required autocomplete="address-level1"><option value="">استان را انتخاب کنید</option><?php foreach ($iranLocations as $provinceName=>$cities): ?><option value="<?= esc($provinceName, 'attr') ?>"><?= esc($provinceName) ?></option><?php endforeach; ?></select></label>
        <label class="block text-sm">شهر<select class="gereh-input mt-2 w-full" name="city" required disabled autocomplete="address-level2"><option value="">ابتدا استان را انتخاب کنید</option></select></label>
        <label class="block text-sm sm:col-span-2">آدرس دقیق<textarea class="gereh-input mt-2 w-full" name="address" rows="3" maxlength="2000" required autocomplete="street-address"></textarea></label>
        <label class="block text-sm">کد پستی<input class="gereh-input mt-2 w-full" name="postal_code" inputmode="numeric" maxlength="10" pattern="[0-9]{10}" dir="ltr" required autocomplete="postal-code"></label>
        <div class="sm:col-span-2">
          <h3 class="mb-2 text-sm font-bold">روش ارسال</h3>
          <div id="shipping-options" role="radiogroup" aria-label="روش ارسال" class="checkout-methods">
          <?php foreach ($shippingMethods as $i=>$method): ?>
          <label class="checkout-method">
            <input type="radio" name="shipping_method" value="<?= esc($method['code'],'attr') ?>" <?= $i===0?'checked':'' ?> required>
            <span class="checkout-method-copy"><strong><?= esc($method['title']) ?></strong>
            <small class="gereh-muted"><?= esc($method['description']??'') ?></small>
            <?php if($method['estimated_days_min']!==null): ?>
            <small class="gereh-muted">زمان تقریبی <?= (int)$method['estimated_days_min'] ?> تا <?= (int)($method['estimated_days_max']??$method['estimated_days_min']) ?> روز کاری</small>
            <?php endif; ?></span>
            <strong class="checkout-method-price"><?= (int)$method['shipping_toman']===0?'رایگان':number_format((int)$method['shipping_toman']).' تومان' ?></strong>
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
      <fieldset class="checkout-methods">
        <legend class="mb-2 text-sm font-bold">روش پرداخت</legend>
        <label class="checkout-method">
          <input type="radio" name="payment_provider" form="checkout-form" value="bank" checked <?= !$checkoutEnabled ? 'disabled' : '' ?>>
          <span class="checkout-method-icon" aria-hidden="true"><?= esc($bankLabel) ?></span>
          <span class="checkout-method-copy"><strong>پرداخت آنلاین با <?= esc($bankLabel) ?></strong><small class="gereh-muted">پرداخت کامل مبلغ سفارش</small></span>
        </label>
        <?php if ($torobConfigured): ?>
          <label id="torobpay-card" class="checkout-method" hidden>
            <input id="torobpay-method" type="radio" name="payment_provider" form="checkout-form" value="torobpay" disabled>
            <span class="checkout-method-icon checkout-method-icon-credit" aria-hidden="true">ترب‌پی</span>
            <span class="checkout-method-copy"><strong>پرداخت اقساطی با ترب‌پی</strong><small class="gereh-muted" id="torobpay-description">خرید اعتباری</small></span>
          </label>
          <p id="torobpay-status" class="gereh-muted text-xs" role="status">برای بررسی خرید اقساطی، استان را انتخاب کنید.</p>
        <?php endif; ?>
      </fieldset>
      <button type="submit" form="checkout-form" id="pay-button" class="gereh-button mt-4 w-full" <?= !$checkoutEnabled?'disabled':'' ?>>تأیید و انتقال به درگاه</button>
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
  const iranLocations=<?= json_encode($iranLocations,JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
  const provinceSelect=form.elements.namedItem('province'),citySelect=form.elements.namedItem('city');
  const updateCities=()=>{
    const previous=citySelect.value,cities=iranLocations[provinceSelect.value]||[];
    citySelect.replaceChildren(new Option(cities.length?'شهر را انتخاب کنید':'ابتدا استان را انتخاب کنید',''));
    for(const city of cities)citySelect.add(new Option(city,city));
    citySelect.disabled=cities.length===0;
    if(cities.includes(previous))citySelect.value=previous;
  };
  updateCities();
  let quoteRequest=0;
  const priceEl=document.getElementById('shipping-price'),totalEl=document.getElementById('grand-total'),optionsEl=document.getElementById('shipping-options');
  const shippingMessage=document.getElementById('shipping-message');
  const renderQuotes=()=>{
    const selected=form.querySelector('input[name="shipping_method"]:checked')?.value;
    optionsEl.replaceChildren();
    for(const method of availableMethods){
      const label=document.createElement('label');label.className='checkout-method';
      const input=document.createElement('input');input.type='radio';input.name='shipping_method';input.value=method.code;input.required=true;input.checked=selected===method.code || (!selected&&method===availableMethods[0]);
      const body=document.createElement('span');body.className='checkout-method-copy';
      const title=document.createElement('strong');title.textContent=method.title;
      const detail=document.createElement('small');detail.className='gereh-muted';detail.textContent=method.description||'';
      const eta=document.createElement('small');eta.className='gereh-muted';
      if(method.estimated_days_min!==null)eta.textContent='زمان تقریبی '+method.estimated_days_min+' تا '+(method.estimated_days_max??method.estimated_days_min)+' روز کاری';
      const price=document.createElement('strong');price.className='checkout-method-price';price.textContent=method.shipping_toman===0?'رایگان':fmt(method.shipping_toman);
      body.append(title);
      if(detail.textContent)body.append(detail);
      if(eta.textContent)body.append(eta);
     label.append(input,body,price);optionsEl.append(label);
    }
    if(!optionsEl.querySelector('input:checked')&&optionsEl.firstElementChild)optionsEl.querySelector('input').checked=true;
    updateTotal();
  };
  const creditCard=document.getElementById('torobpay-card');
  const creditInput=document.getElementById('torobpay-method');
  const creditStatus=document.getElementById('torobpay-status');
  const creditDescription=document.getElementById('torobpay-description');
  const bankInput=document.querySelector('input[name="payment_provider"][value="bank"]');
  let eligibilityRequest=0;
  const updateEligibility=async quote=>{
    if(!creditCard)return;
    const currentRequest=++eligibilityRequest;
    creditInput.disabled=true;
    creditCard.hidden=true;
    bankInput.checked=true;
    creditStatus.hidden=false;
    if(!quote || !provinceSelect.value){
      creditStatus.textContent=provinceSelect.value?'برای بررسی خرید اقساطی، روش ارسال را انتخاب کنید.':'برای بررسی خرید اقساطی، استان را انتخاب کنید.';
      return;
    }
    creditStatus.textContent='در حال بررسی امکان خرید اقساطی…';
    const query=new URLSearchParams({method:quote.code,province:provinceSelect.value});
    try{
      const response=await fetch('<?= site_url('api/v1/checkout/torobpay-eligibility') ?>?'+query,{credentials:'same-origin',cache:'no-store'});
      const result=await response.json();
      if(currentRequest!==eligibilityRequest)return;
      if(!response.ok || result.unavailable)throw Error();
      if(result.eligible!==true){
        creditStatus.textContent='خرید اقساطی برای مبلغ این سفارش در دسترس نیست.';
        return;
      }
      creditDescription.textContent=result.description||'خرید اعتباری';
      creditInput.disabled=false;
      creditCard.hidden=false;
      creditStatus.hidden=true;
    }catch{
      if(currentRequest===eligibilityRequest)creditStatus.textContent='بررسی خرید اقساطی موقتاً در دسترس نیست.';
    }
  };
  const updateTotal=()=>{
    const selected=form.querySelector('input[name="shipping_method"]:checked')?.value;
    const quote=availableMethods.find(x=>x.code===selected);
    priceEl.textContent=quote?(quote.shipping_toman===0?'رایگان':fmt(quote.shipping_toman)):'انتخاب روش ارسال';
    totalEl.textContent=quote?fmt(quote.total_toman):'—';
    button.disabled=!enabled||!quote;
    updateEligibility(quote);
  };
  const updateQuotes=async()=>{
    const currentRequest=++quoteRequest;
    shippingMessage.textContent='در حال محاسبه هزینه ارسال…';
    button.disabled=true;
    updateEligibility(null);
    try{
      const province=String(form.elements.namedItem('province')?.value||'').trim();
      const response=await fetch('<?= site_url('api/v1/shipping/quotes') ?>?province='+encodeURIComponent(province),{credentials:'same-origin',cache:'no-store'});
      const result=await response.json();
      if(currentRequest!==quoteRequest)return;
      if(!response.ok||!result?.success)throw Error(result?.message||'محاسبه ارسال ناموفق بود');
      availableMethods=result.data.methods||[];
      renderQuotes();shippingMessage.textContent=availableMethods.length?'':'هیچ روش ارسال فعالی برای این استان وجود ندارد.';
    }catch(error){if(currentRequest!==quoteRequest)return;availableMethods=[];renderQuotes();shippingMessage.textContent=error.message;}
  };
  form.addEventListener('change',event=>{if(event.target.name==='shipping_method')updateTotal();if(event.target.name==='province'){citySelect.value='';updateCities();updateQuotes();}});
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
        body:JSON.stringify({recipient,shipping_method:chosen,payment_provider:fields.get('payment_provider')||'bank'})
      });
      const data=await response.json().catch(()=>null);
      if(!response.ok || !data?.success)throw Error(data?.message||'ثبت سفارش انجام نشد.');
      const redirect=data?.data?.payment?.redirect_url;
      const url=new URL(redirect);
      if(!['https://www.zarinpal.com','https://sandbox.zarinpal.com','https://gateway.zibal.ir','https://cpg.torobpay.com'].includes(url.origin))throw Error('نشانی درگاه معتبر نیست.');
      if(url.origin!=='https://cpg.torobpay.com')sessionStorage.removeItem(keyName);
      location.assign(url.href);
    }catch(error){message.textContent=error.message;button.disabled=false;}
  });
})();
</script>
<?= view('store/partials/footer', ['hideBottomNav'=>true, 'hideFooter'=>true]) ?>
