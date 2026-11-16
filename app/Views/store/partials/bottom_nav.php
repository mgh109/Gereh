<?php
$navPath = (string) parse_url(current_url(), PHP_URL_PATH);
$basePath = rtrim((string) parse_url(base_url(), PHP_URL_PATH), '/');
if ($basePath !== '' && str_starts_with($navPath, $basePath.'/')) {
    $navPath = substr($navPath, strlen($basePath));
}
$navPath = trim($navPath, '/');
$activeNav = $navPath === '' ? 'home' : (str_starts_with($navPath, 'shop') ? 'shop' : (str_starts_with($navPath, 'cart') ? 'cart' : (str_starts_with($navPath, 'account') ? 'account' : ($navPath === 'favorites' ? 'favorites' : ''))));
$items = [
 ['home', $activeNav === 'home' ? '1b037.svg' : '163f2.svg', 'خانه', '/'],
 ['shop', $activeNav === 'shop' ? '2c710.svg' : '3828f.svg', 'فروشگاه', 'shop'],
 ['cart', 'c8836.svg', 'سبد خرید', 'cart'],
 ['favorites', $activeNav === 'favorites' ? '63aa0.svg' : 'ee3d5.svg', 'علاقه‌مندی', 'favorites'],
 ['account', $activeNav === 'account' ? '64cf7.svg' : '6e816.svg', 'حساب', 'account'],
];
?>
<nav class="home-bottom" aria-label="ناوبری پایین">
<?php foreach ($items as [$key, $icon, $label, $path]): ?>
<a href="<?= site_url($path) ?>" <?= $activeNav === $key ? 'aria-current="page"' : '' ?> <?= $key === 'cart' && $activeNav !== 'cart' ? 'data-open-cart' : '' ?>><img src="<?= base_url('assets/figma/'.$icon) ?>" alt="" width="20" height="20"><span><?= $label ?></span></a>
<?php endforeach; ?>
<span class="nav-system-indicator" aria-hidden="true"></span>
</nav>
