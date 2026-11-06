<?= view('admin/orders/partials/top',['title'=>'تنظیمات پیامک']) ?>
<main class="mx-auto max-w-4xl p-4 sm:p-6">
<a href="<?= site_url('admin') ?>" class="text-sm text-stone-500">بازگشت به پیشخوان</a>
<h1 class="my-5 text-2xl font-bold">تنظیمات سرویس پیامکی</h1>
<p class="mb-5 text-sm text-stone-600">تنظیم ارسال کد ورود با خط خدماتی اشتراکی ملی پیامک؛ رمز و نام کاربری تنها در فایل امن سرور نگهداری می‌شوند.</p>
<?php if(session()->getFlashdata('sms_error')): ?><p role="alert" class="mb-4 rounded-xl bg-red-50 p-4 text-sm text-red-700"><?= esc(session()->getFlashdata('sms_error')) ?></p><?php endif; ?>
<?php if(session()->getFlashdata('sms_success')): ?><p role="status" class="mb-4 rounded-xl bg-green-50 p-4 text-sm text-green-800"><?= esc(session()->getFlashdata('sms_success')) ?></p><?php endif; ?>
<section class="mb-5 rounded-2xl bg-white p-5">
<h2 class="mb-4 font-bold">وضعیت سرویس</h2>
<div class="grid gap-3 text-sm sm:grid-cols-2">
<p>وضعیت ارسال: <strong><?= $settings['enabled']?'فعال':'غیرفعال' ?></strong></p>
<p>اطلاعات اتصال: <strong><?= $settings['credentials_configured']?'تنظیم‌شده در سرور':'تنظیم‌نشده' ?></strong></p>
<p>آماده ارسال OTP: <strong><?= $settings['operational']?'بله':'خیر' ?></strong></p>
<p>شناسه الگو: <strong><?= esc((string)($settings['otp_body_id']??'ثبت نشده')) ?></strong></p>
</div>
</section>
<section class="rounded-2xl bg-white p-5">
<h2 class="mb-4 font-bold">پیکربندی ملی پیامک</h2>
<form method="post" action="<?= site_url('admin/settings/sms') ?>" class="grid gap-4">
<?= csrf_field() ?>
<label class="grid gap-2 text-sm">سرویس‌دهنده
<select name="provider" class="rounded-xl border border-stone-200 p-3">
<option value="melipayamak" <?= $settings['provider']==='melipayamak'?'selected':'' ?>>ملی پیامک (پترن خدماتی)</option>
<option value="webhook" <?= $settings['provider']==='webhook'?'selected':'' ?>>Webhook اختصاصی</option>
</select></label>
<label class="grid gap-2 text-sm">شناسه الگوی خدماتی OTP (Body ID)
<input type="number" min="1" max="2147483647" name="otp_body_id" value="<?= esc((string)($settings['otp_body_id']??''),'attr') ?>" placeholder="شناسه الگوی تأییدشده در پنل ملی پیامک" class="rounded-xl border border-stone-200 p-3"></label>
<p class="text-xs text-stone-600">در متن الگوی ملی پیامک فقط متغیر کد ورود قرار دهید. الگو ابتدا باید در پنل ملی پیامک تأیید شود.</p>
<label class="flex items-center gap-3 rounded-xl bg-[#f9f4ef] p-4 text-sm">
<input type="checkbox" name="enabled" value="1" <?= $settings['enabled']?'checked':'' ?>> فعال‌سازی ارسال پیامک ورود
</label>
<button class="rounded-xl bg-[#012f24] p-4 text-white" type="submit">ذخیره تنظیمات</button>
</form>
</section>
<section class="mt-5 rounded-2xl bg-white p-5">
<h2 class="mb-3 font-bold">اطلاعات محرمانه پنل</h2>
<p class="mb-3 text-sm text-stone-600">نام کاربری و رمز پنل را در فایل <code>.env</code> روی سرور وارد کنید. رمز در مرورگر نمایش داده یا از طریق دیتابیس ذخیره نمی‌شود.</p>
<div class="grid gap-2 rounded-xl bg-[#f9f4ef] p-4 font-mono text-xs" dir="ltr">
<code>GEREH_MELIPAYAMAK_USERNAME</code>
<code>GEREH_MELIPAYAMAK_PASSWORD</code>
</div>
<form class="mt-5" method="post" action="<?= site_url('admin/settings/sms/check') ?>">
<?= csrf_field() ?>
<button type="submit" class="rounded-xl border border-[#012f24] px-5 py-3 text-sm">بررسی اتصال و اعتبار پنل</button>
<p class="mt-2 text-xs text-stone-500">این تست فقط اعتبار پنل را بررسی می‌کند؛ پیامکی ارسال نمی‌شود.</p>
</form>
</section>

<section class="mt-5 rounded-2xl bg-white p-5">
<h2 class="mb-3 text-lg font-bold">الگوهای پیامک رویدادهای فروشگاه</h2>
<p class="mb-4 text-sm text-stone-600">برای هر رویداد یک الگوی خدماتی مجزا در پنل ملی پیامک ثبت و تأیید کنید. ترتیب متغیرهای زیر باید دقیقاً با ترتیب جایگاه‌های الگو در پنل یکسان باشد.</p>
<form method="post" action="<?= site_url('admin/settings/sms/templates') ?>" class="grid gap-4">
<?= csrf_field() ?>
<?php foreach($templates as $template): ?>
<div class="grid gap-3 rounded-xl border border-stone-200 p-4 sm:grid-cols-[minmax(0,1fr)_160px_95px] sm:items-center">
<div><strong class="text-sm"><?= esc($template['title']) ?></strong>
<p class="mt-1 text-xs text-stone-500" dir="ltr"><?= esc(implode(' ; ', $template['variables'])) ?></p>
</div>
<label class="grid gap-1 text-xs">شناسه الگو (Body ID)
<input class="w-full rounded-lg border border-stone-200 p-2" name="templates[<?= esc($template['event_key'],'attr') ?>][body_id]" type="number" min="1" max="2147483647" value="<?= esc((string)($template['body_id']??''),'attr') ?>">
</label>
<label class="flex gap-2 text-sm"><input name="templates[<?= esc($template['event_key'],'attr') ?>][enabled]" type="checkbox" value="1" <?= $template['enabled']?'checked':'' ?>> فعال</label>
</div>
<?php endforeach; ?>
<button class="rounded-xl bg-[#012f24] p-4 text-white" type="submit">ذخیره همه الگوها</button>
</form>
</section>
<section class="mt-5 rounded-2xl bg-white p-5">
<h2 class="mb-3 text-lg font-bold">گزارش ارسال و پیگیری</h2>
<p class="mb-4 text-xs text-stone-500">وضعیت «ارسال‌شده» به معنای پذیرش توسط پنل پیامک است، نه الزاماً تحویل به گوشی گیرنده. خطاهای نامشخص خودکار مجدداً ارسال نمی‌شوند.</p>
<div class="overflow-x-auto"><table class="w-full min-w-[580px] text-right text-xs"><thead><tr><th class="p-3">سفارش</th><th>رویداد</th><th>موبایل</th><th>وضعیت</th><th>شناسه پیگیری</th></tr></thead><tbody>
<?php foreach($recent as $job): ?><tr class="border-t border-stone-100">
<td class="p-3"><a href="<?= site_url('admin/orders/'.(int)$job['order_id']) ?>">#<?= (int)$job['order_id'] ?></a></td>
<td><?= esc($job['event_key']) ?></td>
<td dir="ltr"><?= esc(substr($job['mobile'],0,4).'***'.substr($job['mobile'],-2)) ?></td>
<td><?= esc($job['status']) ?></td>
<td dir="ltr"><?= esc($job['provider_receipt']??'—') ?></td>
</tr><?php endforeach; ?>
<?php if(!$recent): ?><tr><td colspan="5" class="p-5 text-center">هنوز پیامکی در صف ثبت نشده است.</td></tr><?php endif; ?>
</tbody></table></div>
</section>
</main></body></html>