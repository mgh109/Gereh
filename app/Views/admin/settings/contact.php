<?= view('admin/partials/start', ['title'=>'اطلاعات تماس', 'subtitle'=>'اطلاعات نمایش داده‌شده در فوتر و ارتباط با ما', 'active'=>'contact']) ?>
<nav class="admin-tabs" aria-label="تنظیمات"><a class="is-active" href="<?= site_url('admin/settings/contact') ?>">اطلاعات تماس و اینماد</a><a href="<?= site_url('admin/settings/sms') ?>">پیامک و اعلان‌ها</a><a href="<?= site_url('admin/shipping') ?>">ارسال و تعرفه‌ها</a></nav>
<style>
.admin-contact-settings{width:100%;min-width:0}.admin-contact-settings .admin-panel{width:100%;margin:0}.admin-contact-settings .admin-fields{grid-template-columns:repeat(3,minmax(0,1fr));gap:20px}.admin-contact-settings label{font-size:13px}.admin-contact-settings input,.admin-contact-settings textarea{width:100%;max-width:none;box-sizing:border-box;font-size:14px}.admin-contact-settings input{min-height:48px}.admin-contact-settings textarea{min-height:120px;line-height:1.9}.admin-contact-settings fieldset{min-width:0;border:0;border-top:1px solid var(--admin-sand);padding:22px 0 0;margin:4px 0 0}.admin-contact-settings legend{font-size:16px;font-weight:700;padding:0 0 12px}.admin-contact-settings fieldset p{font-size:12px;line-height:1.9;color:var(--admin-muted)}.admin-contact-settings .admin-form-actions{justify-content:flex-start;margin:4px 0 0;align-items:center}.admin-contact-settings .admin-form-actions a{padding:12px 16px;border:1px solid var(--admin-sand);border-radius:10px}
@media(max-width:900px){.admin-contact-settings .admin-fields{grid-template-columns:minmax(0,1fr);gap:16px}}
</style>
<main class="admin-contact-settings">
<section class="admin-panel">
<h2>اطلاعات تماس فروشگاه</h2>
<?php if ($message = session()->getFlashdata('contact_success')): ?><p class="admin-notice admin-success" role="status"><?= esc($message) ?></p><?php endif; ?>
<?php if ($message = session()->getFlashdata('contact_error')): ?><p class="admin-notice admin-error" role="alert"><?= esc($message) ?></p><?php endif; ?>
<form method="post" action="<?= site_url('admin/settings/contact') ?>" class="admin-form">
<div class="admin-fields">
<?= csrf_field() ?>
<?php foreach (['phone'=>'تلفن ثابت با کد شهر', 'email'=>'پست الکترونیکی', 'postal_code'=>'کدپستی'] as $key=>$label): ?>
<label class="grid gap-2"><?= esc($label) ?><input required dir="ltr" type="<?= $key==='email'?'email':'text' ?>" name="<?= $key ?>" maxlength="<?= $key==='email'?254:($key==='phone'?11:10) ?>" <?= $key!=='email'?'inputmode="numeric"':'' ?> value="<?= esc(old($key, $contact[$key]), 'attr') ?>" class="rounded-xl border border-stone-200 p-3"></label>
<?php endforeach; ?>
<label class="admin-field-full">آدرس<textarea required name="address" rows="4" maxlength="1000" class="rounded-xl border border-stone-200 p-3"><?= esc(old('address', $contact['address'])) ?></textarea></label>
</div>
<fieldset class="grid gap-2"><legend>نماد اعتماد الکترونیکی</legend>
<?php $badgeCode = !empty($contact['enamad_link']) && !empty($contact['enamad_image_url']) ? '<a href="'.htmlspecialchars($contact['enamad_link'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'" target="_blank"><img src="'.htmlspecialchars($contact['enamad_image_url'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'" alt="نماد اعتماد الکترونیکی"></a>' : ''; ?>
<label for="enamad-code">کد نمایش نماد</label>
<textarea id="enamad-code" name="enamad_code" rows="4" dir="ltr" maxlength="10000" class="rounded-xl border border-stone-200 p-3" placeholder="کد نماد را از پنل اینماد اینجا وارد کنید"><?= esc(old('enamad_code', $badgeCode)) ?></textarea>
<p>پس از ذخیره، نشان در فوتر نمایش داده می‌شود. برای حذف نشان، این کادر را خالی بگذارید.</p>
</fieldset>
<div class="admin-form-actions"><button type="submit" class="admin-button">ذخیره اطلاعات تماس</button>
<a href="<?= site_url('contact') ?>" target="_blank" rel="noopener">مشاهده صفحه ارتباط با ما</a>
</div></form></section></main>
<?= view('admin/partials/end') ?>
