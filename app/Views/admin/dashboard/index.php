<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>داشبورد مدیریت | گره</title>
<style>
*{box-sizing:border-box}body{margin:0;background:#f5f6f8;color:#222;font-family:Tahoma,Arial,sans-serif}.layout{display:flex;min-height:100vh}.side{width:250px;background:#fff;border-left:1px solid #e8e9ed;padding:24px 16px}.brand{font-size:22px;font-weight:bold;padding:8px 12px 28px}.nav a{display:block;padding:13px 12px;margin:4px 0;border-radius:9px;text-decoration:none;color:#555;font-size:14px}.nav a.active,.nav a:hover{background:#f0f1f3;color:#111}.main{flex:1;min-width:0}.top{height:72px;background:#fff;border-bottom:1px solid #e8e9ed;display:flex;align-items:center;justify-content:space-between;padding:0 28px}.content{padding:30px}.title{font-size:22px;margin:0 0 8px}.sub{font-size:13px;color:#777;margin:0 0 24px}.cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}.card{background:#fff;border:1px solid #e8e9ed;border-radius:13px;padding:20px}.card span{font-size:13px;color:#777}.card strong{display:block;font-size:28px;margin-top:14px}.section{margin-top:22px;background:#fff;border:1px solid #e8e9ed;border-radius:13px;padding:22px}.section h2{font-size:16px;margin:0 0 10px}.section p{font-size:13px;color:#777;line-height:2}.logout{color:#555;text-decoration:none;font-size:13px}@media(max-width:900px){.side{width:190px}.cards{grid-template-columns:repeat(2,1fr)}}@media(max-width:600px){.layout{display:block}.side{width:auto;border-left:0;border-bottom:1px solid #eee;padding:12px}.brand{padding:8px}.nav{display:flex;overflow:auto}.nav a{white-space:nowrap}.content{padding:18px}.top{padding:0 18px}.cards{grid-template-columns:1fr 1fr}.card{padding:14px}.card strong{font-size:23px}}
</style></head>
<body><div class="layout">
<aside class="side"><div class="brand">گره <small style="font-size:11px;color:#888">مدیریت</small></div>
<nav class="nav">
<a class="active" href="<?= site_url('admin') ?>">داشبورد</a><a href="#">محصولات</a><a href="#">دسته‌بندی‌ها</a><a href="#">برندها</a><a href="#">سفارش‌ها</a><a href="#">مشتریان</a><a href="#">تخفیف‌ها</a><a href="#">ارسال</a><a href="#">وبلاگ</a><a href="#">مدیران و دسترسی</a><a href="#">تنظیمات</a>
</nav></aside>
<main class="main"><header class="top"><strong>داشبورد</strong><span><?= esc($adminName ?? 'مدیر') ?> · <a class="logout" href="<?= site_url('admin/logout') ?>">خروج</a></span></header>
<div class="content"><h1 class="title">خلاصه فروشگاه</h1><p class="sub">نمای کلی وضعیت کاتالوگ و موجودی</p>
<div class="cards"><div class="card"><span>همه محصولات</span><strong id="products">—</strong></div><div class="card"><span>محصولات فعال</span><strong id="active">—</strong></div><div class="card"><span>دسته‌بندی‌های فعال</span><strong id="categories">—</strong></div><div class="card"><span>تنوع‌های کم‌موجود</span><strong id="stock">—</strong></div></div>
<section class="section"><h2>شروع مدیریت فروشگاه</h2><p>ساختار عمومی پنل آماده شده است. بخش‌های محصولات، سفارش‌ها، مشتریان، تخفیف‌ها و تنظیمات در مراحل بعدی به سرویس‌های واقعی متصل می‌شوند.</p></section>
</div></main></div>
<script>
fetch('<?= site_url('admin/api/summary') ?>',{headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>{if(!r.ok)throw new Error();return r.json()}).then(({data})=>{document.getElementById('products').textContent=data.products;document.getElementById('active').textContent=data.active_products;document.getElementById('categories').textContent=data.categories;document.getElementById('stock').textContent=data.low_stock_variants}).catch(()=>{});
</script></body></html>