<div id="gereh-cart-overlay" class="fixed inset-0 z-40 bg-black/40" hidden></div>
<aside id="gereh-cart-drawer" class="fixed inset-y-0 right-0 z-50 flex w-[min(360px,92vw)] flex-col bg-[#f9f4ef] p-5 shadow-2xl" role="dialog" aria-modal="true" aria-label="سبد خرید" tabindex="-1" hidden>
<div class="flex items-center justify-between border-b border-stone-200 pb-4">
<h2 class="text-xl font-bold">سبد خرید من</h2><button type="button" id="gereh-cart-close" class="rounded-lg p-2 text-2xl" aria-label="بستن سبد خرید">×</button>
</div>
<p id="gereh-cart-message" role="status" aria-live="polite" class="mt-3 text-sm"></p>
<div id="gereh-cart-items" class="min-h-0 flex-1 space-y-3 overflow-y-auto py-5"><p class="text-sm text-stone-500">در حال دریافت سبد…</p></div>
<div class="border-t border-stone-200 pt-4">
<div class="mb-4 flex justify-between"><span>جمع محصولات</span><strong id="gereh-cart-total">—</strong></div>
<a href="<?= site_url('checkout') ?>" class="gereh-button mb-2 flex w-full">ادامه فرایند خرید</a>
<a href="<?= site_url('cart') ?>" class="block rounded-xl border border-stone-200 p-3 text-center text-sm">ویرایش سبد خرید</a>
</div>
</aside>
<script>
(()=>{
const overlay=document.getElementById('gereh-cart-overlay'),panel=document.getElementById('gereh-cart-drawer'),closer=document.getElementById('gereh-cart-close'),items=document.getElementById('gereh-cart-items'),message=document.getElementById('gereh-cart-message'),total=document.getElementById('gereh-cart-total');
const money=value=>new Intl.NumberFormat('fa-IR').format(Number(value)||0)+' تومان';
let previousFocus=null;
const close=()=>{panel.hidden=true;overlay.hidden=true;document.body.style.overflow='';if(previousFocus)previousFocus.focus()};
function render(summary){
  items.replaceChildren();message.textContent='';
  if(!summary?.lines?.length){const p=document.createElement('p');p.textContent='سبد خرید شما خالی است.';p.className='text-sm text-stone-500';items.append(p)}
  else for(const line of summary.lines){
    const article=document.createElement('article');article.className='rounded-xl bg-white p-4';
    const title=document.createElement('a');title.href='<?= site_url('product') ?>/'+encodeURIComponent(line.product.slug);title.className='font-bold';title.textContent=line.product.name;
    const meta=document.createElement('p');meta.className='mt-2 text-xs text-stone-500';meta.textContent=[line.variant.size_label,line.variant.color_name].filter(Boolean).join(' / ');
    const price=document.createElement('p');price.className='mt-3 text-sm';price.textContent='تعداد: '+line.quantity+' • '+money(line.subtotal);
    article.append(title,meta,price);items.append(article);
  }
  total.textContent=money(summary?.total||0);
}
async function open(summary){
  previousFocus=document.activeElement;panel.hidden=false;overlay.hidden=false;document.body.style.overflow='hidden';panel.focus();
  if(summary){render(summary);return}
  message.textContent='در حال دریافت سبد خرید…';
  try{const response=await fetch('<?= site_url('api/v1/cart') ?>',{credentials:'same-origin',cache:'no-store'});const data=await response.json();if(!response.ok||!data.success)throw Error(data.message||'خطا در دریافت سبد خرید');render(data.data)}
  catch(error){message.textContent=error.message}
}
document.getElementById('cart-drawer-trigger')?.addEventListener('click',()=>open());
document.querySelectorAll('[data-open-cart]').forEach(el=>el.addEventListener('click',e=>{e.preventDefault();open()}));
closer.addEventListener('click',close);overlay.addEventListener('click',close);
document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!panel.hidden)close()});
document.addEventListener('submit',async e=>{
  const form=e.target;
  if(!(form instanceof HTMLFormElement)||!form.matches('.sticky-buy')||!form.action.includes('/cart/add'))return;
  e.preventDefault();
  const fields=new FormData(form),variant=Number(fields.get('variant_id')),quantity=Number(fields.get('quantity')||1);
  if(!Number.isInteger(variant)||variant<1)return;
  const button=form.querySelector('[type=submit]');if(button)button.disabled=true;
  try{
    const csrfResponse=await fetch('<?= site_url('api/v1/auth/csrf') ?>',{credentials:'same-origin',cache:'no-store'});
    if(!csrfResponse.ok)throw Error('دریافت توکن امنیتی ناموفق بود.');
    const token=await csrfResponse.json();
    const response=await fetch('<?= site_url('api/v1/cart/items') ?>',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token.data.token},body:JSON.stringify({variant_id:variant,quantity})});
    const result=await response.json().catch(()=>null);
    if(!response.ok||!result?.success)throw Error(result?.message||'افزودن به سبد خرید انجام نشد.');
    open(result.data);
  }catch(error){open();message.textContent=error.message}
  finally{if(button)button.disabled=false}
});
window.GerehCart={open,close};
})();
</script>