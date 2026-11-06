<?= view('store/partials/header', ['title'=>'فروشگاه | گره']) ?>
<?php
$selected = static fn (string $key, string $value): string => (string) request()->getGet($key) === $value ? 'selected' : '';
$qs = static function (array $updates): string {
    $current = request()->getGet();
    $current = is_array($current) ? $current : [];
    foreach ($updates as $key => $value) {
        if ($value === '') { unset($current[$key]); } else { $current[$key] = $value; }
    }
    return site_url('shop') . ($current ? '?' . http_build_query($current) : '');
};
?>
<style>
.shop-heading{margin:18px 0 16px}.shop-heading h1{font-size:23px;margin:0 0 6px}.category-tabs{display:flex;gap:8px;overflow:auto;margin:16px 0}.category-tabs a{padding:10px 18px;background:white;border-radius:9px;font-size:12px;white-space:nowrap}.category-tabs a[aria-current=page]{background:#012f24;color:#fff}.brand-strip{display:flex;gap:12px;overflow:auto;padding:10px 0 18px}.brand-strip a{background:#fff;border:1px solid #efe3d4;min-width:80px;text-align:center;border-radius:10px;padding:13px 9px;font-size:12px}.brand-strip a[aria-current=page]{border-color:#075d3c;background:#e7f1ec}.toolbar{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:12px 0}.toolbar button,.toolbar select{background:#fff;border:1px solid #efe3d4;border-radius:10px;padding:13px;color:#012f24;width:100%;font-size:13px}.toolbar button{cursor:pointer}.drawer-cover{position:fixed;inset:0;background:#012f2480;z-index:19}.filter-drawer{position:fixed;right:0;top:0;bottom:0;width:min(360px,90vw);z-index:20;background:#f9f4ef;padding:24px;overflow:auto;box-shadow:-10px 0 30px #012f2420}.filter-drawer h2{margin-top:0}.filter-drawer label{display:block;margin:18px 0 7px;font-size:13px}.filter-drawer select,.filter-drawer input{width:100%;border:1px solid #efe3d4;background:white;padding:13px;border-radius:9px}.filter-actions{display:flex;gap:10px;margin-top:26px}.filter-actions button{border:0;background:#012f24;color:white;padding:13px;border-radius:10px;flex:1}.filter-actions a{background:#efe3d4;padding:13px;border-radius:10px}.result-info{display:flex;justify-content:space-between;margin:15px 0;font-size:12px;color:#81766e}
</style>
<main class="container">
<div class="shop-heading"><h1>فروشگاه</h1><p class="muted">کفش مناسب سبک خودت رو پیدا کن</p></div>
<form action="<?= site_url('shop') ?>" method="get" role="search">
<input class="search" name="q" value="<?= esc((string) request()->getGet('q'), 'attr') ?>" placeholder="جست‌وجوی محصولات…" aria-label="جست‌وجوی محصولات">
<?php foreach (['category','brand','sort','min_price','max_price'] as $key): ?>
<?php $value = request()->getGet($key); if (is_scalar($value) && (string)$value !== ''): ?><input type="hidden" name="<?= esc($key) ?>" value="<?= esc((string)$value,'attr') ?>"><?php endif; ?>
<?php endforeach; ?>
</form>
<nav class="category-tabs" aria-label="دسته‌بندی محصولات">
<?php foreach ([''=>'همه','men'=>'مردانه','women'=>'زنانه','sport'=>'ورزشی'] as $slug=>$label): ?>
<a href="<?= esc($qs(['category'=>$slug]),'attr') ?>" <?= (string) request()->getGet('category') === $slug ? 'aria-current="page"' : '' ?>><?= esc($label) ?></a>
<?php endforeach; ?>
</nav>
<?php if ($brands): ?><nav class="brand-strip" aria-label="انتخاب برند">
<a href="<?= esc($qs(['brand'=>'']),'attr') ?>" <?= !request()->getGet('brand') ? 'aria-current="page"' : '' ?>>همه برندها</a>
<?php foreach ($brands as $brand): ?><a href="<?= esc($qs(['brand'=>$brand['slug']]),'attr') ?>" <?= (string) request()->getGet('brand') === $brand['slug'] ? 'aria-current="page"' : '' ?>><?= esc($brand['name']) ?></a><?php endforeach; ?>
</nav><?php endif; ?>
<div class="toolbar"><button type="button" id="show-filters" aria-controls="filter-drawer" aria-expanded="false">☷ فیلتر محصولات</button>
<form action="<?= site_url('shop') ?>" method="get"><label for="sort-products" class="muted">مرتب‌سازی</label>
<?php foreach (['q','category','brand','min_price','max_price'] as $key): $value=request()->getGet($key); if(is_scalar($value) && (string)$value !== ''): ?><input type="hidden" name="<?= esc($key) ?>" value="<?= esc((string)$value,'attr') ?>"><?php endif; endforeach; ?>
<select id="sort-products" name="sort" onchange="this.form.submit()"><option value="">جدیدترین</option><option value="price_asc" <?= $selected('sort','price_asc') ?>>ارزان‌ترین</option><option value="price_desc" <?= $selected('sort','price_desc') ?>>گران‌ترین</option></select></form></div>
<div class="result-info"><span><?= count($products) ?> محصول</span><span>نتایج جست‌وجو و فیلتر</span></div>
<?= view('store/partials/cards',['products'=>$products]) ?>
</main>
<div id="filter-cover" class="drawer-cover" hidden></div>
<aside id="filter-drawer" class="filter-drawer" aria-label="فیلتر محصولات" aria-modal="true" role="dialog" hidden>
<button type="button" id="close-filters" aria-label="بستن فیلتر" style="float:left;border:0;background:none;font-size:24px">×</button><h2>فیلتر محصولات</h2>
<form action="<?= site_url('shop') ?>" method="get">
<?php foreach (['q','sort'] as $key): $value=request()->getGet($key); if(is_scalar($value) && (string)$value !== ''): ?><input type="hidden" name="<?= esc($key) ?>" value="<?= esc((string)$value,'attr') ?>"><?php endif; endforeach; ?>
<label for="filter-category">دسته‌بندی</label><select id="filter-category" name="category"><option value="">همه دسته‌ها</option><option value="men" <?= $selected('category','men') ?>>مردانه</option><option value="women" <?= $selected('category','women') ?>>زنانه</option><option value="sport" <?= $selected('category','sport') ?>>ورزشی</option></select>
<label for="filter-brand">برند</label><select id="filter-brand" name="brand"><option value="">همه برندها</option><?php foreach ($brands as $brand): ?><option value="<?= esc($brand['slug'],'attr') ?>" <?= $selected('brand',$brand['slug']) ?>><?= esc($brand['name']) ?></option><?php endforeach; ?></select>
<label for="min-price">حداقل قیمت (تومان)</label><input id="min-price" type="number" min="0" name="min_price" value="<?= esc((string) request()->getGet('min_price'),'attr') ?>">
<label for="max-price">حداکثر قیمت (تومان)</label><input id="max-price" type="number" min="0" name="max_price" value="<?= esc((string) request()->getGet('max_price'),'attr') ?>">
<div class="filter-actions"><button type="submit">نمایش نتایج</button><a href="<?= site_url('shop') ?>">پاک‌کردن</a></div></form></aside>
<script>
(()=>{const open=document.getElementById('show-filters'),close=document.getElementById('close-filters'),drawer=document.getElementById('filter-drawer'),cover=document.getElementById('filter-cover');let previous=null;function toggle(show){drawer.hidden=!show;cover.hidden=!show;open.setAttribute('aria-expanded',String(show));document.body.style.overflow=show?'hidden':'';if(show){previous=document.activeElement;close.focus()}else{previous?.focus()}}open.addEventListener('click',()=>toggle(true));close.addEventListener('click',()=>toggle(false));cover.addEventListener('click',()=>toggle(false));document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!drawer.hidden)toggle(false)})})();
</script>
<?= view('store/partials/footer') ?>
