<?= view('store/partials/header',['title'=>'مجله و راهنمای خرید کفش | گره','description'=>'مقالات راهنمای انتخاب سایز، نگهداری و خرید کفش']) ?>
<main class="gereh-container mx-auto max-w-5xl px-4 py-8">
<header class="mb-8"><a href="<?= site_url('shop') ?>" class="gereh-muted text-sm">بازگشت به فروشگاه</a><h1 class="gereh-heading mt-4 text-2xl font-bold">مجله گره</h1><p class="gereh-muted mt-2">راهنمای انتخاب، خرید و نگهداری کفش</p></header>
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
<?php foreach($articles as $article): ?>
<article class="gereh-card overflow-hidden rounded-2xl bg-white">
<?php if(!empty($article['cover_image'])): ?><a href="<?= site_url('blog/'.rawurlencode($article['slug'])) ?>"><img class="aspect-video w-full object-cover" src="<?= esc(base_url(ltrim($article['cover_image'],'/')),'attr') ?>" alt="<?= esc($article['title'],'attr') ?>" loading="lazy"></a><?php endif; ?>
<div class="p-5"><h2 class="mb-3 font-bold"><a href="<?= site_url('blog/'.rawurlencode($article['slug'])) ?>"><?= esc($article['title']) ?></a></h2><p class="gereh-muted text-sm"><?= esc($article['excerpt']??'') ?></p><a class="mt-4 inline-block text-sm underline" href="<?= site_url('blog/'.rawurlencode($article['slug'])) ?>">ادامه مطلب</a></div>
</article>
<?php endforeach; ?>
</div>
<?php if(!$articles): ?><div class="gereh-card p-10 text-center">هنوز مطلبی منتشر نشده است.</div><?php endif; ?>
<?php if($pages>1): ?><nav class="mt-6 flex justify-between" aria-label="صفحه‌بندی مطالب">
<?php if($page>1): ?><a href="<?= site_url('blog?page='.($page-1)) ?>">مطالب جدیدتر</a><?php else: ?><span></span><?php endif; ?>
<?php if($page<$pages): ?><a href="<?= site_url('blog?page='.($page+1)) ?>">مطالب قدیمی‌تر</a><?php endif; ?>
</nav><?php endif; ?>
</main><?= view('store/partials/footer') ?>