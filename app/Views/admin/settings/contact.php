<?= view('admin/partials/start', ['title'=>'اطلاعات تماس', 'subtitle'=>'اطلاعات نمایش داده‌شده در فوتر و ارتباط با ما', 'active'=>'contact']) ?>
<main class="admin-legacy admin-settings-page">
<section class="admin-panel">
<h1>اطلاعات تماس فروشگاه</h1>
<?php if ($message = session()->getFlashdata('contact_success')): ?><p role="status"><?= esc($message) ?></p><?php endif; ?>
<?php if ($message = session()->getFlashdata('contact_error')): ?><p role="alert"><?= esc($message) ?></p><?php endif; ?>
<form method="post" action="<?= site_url('admin/settings/contact') ?>" class="grid gap-4">
<?= csrf_field() ?>
<?php foreach (['phone'=>'تلفن ثابت با کد شهر', 'email'=>'پست الکترونیکی', 'postal_code'=>'کدپستی'] as $key=>$label): ?>
<label class="grid gap-2"><?= esc($label) ?><input required dir="ltr" type="<?= $key==='email'?'email':'text' ?>" name="<?= $key ?>" maxlength="<?= $key==='email'?254:($key==='phone'?11:10) ?>" <?= $key!=='email'?'inputmode="numeric"':'' ?> value="<?= esc(old($key, $contact[$key]), 'attr') ?>" class="rounded-xl border border-stone-200 p-3"></label>
<?php endforeach; ?>
<label class="grid gap-2">آدرس<textarea required name="address" rows="4" maxlength="1000" class="rounded-xl border border-stone-200 p-3"><?= esc(old('address', $contact['address'])) ?></textarea></label>
<button type="submit" class="admin-button">ذخیره اطلاعات تماس</button>
<a href="<?= site_url('contact') ?>" target="_blank" rel="noopener">مشاهده صفحه ارتباط با ما</a>
</form></section></main>
<?= view('admin/partials/end') ?>
