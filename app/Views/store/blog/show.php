<?= view('store/partials/header', [
'title'=>($article['meta_title']?:$article['title']).' | گره',
'description'=>$article['meta_description']?:($article['excerpt']?:$article['title']),
'canonical'=>site_url('blog/'.rawurlencode($article['slug']))
]) ?>
<main class="gereh-container mx-auto max-w-3xl px-4 py-8">
<nav class="gereh-muted mb-5 text-sm"><a href="<?= site_url('blog') ?>">مجله گره</a> / <?= esc($article['title']) ?></nav>
<article class="gereh-card rounded-2xl bg-white p-5 sm:p-8">
<h1 class="mb-3 text-2xl font-bold"><?= esc($article['title']) ?></h1>
<time class="gereh-muted text-xs" datetime="<?= esc($article['published_at'],'attr') ?>"><?= esc($article['published_at']) ?></time>
<?php if(!empty($article['cover_image'])): ?><img class="my-6 aspect-video w-full rounded-xl object-cover" src="<?= esc(base_url(ltrim($article['cover_image'],'/')),'attr') ?>" alt="<?= esc($article['title'],'attr') ?>"><?php endif; ?>
<?php if(!empty($article['excerpt'])): ?><p class="my-5 text-base font-semibold"><?= esc($article['excerpt']) ?></p><?php endif; ?>
<div class="prose max-w-none leading-9 whitespace-pre-line"><?= esc($article['content']) ?></div>
</article>
<div class="mt-5"><a class="text-sm underline" href="<?= site_url('shop') ?>">مشاهده کفش‌های فروشگاه</a></div>
</main>
<?= view('store/partials/footer') ?>