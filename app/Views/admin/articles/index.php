<?= view('admin/orders/partials/top',['title'=>'مقالات']) ?>
<main class="mx-auto max-w-6xl p-5">
<header class="mb-6 flex items-center justify-between gap-4"><div><h1 class="text-2xl font-bold">مدیریت مقالات</h1><p class="mt-2 text-sm text-stone-500">محتوای سئویی؛ بدون نمایش در صفحه اصلی</p></div><a class="rounded-xl bg-[#012f24] px-5 py-3 text-sm text-white" href="<?= site_url('admin/articles/create') ?>">افزودن مقاله</a></header>
<?php if(session()->getFlashdata('article_success')): ?><p role="status" class="mb-5 rounded-xl bg-green-50 p-4 text-green-800"><?= esc(session()->getFlashdata('article_success')) ?></p><?php endif; ?>
<div class="overflow-x-auto rounded-xl bg-white p-4"><table class="w-full min-w-[600px] text-right text-sm"><thead><tr><th class="p-3">عنوان</th><th>آدرس</th><th>وضعیت</th><th>انتشار</th><th>عملیات</th></tr></thead><tbody>
<?php foreach($articles as $a): ?><tr class="border-b border-stone-100"><td class="p-3"><?= esc($a['title']) ?></td><td><?= esc($a['slug']) ?></td><td><?= esc($a['status']) ?></td><td><?= esc($a['published_at']??'—') ?></td><td><a class="underline" href="<?= site_url('admin/articles/'.(int)$a['id'].'/edit') ?>">ویرایش</a></td></tr><?php endforeach; ?>
<?php if(!$articles): ?><tr><td colspan="5" class="p-8 text-center">مقاله‌ای ثبت نشده است.</td></tr><?php endif; ?>
</tbody></table></div>
</main></body></html>