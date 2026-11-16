<?= view('store/partials/header', ['title'=>'گره؛ همراه هر قدم', 'homeDesign'=>true]) ?>
<?php
$asset = static fn ($name) => base_url('assets/figma/'.$name);
$image = static fn ($banner, $fallback) => !empty($banner['image_url']) ? $banner['image_url'] : $asset($fallback);
$link = static fn ($banner, $fallback='/shop') => site_url(ltrim($banner['link_path']??$fallback,'/'));
$show = static fn ($banner) => !$banner || !isset($banner['show_text']) || (bool)$banner['show_text'];
$hero=$banners['hero']??null; $promo=$banners['promo']??null;
$featured=array_values(array_filter($products,static fn ($p)=>!empty($p['is_featured'])));
if(!$featured) $featured=array_slice($products,2)?:$products;
?>
<main class="mx-auto w-full max-w-[390px] min-[701px]:max-w-[1120px] px-[13px]">
<section class="home-hero relative h-[245px] overflow-hidden rounded-[10px] min-[701px]:h-[400px] [&>a]:block [&>a]:h-full" aria-label="پیشنهاد ویژه گره">
<a href="<?= esc($link($hero),'attr') ?>"><img class="absolute inset-0 h-full w-full object-cover" src="<?= esc($image($hero,'78d8d.png'),'attr') ?>" alt="" width="364" height="245" fetchpriority="high">
<?php if($show($hero)): ?><div class="absolute left-[13px] top-[17px] w-[155px] text-right text-gereh-paper min-[701px]:left-[45px] min-[701px]:top-[45px] min-[701px]:w-[280px] [&>h1]:m-0 [&>h1]:whitespace-pre-line [&>h1]:text-[25px] [&>h1]:leading-[1.62] [&>h1]:font-bold [&>p]:m-0 [&>p]:text-[25px] [&>p]:leading-[40px] [&>p]:font-bold min-[701px]:[&>h1]:text-[38px] min-[701px]:[&>p]:text-[38px]"><h1><?= esc($hero['title']??"به گره\nخوش اومدی!") ?></h1><p><?= esc($hero['subtitle']??'۵۰٪ تخفیف') ?></p></div><?php endif; ?></a>
<div class="absolute bottom-2 left-1/2 flex -translate-x-1/2 gap-[5px] [direction:ltr] [&>span]:h-[6px] [&>span]:w-5 [&>span]:rounded-[3px] [&>span]:bg-gereh-cream [&>span:last-child]:w-[22px] [&>span:last-child]:bg-gereh-900" aria-hidden="true"><span></span><span></span><span></span></div>
</section>
<section class="home-categories mt-[14px] grid grid-cols-2 gap-3" aria-label="انتخاب کفش">
<?php foreach([['men','8186a.png','کفش مردانه'],['women','6dacd.png','کفش زنانه']] as [$slot,$file,$title]): $banner=$banners[$slot]??null; ?>
<a class="relative h-[112px] overflow-hidden rounded-[10px] text-gereh-paper min-[701px]:h-[200px] [&>span]:absolute [&>span]:right-3 [&>span]:top-[5px] [&>span]:text-[14px] [&>span]:leading-[27px] [&>span]:font-bold" href="<?= esc($link($banner,'/shop?category='.$slot),'attr') ?>"><img class="absolute inset-0 h-full w-full object-cover" src="<?= esc($image($banner,$file),'attr') ?>" alt="" width="176" height="112">
<?php if($show($banner)): ?><span><?= esc($banner['title']??$title) ?></span><img class="absolute left-[10px] top-[11px]" src="<?= $asset('f23e1.svg') ?>" alt="" width="10" height="12"><?php endif; ?></a>
<?php endforeach; ?>
</section>
<section class="home-bestsellers mt-5" aria-labelledby="bestsellers-title">
<div class="home-section-title mb-[10px] flex h-[35px] items-center justify-between gap-2 ps-[5px] pe-[10px] [&>h2]:m-0 [&>h2]:text-[19px] [&>h2]:leading-[35px] [&>h2]:font-bold [&>a]:flex [&>a]:items-center [&>a]:gap-1 [&>a]:whitespace-nowrap [&>a]:text-[13px]"><h2 id="bestsellers-title">پرفروش‌ترین‌ها</h2><a href="<?= site_url('shop') ?>">مشاهده همه <img src="<?= $asset('530b8.svg') ?>" alt="" width="12" height="16"></a></div>
<?= view('store/partials/home_cards',['products'=>array_slice($products,0,4),'compact'=>false]) ?>
</section>
<section class="home-promo relative mt-5 h-32 overflow-hidden rounded-[9px] min-[701px]:h-[220px] [&>a]:block [&>a]:h-full" aria-label="پیشنهاد خرید">
<a href="<?= esc($link($promo),'attr') ?>"><img class="absolute inset-0 h-full w-full object-cover" src="<?= esc($image($promo,'8f210.png'),'attr') ?>" alt="" width="364" height="128" loading="lazy">
<?php if($show($promo)): ?><div class="absolute left-[15px] top-[18px] w-[175px] text-gereh-paper min-[701px]:left-10 min-[701px]:top-10 [&>h2]:m-0 [&>h2]:mb-4 [&>h2]:text-[20px] [&>h2]:leading-8 [&>h2]:font-bold [&>span]:grid [&>span]:h-10 [&>span]:w-[134px] [&>span]:place-items-center [&>span]:rounded-lg [&>span]:bg-gereh-cream [&>span]:text-[12px] [&>span]:font-bold [&>span]:text-gereh-900"><h2><?= esc($promo['title']??'۵۰٪ تخفیف ویژه!') ?></h2><span>همین حالا خرید کن</span></div><?php endif; ?></a>
</section>
<section class="home-brands -mx-[13px] mt-[14px] bg-[#efe3dc] p-4 [&>h2]:m-0 [&>h2]:mb-5 [&>h2]:text-center [&>h2]:text-[17px] [&>h2]:leading-[34px] [&>h2]:font-bold" aria-labelledby="brands-title">
<h2 id="brands-title">انتخاب کفش از برندهای محبوب</h2>
<div class="flex h-[62px] items-center gap-[10px] [&>button]:w-4 [&>button]:flex-none [&>button]:border-0 [&>button]:bg-transparent [&>button]:p-0 [&>button:disabled]:cursor-default [&>button:disabled]:opacity-40" dir="ltr">
<button type="button" data-brand-direction="-1" aria-label="برندهای قبلی"><img src="<?= $asset('5d4d8.svg') ?>" alt="" width="16" height="20"></button>
<div class="flex min-w-0 flex-1 snap-x snap-mandatory items-center justify-between gap-3 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden [&>a]:flex [&>a]:h-[50px] [&>a]:basis-[84px] [&>a]:shrink-0 [&>a]:snap-start [&>a]:items-center [&>a]:justify-center [&>a]:text-center [&>a]:text-xs [&_img]:max-h-[50px] [&_img]:max-w-[84px] [&_img]:flex-none [&_img]:object-contain" id="home-brand-track">
<?php
$referenceBrands=[['name'=>'The North Face','slug'=>'the-north-face','image'=>'59dd8.svg'],['name'=>'New Balance','slug'=>'new-balance','image'=>'dc431.svg'],['name'=>'HOKA','slug'=>'hoka','image'=>'83fb6.svg']];
foreach($referenceBrands as &$ref) foreach($brands as $brand) if(strcasecmp($brand['name'],$ref['name'])===0) $ref['slug']=$brand['slug'];
unset($ref);
foreach($referenceBrands as $brand): ?>
<a href="<?= esc(site_url('shop?brand='.rawurlencode($brand['slug'])),'attr') ?>"><img src="<?= $asset($brand['image']) ?>" alt="<?= esc($brand['name'],'attr') ?>" loading="lazy"></a>
<?php endforeach; ?>
<?php foreach($brands as $brand): if(in_array(strtolower($brand['name']),['the north face','new balance','hoka'],true))continue; ?>
<a href="<?= esc(site_url('shop?brand='.rawurlencode($brand['slug'])),'attr') ?>"><?php if(!empty($brand['logo_path'])): ?><img src="<?= esc(base_url(ltrim($brand['logo_path'],'/')),'attr') ?>" alt="<?= esc($brand['name'],'attr') ?>" loading="lazy"><?php else: ?><?= esc($brand['name']) ?><?php endif; ?></a>
<?php endforeach; ?>
</div>
<button type="button" data-brand-direction="1" aria-label="برندهای بعدی"><img src="<?= $asset('bc13b.svg') ?>" alt="" width="16" height="20"></button>
</div>
</section>
<section class="home-featured my-7 [&>.home-section-title]:mb-[9px]" aria-labelledby="featured-title">
<div class="home-section-title mb-[10px] flex h-[35px] items-center justify-between gap-2 ps-[5px] pe-[10px] [&>h2]:m-0 [&>h2]:text-[19px] [&>h2]:leading-[35px] [&>h2]:font-bold [&>a]:flex [&>a]:items-center [&>a]:gap-1 [&>a]:whitespace-nowrap [&>a]:text-[13px]"><h2 id="featured-title">محصولات منتخب</h2><a href="<?= site_url('shop') ?>">مشاهده همه <img src="<?= $asset('530b8.svg') ?>" alt="" width="12" height="16"></a></div>
<?= view('store/partials/home_cards',['products'=>$featured,'compact'=>true]) ?>
</section>
</main>
<?= view('store/partials/footer',['homeDesign'=>true]) ?>

