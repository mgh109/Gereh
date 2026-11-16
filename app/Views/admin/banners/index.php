<?= view('admin/partials/start',['title'=>'مدیریت صفحه اصلی','subtitle'=>'بنرها، دسته‌های ویژه و محتوای فروشگاه','active'=>'banners']) ?>
<main>
<?php foreach(['banner_success','banner_error'] as $notice): ?><?php if(session()->getFlashdata($notice)): ?><p role="status" class="admin-notice <?= $notice==='banner_error'?'admin-error':'admin-success' ?>"><?= esc(session()->getFlashdata($notice)) ?></p><?php endif; ?><?php endforeach; ?>
<div class="admin-form-actions"><a class="admin-button admin-button-soft" href="<?= site_url('/') ?>" target="_blank" rel="noopener">پیش‌نمایش فروشگاه</a><a class="admin-button" href="#new-banner">+ افزودن بنر</a></div>
<section class="admin-panel"><div class="admin-panel-heading"><h2>بنرهای فروشگاه</h2><span class="admin-muted"><?= count($banners) ?> بنر</span></div><div class="admin-banner-grid">
<?php foreach($banners as $banner): ?>
<article>
<?php if($banner['image_path']): ?><img class="admin-banner-preview" src="<?= esc(base_url(ltrim($banner['image_path'],'/')),'attr') ?>" alt="<?= esc($banner['title'],'attr') ?>"><?php else: ?><div class="admin-gallery-empty">تصویر بنر هنوز بارگذاری نشده است.</div><?php endif; ?>
<div class="admin-panel-heading" style="margin-top:12px"><h3><?= esc($banner['title']) ?></h3><span class="admin-badge <?= $banner['is_active']?'':'admin-badge-warning' ?>"><?= $banner['is_active']?'فعال':'غیرفعال' ?></span></div>
<p class="admin-muted"><?= esc(['hero'=>'بنر اصلی','women'=>'زنانه','men'=>'مردانه','promo'=>'تخفیف'][$banner['slot']]??$banner['slot']) ?> / <?= esc($banner['link_path']) ?></p>
<details class="admin-banner-card"><summary>ویرایش تصویر و لینک</summary>
<?= view('admin/banners/fields',['banner'=>$banner]) ?>
<form class="admin-form" method="post" enctype="multipart/form-data" action="<?= site_url('admin/banners/'.(int)$banner['id'].'/image') ?>"><?= csrf_field() ?><label>تصویر بنر<input type="file" name="image" accept="image/png,image/jpeg,image/webp" required></label><button type="submit">بارگذاری تصویر</button></form>
</details></article>
<?php endforeach; ?>
<?php if(!$banners): ?><p class="admin-muted">هنوز بنری ثبت نشده است؛ از فرم زیر بنر جدید اضافه کنید.</p><?php endif; ?>
</div></section>
<section class="admin-panel" id="new-banner"><h2>افزودن بنر جدید</h2><?= view('admin/banners/fields',['banner'=>['id'=>0,'slot'=>'hero','title'=>'','subtitle'=>'','link_path'=>'/shop','show_text'=>1,'is_active'=>1]]) ?></section>
<aside class="admin-notice"><strong>مجله در صفحه اصلی نمایش داده نمی‌شود</strong><p>مقالات را از بخش مجله و راهنماها مدیریت کنید.</p></aside>
</main><?= view('admin/partials/end') ?>
