<?= view('store/partials/header', ['title'=>esc($product['name']).' | گره']) ?>
<style>
.product-gallery{display:grid;gap:12px}.product-gallery .product-image{border-radius:14px;overflow:hidden;aspect-ratio:1}.thumbs{display:flex;gap:9px;overflow:auto}.thumbs button{padding:0;border:2px solid transparent;border-radius:9px;width:72px;height:72px;overflow:hidden;flex:none;background:#efe3d4;cursor:pointer}.thumbs button[aria-pressed=true]{border-color:#075d3c}.thumbs img{width:100%;height:100%;object-fit:cover}.options{margin:18px 0}.options h3{font-size:14px}.choice-group{display:flex;gap:8px;flex-wrap:wrap}.choice-group button{border:1px solid #ddd5cb;background:white;padding:11px 16px;border-radius:9px;color:#012f24;cursor:pointer}.choice-group button[aria-pressed=true]{background:#012f24;color:#fff;border-color:#012f24}.choice-group button:disabled{opacity:.4;cursor:not-allowed}.price-old{text-decoration:line-through;color:#81766e;font-size:13px}.product-details-section{background:white;padding:20px;border-radius:12px;margin:18px 0}.product-details-section h2{font-size:17px}.sticky-buy{z-index:12}.sticky-buy button:disabled{opacity:.5;cursor:not-allowed}@media(max-width:700px){.sticky-buy{position:fixed;bottom:60px;left:0;right:0;z-index:12}.detail{margin-bottom:140px}}
</style>
<?php
$images = array_values(array_filter($media, static fn ($m) => ($m['media_type'] ?? 'image') === 'image' && !empty($m['file_path'])));
$firstImage = $images ? base_url(ltrim($images[0]['file_path'], '/')) : null;
?>
<main class="container">
<p class="muted"><a href="<?= site_url('shop') ?>">فروشگاه</a> / <?= esc($product['name']) ?></p>
<div class="detail">
<section class="product-gallery" aria-label="گالری تصاویر محصول">
<div class="product-image"><?php if ($firstImage): ?><img id="main-product-image" src="<?= esc($firstImage,'attr') ?>" alt="<?= esc($images[0]['alt_text'] ?: $product['name'],'attr') ?>"><?php else: ?>تصویر محصول ثبت نشده<?php endif; ?></div>
<?php if (count($images)>1): ?><div class="thumbs" role="group" aria-label="انتخاب تصویر">
<?php foreach ($images as $i=>$image): ?><button type="button" class="image-thumb" aria-label="تصویر <?= $i+1 ?>" aria-pressed="<?= $i===0?'true':'false' ?>" data-src="<?= esc(base_url(ltrim($image['file_path'],'/')),'attr') ?>" data-alt="<?= esc($image['alt_text'] ?: $product['name'],'attr') ?>"><img src="<?= esc(base_url(ltrim($image['file_path'],'/')),'attr') ?>" alt="" loading="lazy"></button><?php endforeach; ?>
</div><?php endif; ?>
</section>
<section class="buy"><h1><?= esc($product['name']) ?></h1><p class="muted"><?= esc($product['short_description'] ?? '') ?></p>
<div class="price-old" id="old-price" <?= empty($product['compare_at_price_toman']) ? 'hidden' : '' ?>><?= number_format((int)($product['compare_at_price_toman'] ?? 0)) ?> تومان</div>
<h2 id="current-price"><?= number_format((int)$product['price_toman']) ?> تومان</h2>
<?php if ($variants): ?>
<?php $colors=[];$sizes=[];foreach($variants as $v){$color=trim((string)($v['color_name']??''));$size=trim((string)($v['size_label']??''));if($color!=='')$colors[$color]=true;if($size!=='')$sizes[$size]=true;} ?>
<?php if ($colors): ?><div class="options"><h3>انتخاب رنگ</h3><div class="choice-group" id="color-options" role="group" aria-label="رنگ"><?php foreach(array_keys($colors) as $color): ?><button type="button" data-value="<?= esc($color,'attr') ?>" aria-pressed="false"><?= esc($color) ?></button><?php endforeach; ?></div></div><?php endif; ?>
<?php if ($sizes): ?><div class="options"><h3>انتخاب سایز</h3><div class="choice-group" id="size-options" role="group" aria-label="سایز"><?php foreach(array_keys($sizes) as $size): ?><button type="button" data-value="<?= esc($size,'attr') ?>" aria-pressed="false"><?= esc($size) ?></button><?php endforeach; ?></div></div><?php endif; ?>
<p id="stock-status" class="muted" aria-live="polite">رنگ و سایز موردنظر را انتخاب کنید.</p>
<?php else: ?><p class="muted">تنوع قابل خرید برای این محصول ثبت نشده است.</p><?php endif; ?>
<a href="<?= site_url('size-guide') ?>" class="muted">راهنمای خرید و انتخاب سایز</a>
</section></div>
<section class="product-details-section"><h2>معرفی محصول</h2><p><?= nl2br(esc($product['description'] ?? '')) ?></p></section>
<section class="product-details-section"><h2>مشخصات محصول</h2><p class="muted">اطلاعات سایز و رنگ از تنوع‌های ثبت‌شده محصول نمایش داده می‌شود.</p></section>
<section class="product-details-section"><h2>دیدگاه خریداران</h2><p class="muted">هنوز دیدگاهی ثبت نشده است.</p></section>
</main>
<div class="sticky-buy"><strong id="sticky-price"><?= number_format((int)$product['price_toman']) ?> تومان</strong><button class="pill" type="button" disabled title="فرایند سبد خرید در فاز ۳ فعال می‌شود">افزودن به سبد خرید</button></div>
<script>
(()=>{const main=document.getElementById('main-product-image');document.querySelectorAll('.image-thumb').forEach(btn=>btn.addEventListener('click',()=>{if(!main)return;main.src=btn.dataset.src;main.alt=btn.dataset.alt;document.querySelectorAll('.image-thumb').forEach(b=>b.setAttribute('aria-pressed',String(b===btn)))}));
const variants=<?= json_encode(array_map(static fn($v)=>['id'=>(int)$v['id'],'color'=>(string)($v['color_name']??''),'size'=>(string)($v['size_label']??''),'price'=>(int)($v['price_toman']??$product['price_toman']),'stock'=>(int)$v['stock']],$variants),JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
let color='',size='';const colors=document.getElementById('color-options'),sizes=document.getElementById('size-options'),stock=document.getElementById('stock-status');function choose(group,value){group.querySelectorAll('button').forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.value===value)))}function refresh(){const matched=variants.find(v=>(!colors||v.color===color)&&(!sizes||v.size===size));if(matched){const price=new Intl.NumberFormat('fa-IR').format(matched.price)+' تومان';document.getElementById('current-price').textContent=price;document.getElementById('sticky-price').textContent=price;}if(stock)stock.textContent=(!colors||color)&&(!sizes||size)?(matched?.stock>0?'موجود: '+matched.stock+' عدد':'ناموجود'):'رنگ و سایز موردنظر را انتخاب کنید.'}
colors?.querySelectorAll('button').forEach(b=>b.addEventListener('click',()=>{color=b.dataset.value;choose(colors,color);refresh()}));sizes?.querySelectorAll('button').forEach(b=>b.addEventListener('click',()=>{size=b.dataset.value;choose(sizes,size);refresh()}));refresh()})();
</script>
<?= view('store/partials/footer') ?>
