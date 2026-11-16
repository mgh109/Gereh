<style>
.gereh-page-loading{position:fixed;inset:0;z-index:1000;pointer-events:none;font-family:Estedad,Tahoma,sans-serif;direction:rtl}
.gereh-page-loading[hidden]{display:none!important}
.gereh-loading-track{position:absolute;inset:0 0 auto;height:3px;background:#012f2412;overflow:hidden}
.gereh-loading-track::after{content:"";display:block;width:35%;height:100%;background:#075d3c;border-radius:8px;animation:gereh-loading-bar 1.2s ease-in-out infinite}
.gereh-loading-status{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);display:flex;align-items:center;gap:12px;padding:18px 24px;border:1px solid #e4d8ca;border-radius:16px;background:#fffaf5f5;box-shadow:0 8px 36px #012f2418;color:#012f24;font-size:13px;white-space:nowrap}
.gereh-loading-spinner{width:24px;height:24px;box-sizing:border-box;border:3px solid #e4d8ca;border-top-color:#075d3c;border-radius:50%;animation:gereh-loading-spin .8s linear infinite;flex:none}
@keyframes gereh-loading-spin{to{transform:rotate(360deg)}}
@keyframes gereh-loading-bar{from{transform:translateX(-110%)}to{transform:translateX(300%)}}
@media(prefers-reduced-motion:reduce){.gereh-loading-spinner,.gereh-loading-track::after{animation:none}.gereh-loading-track::after{width:100%}}
</style>
<div class="gereh-page-loading" id="gereh-page-loading" hidden><div class="gereh-loading-track" aria-hidden="true"></div><div class="gereh-loading-status" role="status" aria-live="polite"><span class="gereh-loading-spinner" aria-hidden="true"></span><span id="gereh-loading-label"></span></div></div>
<script>
(()=>{
 const loader=document.getElementById('gereh-page-loading');
 if(!loader||loader.dataset.ready)return;loader.dataset.ready='1';
 const label=document.getElementById('gereh-loading-label');
 const verifiedForms=new WeakSet(),preparingForms=new WeakSet();
 const tokenName=<?= json_encode(csrf_token(),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,tokenUrl=<?= json_encode(site_url('session/csrf'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
 let pending=false,showTimer,resetTimer,submittedForm=null,submitter=null,originalDisabled=false,previousBusy=null;
 function reset(){
  clearTimeout(showTimer);clearTimeout(resetTimer);loader.hidden=true;label.textContent='';pending=false;
  if(previousBusy===null)document.documentElement.removeAttribute('aria-busy');else document.documentElement.setAttribute('aria-busy',previousBusy);
  if(submitter){submitter.disabled=originalDisabled;submitter=null}if(submittedForm){preparingForms.delete(submittedForm);verifiedForms.delete(submittedForm)}submittedForm=null;
 }
 function start(text,form=null,button=null){
  if(pending)return;pending=true;submittedForm=form;
  previousBusy=document.documentElement.getAttribute('aria-busy');document.documentElement.setAttribute('aria-busy','true');
  showTimer=setTimeout(()=>{label.textContent=text;loader.hidden=false},220);
  // Delay disabling until the browser has serialized the submitted button's value.
  if(button)setTimeout(()=>{if(pending&&submittedForm===form){submitter=button;originalDisabled=button.disabled;button.disabled=true}},0);
  // Downloads, aborted navigation and connection failures must not leave a stuck page.
  resetTimer=setTimeout(reset,15000);
 }
 document.addEventListener('click',event=>{
  if(event.defaultPrevented||event.button!==0||event.ctrlKey||event.metaKey||event.shiftKey||event.altKey)return;
  const link=event.target.closest?.('a[href]');
  if(!link||link.hasAttribute('download')||link.hasAttribute('data-no-loading')||(link.target&&link.target!=='_self'))return;
  const href=link.getAttribute('href');if(!href||href.startsWith('#'))return;
  let url;try{url=new URL(link.href,location.href)}catch{return}
  if(!['http:','https:'].includes(url.protocol)||url.origin!==location.origin)return;
  if(url.hash&&url.pathname===location.pathname&&url.search===location.search)return;
  queueMicrotask(()=>{if(!event.defaultPrevented)start('در حال بارگذاری…')});
 });
 window.addEventListener('submit',event=>{
  const form=event.target;if(event.defaultPrevented||form.hasAttribute('data-no-loading')||form.method==='dialog'||(form.target&&form.target!=='_self'))return;
  if(verifiedForms.has(form)){verifiedForms.delete(form);return}
  if(preparingForms.has(form)||(pending&&submittedForm===form)){event.preventDefault();return}
  const csrfInput=Array.from(form.querySelectorAll('input[type="hidden"]')).find(input=>input.name===tokenName);
  if(form.method.toLowerCase()!=='post'||!csrfInput){queueMicrotask(()=>{if(!event.defaultPrevented)start('در حال بارگذاری…',form,event.submitter)});return}
  const submitButton=event.submitter;
  event.preventDefault();preparingForms.add(form);start('در حال ارسال اطلاعات…',form);
  fetch(tokenUrl,{credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'}})
   .then(response=>{if(!response.ok)throw Error('token');return response.json()})
   .then(token=>{
    if(!pending||submittedForm!==form)return;
    if(token.name!==tokenName||typeof token.hash!=='string'||!token.hash)throw Error('token');
    document.querySelectorAll('input[type="hidden"]').forEach(input=>{if(input.name===tokenName)input.value=token.hash});
    preparingForms.delete(form);verifiedForms.add(form);
    // requestSubmit keeps native validation and the original submit button value.
    form.requestSubmit(submitButton||undefined);
   }).catch(()=>{if(!pending||submittedForm!==form)return;preparingForms.delete(form);verifiedForms.delete(form);reset();label.textContent='ارتباط برقرار نشد؛ دوباره روی دکمه ثبت بزنید.';loader.hidden=false;resetTimer=setTimeout(reset,5000)});
 });
 document.addEventListener('invalid',event=>{if(event.target.form===submittedForm)reset()},true);
 window.addEventListener('pageshow',reset);
 window.addEventListener('pagehide',reset);
})();
</script>
