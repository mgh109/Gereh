<?php
$compact = !empty($compact);
$faNumber = static fn ($value) => strtr(number_format((int) $value), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹',','=>'٬']);
?>
<?php if (!$products): ?>
<div class="grid <?= $compact ? 'min-h-[219px]' : 'min-h-[292px]' ?> place-items-center rounded-[10px] bg-gereh-cream/25 text-xs text-gereh-muted">هنوز محصولی برای نمایش ثبت نشده است.</div>
<?php else: ?>
<div class="home-products <?= $compact ? 'home-products-compact -ml-[13px] flex snap-x snap-mandatory gap-3 overflow-x-auto pl-[13px] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden min-[701px]:gap-6' : '-ml-1 grid grid-cols-2 gap-3 min-[701px]:gap-6' ?>">
<?php foreach ($products as $item):
    $url = site_url('product/' . rawurlencode($item['slug']));
    $sale = (int) ($item['compare_at_price_toman'] ?? 0) > (int) $item['price_toman'];
?>
<article class="home-product min-w-0 <?= $compact ? 'basis-[134px] shrink-0 snap-start min-[701px]:basis-[220px]' : '' ?>" data-product-id="<?= (int) $item['id'] ?>">
    <div class="relative overflow-hidden rounded-[10px] bg-gereh-cream <?= $compact ? 'h-[133px] min-[701px]:h-[219px]' : 'h-[204px] min-[701px]:h-[350px]' ?>">
        <a href="<?= esc($url, 'attr') ?>" class="grid h-full place-items-center text-xs text-gereh-muted">
            <?php if (!empty($item['image_url'])): ?><img class="block h-full w-full object-cover" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='480' height='480'%3E%3C/svg%3E" data-lazy-src="<?= esc($item['image_url'], 'attr') ?>" alt="<?= esc($item['image_alt'] ?? $item['name'], 'attr') ?>" loading="lazy" decoding="async" width="480" height="480"><noscript><img class="block h-full w-full object-cover" src="<?= esc($item['image_url'],'attr') ?>" alt="<?= esc($item['name'],'attr') ?>" loading="lazy"></noscript><?php else: ?><span>تصویر محصول</span><?php endif; ?>
        </a>
        <button class="absolute right-[9px] top-[9px] grid size-5 place-items-center border-0 bg-transparent p-0 aria-pressed:rounded-full aria-pressed:bg-[#e8c27d] aria-pressed:shadow-[0_0_0_4px_#e8c27d]" type="button" aria-label="علاقه‌مندی: <?= esc($item['name'], 'attr') ?>" aria-pressed="false" data-favorite data-id="<?= (int) $item['id'] ?>" data-name="<?= esc($item['name'], 'attr') ?>" data-url="<?= esc($url, 'attr') ?>" data-image="<?= esc($item['image_url'] ?? '', 'attr') ?>" data-price="<?= (int)$item['price_toman'] ?>"><img src="<?= base_url('assets/figma/c37a2.svg') ?>" alt="" width="20" height="20"></button>
        <a class="absolute right-[10px] top-[43px] grid h-[18px] w-[17px] place-items-center" href="<?= esc($url, 'attr') ?>" aria-label="انتخاب سایز و خرید <?= esc($item['name'], 'attr') ?>"><img src="<?= base_url('assets/figma/e7fef.svg') ?>" alt="" width="17" height="18"></a>
        <?php if ($sale): ?><span class="absolute left-2 top-[9px] grid h-[18px] w-[38px] place-items-center rounded-lg bg-gereh-700 text-[9px] text-white">حراج</span><?php endif; ?>
    </div>
    <div class="<?= $compact ? 'mt-px' : 'mt-[3px]' ?> flex h-[22px] items-center justify-between text-[#e9aa00] <?= $compact ? 'text-xs' : 'text-sm' ?>">
        <?php if (isset($item['rating']) && (int) ($item['review_count'] ?? 0) > 0): ?><span aria-label="امتیاز <?= esc((string) $item['rating'], 'attr') ?> از ۵"><?= str_repeat('★', min(5, max(0, (int) round($item['rating'])))) ?></span><small class="text-[10px] text-gereh-muted">(<?= $faNumber($item['review_count']) ?>)</small><?php endif; ?>
    </div>
    <h3 class="m-0 h-6 truncate font-normal leading-6 text-[#736c66] <?= $compact ? 'text-[11px]' : 'text-xs' ?>"><a href="<?= esc($url, 'attr') ?>"><?= esc($item['name']) ?></a></h3>
    <div class="h-4 text-[10px] leading-4 text-[#94887e]"><?php if ($sale): ?><del><?= $faNumber($item['compare_at_price_toman']) ?></del><?php endif; ?></div>
    <p class="m-0 whitespace-nowrap font-bold leading-[23px] <?= $compact ? 'text-[11px]' : 'text-[13px]' ?> <?= $sale ? 'text-[#cb3d2e]' : 'text-gereh-900' ?>"><?= $faNumber($item['price_toman']) ?> تومان</p>
</article>
<?php endforeach; ?>
</div>
<?php endif; ?>

