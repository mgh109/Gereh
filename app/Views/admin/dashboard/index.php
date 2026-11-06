<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>پیشخوان | مدیریت گره</title><link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
<style>
@font-face{font-family:Estedad;src:local("Estedad"),local("Estedad VF")}*{box-sizing:border-box}
:root{--green:#012f24;--green2:#075d3c;--ink:#263b33;--muted:#81766e;--canvas:#f5f5f2;--sand:#efe3d4;--soft:#f9f4ef;--white:#fff}
body{margin:0;background:var(--canvas);color:var(--ink);font-family:Estedad,Tahoma,sans-serif;font-size:12px}
.shell{direction:ltr;display:flex;gap:24px;padding:24px;min-height:100vh;align-items:flex-start}
.main{direction:rtl;flex:1;min-width:0;max-width:1108px;height:calc(100vh - 48px);min-height:900px;overflow:auto}
.sidebar{direction:rtl;flex:0 0 260px;height:calc(100vh - 48px);min-height:900px;background:#fff;border-radius:24px;padding:16px;display:flex;flex-direction:column;align-items:center;gap:18px}
.logo{height:64px;width:94px;object-fit:contain}.side-title{font-size:12px;font-weight:700;color:var(--green);margin-top:-8px}
.rule{height:1px;width:100%;background:var(--sand);flex:none}
.nav{width:100%;height:688px;overflow:auto;background:var(--soft);padding:8px;border-radius:18px;display:flex;flex-direction:column;gap:8px}
.nav a{height:54px;flex:none;display:flex;align-items:center;gap:12px;padding:12px;color:var(--ink);text-decoration:none;border-radius:10px;font-size:12px}
.nav a img{width:20px;height:20px}.nav a span{flex:1}.nav a.active{background:var(--green);color:white;font-weight:700}
.side-foot{height:42px;display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:11px}
.topbar{height:64px;background:#fff;border-radius:16px;padding:12px 16px;display:flex;align-items:center;gap:16px}
.account{width:230px;display:flex;align-items:center;gap:10px;white-space:nowrap}.role{background:#e7f1ec;color:var(--green2);padding:6px 10px;border-radius:8px;font-size:11px}
.account img,.search img,.top-actions img{width:20px;height:20px}.muted{color:var(--muted)}
.search{height:40px;width:390px;background:var(--soft);border-radius:9px;padding:0 12px;display:flex;align-items:center;gap:10px;color:var(--muted)}
.search span{flex:1}.top-actions{flex:1;display:flex;align-items:center;justify-content:flex-start;gap:8px;color:var(--green);font-size:14px;font-weight:700}
.page-head{height:76px;margin-top:24px;display:flex;align-items:center;gap:16px}
.store-link{width:152px;height:44px;flex:none;background:var(--soft);border-radius:10px;display:grid;place-items:center;color:var(--green);text-decoration:none;font-size:12px}
.page-title{flex:1}.page-title h1{font-size:25px;color:var(--green);margin:0;font-weight:700;line-height:1.7}.page-title p{font-size:12px;color:var(--muted);margin:0}
.dashboard{margin-top:24px;display:flex;flex-direction:column;gap:20px}
.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:20px}
.stat{height:144px;background:#fff;border-radius:16px;padding:18px;display:flex;flex-direction:column;gap:4px}
.stat-head{display:flex;align-items:center;gap:10px;color:var(--muted);font-size:12px}.stat-head img{width:20px;height:20px}.stat-value{font-size:25px;font-weight:700;color:var(--green);white-space:nowrap}.stat-note{font-size:10px;color:var(--green2)}
.middle{display:grid;grid-template-columns:332px minmax(0,1fr);gap:20px}
.panel{background:#fff;border-radius:16px;overflow:hidden}
.panel-title{padding:24px 24px 0;font-size:16px;font-weight:700;color:var(--green)}
.attention{height:306px}.attention-list{padding:16px 24px}.attention-row{height:50px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #f1eee9}.attention-row:last-child{border:0}.attention-row a{color:var(--green2);text-decoration:none}
.chart{height:306px;padding:24px}.chart-head{display:flex;justify-content:space-between;align-items:center;font-size:16px;font-weight:700;color:var(--green)}.chart-head small{font-size:11px;font-weight:400;color:var(--muted)}
.bars{height:185px;display:flex;align-items:flex-end;justify-content:space-around;gap:18px;padding:16px 6px 0;border-bottom:1px solid #eee}.bar-item{height:100%;flex:1;display:flex;flex-direction:column;justify-content:flex-end;align-items:center;gap:8px;color:var(--muted);font-size:10px}.bar{width:38px;max-width:70%;background:#dce9e2;border-radius:7px 7px 0 0}.bar.highlight{background:var(--green2)}.chart-total{text-align:right;margin-top:14px;color:var(--green);font-weight:600}
.orders{padding:24px}.orders-head{display:flex;justify-content:space-between;align-items:center;font-size:16px;font-weight:700;color:var(--green);margin-bottom:18px}.orders-head a{font-size:11px;color:var(--green2);text-decoration:none}
table{width:100%;border-collapse:collapse;text-align:right}th{height:48px;background:var(--soft);font-weight:500;color:var(--muted)}td{height:64px;border-bottom:1px solid #f0eee9}th,td{padding:0 14px;white-space:nowrap}.status{display:inline-block;padding:6px 12px;border-radius:20px;background:#e7f1ec;color:var(--green2);font-size:10px}.logout{border:0;background:none;color:var(--muted);font:inherit;cursor:pointer}
@media(max-width:1150px){.shell{gap:16px;padding:16px}.sidebar{flex-basis:230px}.stats{grid-template-columns:repeat(2,minmax(0,1fr))}.middle{grid-template-columns:1fr}.main{height:auto;min-height:0}.sidebar{height:auto;min-height:0}.nav{height:auto}.attention{height:auto}}
@media(max-width:760px){.shell{display:flex;flex-direction:column;padding:12px}.sidebar{width:100%;flex-basis:auto;gap:10px;border-radius:16px}.logo{height:44px}.nav{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));height:auto}.nav a{height:44px}.side-foot,.rule{display:none}.main{width:100%;height:auto}.topbar{height:auto;flex-wrap:wrap;padding:12px}.account{width:100%}.search{width:100%}.top-actions{display:none}.page-head{height:auto;align-items:flex-start}.page-title h1{font-size:20px}.store-link{width:112px}.stats{gap:10px}.stat{height:125px;padding:12px}.stat-value{font-size:18px}.chart{padding:16px}.orders{padding:12px;overflow:auto}.orders table{min-width:650px}}
</style>
</head>
<body>
<div class="shell">
<main class="main">
<header class="topbar">
<div class="account"><span class="role">مدیر فروشگاه</span><img src="https://www.figma.com/api/mcp/asset/e6cef.svg" alt=""><span class="muted"><?= esc($adminName ?? 'مدیر فروشگاه') ?></span></div>
<div class="search"><span>جست‌وجو در محصولات، سفارش‌ها و مشتریان…</span><img src="https://www.figma.com/api/mcp/asset/d78e2.svg" alt=""></div>
<div class="top-actions"><span>پنل مدیریت گره</span><img src="https://www.figma.com/api/mcp/asset/cf9ce.svg" alt=""></div>
</header>
<section class="page-head"><a class="store-link" href="<?= site_url('/') ?>" target="_blank">مشاهده فروشگاه</a><div class="page-title"><h1>پیشخوان</h1><p>نگاهی به وضعیت امروز فروشگاه</p></div></section>
<section class="dashboard">
<div class="stats">
<article class="stat"><div class="stat-head"><span>درآمد امروز</span><img src="https://www.figma.com/api/mcp/asset/5bd26.svg" alt=""></div><strong class="stat-value" id="paid-revenue">—</strong><span class="stat-note">اطلاعات پرداخت‌های ثبت‌شده</span></article>
<article class="stat"><div class="stat-head"><span>سفارش‌های جدید</span><img src="https://www.figma.com/api/mcp/asset/d88f1.svg" alt=""></div><strong class="stat-value" id="new-orders">—</strong><span class="stat-note">سفارش‌های منتظر پرداخت</span></article>
<article class="stat"><div class="stat-head"><span>محصولات فعال</span><img src="https://www.figma.com/api/mcp/asset/b7b77.svg" alt=""></div><strong class="stat-value" id="active">—</strong><span class="stat-note">اطلاعات موجودی واقعی</span></article>
<article class="stat"><div class="stat-head"><span>مشتریان</span><img src="https://www.figma.com/api/mcp/asset/53df4.svg" alt=""></div><strong class="stat-value" id="customer-count">—</strong><span class="stat-note">حساب‌های ثبت‌شده</span></article>
</div>
<div class="middle">
<section class="panel attention"><div class="panel-title">نیازمند توجه</div><div class="attention-list">
<div class="attention-row"><a href="#">مشاهده ‹</a><span>سفارش‌های منتظر پرداخت</span></div>
<div class="attention-row"><a href="#">مشاهده ‹</a><span>تنوع‌های دارای موجودی کم</span></div>
<div class="attention-row"><a href="#">مشاهده ‹</a><span>گزارش مرجوعی هنوز فعال نیست</span></div>
<div class="attention-row"><a href="#">مشاهده ‹</a><span>مدیریت دیدگاه هنوز فعال نیست</span></div>
</div></section>
<section class="panel chart"><div class="chart-head"><small>نمودار نمونه؛ داده واقعی هنوز متصل نشده</small><span>روند فروش هفتگی</span></div><div class="bars" aria-hidden="true" style="opacity:.15">
<div class="bar-item"><div class="bar" style="height:40%"></div><span>شنبه</span></div><div class="bar-item"><div class="bar" style="height:64%"></div><span>یکشنبه</span></div><div class="bar-item"><div class="bar" style="height:51%"></div><span>دوشنبه</span></div><div class="bar-item"><div class="bar" style="height:78%"></div><span>سه‌شنبه</span></div><div class="bar-item"><div class="bar" style="height:68%"></div><span>چهارشنبه</span></div><div class="bar-item"><div class="bar highlight" style="height:91%"></div><span>پنجشنبه</span></div><div class="bar-item"><div class="bar" style="height:82%"></div><span>جمعه</span></div>
</div><div class="chart-total">آمار نمودار در انتظار پیاده‌سازی</div></section>
</div>
<section class="panel orders"><div class="orders-head"><a href="<?= site_url('admin/orders') ?>">همه سفارش‌ها ‹</a><span>آخرین سفارش‌ها</span></div><table><thead><tr><th>عملیات</th><th>وضعیت</th><th>مبلغ (تومان)</th><th>مشتری</th><th>شماره سفارش</th></tr></thead><tbody id="recent-orders"><tr><td colspan="5">در حال دریافت سفارش‌های واقعی…</td></tr>
</tbody></table></section>
</section>
</main>
<aside class="sidebar">
<img class="logo" src="https://www.figma.com/api/mcp/asset/b7853.png" alt="گره">
<div class="side-title">مدیریت فروشگاه گره</div><div class="rule"></div>
<nav class="nav">
<a class="active" href="<?= site_url('admin') ?>"><img src="https://www.figma.com/api/mcp/asset/e1f74.svg" alt=""><span>پیشخوان</span></a>
<a href="<?= site_url('admin/products') ?>"><img src="https://www.figma.com/api/mcp/asset/7bc70.svg" alt=""><span>محصولات</span></a>
<a href="<?= site_url('admin/orders') ?>"><img src="https://www.figma.com/api/mcp/asset/409db.svg" alt=""><span>سفارش‌ها</span></a>
<a href="#"><img src="https://www.figma.com/api/mcp/asset/7bc70.svg" alt=""><span>موجودی و تنوع‌ها</span></a>
<a href="<?= site_url('admin/taxonomy') ?>"><img src="https://www.figma.com/api/mcp/asset/f8188.svg" alt=""><span>دسته‌بندی و برندها</span></a>
<a href="<?= site_url('admin/customers') ?>"><img src="https://www.figma.com/api/mcp/asset/e6cef.svg" alt=""><span>مشتریان</span></a>
<a href="#"><img src="https://www.figma.com/api/mcp/asset/f8188.svg" alt=""><span>تخفیف‌ها</span></a>
<a href="<?= site_url('admin/banners') ?>"><img src="https://www.figma.com/api/mcp/asset/26d14.svg" alt=""><span>مدیریت صفحه اصلی</span></a>
<a href="<?= site_url('admin/articles') ?>"><img src="https://www.figma.com/api/mcp/asset/41638.svg" alt=""><span>مجله و راهنماها</span></a>
<a href="#"><img src="https://www.figma.com/api/mcp/asset/b1127.svg" alt=""><span>تنظیمات</span></a>
</nav>
<div class="rule"></div><div class="side-foot">راهنمای مدیریت　 •　 <form method="post" action="<?= site_url('admin/logout') ?>" style="display:inline"><?= csrf_field() ?><button class="logout" type="submit">خروج</button></form></div>
</aside>
</div>
<script>
const formatFa=n=>new Intl.NumberFormat('fa-IR').format(Number(n)||0);
const setStat=(id,value)=>{const el=document.getElementById(id);if(el)el.textContent=value};
fetch('<?= site_url('api/v1/admin/dashboard') ?>',{credentials:'same-origin',cache:'no-store'}).then(r=>{if(!r.ok)throw Error();return r.json()}).then(({data})=>{
setStat('active',formatFa(data.active_products)+' محصول');
setStat('paid-revenue',formatFa(data.revenue_toman)+' تومان');
setStat('new-orders',formatFa(data.new_orders)+' سفارش');
setStat('customer-count',formatFa(data.customers)+' نفر');
}).catch(()=>{['active','paid-revenue','new-orders','customer-count'].forEach(id=>setStat(id,'ناموجود'))});
fetch('<?= site_url('api/v1/admin/orders?per_page=5') ?>',{credentials:'same-origin',cache:'no-store'}).then(r=>{if(!r.ok)throw Error();return r.json()}).then(({data})=>{
const tbody=document.getElementById('recent-orders');tbody.textContent='';
if(!Array.isArray(data)||!data.length){tbody.insertRow().insertCell().textContent='سفارشی ثبت نشده است.';return}
for(const order of data){
const tr=tbody.insertRow();const a=document.createElement('a');a.href='<?= site_url('admin/orders') ?>/'+Number(order.id);a.textContent='مشاهده';tr.insertCell().appendChild(a);
tr.insertCell().textContent=order.order_status||'—';tr.insertCell().textContent=formatFa(order.total_toman);tr.insertCell().textContent=order.receiver_name||'—';tr.insertCell().textContent=order.order_number||'—';
}}).catch(()=>{document.getElementById('recent-orders').textContent='دریافت سفارش‌ها ممکن نیست.'});
</script>
</body></html>