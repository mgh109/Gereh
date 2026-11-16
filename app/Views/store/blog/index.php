<?= view('store/partials/header',['title'=>'مجله و راهنمای خرید کفش | گره','description'=>'مقالات راهنمای انتخاب سایز، نگهداری و خرید کفش']) ?>
<main class="magazine-screen gereh-container mx-auto max-w-5xl px-4 py-8">
<header class="mb-8"><a href="<?= site_url('shop') ?>" class="gereh-muted text-sm">بازگشت به فروشگاه</a><h1 class="gereh-heading mt-4 text-2xl font-bold">مجله گره</h1><p class="gereh-muted mt-2">راهنمای انتخاب، خرید و نگهداری کفش</p></header>
<?php $articleSearch=request()->getGet('q');$articleSearch=is_string($articleSearch)?$articleSearch:''; ?>
<form class="magazine-search" action="<?= site_url('blog') ?>" method="get" role="search"><label class="sr-only" for="article-search">جست‌وجوی مقالات</label><input id="article-search" name="q" type="search" placeholder="جست‌وجو در راهنماها و مقاله‌ها" value="<?= esc($articleSearch,'attr') ?>"><button type="submit">جست‌وجو</button></form>
<nav class="magazine-topics" aria-label="موضوع‌های مجله"><?php foreach([''=>'همه','خرید'=>'خرید','سایز'=>'سایز','نگهداری'=>'نگهداری'] as $term=>$label): ?><a href="<?= esc(site_url('blog').($term!==''?'?q='.rawurlencode($term):''),'attr') ?>" <?= $articleSearch===$term?'aria-current="page"':'' ?>><?= $label ?></a><?php endforeach; ?></nav>
<div class="magazine-grid grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
<?php foreach($articles as $article): ?>
<article class="gereh-card overflow-hidden rounded-2xl bg-white">
<?php if(!empty($article['cover_image'])): ?><a href="<?= site_url('blog/'.rawurlencode($article['slug'])) ?>"><img class="aspect-video w-full object-cover" src="<?= esc(base_url(ltrim($article['cover_image'],'/')),'attr') ?>" alt="<?= esc($article['title'],'attr') ?>" loading="lazy"></a><?php endif; ?>
<div class="p-5"><h2 class="mb-3 font-bold"><a href="<?= site_url('blog/'.rawurlencode($article['slug'])) ?>"><?= esc($article['title']) ?></a></h2><p class="gereh-muted text-sm"><?= esc($article['excerpt']??'') ?></p><a class="mt-4 inline-block text-sm underline" href="<?= site_url('blog/'.rawurlencode($article['slug'])) ?>">ادامه مطلب</a></div>
</article>
<?php endforeach; ?>
</div>
<?php if(!$articles): ?><div class="gereh-card p-10 text-center">هنوز مطلبی منتشر نشده است.</div><?php endif; ?>
<?php if($pages>1): ?><nav class="mt-6 flex justify-between" aria-label="صفحه‌بندی مطالب">
<?php if($page>1): ?><a href="<?= esc(site_url('blog?'.http_build_query(['page'=>$page-1,'q'=>$articleSearch])),'attr') ?>">مطالب جدیدتر</a><?php else: ?><span></span><?php endif; ?>
<?php if($page<$pages): ?><a href="<?= esc(site_url('blog?'.http_build_query(['page'=>$page+1,'q'=>$articleSearch])),'attr') ?>">مطالب قدیمی‌تر</a><?php endif; ?>
</nav><?php endif; ?>
<a class="gereh-button mt-8 flex w-full" href="<?= site_url('shop') ?>">مشاهده کتونی‌ها در فروشگاه</a>
</main><?= view('store/partials/footer', ['hideBottomNav'=>true]) ?>
