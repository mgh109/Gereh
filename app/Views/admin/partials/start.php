<?php
helper('admin_ui');
$active = $active ?? '';
$adminName = $adminName ?? session()->get('admin_name') ?? 'مدیر فروشگاه';
$asset = static fn (string $name): string => base_url('assets/figma/admin/'.$name);
$links = [
    ['dashboard', 'admin', 'پیشخوان', '88cd6.svg'],
    ['products', 'admin/products', 'محصولات', '7bc70.svg'],
    ['orders', 'admin/orders', 'سفارش‌ها', '409db.svg'],
    ['comments','admin/comments','دیدگاه محصولات','41638.svg'],
    ['inventory', 'admin/inventory', 'موجودی و تنوع‌ها', '7bc70.svg'],
    ['taxonomy', 'admin/taxonomy', 'دسته‌بندی و برندها', 'f8188.svg'],
    ['customers', 'admin/customers', 'مشتریان', 'e6cef.svg'],
    ['shipping', 'admin/shipping', 'روش‌های ارسال', 'f8188.svg'],
    ['banners', 'admin/banners', 'مدیریت صفحه اصلی', '26d14.svg'],
    ['articles', 'admin/articles', 'مجله و راهنماها', '41638.svg'],
    ['settings', 'admin/settings/sms', 'تنظیمات', 'b1127.svg'],
];
$activeIcons = ['products'=>'84b0d.svg','orders'=>'cfb7c.svg','inventory'=>'84b0d.svg','taxonomy'=>'59637.svg','customers'=>'2b11f.svg','banners'=>'1a183.svg','articles'=>'c5b59.svg','settings'=>'00507.svg'];
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= esc($title ?? 'مدیریت فروشگاه') ?> | مدیریت گره</title>
<link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
<script src="<?= base_url('assets/js/admin.js') ?>" defer></script>
</head>
<body class="admin-body">
<div class="admin-shell">
<aside class="admin-sidebar" aria-label="منوی مدیریت">
    <a class="admin-brand" href="<?= site_url('admin') ?>"><img src="<?= $asset('b7853.png') ?>" alt="گره" width="94" height="64"><strong>مدیریت فروشگاه گره</strong></a>
    <div class="admin-divider"></div>
    <nav class="admin-nav" aria-label="صفحات مدیریت">
    <?php foreach ($links as [$key, $url, $label, $icon]): ?>
        <?php if ($url): ?><a href="<?= site_url($url) ?>" <?= $active === $key ? 'class="is-active" aria-current="page"' : '' ?>>
        <?php else: ?><span class="admin-nav-unavailable" aria-disabled="true" title="مدیریت کدهای تخفیف هنوز فعال نشده است">
        <?php endif; ?>
        <img src="<?= $asset($active === $key ? ($activeIcons[$key] ?? $icon) : $icon) ?>" alt="" width="20" height="20"><span><?= esc($label) ?></span>
        <?= $url ? '</a>' : '</span>' ?>
    <?php endforeach; ?>
    </nav>
    <div class="admin-divider"></div>
    <div class="admin-sidebar-footer"><a href="<?= site_url('/') ?>" target="_blank" rel="noopener">مشاهده فروشگاه</a><span>•</span><form method="post" action="<?= site_url('admin/logout') ?>"><?= csrf_field() ?><button type="submit">خروج</button></form></div>
</aside>
<div class="admin-content">
<header class="admin-topbar">
    <div class="admin-account"><span class="admin-role">مدیر فروشگاه</span><img src="<?= $asset('e6cef.svg') ?>" alt="" width="20" height="20"><span><?= esc($adminName) ?></span></div>
    <form class="admin-global-search" method="get" action="<?= site_url('admin/products') ?>" role="search"><input name="q" type="search" placeholder="جست‌وجو در محصولات…" aria-label="جست‌وجوی محصولات"><button type="submit" aria-label="جست‌وجو"><img src="<?= $asset('d78e2.svg') ?>" alt="" width="20" height="20"></button></form>
    <div class="admin-topbar-title"><strong>پنل مدیریت گره</strong><img src="<?= $asset('cf9ce.svg') ?>" alt="" width="20" height="20"></div>
</header>
<header class="admin-page-heading"><a class="admin-button admin-button-soft" href="<?= site_url('/') ?>" target="_blank" rel="noopener">مشاهده فروشگاه</a><div><h1><?= esc($title ?? '') ?></h1><?php if (!empty($subtitle)): ?><p><?= esc($subtitle) ?></p><?php endif; ?></div></header>
