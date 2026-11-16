<?= view('store/partials/header', ['title' => 'حساب کاربری | گره', 'description' => 'پروفایل و پیگیری سفارش‌های فروشگاه گره']) ?>
<main class="account-screen gereh-container mx-auto min-h-[65vh] max-w-4xl px-4 py-8">
  <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <h1 class="gereh-heading text-2xl font-bold">حساب کاربری</h1>
    <button type="button" class="gereh-muted text-sm" id="logout">خروج از حساب</button>
  </div>
  <p class="gereh-muted">خریدها و اطلاعاتت را از اینجا مدیریت کن</p>
  <section class="account-welcome"><h2><?= esc(trim(($customer['first_name'] ?? '').' '.($customer['last_name'] ?? '')) ?: 'کاربر گره') ?>، خوش اومدی</h2><p>حساب شما با شماره موبایل تأیید شده است.</p><a href="#profile" class="mt-3 inline-block text-xs font-bold">ویرایش اطلاعات حساب ‹</a></section>
  <nav class="account-links" aria-label="مدیریت حساب"><a href="#orders">سفارش‌های من</a><a href="<?= site_url('favorites') ?>">علاقه‌مندی‌ها</a><a href="#profile">اطلاعات حساب</a><a href="<?= site_url('blog') ?>">راهنمای خرید</a></nav>
  <section id="profile" class="gereh-card mb-6 rounded-2xl p-5" hidden>
    <h2 class="mb-4 font-bold">اطلاعات شخصی</h2>
    <p class="gereh-muted mb-4" dir="ltr"><?= esc($customer['mobile']) ?></p>
    <form id="profile-form" class="grid gap-4 sm:grid-cols-2">
      <label class="block text-sm">نام<input id="first-name" class="gereh-input mt-2 w-full" name="first_name" maxlength="100" value="<?= esc($customer['first_name'] ?? '', 'attr') ?>"></label>
      <label class="block text-sm">نام خانوادگی<input id="last-name" class="gereh-input mt-2 w-full" name="last_name" maxlength="100" value="<?= esc($customer['last_name'] ?? '', 'attr') ?>"></label>
      <div class="sm:col-span-2"><button type="submit" class="gereh-button" id="save-profile">ذخیره تغییرات</button></div>
    </form>
    <p id="profile-message" role="status" aria-live="polite" class="mt-3 text-sm"></p>
  </section>
  <section id="orders" class="gereh-card rounded-2xl p-5" hidden>
    <h2 class="mb-5 font-bold">سفارش‌های من</h2>
    <?php if (empty($orders)): ?>
      <p class="gereh-muted mb-4">هنوز سفارشی ثبت نکرده‌اید.</p><a class="gereh-button" href="<?= site_url('shop') ?>">مشاهده فروشگاه</a>
    <?php else: ?>
      <div class="space-y-3">
        <?php foreach ($orders as $order): ?>
        <article class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-stone-200 p-4">
          <div><strong><?= esc($order['order_number']) ?></strong><p class="gereh-muted mt-1"><?= esc($order['created_at']) ?> • <?= esc($order['order_status']) ?></p></div>
          <div class="text-left"><strong><?= number_format((int)$order['total_toman']) ?> تومان</strong><p class="gereh-muted mt-1"><?= esc($order['payment_status']) ?></p></div>
        </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>
<script>
(()=>{
  function showAccountSection(){
    for(const id of ['profile','orders'])document.getElementById(id).hidden=location.hash!=='#'+id;
    const active=document.getElementById(location.hash.slice(1));
    if(active && ['profile','orders'].includes(active.id))active.scrollIntoView({block:'start'});
  }
  window.addEventListener('hashchange',showAccountSection);showAccountSection();
  async function send(method,path,data){
    const token=await fetch('<?= site_url('api/v1/auth/csrf') ?>',{credentials:'same-origin',cache:'no-store'}).then(r=>r.json());
    const response=await fetch(path,{method,credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token.data?.token||''},body:JSON.stringify(data)});
    const result=await response.json().catch(()=>null);
    if(!response.ok||!result?.success)throw Error(result?.message||'درخواست انجام نشد.');
    return result;
  }
  document.getElementById('profile-form').addEventListener('submit',async event=>{
    event.preventDefault();const button=document.getElementById('save-profile'),message=document.getElementById('profile-message');button.disabled=true;
    try{await send('PATCH','<?= site_url('api/v1/me') ?>',{first_name:document.getElementById('first-name').value,last_name:document.getElementById('last-name').value});message.textContent='اطلاعات ذخیره شد.';}
    catch(error){message.textContent=error.message;}finally{button.disabled=false;}
  });
  document.getElementById('logout').addEventListener('click',async()=>{
    const button=document.getElementById('logout');button.disabled=true;
    try{await send('POST','<?= site_url('api/v1/auth/logout') ?>',{});location.assign('<?= site_url('/') ?>')}
    catch(error){alert(error.message);button.disabled=false;}
  });
})();
</script>
<?= view('store/partials/footer', ['hideFooter'=>true]) ?>
