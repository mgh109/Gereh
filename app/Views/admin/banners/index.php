<?= view('admin/orders/partials/top',['title'=>'محتوای صفحه اصلی']) ?>
<main class="mx-auto max-w-5xl p-5">
<h1 class="mb-5 text-2xl font-bold">مدیریت بنرهای صفحه اصلی</h1>
<p class="mb-4 text-sm text-stone-500">چهار جایگاه اصلی: بنر اول، زنانه، مردانه و تخفیف. تصاویر واقعی را از فایل‌های مورد تأیید طراحی آپلود کنید.</p>
<?php foreach(['banner_success','banner_error'] as $notice): ?>
<?php if(session()->getFlashdata($notice)): ?><p role="status" class="mb-4 rounded-lg bg-white p-4"><?= esc(session()->getFlashdata($notice)) ?></p><?php endif; ?>
<?php endforeach; ?>
<section class="grid gap-4">
<?php foreach(array_merge($banners,[['id'=>0,'slot'=>'hero','title'=>'','subtitle'=>'','link_path'=>'/shop','image_path'=>null,'show_text'=>1,'is_active'=>1]]) as $banner): ?>
<div class="rounded-2xl bg-white p-5">
<h2 class="mb-4 font-bold"><?= $banner['id']?'بنر #'.(int)$banner['id']:'افزودن بنر جدید' ?></h2>
<form action="<?= site_url('admin/banners') ?>" method="post" class="grid gap-3 sm:grid-cols-2">
<?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$banner['id'] ?>">
<label class="grid gap-1 text-sm">جایگاه<select name="slot" class="rounded-lg border p-3">
<?php foreach(['hero'=>'بنر اصلی','women'=>'زنانه','men'=>'مردانه','promo'=>'تخفیف'] as $value=>$title): ?>
<option value="<?= $value ?>" <?= $banner['slot']===$value?'selected':'' ?>><?= $title ?></option>
<?php endforeach; ?></select></label>
<label class="grid gap-1 text-sm">عنوان<input class="rounded-lg border p-3" name="title" maxlength="220" required value="<?= esc($banner['title'],'attr') ?>"></label>
<label class="grid gap-1 text-sm">زیرعنوان<input class="rounded-lg border p-3" name="subtitle" maxlength="500" value="<?= esc($banner['subtitle']??'','attr') ?>"></label>
<label class="grid gap-1 text-sm">مسیر لینک<input class="rounded-lg border p-3" name="link_path" required value="<?= esc($banner['link_path'],'attr') ?>"></label>
<label class="text-sm"><input type="checkbox" name="show_text" value="1" <?= $banner['show_text']?'checked':'' ?>> نمایش متن روی تصویر</label>
<label class="text-sm"><input type="checkbox" name="is_active" value="1" <?= $banner['is_active']?'checked':'' ?>> فعال</label>
<button class="rounded-lg bg-[#012f24] p-3 text-white sm:col-span-2" type="submit">ذخیره اطلاعات</button>
</form>
<?php if($banner['id']): ?>
<div class="mt-4 border-t pt-4">
<?php if($banner['image_path']): ?><img class="mb-3 h-32 max-w-xs rounded-lg object-cover" src="<?= esc(base_url(ltrim($banner['image_path'],'/')),'attr') ?>" alt="<?= esc($banner['title'],'attr') ?>"><?php endif; ?>
<form class="flex flex-wrap gap-2" method="post" enctype="multipart/form-data" action="<?= site_url('admin/banners/'.(int)$banner['id'].'/image') ?>"><?= csrf_field() ?>
<input type="file" name="image" accept="image/png,image/jpeg,image/webp" required><button class="rounded-lg border px-4 py-2 text-sm" type="submit">بارگذاری تصویر</button></form>
</div><?php endif; ?>
</div>
<?php endforeach; ?>
</section>
</main></body></html>