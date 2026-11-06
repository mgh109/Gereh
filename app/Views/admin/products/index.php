<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>محصولات | پنل مدیریت گره</title><link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
<style>
:root{font-family:Estedad,Tahoma,sans-serif;color:#012f24;background:#f5f5f2}*{box-sizing:border-box}body{margin:0}a{color:inherit;text-decoration:none}.shell{display:grid;grid-template-columns:260px minmax(0,1fr);gap:24px;padding:24px;min-height:100vh}.sidebar{background:#012f24;color:#efe3d4;border-radius:18px;padding:26px 18px}.logo{font-size:28px;font-weight:900;margin:0 10px 38px}.nav{display:grid;gap:8px}.nav a{padding:14px 18px;border-radius:10px;font-size:13px}.nav a.active{background:#075d3c}.content{min-width:0}.top{background:white;border-radius:16px;min-height:64px;display:flex;align-items:center;justify-content:space-between;gap:20px;padding:12px 16px;font-size:13px}.search{background:#f9f4ef;border:0;border-radius:9px;padding:12px;width:min(390px,100%);font:inherit}.heading{display:flex;align-items:center;justify-content:space-between;margin:24px 0}.heading h1{font-size:25px;margin:0 0 8px}.muted{color:#81766e;font-size:12px}.button{background:#f9f4ef;border-radius:10px;padding:14px 24px;font-size:12px}.panel{background:white;border-radius:16px;padding:24px}.filters{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px}.filters input,.filters select{border:1px solid #e9e6e1;border-radius:9px;background:#fff;padding:13px;font:inherit;color:#012f24}.filters input{flex:1;min-width:200px}.filters button{border:0;background:#075d3c;color:white;border-radius:9px;padding:12px 24px;cursor:pointer}.scroll{overflow:auto}table{border-collapse:collapse;width:100%;min-width:680px;text-align:right;font-size:12px}th{background:#f9f4ef;color:#81766e;font-weight:500}td,th{padding:18px 14px;border-bottom:1px solid #eeeae5}.tag{display:inline-block;border-radius:8px;padding:6px 12px;background:#e7f1ec;color:#075d3c}.tag.off{background:#f9f4ef;color:#81766e}.empty{text-align:center;padding:40px;color:#81766e}.note{padding:15px;background:#fff7e8;border-radius:10px;color:#7d5a22;margin-bottom:18px}@media(max-width:850px){.shell{grid-template-columns:1fr;padding:12px}.sidebar{padding:14px}.logo{margin:0 0 12px}.nav{display:flex;overflow:auto}.nav a{white-space:nowrap}.top{flex-wrap:wrap}.heading{align-items:flex-start}.panel{padding:14px}}
</style>
</head>
<body>
<div class="shell">
<aside class="sidebar"><div class="logo">گره <small style="font-size:11px;font-weight:400">مدیریت فروشگاه</small></div><nav class="nav" aria-label="مدیریت">
<a href="<?= site_url('admin') ?>">پیشخوان</a><a class="active" href="<?= site_url('admin/products') ?>">محصولات</a><a href="<?= site_url('admin/taxonomy') ?>">برندها و دسته‌ها</a><a href="<?= site_url('/') ?>">مشاهده فروشگاه</a>
</nav></aside>
<main class="content">
<header class="top"><strong>پنل مدیریت گره</strong><input class="search" type="search" placeholder="جست‌وجو در محصولات، سفارش‌ها و مشتریان…" aria-label="جست‌وجو" form="product-filters" name="q"><span><?= esc($adminName) ?></span></header>
<section class="heading"><div><h1>محصولات</h1><div class="muted">مدیریت اطلاعات، تصاویر و وضعیت انتشار</div></div><a class="button" href="<?= site_url('admin/products/create') ?>">+ افزودن محصول</a></section>
<section class="panel">
<?php if (!$databaseReady): ?><div class="note">دیتابیس هنوز راه‌اندازی نشده است؛ فهرست واقعی محصولات پس از اجرای Migration نمایش داده می‌شود.</div><?php endif; ?>
<form id="product-filters" class="filters" method="get" action="<?= site_url('admin/products') ?>">
<select name="status" aria-label="وضعیت محصول"><option value="">همه وضعیت‌ها</option><option value="active">فعال</option><option value="draft">پیش‌نویس</option></select>
<button type="submit">اعمال فیلتر</button>
</form>
<div class="scroll"><table><thead><tr><th>محصول</th><th>شناسه</th><th>قیمت (تومان)</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>
<?php
$q = trim((string) service('request')->getGet('q'));
$status = (string) service('request')->getGet('status');
$visible = array_filter($products, static fn ($p) => ($q === '' || mb_stripos((string) ($p['name'] ?? ''), $q) !== false) && ($status === '' || ($p['status'] ?? '') === $status));
?>
<?php foreach ($visible as $product): ?><tr><td><?= esc($product['name']) ?></td><td>#<?= (int) $product['id'] ?></td><td><?= number_format((int) $product['price_toman']) ?></td><td><span class="tag <?= ($product['status'] ?? '') === 'active' ? '' : 'off' ?>"><?= ($product['status'] ?? '') === 'active' ? 'فعال' : 'غیرفعال' ?></span></td><td><a href="<?= site_url('admin/products/'.$product['id'].'/edit') ?>">ویرایش</a></td></tr><?php endforeach; ?>
<?php if (!$visible): ?><tr><td colspan="5" class="empty">محصولی برای نمایش وجود ندارد.</td></tr><?php endif; ?>
</tbody></table></div>
</section></main></div>
</body></html>
