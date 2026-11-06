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
          <label class="block rounded-xl border border-stone-200 p-3 text-sm"><input type="radio" name="shipping_method" value="standard" checked> ارسال استاندارد</label>
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
      <div class="mt-3 flex justify-between text-sm"><span>ارسال</span><strong><?= $shippingToman === null ? 'هنوز تنظیم نشده' : number_format($shippingToman).' تومان' ?></strong></div>
      <?php if ($shippingToman !== null): ?>
      <div class="my-4 flex justify-between border-t border-stone-200 pt-4"><strong>مبلغ نهایی</strong><strong><?= number_format((int)$cart['total'] + $shippingToman) ?> تومان</strong></div>
      <?php endif; ?>
      <button type="submit" form="checkout-form" id="pay-button" class="gereh-button mt-4 w-full" <?= (!$checkoutEnabled || $shippingToman===null) ? 'disabled' : '' ?>>تأیید و انتقال به درگاه</button>
      <p class="gereh-muted mt-4 text-xs">موجودی و مبلغ نهایی در سرور مجدداً بررسی می‌شود.</p>
    </aside>
  </div>
</main>
<script>
(()=>{
  const enabled=<?= $checkoutEnabled ? 'true' : 'false' ?>;
  const form=document.getElementById('checkout-form'),button=document.getElementById('pay-button'),message=document.getElementById('checkout-message');
  const keyName='gereh_checkout_idempotency';
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
      const response=await fetch('<?= site_url('api/v1/checkout') ?>',{
        method:'POST',credentials:'same-origin',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf.data.token,'Idempotency-Key':key},
        body:JSON.stringify({recipient,shipping_method:'standard'})
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
