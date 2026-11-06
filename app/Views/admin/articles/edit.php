<?= view('admin/orders/partials/top',['title'=>'ویرایش مقاله']) ?>
<main class="mx-auto max-w-4xl p-5"><a href="<?= site_url('admin/articles') ?>">بازگشت به مقالات</a><h1 class="my-5 text-2xl font-bold"><?= $article?'ویرایش مقاله':'مقاله جدید' ?></h1>
<?php if(session()->getFlashdata('article_error')): ?><p role="alert"><?= esc(session()->getFlashdata('article_error')) ?></p><?php endif; ?>
<?php if(session()->getFlashdata('errors')): ?><div role="alert"><?php foreach(session()->getFlashdata('errors') as $error): ?><p><?= esc($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<form method="post" class="grid gap-4 rounded-xl bg-white p-6" action="<?= $article?site_url('admin/articles/'.(int)$article['id'].'/edit'):site_url('admin/articles/create') ?>">
<?= csrf_field() ?>
<label>عنوان<input class="mt-2 block w-full rounded-lg border p-3" name="title" required maxlength="240" value="<?= esc(old('title',$article['title']??''),'attr') ?>"></label>
<label>شناسه انگلیسی<input class="mt-2 block w-full rounded-lg border p-3" name="slug" required maxlength="240" pattern="[A-Za-z0-9_-]+" dir="ltr" value="<?= esc(old('slug',$article['slug']??''),'attr') ?>"></label>
<label>خلاصه<textarea class="mt-2 block w-full rounded-lg border p-3" name="excerpt" rows="3"><?= esc(old('excerpt',$article['excerpt']??'')) ?></textarea></label>
<label>متن مقاله<textarea class="mt-2 block w-full rounded-lg border p-3" name="content" rows="12" required><?= esc(old('content',$article['content']??'')) ?></textarea></label>
<label>عنوان SEO<input class="mt-2 block w-full rounded-lg border p-3" name="meta_title" maxlength="240" value="<?= esc(old('meta_title',$article['meta_title']??''),'attr') ?>"></label>
<label>توضیحات SEO<textarea class="mt-2 block w-full rounded-lg border p-3" name="meta_description" maxlength="320" rows="3"><?= esc(old('meta_description',$article['meta_description']??'')) ?></textarea></label>
<label>وضعیت<select name="status" class="mt-2 block w-full rounded-lg border p-3"><option value="draft">پیش‌نویس</option><option value="published" <?= old('status',$article['status']??'draft')==='published'?'selected':'' ?>>منتشرشده</option></select></label>
<button class="rounded-lg bg-[#012f24] p-4 text-white" type="submit">ذخیره</button>
</form></main></body></html>