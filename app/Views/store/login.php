<?= view('store/partials/header', ['title' => 'ورود به حساب | گره', 'description' => 'ورود و ثبت‌نام با شماره موبایل']) ?>
<main class="gereh-container mx-auto min-h-[65vh] max-w-xl px-4 py-8" id="login-main">
  <div class="gereh-card rounded-2xl p-6 shadow-sm sm:p-8">
    <a href="<?= site_url('/') ?>" class="gereh-muted text-sm">بازگشت به فروشگاه</a>
    <h1 class="gereh-heading mt-5 text-2xl font-bold">ورود یا ثبت‌نام</h1>
    <p class="gereh-muted my-4 text-sm">برای مشاهده سفارش‌ها و پیگیری خرید، شماره موبایل خود را وارد کنید.</p>
    <form id="mobile-form" class="space-y-4" novalidate>
      <label class="block text-sm font-semibold" for="mobile">شماره موبایل</label>
      <input class="gereh-input w-full" id="mobile" type="tel" name="mobile" inputmode="numeric" dir="ltr" autocomplete="tel-national" placeholder="09123456789" maxlength="11" required pattern="09[0-9]{9}">
      <button class="gereh-button w-full" type="submit" id="send-otp">دریافت کد ورود</button>
    </form>
    <form id="verify-form" class="mt-5 hidden space-y-4" novalidate>
      <p class="text-sm">کد شش‌رقمی ارسال‌شده را وارد کنید.</p>
      <label class="block text-sm font-semibold" for="otp">کد تأیید</label>
      <input class="gereh-input w-full text-center text-xl tracking-[.6em]" id="otp" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" dir="ltr" required>
      <button class="gereh-button w-full" id="verify-otp" type="submit">ورود به حساب</button>
      <button type="button" class="gereh-muted w-full text-sm" id="change-mobile">اصلاح شماره موبایل</button>
    </form>
    <p id="login-message" role="status" aria-live="polite" class="mt-5 text-sm"></p>
  </div>
</main>
<script>
(() => {
  const mobileForm=document.getElementById('mobile-form');
  const verifyForm=document.getElementById('verify-form');
  const mobile=document.getElementById('mobile');
  const otp=document.getElementById('otp');
  const message=document.getElementById('login-message');
  const next=new URLSearchParams(location.search).get('return');
  const destination=next==='checkout'?'<?= site_url('checkout') ?>':'<?= site_url('account') ?>';
  let csrf=null;
  const show=(text,error=false)=>{message.textContent=text;message.style.color=error?'#b91c1c':'#075d3c'};
  const fetchCsrf=async()=>{
    const response=await fetch('<?= site_url('api/v1/auth/csrf') ?>',{credentials:'same-origin',cache:'no-store'});
    if(!response.ok)throw Error('دریافت توکن امنیتی ممکن نیست.');
    const result=await response.json();
    csrf=result.data?.token;
    if(!csrf)throw Error('توکن امنیتی معتبر نیست.');
  };
  const submit=async(path,data)=>{
    await fetchCsrf();
    const response=await fetch('<?= site_url('api/v1/auth') ?>/'+path,{
      method:'POST',credentials:'same-origin',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
      body:JSON.stringify(data)
    });
    const result=await response.json().catch(()=>null);
    if(!response.ok||!result?.success)throw Error(result?.message||'درخواست انجام نشد.');
    return result;
  };
  mobileForm.addEventListener('submit',async event=>{
    event.preventDefault();const button=document.getElementById('send-otp');
    if(!/^09[0-9]{9}$/.test(mobile.value.trim()))return show('شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.',true);
    button.disabled=true;show('در حال درخواست کد...');
    try{await submit('otp/request',{mobile:mobile.value.trim()});mobileForm.classList.add('hidden');verifyForm.classList.remove('hidden');otp.focus();show('در صورت ارسال موفق، کد به موبایل شما پیامک می‌شود.');}
    catch(error){show(error.message,true)}finally{button.disabled=false}
  });
  verifyForm.addEventListener('submit',async event=>{
    event.preventDefault();const button=document.getElementById('verify-otp');
    if(!/^[0-9]{6}$/.test(otp.value.trim()))return show('کد ورود باید ۶ رقم باشد.',true);
    button.disabled=true;show('در حال بررسی کد...');
    try{await submit('otp/verify',{mobile:mobile.value.trim(),code:otp.value.trim()});window.location.assign(destination);}
    catch(error){show(error.message,true)}finally{button.disabled=false}
  });
  document.getElementById('change-mobile').addEventListener('click',()=>{verifyForm.classList.add('hidden');mobileForm.classList.remove('hidden');otp.value='';show('')});
})();
</script>
<?= view('store/partials/footer') ?>
