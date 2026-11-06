<?= view('admin/orders/partials/top',['title'=>'روش‌های ارسال']) ?>
<main class="mx-auto max-w-5xl p-4 sm:p-6">
<h1 class="mb-3 text-2xl font-bold">مدیریت روش‌های ارسال</h1>
<p class="mb-6 text-sm text-stone-600">هزینه‌های ثبت‌شده در این بخش مبنای محاسبه قطعی سمت سرور است. پیش از فعال‌سازی روش سریع یا تحویل حضوری، قرارداد و محدوده ارائه خدمات را تأیید کنید.</p>
<?php if(session()->getFlashdata('shipping_error')): ?><p role="alert" class="mb-4 rounded-xl bg-red-50 p-4 text-red-700"><?= esc(session()->getFlashdata('shipping_error')) ?></p><?php endif; ?>
<?php if(session()->getFlashdata('shipping_success')): ?><p role="status" class="mb-4 rounded-xl bg-green-50 p-4 text-green-800"><?= esc(session()->getFlashdata('shipping_success')) ?></p><?php endif; ?>
<div class="grid gap-5">
<?php foreach(array_merge($methods,[[
  'id'=>0,'code'=>'','title'=>'','description'=>'','type'=>'flat',
  'base_fee_toman'=>0,'free_over_toman'=>null,'province_filter'=>null,
  'estimated_days_min'=>null,'estimated_days_max'=>null,
  'sort_order'=>100,'is_active'=>0
]]) as $method): ?>
<form method="post" action="<?= site_url('admin/shipping/'.(int)$method['id']) ?>" class="grid gap-4 rounded-2xl bg-white p-5 md:grid-cols-2">
<?= csrf_field() ?>
<div class="md:col-span-2"><h2 class="text-lg font-bold"><?= $method['id']?esc($method['title']):'افزودن روش ارسال دلخواه' ?></h2><span class="text-xs text-stone-500" dir="ltr"><?= esc($method['code']) ?></span></div>
<?php if(!$method['id']): ?><label class="grid gap-1 text-sm md:col-span-2">شناسه انگلیسی یکتا
<input name="code" maxlength="30" pattern="[a-z][a-z0-9_-]{1,29}" required placeholder="my_courier" class="rounded-xl border border-stone-200 p-3" dir="ltr"></label><?php endif; ?>
<label class="grid gap-1 text-sm">عنوان روش ارسال<input name="title" maxlength="120" required value="<?= esc($method['title'],'attr') ?>" class="rounded-xl border border-stone-200 p-3"></label>
<label class="grid gap-1 text-sm">نوع تعرفه<select name="type" class="rounded-xl border border-stone-200 p-3"><option value="flat" <?= $method['type']==='flat'?'selected':'' ?>>هزینه ثابت / رایگان مشروط</option><option value="pickup" <?= $method['type']==='pickup'?'selected':'' ?>>تحویل حضوری رایگان</option></select></label>
<label class="grid gap-1 text-sm md:col-span-2">توضیحات<textarea name="description" maxlength="300" rows="2" class="rounded-xl border border-stone-200 p-3"><?= esc($method['description']??'') ?></textarea></label>
<label class="grid gap-1 text-sm">هزینه پایه (تومان)<input name="base_fee_toman" type="number" min="0" max="1000000000" required value="<?= (int)$method['base_fee_toman'] ?>" class="rounded-xl border border-stone-200 p-3"></label>
<label class="grid gap-1 text-sm">ارسال رایگان از مبلغ (تومان)<input name="free_over_toman" type="number" min="1" max="100000000000" value="<?= esc((string)($method['free_over_toman']??''),'attr') ?>" placeholder="خالی = بدون تخفیف ارسال" class="rounded-xl border border-stone-200 p-3"></label>
<label class="grid gap-1 text-sm md:col-span-2">استان‌های مجاز (با ویرگول جدا کنید)<input name="province_filter" maxlength="250" value="<?= esc((string)($method['province_filter']??''),'attr') ?>" placeholder="خالی = تمام استان‌ها؛ مثال: تهران,البرز" class="rounded-xl border border-stone-200 p-3"></label>
<label class="grid gap-1 text-sm">حداقل روز کاری<input name="estimated_days_min" type="number" min="0" max="90" value="<?= esc((string)($method['estimated_days_min']??''),'attr') ?>" class="rounded-xl border border-stone-200 p-3"></label>
<label class="grid gap-1 text-sm">حداکثر روز کاری<input name="estimated_days_max" type="number" min="0" max="90" value="<?= esc((string)($method['estimated_days_max']??''),'attr') ?>" class="rounded-xl border border-stone-200 p-3"></label>
<label class="grid gap-1 text-sm">اولویت نمایش<input name="sort_order" type="number" min="0" max="10000" value="<?= (int)$method['sort_order'] ?>" class="rounded-xl border border-stone-200 p-3"></label>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" <?= $method['is_active']?'checked':'' ?>> روش ارسال فعال باشد</label>
<button type="submit" class="rounded-xl bg-[#012f24] p-3 text-white md:col-span-2">ذخیره روش ارسال</button>
</form>
<?php endforeach; ?>
</div>
</main></body></html>