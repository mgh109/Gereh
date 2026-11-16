<?= view('store/partials/header', [
'title'=>($article['meta_title']?:$article['title']).' | گره',
'description'=>$article['meta_description']?:($article['excerpt']?:$article['title']),
'type'=>'article','image'=>!empty($article['cover_image'])?base_url(ltrim($article['cover_image'],'/')):null,
'canonical'=>site_url('blog/'.rawurlencode($article['slug']))
]) ?>
<script type="application/ld+json"><?= json_encode(['@context'=>'https://schema.org','@type'=>'BlogPosting','headline'=>$article['title'],'datePublished'=>date(DATE_ATOM,strtotime($article['published_at'])),'dateModified'=>date(DATE_ATOM,strtotime($article['updated_at']??$article['published_at'])),'mainEntityOfPage'=>site_url('blog/'.rawurlencode($article['slug'])),'publisher'=>['@type'=>'Organization','name'=>'گره','url'=>site_url('/')]]+(!empty($article['cover_image'])?['image'=>base_url(ltrim($article['cover_image'],'/'))]:[]),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?></script>
<main class="article-screen gereh-container mx-auto max-w-3xl px-4 py-8">
<nav class="gereh-muted mb-5 text-sm"><a href="<?= site_url('blog') ?>">مجله گره</a> / <?= esc($article['title']) ?></nav>
<article class="gereh-card rounded-2xl bg-white p-5 sm:p-8">
<h1 class="mb-3 text-2xl font-bold"><?= esc($article['title']) ?></h1>
<time class="gereh-muted text-xs" datetime="<?= esc($article['published_at'],'attr') ?>"><?= esc($article['published_at']) ?></time>
<?php if(!empty($article['cover_image'])): ?><img class="my-6 aspect-video w-full rounded-xl object-cover" src="<?= esc(base_url(ltrim($article['cover_image'],'/')),'attr') ?>" alt="<?= esc($article['title'],'attr') ?>"><?php endif; ?>
<?php if(!empty($article['excerpt'])): ?><p class="my-5 text-base font-semibold"><?= esc($article['excerpt']) ?></p><?php endif; ?>
<div class="prose max-w-none leading-9 whitespace-pre-line"><?= esc($article['content']) ?></div>
</article>
<div class="article-cta"><strong>انتخابت را در فروشگاه پیدا کن</strong><a class="gereh-button" href="<?= site_url('shop') ?>">مشاهده محصولات</a></div>
</main>
<?= view('store/partials/footer', ['hideBottomNav'=>true]) ?>
