<?= view('store/partials/header', ['title'=>'گره؛ همراه هر قدم']) ?>
<?php
$hero=$banners['hero']??null;
$women=$banners['women']??null;
$men=$banners['men']??null;
$promo=$banners['promo']??null;
?>
<main class="container">
<section class="hero" style="position:relative;overflow:hidden">
<?php if(!empty($hero['image_url'])): ?><img src="<?= esc($hero['image_url'],'attr') ?>" alt="" width="364" height="245" fetchpriority="high" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover"><?php endif; ?>
<?php if(!$hero || $hero['show_text']): ?>
<div style="position:relative;z-index:1">
<p>GEREH / SHOE STORE</p><h1><?= esc($hero['title']??'گره؛ همراه هر قدم') ?></h1>
<p><?= esc($hero['subtitle']??'کفش مناسب سبک تو؛ برای هر قدم و هر مسیر') ?></p>
<a class="pill" style="display:inline-block;margin-top:16px" href="<?= site_url(ltrim($hero['link_path']??'/shop','/')) ?>">مشاهده محصولات</a>
</div>
<?php endif; ?>
</section>
<section class="two" aria-label="دسته‌بندی کفش">
<?php foreach([['women',$women,'کفش زنانه'],['men',$men,'کفش مردانه']] as [$slot,$banner,$fallback]): ?>
<a class="banner" href="<?= site_url(ltrim($banner['link_path']??('/shop?category='.$slot),'/')) ?>" style="position:relative;overflow:hidden">
<?php if(!empty($banner['image_url'])): ?><img src="<?= esc($banner['image_url'],'attr') ?>" loading="lazy" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover"><?php endif; ?>
<?php if(!$banner || $banner['show_text']): ?><span style="position:relative;z-index:1"><?= esc($banner['title']??$fallback) ?></span><span style="position:relative;z-index:1">‹</span><?php endif; ?>
</a>
<?php endforeach; ?>
</section>
<section class="section"><div class="section-title"><h2>پرفروش‌ترین‌ها</h2><a class="muted" href="<?= site_url('shop') ?>">مشاهده همه ‹</a></div><?= view('store/partials/cards',['products'=>$products]) ?></section>
<section class="section">
<a class="banner" href="<?= site_url(ltrim($promo['link_path']??'/shop','/')) ?>" style="height:128px;background:#012f24;position:relative;overflow:hidden">
<?php if(!empty($promo['image_url'])): ?><img src="<?= esc($promo['image_url'],'attr') ?>" loading="lazy" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover"><?php endif; ?>
<?php if(!$promo || $promo['show_text']): ?><span style="position:relative;z-index:1"><?= esc($promo['title']??'انتخابی برای قدم‌های تازه') ?></span><span style="position:relative;z-index:1">مشاهده ‹</span><?php endif; ?>
</a></section>
<?php if($brands): ?><section class="section"><div class="section-title"><h2>انتخاب از روی برندها</h2><a class="muted" href="<?= site_url('shop') ?>">همه برندها ‹</a></div>
<div style="display:flex;gap:12px;overflow-x:auto;padding:8px 0">
<?php foreach($brands as $brand): ?><a href="<?= site_url('shop?brand='.rawurlencode($brand['slug'])) ?>" style="flex:0 0 105px;background:white;border:1px solid #efe3d4;border-radius:12px;min-height:82px;display:flex;align-items:center;justify-content:center;padding:12px">
<?php if(!empty($brand['logo_path'])): ?><img src="<?= esc(base_url(ltrim($brand['logo_path'],'/')),'attr') ?>" alt="<?= esc($brand['name'],'attr') ?>" loading="lazy" style="max-width:100%;max-height:55px;object-fit:contain"><?php else: ?><?= esc($brand['name']) ?><?php endif; ?>
</a><?php endforeach; ?>
</div></section><?php endif; ?>
<section class="section"><div class="section-title"><h2>محصولات منتخب</h2><a class="muted" href="<?= site_url('shop') ?>">مشاهده همه ‹</a></div><?= view('store/partials/cards',['products'=>array_slice($products,0,4)]) ?></section>
</main><?= view('store/partials/footer') ?>